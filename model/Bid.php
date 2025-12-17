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
}
