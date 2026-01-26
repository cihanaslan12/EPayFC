<?php

require_once "framework/Configuration.php";

class Functions {
    public static function title_lenght(string $title, int $min, int $max): string {
        $title_lenght = strlen($title);
        if (empty($title_lenght)) {
            return 'Title is required !';
        } else if ($title_lenght < $min) {
            return 'Title must be at least 3 characters !';
        } else if ($title_lenght > $max) {
            return 'Max 255 characters !';
        } else {
            return '';
        }
    }

    public static function description_lenght(string $description, int $min): string {
        if (!empty($description) && strlen($description) < $min) {
            return 'Empty or min 3 characters !';
        }
        return '';
    }

    public static function auction_or_direct(float $starting_bid, float $instant_purchased_price, float $direct_sale_price): string {
        if(($starting_bid > 0 || $instant_purchased_price > 0) && $direct_sale_price > 0) {
            return 'Cannot create both auction and direct sale.';
        }
        return '';
    }
}