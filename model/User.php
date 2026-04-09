<?php

require_once "framework/Model.php";
require_once "model/Item.php";
require_once "utils/Uploader.php";

class User extends Model {
    public function __construct(
        private ?string $mail,
        private ?string $fullName,
        private ?string $pseudo,
        private ?string $hashedPassword,
        private ?bool $is_guest = false,
        private ?string $iban = null,
        private ?string $picturePath = null,
        private ?string $role = "user",
        private ?int $id = null,
    ) {
    }

    public function get_id(): ?int {
        return $this->id;
    }

    public function get_mail(): ?string {
        return $this->mail;
    }

    public function get_full_name(): ?string {
        return $this->fullName;
    }

    public function get_pseudo(): ?string {
        return $this->pseudo;
    }

    public function get_hashedPassword(): ?string {
        return $this->hashedPassword;
    }

    public function is_guest(): ?bool {
        return $this->is_guest;
    }

    public function get_picture_path(): ?string {
        return $this->picturePath;
    }

    public function get_iban(): ?string {
        return $this->iban;
    }

    public function get_role(): ?string {
        return $this->role;
    }

    public static function get_by_id(?int $id): User|false {
        $query = self::execute("SELECT * FROM users WHERE id = :id", array("id" => $id));
        $row = $query->fetch();
        return $row ? new User(id: $row['id'], mail: $row['email'], fullName: $row['full_name'], pseudo: $row['pseudo'], hashedPassword: $row['password'], picturePath: $row['picture_path'], iban: $row['iban'], role: $row['role']) : false;
    }

    public static function get_by_mail(string $mail): User|false {
        $query = self::execute("SELECT * FROM users WHERE email = :mail", array("mail" => $mail));
        $row = $query->fetch();
        return $row ? new User(id: $row['id'], mail: $row['email'], fullName: $row['full_name'], pseudo: $row['pseudo'], hashedPassword: $row['password'], picturePath: $row['picture_path'], iban: $row['iban'], role: $row['role']) : false;
    }

    public static function get_pseudo_by_owner_id(int $id): string {
        $sql = "SELECT pseudo FROM users WHERE id = :id" ;
        $query = self::execute($sql, ["id" => $id]);
        $user = $query->fetch(PDO::FETCH_ASSOC);
        return $user['pseudo'];
    }

    public static function am_i_bidder(int $user_id, int $item_id): bool {
        $sql = "SELECT * 
                FROM bids b
                    JOIN v_items_status vis ON b.item = vis.id
                    JOIN users u ON b.owner = u.id
                WHERE b.owner = :bidder_id
                    AND b.item = :item_id
                    AND (vis.end_at > :now OR vis.buy_now_reached = 0) ";
        $query = self::execute($sql, ["bidder_id" => $user_id, "item_id" => $item_id, "now" => AppTime::get_current_datetime()]);
        $res = $query->fetch();
        return (bool)$res;
    }

    public static function am_i_highest_bidder(int $user_id, int $item_id): bool {
        $sql = "SELECT owner
                FROM bids
                WHERE item = :item_id
                ORDER BY amount DESC
                LIMIT 1";
        $query = self::execute($sql, ["item_id" => $item_id]);
        $res = $query->fetch();
        if ($res)
            return (int)$res['owner'] == $user_id;
        else
            return false;
    }

    public static function get_by_pseudo(string $pseudo): User|false {
        $query = self::execute("SELECT * FROM users WHERE pseudo = :pseudo", array("pseudo" => $pseudo));
        $row = $query->fetch();
        return $row ? new User(id: $row['id'], mail: $row['email'], fullName: $row['full_name'], pseudo: $row['pseudo'], hashedPassword: $row['password'], picturePath: $row['picture_path'], iban: $row['iban'], role: $row['role']) : false;
    }

    public function persist(): User
    {
        if (self::get_by_id($this->id)) {
            self::execute("UPDATE users SET  email=:mail, full_name=:full_name, pseudo=:pseudo, password=:hashed_password, picture_path=:picture_path, iban=:iban, role=:role
            WHERE ID=:id",
                array(
                    "mail" => $this->mail,
                    "full_name" => $this->fullName,
                    "pseudo" => $this->pseudo,
                    "hashed_password" => $this->hashedPassword,
                    "picture_path" => $this->picturePath,
                    "iban" => $this->iban,
                    "role" => $this->role,
                )
            );
        } else {
            self::execute("INSERT INTO users(email, full_name, pseudo, password, picture_path, iban, role) VALUES (:mail, :full_name, :pseudo, :hashed_password, :picture_path, :iban, :role)",
                array(
                    "mail" => $this->mail,
                    "full_name" => $this->fullName,
                    "pseudo" => $this->pseudo,
                    "hashed_password" => $this->hashedPassword,
                    "picture_path" => $this->picturePath,
                    "iban" => $this->iban,
                    "role" => $this->role,
                ),
                true,
            );
            $this->id = self::lastInsertId();
        }
        return $this;
    }

    public function valid_password(string $new_password): bool {
        return preg_match('/^(?=.*[A-Z])(?=.*[0-9])(?=.*[^a-zA-Z0-9]).{8,16}$/', $new_password) === 1;
    }

    public function update_password(string $hashed_password): void {
        $sql = "UPDATE users SET password = :password WHERE id = :user_id ";
        self::execute($sql, ['password' => $hashed_password, 'user_id' => $this->get_id()]);
    }

    public static function check_password(string $clear_password, string $hashed_password): bool {
        return password_verify($clear_password, $hashed_password);
    }

