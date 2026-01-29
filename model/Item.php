<?php

require_once "framework/Model.php";
require_once "model/ItemPicture.php";
require_once "model/Bid.php";
require_once "utils/AppTime.php";
require_once "utils/Uploader.php";
require_once "utils/Functions.php";

class Item extends Model
{
    public function __construct(
        private string  $title,
        private ?string $description,
        private ?int    $owner,
        private ?string $created_at,
        private ?int    $duration_days,
        private ?string $owner_pseudo = null,
        private ?string $end_at = null,
        private ?string $time_left = null,
        private ?int    $has_bids = null,
        private ?int    $is_direct_sale = null,
        private ?int    $is_auction = null,
        private ?int    $id = null,
        private ?string $buy_now_price = null,
        private ?string $starting_bid = null,

        private ?string $seller_pseudo = null,
        private ?string $seller_picture_path = null,
        private ?int    $bid_count = null,
        private ?string $max_bid = null,

        private ?int    $has_buy_now = null,
        private ?int    $buy_now_reached = null,
        private ?int    $not_purchased_direct_sale = null,

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

    public function get_title(): string {
        return $this->title;
    }

    public function get_description(): string {
        return $this->description;
    }

    public function get_owner(): int {
        return $this->owner;
    }

    public function get_owner_pseudo(): ?string
    {
        return $this->owner_pseudo;
    }

    public function get_created_at(): string
    {
        return $this->created_at;
    }

    public function get_duration_days(): int {
        return $this->duration_days;
    }

    public function get_end_at(): ?string
    {
        return $this->end_at;
    }

    public function get_time_left(): ?string
    {
        return $this->time_left;
    }

    public function get_has_bids(): ?int
    {
        return $this->has_bids;
    }

    public function get_is_direct_sale(): ?int
    {
        return $this->is_direct_sale;
    }

    public function get_is_auction(): ?int
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

    public function get_bid_count(): ?int {
        return $this->bid_count;
    }

    public function get_seller_pseudo(): ?string {
        return $this->seller_pseudo;
    }
    public function get_seller_picture_path(): ?string {
        return $this->seller_picture_path;
    }

    public function get_has_buy_now(): ?int {
        return $this->has_buy_now;
    }

    public function get_buy_now_reached(): ?int {
        return $this->buy_now_reached;
    }
    public function get_not_purchased_direct_sale(): ?int {
        return $this->not_purchased_direct_sale;
    }


    public static function get_by_id(?int $id): Item|false {
        $query = self::execute("SELECT * FROM v_items_status WHERE id = :id", array("id" => $id));
        $row = $query->fetch();
        return $row ? new Item(
            title: $row['title'],
            description: $row['description'],
            owner: $row['owner'],
            created_at: $row['created_at'],
            duration_days: $row['duration_days'],
            owner_pseudo: null,
            end_at: $row['end_at'],
            time_left: null,
            has_bids: $row['has_bids'],
            is_direct_sale: $row['is_direct_sale'],
            is_auction: $row['is_auction'],
            id: $row['id'],
            buy_now_price: $row['buy_now_price'],
            starting_bid: $row['starting_bid'],
            max_bid: null,
            thumbnail: null,
            bidder: null,
            highest_bidder: null
        ) : false;
    }

    public function add_pictures(string $upload_images, string $name): void {
        $item = $this->get_id();
        ItemPicture::add_pictures($upload_images, $name, $item);
    }

    public function get_item_pictures(): array {
        return ItemPicture::get_item_pictures($this->get_id());
    }

    public static function get_time_left_string(int $secs_left): string {
        $d_left = (int)($secs_left / 86400);
        $h_left = (int)(($secs_left % 86400) / 3600);
        $m_left = (int)(($secs_left % 3600) / 60);
        $s_left = ($secs_left % 60);

        if ($d_left > 0)
            return $d_left . "d " . $h_left . "h left";
        else if ($h_left > 0)
            return $h_left . "h " . $m_left . "m left";
        else if ($m_left > 0)
            return $m_left . "m" . $s_left . "s left";
        else if ($s_left > 0)
            return $s_left . "s left";
        else
            return "closed";
    }

    public static function get_participating_items(User $user): array {
        $sql = "SELECT DISTINCT vis.*, GREATEST(TIMESTAMPDIFF(SECOND, :now, vis.end_at), 0) as secs_left
                FROM v_items_status vis
                    JOIN bids b ON b.item = vis.id
                WHERE vis.not_purchased_direct_sale
                    OR (vis.is_auction AND vis.end_at > :now
                        AND (NOT vis.has_buy_now OR NOT vis.buy_now_reached))
                    AND  b.owner = :id
                ORDER BY vis.end_at ASC ";

        return self::fetchItems($sql, $user);
    }

    public static function get_other_available_items(User $user): array {
        $sql = "SELECT DISTINCT vis.*, GREATEST(TIMESTAMPDIFF(SECOND, :now, vis.end_at), 0) as secs_left
            FROM v_items_status vis
            WHERE vis.owner != :id
              AND vis.id NOT IN (
                    SELECT item
                    FROM bids
                    WHERE owner = :id
              )
              AND (
                    (vis.is_direct_sale = 1 AND vis.not_purchased_direct_sale = 1 AND vis.end_at > :now)
                 OR (vis.is_auction = 1 AND vis.end_at > :now
                     AND (NOT vis.has_buy_now OR NOT vis.buy_now_reached))
              )
            ORDER BY vis.end_at ASC";

        return self::fetchItems($sql, $user);
    }
    public static function get_all_available_items_for_guest(): array {
        $sql = "SELECT DISTINCT vis.*, GREATEST(TIMESTAMPDIFF(SECOND, :now, vis.end_at), 0) as secs_left
                FROM v_items_status vis
                WHERE vis.end_at > :now
                    AND (vis.not_purchased_direct_sale
                        OR (vis.is_auction
                            AND (NOT vis.has_buy_now OR NOT vis.buy_now_reached)))
                ORDER BY vis.end_at ASC ";

        return self::fetchItems($sql, null);
    }

    private static function fetchItems(string $sql, ?User $user): array {
        $user_id = $user ? $user->get_id() : null;
        $query = self::execute($sql, ["id" => $user_id, "now" => AppTime::get_current_datetime()]
        );
        $row = $query->fetchAll();
        $items = [];
        foreach ($row as $item) {
            $items[] = new Item(
                title: $item['title'],
                description: $item['description'],
                owner: $item['owner'],
                created_at: $item['created_at'],
                duration_days: $item['duration_days'],
                owner_pseudo: User::get_pseudo_by_owner_id($item['owner']),
                end_at: $item['end_at'],
                time_left: self::get_time_left_string($item['secs_left']),
                has_bids: $item['has_bids'],
                is_direct_sale: $item['is_direct_sale'],
                is_auction: $item['is_auction'],
                id: $item['id'],
                buy_now_price: $item['buy_now_price'],
                starting_bid: $item['starting_bid'],
                max_bid: $item['max_bid'],
                thumbnail: ItemPicture::get_item_thumbnail($item['id']),
                bidder: $user_id ? User::am_i_bidder($user->get_id(), $item['id']) : null,
                highest_bidder: $user_id ? User::am_i_highest_bidder($user->get_id(), $item['id']) : null,
            );
        }
        return $items;
    }

    public static function get_open_item_with_seller(int $id): Item|false {
        $sql = "SELECT vis.*, 
                   u.pseudo AS seller_pseudo, 
                   u.picture_path AS seller_picture_path
            FROM v_items_status vis
            JOIN users u ON u.id = vis.owner
            WHERE vis.id = :id";
        $query = self::execute($sql, ["id" => $id]);
        $row = $query->fetch();
        return $row ? new Item(
            title: $row["title"],
            description: $row["description"] ?? "",
            owner: (int)$row["owner"],
            created_at: $row["created_at"],
            duration_days: (int)$row["duration_days"],
            id: (int)$row["id"],
            buy_now_price: $row["buy_now_price"],
            starting_bid: $row["starting_bid"],
            end_at: $row["end_at"],
            bid_count: isset($row["bid_count"]) ? (int)$row["bid_count"] : null,
            max_bid: $row["max_bid"],
            seller_pseudo: $row["seller_pseudo"],
            seller_picture_path: $row["seller_picture_path"],
            is_direct_sale: (int)$row["is_direct_sale"],
            is_auction: (int)$row["is_auction"],
            has_buy_now: (int)$row["has_buy_now"],
            has_bids: (int)$row["has_bids"],
            buy_now_reached: (int)$row["buy_now_reached"],
            not_purchased_direct_sale: (int)$row["not_purchased_direct_sale"]

        ) : false;
    }

    public static function get_pictures_by_item(int $item_id): array {
        $sql = "SELECT item, priority, picture_path
            FROM item_pictures
            WHERE item = :id
            ORDER BY priority";
        $query = self::execute($sql, ["id" => $item_id]);
        $rows = $query->fetchAll();

        $pics = [];
        foreach ($rows as $r) {
            $pics[] = new ItemPicture(
                item: (int)$r["item"],
                priority: (int)$r["priority"],
                picture_path: $r["picture_path"]
            );
        }
        return $pics;
    }

    public function get_pictures(): array {
        return self::get_pictures_by_item($this->id);
    }

    public function get_bids(): array {
        return Bid::get_by_item($this->id);
    }

    public function is_open(string $now): bool {
        if ($this->is_direct_sale === 1 && $this->not_purchased_direct_sale === 1) {
            return $this->end_at !== null && $this->end_at > $now;
        }

        // Si l'item est une enchère active
        if ($this->is_auction === 1 && $this->end_at !== null) {
            $buyNowBlocks = ($this->has_buy_now === 1 && $this->buy_now_reached === 1);
            return ($this->end_at > $now) && !$buyNowBlocks;
        }

        return false;
    }

    public static function get_my_active_items(User $user): array {
        $sql = "SELECT DISTINCT vis.*, GREATEST(TIMESTAMPDIFF(SECOND, :now, vis.end_at), 0) as secs_left
            FROM v_items_status vis
            WHERE vis.owner = :id
              AND (
                    (vis.is_direct_sale = 1 AND vis.not_purchased_direct_sale = 1 AND vis.end_at > :now)
                 OR (vis.is_auction = 1 AND vis.end_at > :now
                     AND (NOT vis.has_buy_now OR NOT vis.buy_now_reached))
                  )
            ORDER BY vis.end_at DESC";
        return self::fetchItems($sql, $user);
    }

    public static function get_my_closed_unsold_items(User $user): array {
        $sql = "SELECT DISTINCT vis.*, GREATEST(TIMESTAMPDIFF(SECOND, :now, vis.end_at), 0) as secs_left
            FROM v_items_status vis
            WHERE vis.owner = :id
              AND (
                    (vis.is_direct_sale = 1 AND vis.not_purchased_direct_sale = 1 AND vis.end_at <= :now)
                 OR (vis.is_auction = 1 AND vis.end_at <= :now AND vis.has_bids = 0)
                  )
            ORDER BY vis.end_at DESC";
        return self::fetchItems($sql, $user);
    }

    public static function get_my_sold_items(User $user): array {
        $sql = "SELECT DISTINCT vis.*, 0 as secs_left
            FROM v_items_status vis
            WHERE vis.owner = :id
              AND (
                    (vis.is_direct_sale = 1 AND vis.not_purchased_direct_sale = 0)
                 OR (vis.is_auction = 1 AND vis.has_bids = 1
                     AND (vis.end_at <= :now OR vis.buy_now_reached = 1))
                  )
            ORDER BY vis.end_at DESC";
        return self::fetchItems($sql, $user);
    }

    public static function validations(string $title, string $description, float $starting_bid, float $instant_purchase_price, float $direct_sale_price): array {
        $errors = [];

        $title_min = Configuration::get('TITLE_MIN_LENGTH');
        $title_max = Configuration::get('TITLE_MAX_LENGTH');
        $desc_min = Configuration::get('DESCR_MIN_LENGTH');

        if ($title_error = Functions::title_length($title, $title_min, $title_max)) {
            $errors['title'] = $title_error;
        }
        if ($desc_error = Functions::description_length($description, $desc_min)) {
            $errors['description'] = $desc_error;
        }
        if ($price_error = Functions::auction_or_direct($starting_bid, $instant_purchase_price, $direct_sale_price)) {
            $errors['price'] = $price_error;
        }
        if ($auction_error = Functions::auction_error($starting_bid, $instant_purchase_price)) {
            $errors['auction'] = $auction_error;
        }
        return $errors;
    }

    public static function insert_into_db(int $user_id, string $title, string $description, int $duration, float $starting_bid, float $instant_or_direct): int {
        $sql = "INSERT INTO items (title, description, duration_days, starting_bid, buy_now_price, owner, created_at) 
                        VALUES (:title, :description, :duration, :starting_bid, :buy_now_price, :user_id, NOW())" ;
        self::execute($sql, ['title' => $title, 'description' => $description, 'duration' => $duration, 'starting_bid' => $starting_bid,
            'buy_now_price' => $instant_or_direct, 'user_id' => $user_id]);
        return self::lastInsertId();
    }
}