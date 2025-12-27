<?php

require_once "framework/Model.php";
require_once "model/Item.php";

class User extends Model {
    public function __construct(
        private string $mail,
        private string $fullName,
        private string $pseudo,
        private string $hashedPassword,
        private ?string $iban = null,
        private ?string $picturePath = null,
        private string $role = "user",
        private ?int $id = null,
    ) {
    }

    public function get_id(): ?int {
        return $this->id;
    }

    public function get_mail(): string {
        return $this->mail;
    }

    public function get_full_name(): string {
        return $this->fullName;
    }

    public function get_pseudo(): string {
        return $this->pseudo;
    }

    public function get_hashedPassword(): string {
        return $this->hashedPassword;
    }

    public function get_picture_path(): string {
        return $this->picturePath;
    }

    public function get_iban(): string {
        return $this->iban;
    }

    public function get_role(): string {
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


    private static function check_password(string $clear_password, string $hashed_password): bool {
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
}