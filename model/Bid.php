<?php

require_once "framework/Model.php";

class Bid extends Model {
    public function __construct(
        private int $owner_id,
        private string $owner_pseudo,
        private int $item_id,
        private string $created_at,
        private string $amount
    ) {}

    public function get_owner_pseudo(): string { return $this->owner_pseudo; }
    public function get_created_at(): string { return $this->created_at; }
    public function get_amount(): string { return $this->amount; }
    public function get_owner_id(): int { return $this->owner_id; }


    public static function get_by_item(int $item_id): array {
        $sql = "SELECT b.owner, u.pseudo, b.item, b.created_at, b.amount
                FROM bids b
                JOIN users u ON u.id = b.owner
                WHERE b.item = :item_id
                ORDER BY b.created_at DESC";
        $query = self::execute($sql, ["item_id" => $item_id]);
        $rows = $query->fetchAll();

        $bids = [];
        foreach ($rows as $r) {
            $bids[] = new Bid(
                owner_id: (int)$r["owner"],
                owner_pseudo: $r["pseudo"],
                item_id: (int)$r["item"],
                created_at: $r["created_at"],
                amount: $r["amount"]
            );
        }
        return $bids;
    }

    public static function get_highest_for_item(int $item_id): ?Bid {
        $sql = "SELECT b.owner, u.pseudo, b.item, b.created_at, b.amount
            FROM bids b
            JOIN users u ON u.id = b.owner
            WHERE b.item = :item_id
            ORDER BY b.amount DESC, b.created_at ASC
            LIMIT 1";
        $q = self::execute($sql, ["item_id" => $item_id]);
        $r = $q->fetch();
        return $r ? new Bid((int)$r["owner"], $r["pseudo"], (int)$r["item"], $r["created_at"], $r["amount"]) : null;
    }

    public static function place_bid(User $user, Item $item, string $amount, string $now, array &$errors): bool {
        if (!$item->is_open($now)) {
            $errors["bid"] = "This item is closed.";
            return false;
        }
        if ($user->get_id() === $item->get_owner()) {
            $errors["bid"] = "You cannot bid on your own item.";
            return false;
        }
        if ($item->get_is_auction() !== 1) {
            $errors["bid"] = "Bidding is not allowed on this item.";
            return false;
        }

        if (!is_numeric($amount)) {
            $errors["amount"] = "Invalid amount.";
            return false;
        }
        $amountF = (float)$amount;
        if ($amountF <= 0) {
            $errors["amount"] = "Amount must be > 0.";
            return false;
        }

        $min = null;
        if ($item->get_max_bid() !== null) {
            $min = (float)$item->get_max_bid();
        } else if ($item->get_starting_bid() !== null) {
            $min = (float)$item->get_starting_bid();
        } else {
            $errors["bid"] = "This item has no starting bid.";
            return false;
        }

        if ($item->get_max_bid() !== null) {
            if ($amountF <= $min) {
                $errors["amount"] = "Your bid must be greater than " . number_format($min, 2, '.', '');
                return false;
            }
        } else {
            if ($amountF < $min) {
                $errors["amount"] = "Your bid must be at least " . number_format($min, 2, '.', '');
                return false;
            }
        }

        $sql = "INSERT INTO bids(owner, item, created_at, amount)
            VALUES(:owner, :item, :created_at, :amount)";
        self::execute($sql, [
            "owner" => $user->get_id(),
            "item" => $item->get_id(),
            "created_at" => $now,
            "amount" => number_format($amountF, 2, '.', '')
        ]);

        return true;
    }

    public static function buy_now(User $user, Item $item, string $now, array &$errors): bool {
        if ($item->get_has_buy_now() !== 1 || $item->get_buy_now_price() === null) {
            $errors["buy_now"] = "Buy now is not available for this item.";
            return false;
        }

        if (!$item->is_open($now)) {
            $errors["buy_now"] = "This item is closed.";
            return false;
        }

        if ($user->get_id() === $item->get_owner()) {
            $errors["buy_now"] = "You cannot buy your own item.";
            return false;
        }

        $amountF = (float)$item->get_buy_now_price();
        if ($amountF <= 0) {
            $errors["buy_now"] = "Invalid buy now price.";
            return false;
        }

        $amount = number_format($amountF, 2, '.', '');


        if ($item->get_is_auction() === 1) {
            $tmp = [];
            if (self::place_bid($user, $item, $amount, $now, $tmp)) {
                return true;
            }
            $errors["buy_now"] = $tmp["amount"] ?? $tmp["bid"] ?? "Buy now failed.";
            return false;
        }

        if ($item->get_is_direct_sale() !== 1) {
            $errors["buy_now"] = "Buy now is not available for this item.";
            return false;
        }

        $sql = "INSERT INTO bids(owner, item, created_at, amount)
            VALUES(:owner, :item, :created_at, :amount)";
        self::execute($sql, [
            "owner" => $user->get_id(),
            "item" => $item->get_id(),
            "created_at" => $now,
            "amount" => $amount
        ]);

        return true;
    }


}
