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

    public static function get_participating_items(User $user): array {
        $sql = "SELECT * 
                FROM bids b 
                    JOIN v_items_status vis ON b.item = vis.id
                    JOIN users u ON b.owner = u.id
                WHERE u.id = :id
                AND (vis.end_at > 0 OR vis.buy_now_reached = 0) 
                ORDER BY vis.end_at DESC ";
        $query = self::execute($sql, ["id" => $user->get_id()]
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

    public static function get_other_available_items(User $user): array {
        $sql = "SELECT DISTINCT vis.*
                FROM bids b
                    JOIN v_items_status vis ON b.item = vis.id
                    JOIN users u ON b.owner = u.id
                WHERE b.owner != :id
                AND vis.owner != :id
                AND (vis.end_at > 0 OR vis.buy_now_reached = 0)
                ORDER BY vis.end_at DESC ";
        $query = self::execute($sql, ["id" => $user->get_id()]
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

    public static function get_open_item_with_seller(int $id): array|false {
        $sql = "SELECT vis.*, 
                   u.pseudo AS seller_pseudo, 
                   u.picture_path AS seller_picture_path
            FROM v_items_status vis
            JOIN users u ON u.id = vis.owner
            WHERE vis.id = :id";
        $query = self::execute($sql, ["id" => $id]);
        $row = $query->fetch();
        return $row ?: false;
    }

}