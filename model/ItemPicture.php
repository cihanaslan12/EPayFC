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
        $prior_max = (int)$query->fetch();
        if ($prior_max == null)
            return 0;
        return $prior_max;
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
}