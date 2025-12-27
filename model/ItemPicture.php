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

    public static function get_item_thumbnail(int $int): ?string {
        $sql = "SELECT picture_path
                FROM item_pictures
                WHERE item = :id
                    AND priority = 1 ";
        $query = self::execute($sql, ['id' => $int]);
        $picture_path = $query->fetch();

        if($picture_path['picture_path'] !== "" && !empty($picture_path['picture_path'])) {
            $p_p_explode = explode(".", $picture_path['picture_path']);
            return $p_p_explode[0] . '_thumbnail.' . $p_p_explode[1];
        } else {
            return null;
        }
    }
}