<?php

use Decimal\Decimal;

require_once "framework/Model.php";

class Item extends Model {
    public function __construct(
        private string $title,
        private string $description,
        private int $owner,
        private DateTime $created_at,
        private int $duration_days,
        private ?int $id = null,
        private ?Decimal $buy_now_price = null,
        private ?Decimal $starting_bid = null,
    ) {
    }

    public function get_id(): ?int {
        return $this->id;
    }

    public function get_title(): string {
        return $this->title;
    }

    public function get_description(): string {
        return $this->description;
    }

    public function get_owner(): int {
        return $this->owner;
    }

    public function get_created_at(): DateTime {
        return $this->created_at;
    }

    public function get_duration_days(): int {
        return $this->duration_days;
    }

    public function get_buy_now_price(): ?Decimal {
        return $this->buy_now_price;
    }

    public function get_starting_bid(): ?Decimal {
        return $this->starting_bid;
    }

    public static function get_by_id(?int $id): Item|false {
        $query = self::execute("SELECT * FROM items WHERE id = :id", array("id" => $id));
        $row = $query->fetch();
        return $row ? new Item(title: $row['title'], description: $row['description'], owner: $row['owner'],
            created_at: $row['created_at'], duration_days: $row['duration_days'], id: $row['id'],
            buy_now_price: $row['buy_now_price'], starting_bid: $row['starting_bid']) : false;
    }

    public static function get_participating_items(?int $id): array {
        $sql = "SELECT * FROM items i JOINS users u WHERE i.id = u.id AND i.id = :id ";
        $query = self::execute($sql, array("id" => $id));
        $row = $query->fetchAll();
        $my_participations= [];
        foreach ($row as $item) {
            $my_participations[] = new Item();
        }
        return $my_participations;
    }
}