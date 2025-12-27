<?php

require_once "framework/Model.php";
require_once "model/ItemPicture.php";
require_once "utils/AppTime.php";

class Item extends Model
{
    public function __construct(
        private string  $title,
        private string  $description,
        private int     $owner,
        private string  $owner_pseudo,
        private string  $created_at,
        private int     $duration_days,
        private string  $end_at,
        private int     $has_bids,
        private int     $is_direct_sale,
        private int     $is_auction,
        private ?int    $id = null,
        private ?string $buy_now_price = null,
        private ?string $starting_bid = null,
        private ?string $max_bid = null,
        private ?string $thumbnail = null,
        private ?bool   $bidder = null,
        private ?bool   $highest_bidder = null,
    )
    {
    }

    public function get_id(): ?int
    {
        return $this->id;
    }

    public function get_title(): string
    {
        return $this->title;
    }

    public function get_description(): string
    {
        return $this->description;
    }

    public function get_owner(): int
    {
        return $this->owner;
    }

    public function get_owner_pseudo(): string
    {
        return $this->owner_pseudo;
    }

    public function get_created_at(): string
    {
        return $this->created_at;
    }

    public function get_duration_days(): int
    {
        return $this->duration_days;
    }

    public function get_end_at(): string
    {
        return $this->end_at;
    }

    public function get_has_bids(): int
    {
        return $this->has_bids;
    }

    public function get_is_direct_sale(): int
    {
        return $this->is_direct_sale;
    }

    public function get_is_auction(): int
    {
        return $this->is_auction;
    }

    public function get_buy_now_price(): ?string {
        return $this->buy_now_price;
    }

    public function get_starting_bid(): ?string {
        return $this->starting_bid;
    }

    public function get_max_bid(): ?string
    {
        return $this->max_bid;
    }

    public function get_thumbnail(): ?string
    {
        return $this->thumbnail;
    }

    public function get_bidder(): ?bool
    {
        return $this->bidder;
    }

    public function get_highest_bidder(): ?bool
    {
        return $this->highest_bidder;
    }

    public static function get_by_id(?int $id): Item|false {
        $query = self::execute("SELECT * FROM items WHERE id = :id", array("id" => $id));
        $row = $query->fetch();
        return $row ? new Item(title: $row['title'], description: $row['description'], owner: $row['owner'],
            created_at: $row['created_at'], duration_days: $row['duration_days'], id: $row['id'],
            buy_now_price: $row['buy_now_price'], starting_bid: $row['starting_bid']) : false;
    }

    public function get_item_pictures(): array {
        $sql = "SELECT *
                FROM item_pictures
                WHERE item = :id 
                ORDER BY priority ASC ";
        $query = self::execute($sql, ['id' => $this->get_id()]);
        $rows = $query->fetchAll();

        $pictures = [];
        foreach($rows as $picture)
            $pictures[] = new ItemPicture(
                item: $picture['item'],
                priority: $picture['priority'],
                picture_path: $picture['picture_path'],
            );
        return $pictures;
    }

    public static function get_participating_items(User $user): array {
        $sql = "SELECT DISTINCT vis.* 
                FROM v_items_status vis
                    JOIN bids b ON b.item = vis.id
                    JOIN users u ON b.owner = u.id
                WHERE b.owner = :id
                AND (vis.end_at > :now OR vis.buy_now_reached = 0) 
                ORDER BY vis.end_at ASC ";

        return self::fetchItems($sql, $user);
    }

    public static function get_other_available_items(User $user): array {
        $sql = "SELECT DISTINCT vis.*
                FROM v_items_status vis
                    JOIN users u ON vis.owner = u.id
                WHERE vis.owner != :id
                AND vis.id NOT IN (SELECT item
                                    FROM bids
                                    WHERE owner = :id)
                AND (vis.end_at > :now OR vis.buy_now_reached = 0)
                ORDER BY vis.end_at ASC ";

        return self::fetchItems($sql, $user);
    }

    private static function fetchItems(string $sql, User $user): array {
        $query = self::execute($sql, ["id" => $user->get_id(), "now" => AppTime::get_current_datetime()]
        );
        $row = $query->fetchAll();
        $items = [];
        foreach ($row as $item) {
            $items[] = new Item(
                title: $item['title'],
                description: $item['description'],
                owner: $item['owner'],
                owner_pseudo: User::get_pseudo_by_owner_id($item['owner']),
                created_at: $item['created_at'],
                duration_days: $item['duration_days'],
                end_at: $item['end_at'],  /* à changer en temps restant */
                has_bids: $item['has_bids'],
                is_direct_sale: $item['is_direct_sale'],
                is_auction: $item['is_auction'],
                id: $item['id'],
                buy_now_price: $item['buy_now_price'],
                starting_bid: $item['starting_bid'],
                max_bid: $item['max_bid'],
                thumbnail: ItemPicture::get_item_thumbnail($item['id']),
                bidder: User::am_i_bidder($user->get_id(), $item['id']),
                highest_bidder: User::am_i_highest_bidder($user->get_id(), $item['id']),
            );
        }
        return $items;
    }
}