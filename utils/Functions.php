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

    public static function duration_error(mixed $duration, int $min, int $max): string {
        if ($duration === '' || $duration === null) {
            return 'Duration is required.';
        }

        if (filter_var($duration, FILTER_VALIDATE_INT) === false) {
            return 'Duration must be an integer.';
        }

        $duration = (int)$duration;

        if ($duration < $min || $duration > $max) {
            return "Duration must be between $min and $max days.";
        }

        return '';
    }
}