    public static function validate_login(string $mail, string $password): array {
        $errors = [];
        $user = User::get_by_mail($mail);
        if ($user) {
            if (!self::check_password($password, $user->hashedPassword)) {
                $errors['password'] = "Wrong password. Please try again.";
            }
        } else {
            $errors['mail'] = "Can't find a user with the mail '$mail'. Please sign up.";
        }
        return $errors;
    }

    public function get_participating_items(): array
    {
        return Item::get_participating_items($this);
    }

    public function get_other_available_items(): array
    {
        return Item::get_other_available_items($this);
    }

    public function get_all_available_items_for_guest(): array
    {
        return Item::get_all_available_items_for_guest();
    }

    public function get_my_sold_items(): array {
        return Item::get_my_sold_items($this);
    }

    public function get_my_sold_items_total(): float {
        return Item::get_my_sold_items_total($this);
    }

    public function get_average_ticket(): float {
        return (float)($this->get_my_sold_items_total() / count($this->get_my_sold_items()));
    }

    public function get_loyal_bidder(): ?User {
        $sql = "SELECT b.owner as user_id, COUNT(DISTINCT vis.id) as purchase_count
            FROM bids b
            	JOIN v_items_status vis ON b.item = vis.id
            WHERE vis.owner = :id
              AND (
                    (vis.is_direct_sale = 1 AND vis.not_purchased_direct_sale = 0)
                 OR 
                    (vis.is_auction = 1 
                        AND vis.has_bids = 1
                        AND (vis.end_at <= :now OR vis.buy_now_reached = 1)
                        AND vis.max_bid = b.amount)) 
              GROUP BY b.owner
              ORDER BY purchase_count DESC, user_id DESC
              LIMIT 1 ";
        $query = self::execute($sql, ['id' => $this->get_id(), 'now' => AppTime::get_current_datetime()]);
        $res = $query->fetch();
        return self::get_by_id($res['user_id']);
    }

    public static function is_mail_unique(string $mail, int $exclude_user_id): bool {
        $sql = "SELECT COUNT(*) FROM users WHERE email = :mail AND id <> :id";
        $q = self::execute($sql, ["mail" => $mail, "id" => $exclude_user_id]);
        return (int)$q->fetchColumn() === 0;
    }

    public static function is_pseudo_unique(string $pseudo, int $exclude_user_id): bool {
        $sql = "SELECT COUNT(*) FROM users WHERE pseudo = :pseudo AND id <> :id";
        $q = self::execute($sql, ["pseudo" => $pseudo, "id" => $exclude_user_id]);
        return (int)$q->fetchColumn() === 0;
    }

    public static function update_profile(int $id, string $full_name, string $pseudo, string $mail, ?string $iban): void {
        $sql = "UPDATE users
            SET full_name = :full_name,
                pseudo = :pseudo,
                email = :mail,
                iban = :iban
            WHERE id = :id";
        self::execute($sql, [
            "full_name" => $full_name,
            "pseudo" => $pseudo,
            "mail" => $mail,
            "iban" => $iban,
            "id" => $id
        ]);
    }

    public static function is_full_name_unique(string $full_name, int $exclude_user_id): bool {
        $sql = "SELECT COUNT(*) FROM users WHERE full_name = :full_name AND id <> :id";
        $q = self::execute($sql, ["full_name" => $full_name, "id" => $exclude_user_id]);
        return (int)$q->fetchColumn() === 0;
    }

    public static function exists_full_name(string $full_name): bool
    {
        $q = self::execute(
            "SELECT COUNT(*) FROM users WHERE full_name = :fn",
            ["fn" => $full_name]
        );
        return ((int)$q->fetchColumn()) > 0;
    }

    public static function update_profile_picture(int $user_id, string $tmp_path, string $original_name, array &$errors): bool {
        if (!Uploader::check_extension($original_name)) {
            $errors["picture"] = "Unsupported image format: JPG, PNG, GIF, WebP.";
            return false;
        }

        $size_ok = Uploader::check_size(filesize($tmp_path));
        if (!$size_ok) {
            $errors["picture"] = "Image size is max 5MB.";
            return false;
        }

        $original = Uploader::create_image_from($tmp_path, $original_name);
        if (!$original) {
            $errors["picture"] = "Could not read the image file.";
            return false;
        }

        $config = parse_ini_file(__DIR__ . '/../config/dev.ini');
        $max_w = (int)$config["MAX_THUMB_WIDTH"];
        $max_h = (int)$config["MAX_THUMB_HEIGHT"];

        $ow = imagesx($original);
        $oh = imagesy($original);

        $ratio = min($max_w / $ow, $max_h / $oh);
        $ratio = ($ratio < 1) ? $ratio : 1;

        $nw = (int)round($ow * $ratio);
        $nh = (int)round($oh * $ratio);

        $new_img = imagecreatetruecolor($nw, $nh);
        imagecopyresampled($new_img, $original, 0, 0, 0, 0, $nw, $nh, $ow, $oh);

        $dir = "uploads/users/$user_id/";
        if (!file_exists($dir)) {
            mkdir($dir, 0777, true);
        }
        $path = $dir . "profile.jpg";

        imagejpeg($new_img, $path, 85);

        imagedestroy($original);
        imagedestroy($new_img);

        self::execute("UPDATE users SET picture_path = :p WHERE id = :id", [
            "p" => $path,
            "id" => $user_id
        ]);

        return true;
    }

    public static function delete_profile_picture(int $user_id): void {
        $q = self::execute("SELECT picture_path FROM users WHERE id = :id", ["id" => $user_id]);
        $row = $q->fetch();

        if ($row && !empty($row["picture_path"])) {
            $path = $row["picture_path"];
            if (file_exists($path)) {
                @unlink($path);
            }
        }

        self::execute("UPDATE users SET picture_path = NULL WHERE id = :id", [
            "id" => $user_id
        ]);
    }



}