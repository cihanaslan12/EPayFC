<?php

require_once "framework/Model.php";

class Item extends Model {
    public function __construct(
        private string $title,
        private string $description,
        private int $owner,
        private string $created_at,
        private int $duration_days,
        private ?int $id = null,
        private ?string $buy_now_price = null,
        private ?string $starting_bid = null,
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

    public function get_created_at(): string {
        return $this->created_at;
    }

    public function get_duration_days(): int {
        return $this->duration_days;
    }

    public function get_buy_now_price(): ?string {
        return $this->buy_now_price;
    }

    public function get_starting_bid(): ?string {
        return $this->starting_bid;
    }

    public static function get_by_id(?int $id): Item|false {
        $query = self::execute("SELECT * FROM items WHERE id = :id", array("id" => $id));
        $row = $query->fetch();
        return $row ? new Item(title: $row['title'], description: $row['description'], owner: $row['owner'],
            created_at: $row['created_at'], duration_days: $row['duration_days'], id: $row['id'],
            buy_now_price: $row['buy_now_price'], starting_bid: $row['starting_bid']) : false;
    }

    public static function get_participating_items(?int $user_id): array {
        $sql = "SELECT DISTINCT i.id, i.title, i.description, i.owner, i.created_at, i.buy_now_price, i.duration_days, i.starting_bid 
                    FROM items i JOIN users u ON i.owner = u.id 
                    WHERE u.id = :id ";
        $query = self::execute($sql, ["id" => $user_id]
        );
        $row = $query->fetchAll();
        $my_participations = [];
        foreach ($row as $item) {
            $my_participations[] = new Item(
                title: $item['title'],
                description: $item['description'],
                owner: $item['owner'],
                created_at: $item['created_at'],
                duration_days: $item['duration_days'],
                id: $item['id'],
                buy_now_price: $item['buy_now_price'],
                starting_bid: $item['starting_bid']
            );
        }
        return $my_participations;
    }

    public static function get_other_available_items(?int $user_id): array {
        $sql = "SELECT DISTINCT i.id, i.title, i.description, i.owner, i.created_at, i.buy_now_price, i.duration_days, i.starting_bid 
                    FROM items i JOIN users u on i.owner = u.id
                    WHERE u.id != :id ";
        $query = self::execute($sql, ["id" => $user_id]
        );
        $row = $query->fetchAll();
        $other_items = [];
        foreach ($row as $item) {
            $other_items[] = new Item(
                title: $item['title'],
                description: $item['description'],
                owner: $item['owner'],
                created_at: $item['created_at'],
                duration_days: $item['duration_days'],
                id: $item['id'],
                buy_now_price: $item['buy_now_price'],
                starting_bid: $item['starting_bid']
            );
        }
        return $other_items;
    }
}