<?php

require_once "framework/Model.php";

class User extends Model {
    public function __construct(
        private string $mail,
        private string $full_name,
        private string $pseudo,
        private string $hashed_password,
        private string $iban,
        private ?string $picture_path = null,
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
        return $this->full_name;
    }

    public function get_pseudo(): string {
        return $this->pseudo;
    }

    public function get_hashedPassword(): string {
        return $this->hashed_password;
    }

    public function get_picture_path(): string {
        return $this->picture_path;
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
        return $row ? new User(id: $row['id'], mail: $row['email'], full_name: $row['full_name'], pseudo: $row['pseudo'], hashed_password: $row['password'], picture_path: $row['picture_path'], iban: $row['iban'], role: $row['role']) : false;
    }

    public static function get_by_mail(string $mail): User|false {
        $query = self::execute("SELECT * FROM users WHERE email = :mail", array("mail" => $mail));
        $row = $query->fetch();
        return $row ? new User(id: $row['id'], mail: $row['email'], full_name: $row['full_name'], pseudo: $row['pseudo'], hashed_password: $row['password'], picture_path: $row['picture_path'], iban: $row['iban'], role: $row['role']) : false;
    }

    public static function get_by_fullname(string $fullname): User|false {
        $query = self::execute("SELECT * FROM users WHERE full_name = :full_name", array("full_name" => $fullname));
        $row = $query->fetch();
        return $row ? new User(id: $row['id'], mail: $row['email'], full_name: $row['full_name'], pseudo: $row['pseudo'], hashed_password: $row['password'], picture_path: $row['picture_path'], iban: $row['iban'], role: $row['role']) : false;
    }

    public function persist(): User
    {
        if (self::get_by_id($this->id)) {
            self::execute("UPDATE users SET  email=:mail, full_name=:full_name, pseudo=:pseudo, password=:hashed_password, picture_path=:picture_path, iban=:iban, role=:role
            WHERE ID=:id",
                array(
                    "mail" => $this->mail,
                    "full_name" => $this->full_name,
                    "pseudo" => $this->pseudo,
                    "hashed_password" => $this->hashed_password,
                    "picture_path" => $this->picture_path,
                    "iban" => $this->iban,
                    "role" => $this->role,
                )
            );
        } else {
            self::execute("INSERT INTO users(email, full_name, pseudo, password, picture_path, iban, role) VALUES (:mail, :full_name, :pseudo, :hashed_password, :picture_path, :iban, :role)",
                array(
                    "mail" => $this->mail,
                    "full_name" => $this->full_name,
                    "pseudo" => $this->pseudo,
                    "hashed_password" => $this->hashed_password,
                    "picture_path" => $this->picture_path,
                    "iban" => $this->iban,
                    "role" => $this->role,
                ),
                true,
            );
            $this->id = self::lastInsertId();
        }
        return $this;
    }
}