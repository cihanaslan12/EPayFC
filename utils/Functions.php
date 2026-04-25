<?php

require_once "framework/Configuration.php";

class Functions {
    public static function title_length(string $title, int $min, int $max): string {
        $title_length = strlen($title);
        if (empty($title_length)) {
            return 'Title is required.';
        } else if ($title_length < $min || $title_length > $max) {
            return 'Title length must be between 3 and 255 characters.';
        } else {
            return '';
        }
    }

    public static function description_length(string $description, int $min): string {
        if (!empty($description) && strlen($description) < $min) {
            return 'Empty or min 3 characters !';
        }
        return '';
    }

    public static function auction_or_direct(float $starting_bid, float $instant_purchase_price, float $direct_sale_price): string {
        if(($starting_bid > 0 || $instant_purchase_price > 0) && $direct_sale_price > 0) {
            return 'Cannot create both auction and direct sale.';
        } else if (empty($starting_bid) && empty($direct_sale_price)) {
            return 'Starting Bid or Sale Price must be provided';
        }
        return '';
    }

    public static function auction_error(float $starting_bid, float $instant_purchase_price): string {
        if ($starting_bid > 0 && $instant_purchase_price > 0) {
            if ($starting_bid >= $instant_purchase_price) {
                return 'Buy now price must be greater then the starting bid.';
            }
        }
        return '';
    }

    /**
     * Permet d'encoder un string au format base64url, c'est-à-dire un format base64 dans lequel
     * les caractères '+' et '/' sont remplacés respectivement par '-' et '_', ce qui permet d'utiliser le
     * résultat dans une URL.
     * @param string $data Le string à encoder.
     * @return string Le string encodé.
     */
    private static function base64url_encode(string $data) : string {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    /**
     * Permet de décoder un string encodé au format base64url.
     * @param string $data Le string à décoder.
     * @return string Le string décodé.
     */
    private static function base64url_decode(string $data) : string {
        return base64_decode(strtr($data, '-_', '+/') . str_repeat('=', 3 - (3 + strlen($data)) % 4));
    }

    /**
     * Permet d'encoder une structure de donnée (par exemple un tableau associatif ou un objet) au format base64url.
     * @param mixed $data La structure de données à encoder.
     * @return string Le string résultant de l'encodage.
     */
    public static function url_safe_encode(mixed $data) : string {
        return self::base64url_encode(gzcompress(json_encode($data), 9));
    }

    /**
     * Permet de décoder un string au format base64url.
     * @param string Le string à décoder.
     * @return mixed $data La structure de données décodée.
     */
    public static function url_safe_decode(string $data) : mixed {
        return json_decode(@gzuncompress(self::base64url_decode($data)), true, 512, JSON_OBJECT_AS_ARRAY);
    }
}