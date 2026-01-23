<?php

require_once "framework/Model.php";

class ItemPicture extends Model {
    public function __construct(
        private int $item,
        private int $priority,
        private string $picture_path,
    ) {
    }

    public function get_item(): int
    {
        return $this->item;
    }

    public function get_priority(): int
    {
        return $this->priority;
    }

    public function get_picture_path(): string
    {
        return $this->picture_path;
    }

    public function get_picture_thumbnail(): string {
        $picture_path = $this->get_picture_path();
        $thumbnail = explode('.', $picture_path);
        return $thumbnail[0] . '_thumbnail.' . $thumbnail[1];
    }

    public static function get_item_thumbnail(int $int): ?string {
        $sql = "SELECT picture_path
                FROM item_pictures
                WHERE item = :id
                    AND priority = 1 ";
        $query = self::execute($sql, ['id' => $int]);
        $picture_path = $query->fetch();

        if ($picture_path)
            if($picture_path['picture_path'] !== "" && !empty($picture_path['picture_path'])) {
                $p_p_explode = explode(".", $picture_path['picture_path']);
                return $p_p_explode[0] . '_thumbnail.' . $p_p_explode[1];
            } else {
                return null;
            }
        return false;
    }

    public static function create_new_picture_name(int $item_id): string {
        $pictures_priority = self::get_pictures_priority_max($item_id) + 1;

        $save_dir = "uploads/items/$item_id/";
        if (!file_exists($save_dir)) {
            mkdir($save_dir, 0777, true);
        }

        $uniq_id = uniqid("{$item_id}_{$pictures_priority}_", true);

        return $save_dir . $uniq_id . 'jpg';
    }

    public static function create_new_thumbnail_name(int $item_id): string {
        $name = explode('.', self::create_new_picture_name($item_id));
        return $name[0] . '_thumbnail.jpg';
    }

    public static function get_pictures_priority_max(int $item_id): int {
        $sql = "SELECT MAX(priority)
                FROM item_pictures
                WHERE item = :id ";
        $query = self::execute($sql, ['id' => $item_id]);
        $res = $query->fetch();
        $prior_max = $res[0];
        if ($prior_max == null)
            return 0;
        return (int)$prior_max;
    }

    public static function add_pictures(string $upload_image, string $name, int $item_id): void {
        $config = parse_ini_file(__DIR__.'/../config/dev.ini');

        $original_image = Uploader::create_image_from($upload_image, $name);
        if(!$original_image) {
            return;
        }

        $original_width = imagesx($original_image);
        $original_height = imagesy($original_image);

        $max_img_width = $config['MAX_IMG_WIDTH'];
        $max_img_height = $config['MAX_IMG_HEIGHT'];
        $max_thumb_width = $config['MAX_THUMB_WIDTH'];
        $max_thumb_height = $config['MAX_THUMB_HEIGHT'];

        $calculate_img_ratio = min($max_img_width / $original_width, $max_img_height / $original_height);
        $calculate_thumb_ratio = min($max_thumb_width / $original_width, $max_thumb_height / $original_height);

        $img_ratio = ($calculate_img_ratio < 1) ? $calculate_img_ratio : 1;
        $thumb_ratio = ($calculate_thumb_ratio < 1) ? $calculate_thumb_ratio : 1;

        $new_img_width = round($original_width * $img_ratio);
        $new_img_height = round($original_height * $img_ratio);
        $new_thumb_width = round($original_width * $thumb_ratio);
        $new_thumb_height = round($original_height * $thumb_ratio);

        $new_image = imagecreatetruecolor($new_img_width, $new_img_height);
        $new_thumbnail = imagecreatetruecolor($new_thumb_width, $new_thumb_height);

        imagecopyresampled($new_image, $original_image, 0, 0, 0, 0, $new_img_width, $new_img_height, $original_width, $original_height);
        imagecopyresampled($new_thumbnail, $original_image, 0, 0, 0, 0, $new_thumb_width, $new_thumb_height, $original_width, $original_height);

        $img_path = self::create_new_picture_name($item_id);
        $thumb_path = self::create_new_thumbnail_name($item_id);

        imagejpeg($new_image, $img_path, 90);
        imagejpeg($new_thumbnail, $thumb_path, 75);

        self::insert_new_images($img_path, $item_id);

        imagedestroy($original_image);
        imagedestroy($new_image);
        imagedestroy($new_thumbnail);
    }

    public static function insert_new_images(string $path, int $item_id): void {
        $pos = self::get_pictures_priority_max($item_id) + 1;
        $sql = "INSERT INTO item_pictures (item, priority, picture_path) 
                    VALUES (:item_id, :priority, :path) ";
        self::execute($sql, ['item_id' => $item_id, 'priority' => $pos, 'path' => $path]);
    }

    public static function get_item_pictures(int $item_id): array {
        $sql = "SELECT *
                FROM item_pictures
                WHERE item = :item_id 
                ORDER BY priority ASC ";
        $query = self::execute($sql, ['item_id' => $item_id]);
        $rows = $query->fetchAll();

        $pictures = [];
        foreach($rows as $picture)
            $pictures[] = new ItemPicture(
                item: $picture['item'],
                priority: $picture['priority'],
                picture_path: $picture['picture_path'],
            );
        return $pictures;
    }

    public static function get_by_item_and_priority(int $item_id, int $priority): ItemPicture|false {
        $sql = "SELECT * FROM item_pictures WHERE item = :item_id AND priority = :priority ";
        $query = self::execute($sql, ['item_id' => $item_id, 'priority' => $priority]);
        $row = $query->fetch();
        return $row ? new ItemPicture(
            item: $row['item'],
            priority: $row['priority'],
            picture_path: $row['picture_path']
        ) : false;
    }

    public function priority_minus(): void {
        $item = $this->get_item();
        $current_priority = $this->get_priority();
        if($current_priority > 1) {
            $previous_priority = $current_priority - 1;

            $sql = "UPDATE item_pictures SET priority = -1 WHERE priority = :current AND item = :item ";
            self::execute($sql, ['current' => $current_priority, 'item' => $item]);

            $sql = "UPDATE item_pictures SET priority = :current WHERE priority = :previous AND item = :item  ";
            self::execute($sql, ['current' => $current_priority, 'previous' => $previous_priority, 'item' => $item]);

            $sql = "UPDATE item_pictures SET priority = :previous WHERE priority = -1 AND item = :item  ";
            self::execute($sql, ['previous' => $previous_priority, 'item' => $item]);
        }
    }

    public function priority_plus(): void {
        $item = $this->get_item();
        $current_priority = $this->get_priority();
        if ($current_priority < self::get_pictures_priority_max($item)) {
            $next_priority = $current_priority + 1;

            $sql = "UPDATE item_pictures SET priority = -1 WHERE priority = :current AND item = :item  ";
            self::execute($sql, ['current' => $current_priority, 'item' => $item]);

            $sql = "UPDATE item_pictures SET priority = :current WHERE priority = :next AND item = :item  ";
            self::execute($sql, ['current' => $current_priority, 'next' => $next_priority, 'item' => $item]);

            $sql = "UPDATE item_pictures SET priority = :next WHERE priority = -1 AND item = :item  ";
            self::execute($sql, ['next' => $next_priority, 'item' => $item]);
        }
    }

    public function delete_picture(): void {

    }
}