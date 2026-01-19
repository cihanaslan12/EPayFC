<?php

class Uploader {
    public static function check_extension(string $file_name): bool {
        $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $valid_ext = ['jpg', 'png', 'gif', 'webp'];
        return in_array($ext, $valid_ext);
    }

    public static function check_size(int $file_size): bool {
        $dev_ini = parse_ini_file(__DIR__ . "../config/dev.ini");
        $max_size = $dev_ini['UPLOAD_MAX_FILESIZE'];
        return $file_size <= $max_size;
    }

    public static function get_extension(string $file): string {
        return strtolower(pathinfo($file, PATHINFO_EXTENSION));
    }

    public static function create_image_from(string $extension): GdImage|false {
        switch($extension) {
            case 'jpg':
                $image = imagecreatefromjpeg($extension);
                break;
            case 'png':
                $image = imagecreatefrompng($extension);
                break;
            case 'gif':
                $image = imagecreatefromgif($extension);
                break;
            case 'webp':
                $image = imagecreatefromwebp($extension);
                break;
            default:
                return false;
        }
        return $image;
    }

}