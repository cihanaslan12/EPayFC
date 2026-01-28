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
}