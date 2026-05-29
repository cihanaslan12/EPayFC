<?php

require_once "framework/Model.php";

class Category extends Model
{
    public const MAX_CATEGORIES_PER_ITEM = 3;
    public function __construct(
        private ?int $id,
        private string $name,
        private int $priority,
        private ?int $item_count = null
    ) {}

    public function get_id(): ?int
    {
        return $this->id;
    }

    public function get_name(): string
    {
        return $this->name;
    }

    public function get_priority(): int
    {
        return $this->priority;
    }

    public function get_item_count(): ?int
    {
        return $this->item_count;
    }

    private static function from_row(array $row): Category
    {
        return new Category(
            id: (int)$row['id'],
            name: $row['name'],
            priority: (int)$row['priority'],
            item_count: isset($row['item_count']) ? (int)$row['item_count'] : null
        );
    }

    public static function get_all_by_priority(): array
    {
        $sql = "SELECT c.id, c.name, c.priority,
                       COUNT(ic.item) AS item_count
                FROM categories c
                LEFT JOIN item_categories ic ON ic.category = c.id
                GROUP BY c.id, c.name, c.priority
                ORDER BY c.priority ASC";

        $query = self::execute($sql, []);
        $rows = $query->fetchAll();

        $categories = [];
        foreach ($rows as $row) {
            $categories[] = self::from_row($row);
        }
        return $categories;
    }

    public static function get_by_item_alphabetically(int $item_id): array
    {
        $sql = "SELECT c.id, c.name, c.priority
                FROM categories c
                JOIN item_categories ic ON ic.category = c.id
                WHERE ic.item = :item_id
                ORDER BY c.name ASC";

        $query = self::execute($sql, ['item_id' => $item_id]);
        $rows = $query->fetchAll();

        $categories = [];
        foreach ($rows as $row) {
            $categories[] = self::from_row($row);
        }
        return $categories;
    }

    public static function get_ids_for_item(int $item_id): array
    {
        $sql = "SELECT category
                FROM item_categories
                WHERE item = :item_id";

        $query = self::execute($sql, ['item_id' => $item_id]);
        $rows = $query->fetchAll();

        return array_map(fn($row) => (int)$row['category'], $rows);
    }

    public static function set_for_item(int $item_id, array $category_ids): void
    {
        self::delete_all_for_item($item_id);

        $category_ids = self::normalize_ids($category_ids);
        foreach ($category_ids as $category_id) {
            $sql = "INSERT INTO item_categories (item, category)
                    VALUES (:item_id, :category_id)";
            self::execute($sql, [
                'item_id' => $item_id,
                'category_id' => $category_id
            ]);
        }
    }

    public static function delete_all_for_item(int $item_id): void
    {
        $sql = "DELETE FROM item_categories WHERE item = :item_id";
        self::execute($sql, ['item_id' => $item_id]);
    }

    public static function normalize_ids(mixed $category_ids): array
    {
        if (!is_array($category_ids)) {
            return [];
        }

        $ids = array_map('intval', $category_ids);
        $ids = array_filter($ids, fn(int $id) => $id > 0);
        return array_values(array_unique($ids));
    }

    public static function validate_item_categories(array $category_ids): ?string
    {
        if (count($category_ids) > self::MAX_CATEGORIES_PER_ITEM) {
            return "You can select up to " . self::MAX_CATEGORIES_PER_ITEM . " categories.";
        }

        if (!self::ids_exist($category_ids)) {
            return "Invalid category selected.";
        }

        return null;
    }

    private static function ids_exist(array $category_ids): bool
    {
        if (empty($category_ids)) {
            return true;
        }

        $placeholders = [];
        $params = [];

        foreach ($category_ids as $index => $category_id) {
            $key = "id" . $index;
            $placeholders[] = ":" . $key;
            $params[$key] = $category_id;
        }

        $sql = "SELECT COUNT(*) AS count_categories
            FROM categories
            WHERE id IN (" . implode(",", $placeholders) . ")";

        $query = self::execute($sql, $params);
        $row = $query->fetch();

        return (int)$row['count_categories'] === count($category_ids);
    }

