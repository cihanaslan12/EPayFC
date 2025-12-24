<?php

require_once "framework/Model.php";

class ItemPicture {
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
}