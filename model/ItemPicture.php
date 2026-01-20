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
                FROM items_pictures
                WHERE item = :id ";
        $query = self::execute($sql, ['id' => $item_id]);
        $prior_max = $query->fetch();
        if ($prior_max == null)
            return 0;
        return $prior_max;
    }

    public static function add_pictures(array $upload_images, int $item_id): void {
        $config = parse_ini_file(__DIR__.'/../config/dev.ini');

        foreach ($upload_images['tmp_name'] as $upload_image) {
            $original_image = Uploader::create_image_from($upload_image);

            $original_width = imagesx($original_image);
            $original_height = imagesy($original_image);

            $new_image = imagecreatetruecolor($config['MAX_IMG_WIDTH'], $config['MAX_IMG_HEIGHT']);
            $new_thumbnail = imagecreatetruecolor($config['MAX_THUMB_WIDTH'], $config['MAX_THUMB_HEIGHT']);

            imagecopyresampled($new_image, $original_image, 0, 0, 0, 0, $config['MAX_IMG_WIDTH'], $config['MAX_IMG_HEIGHT'], $original_width, $original_height);
            imagecopyresampled($new_thumbnail, $original_image, 0, 0, 0, 0, $config['MAX_THUMB_WIDTH'], $config['MAX_THUMB_HEIGHT'], $original_width, $original_height);

            imagejpeg($new_image, self::create_new_picture_name($item_id));
            imagejpeg($new_thumbnail, self::create_new_thumbnail_name($item_id));

            imagedestroy($original_image);
            imagedestroy($new_image);
            imagedestroy($new_thumbnail);
        }
    }

}