    public static function get_by_id(int $id): Category|false
    {
        $sql = "SELECT c.id, c.name, c.priority,
                   COUNT(ic.item) AS item_count
            FROM categories c
            LEFT JOIN item_categories ic ON ic.category = c.id
            WHERE c.id = :id
            GROUP BY c.id, c.name, c.priority";

        $query = self::execute($sql, ['id' => $id]);
        $row = $query->fetch();

        return $row ? self::from_row($row) : false;
    }

    public static function validate_name(string $name, ?int $excluded_id = null): array
    {
        $errors = [];
        $name = trim($name);

        $min = (int) Configuration::get('CATEGORY_NAME_MIN_LENGTH', '3');
        $max = (int) Configuration::get('CATEGORY_NAME_MAX_LENGTH', '25');

        if ($name === '') {
            $errors[] = "Category name is required.";
        } elseif (strlen($name) < $min || strlen($name) > $max) {
            $errors[] = "Category name must contain between $min and $max characters.";
        }

        if ($name !== '' && self::name_exists($name, $excluded_id)) {
            $errors[] = "This category already exists.";
        }

        return $errors;
    }

    private static function name_exists(string $name, ?int $excluded_id = null): bool
    {
        $sql = "SELECT id FROM categories WHERE name = :name";
        $params = ['name' => trim($name)];

        if ($excluded_id !== null) {
            $sql .= " AND id != :id";
            $params['id'] = $excluded_id;
        }

        $query = self::execute($sql, $params);
        return (bool)$query->fetch();
    }

    public static function create(string $name): int
    {
        $priority = self::get_next_priority();

        $sql = "INSERT INTO categories (name, priority)
            VALUES (:name, :priority)";

        self::execute($sql, [
            'name' => trim($name),
            'priority' => $priority
        ]);

        return self::lastInsertId();
    }

    public static function update_name(int $id, string $name): void
    {
        $sql = "UPDATE categories
            SET name = :name
            WHERE id = :id";

        self::execute($sql, [
            'id' => $id,
            'name' => trim($name)
        ]);
    }

    public static function delete_by_id(int $id): void
    {
        $category = self::get_by_id($id);

        if (!$category) {
            return;
        }

        if ((int)$category->get_item_count() > 0) {
            throw new Exception("Cannot delete a category that contains items.");
        }

        $sql = "DELETE FROM categories WHERE id = :id";
        self::execute($sql, ['id' => $id]);
    }

    public static function move_up(int $id): void
    {
        self::swap_with_neighbor($id, 'up');
    }

    public static function move_down(int $id): void
    {
        self::swap_with_neighbor($id, 'down');
    }

    private static function get_next_priority(): int
    {
        $query = self::execute(
            "SELECT COALESCE(MAX(priority), 0) + 1 AS next_priority FROM categories",
            []
        );

        $row = $query->fetch();
        return (int)$row['next_priority'];
    }

    private static function swap_with_neighbor(int $id, string $direction): void
    {
        $category = self::get_by_id($id);

        if (!$category) {
            return;
        }

        if ($direction === 'up') {
            $sql = "SELECT id, priority
                FROM categories
                WHERE priority < :priority
                ORDER BY priority DESC
                LIMIT 1";
        } else {
            $sql = "SELECT id, priority
                FROM categories
                WHERE priority > :priority
                ORDER BY priority ASC
                LIMIT 1";
        }

        $query = self::execute($sql, ['priority' => $category->get_priority()]);
        $neighbor = $query->fetch();

        if (!$neighbor) {
            return;
        }

        $current_id = (int)$category->get_id();
        $current_priority = (int)$category->get_priority();
        $neighbor_id = (int)$neighbor['id'];
        $neighbor_priority = (int)$neighbor['priority'];

        self::execute("UPDATE categories SET priority = -1 WHERE id = :id", [
            'id' => $current_id
        ]);

        self::execute("UPDATE categories SET priority = :priority WHERE id = :id", [
            'id' => $neighbor_id,
            'priority' => $current_priority
        ]);

        self::execute("UPDATE categories SET priority = :priority WHERE id = :id", [
            'id' => $current_id,
            'priority' => $neighbor_priority
        ]);
    }
}