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
}