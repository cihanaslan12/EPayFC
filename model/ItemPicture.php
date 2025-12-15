<?php

class ItemPicture {
    public function __construct(
        private int $priority,
        private string $picture_path
    ) {}

    public function get_priority(): int {
        return $this->priority;
    }

    public function get_picture_path(): string {
        return $this->picture_path;
    }

    public function get_thumbnail_path(): string {
        $path = $this->picture_path;
        $dot = strrpos($path, '.');
        if ($dot === false) return $path;
        return substr($path, 0, $dot) . "_thumbnail" . substr($path, $dot);
    }
}
