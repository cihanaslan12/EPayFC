<?php

require_once "framework/Controller.php";
require_once "model/User.php";

class ControllerUser extends Controller {
    public function index(): void {
        if ($this->user_logged()) {
            $this->redirect("item", "browse");
        } else {
            $this->login();
        }
    }

    public function logout(): void {
        parent::logout();
        $this->redirect("user", "login");
    }

    public function login(): void {
        if ($this->user_logged()) {
            $this->redirect("item", "browse");
        }

        $mail = '';
        $password = '';
        $errors = [];
        if (isset($_POST['login_user'])) {
            if (isset($_POST['email']) && isset($_POST['password'])) {
                $mail = $_POST['email'];
                $password = $_POST['password'];

                $errors = User::validate_login($mail, $password);
                if (empty($errors)) {
                    $this->log_user(User::get_by_mail($mail));
                }
            }
        } else if (isset($_POST['login_guest'])) {
            $this->redirect("item", "browse");
            return;
        }
        (new View("login"))->show(['mail' => $mail, 'password' => $password, 'errors' => $errors]);
    }

    public function signup(): void {
        if ($this->user_logged()) {
            $this->redirect("item", "browse");
            return;
        }

        $full_name = "";
        $mail = "";
        $pseudo = "";
        $password = "";
        $password_confirm = "";
        $errors = [];

        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            $full_name = trim($_POST["full_name"] ?? "");
            $mail = trim($_POST["mail"] ?? "");
            $pseudo = trim($_POST["pseudo"] ?? "");
            $password = $_POST["password"] ?? "";
            $password_confirm = $_POST["password_confirm"] ?? "";

            if (mb_strlen($full_name) < 3) $errors["full_name"] = "Full name must be at least 3 characters.";
            if (!filter_var($mail, FILTER_VALIDATE_EMAIL)) $errors["mail"] = "Invalid email format.";
            if (mb_strlen($pseudo) < 3 || mb_strlen($pseudo) > 30) $errors["pseudo"] = "Pseudo must be between 3 and 30 characters.";
            if ($password === "" || $password_confirm === "") $errors["password"] = "Password is required.";
            if ($password !== $password_confirm) $errors["password_confirm"] = "Passwords do not match.";


            $tmpUser = new User($mail, $full_name, $pseudo, null);
            if ($password !== "" && !$tmpUser->valid_password($password)) {
                $errors["password"] = "Password must be 8-16 characters with uppercase, number, and punctuation.";
            }

            if (!isset($errors["mail"]) && User::get_by_mail($mail)) {
                $errors["mail"] = "This email is already used.";
            }
            if (!isset($errors["pseudo"]) && User::get_by_pseudo($pseudo)) {
                $errors["pseudo"] = "This pseudo is already used.";
            }

            if (!isset($errors["full_name"]) && User::exists_full_name($full_name)) {
                $errors["full_name"] = "This full name is already used.";
            }

            if (empty($errors)) {
                $user = new User(
                    mail: $mail,
                    fullName: $full_name,
                    pseudo: $pseudo,
                    hashedPassword: password_hash($password, PASSWORD_BCRYPT)
                );
                $user->persist();
                $this->log_user($user);
                $this->redirect("item", "browse");
                return;
            }
        }

        (new View("signup"))->show([
            "full_name" => $full_name,
            "mail" => $mail,
            "pseudo" => $pseudo,
            "password" => $password,
            "password_confirm" => $password_confirm,
            "errors" => $errors
        ]);
    }

    public function debug() {
        if (Configuration::is_dev() && isset($_GET['param1'])) {
            switch ($_GET['param1']) {
                case 'boris':
                    $this->log_user(User::get_by_mail('boverhaegen@epfc.eu'), "item", "browse");
                case "marc":
                    $this->log_user(User::get_by_mail("mamichel@epfc.eu"), "item", "browse");
                    break;
                case 'quentin':
                    $this->log_user(User::get_by_mail('quhouben@epfc.eu'), "item", "browse");
                    break;
                case "xavier":
                    $this->log_user(User::get_by_mail("xapigeolet@epfc.eu"), "item", "browse");
                    break;
                default:
                    $this->redirect();
                    break;
            }
        } else {
            $this->redirect();
        }
    }

    public function profile(): void {
        $user = $this->get_user_or_false();
        if (!$user) {
            $this->redirect("user", "login");
            return;
        }

        (new View("profile"))->show([
            "user" => $user,
            "show_back" => false,
            "page_title" => "Profile",
            "show_save" => false
        ]);
    }

    public function change_password(): void {
        $session = $this->get_user_or_redirect();
        $user = User::get_by_id($session->get_id());    // on récupère le user (et ses infos actuels)
        $current_password = '';
        $new_password = '';
        $confirm_new_password = '';
        $errors = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $current_password = $_POST['current'];
            $new_password = $_POST['new_password'];
            $confirm_new_password = $_POST['confirm_new_password'];
            $check = password_verify($current_password, $user->get_hashedPassword());
            if (!$check) {
                $errors['current'] = "Current password is wrong ! ";
            }
            if ($new_password === '' || $confirm_new_password === '') {
                $errors['confirm_new_password'] = "Your new password is empty! ";
            } else if ($user->valid_password($new_password)) {
                $errors['confirm_new_password'] = "Password must be 8-16 characters with uppercase, number, and punctuation";
            } else if ($new_password !== $confirm_new_password) {
                $errors['confirm_new_password'] = "You have to enter twice the same password. ";
            }
            if (!$errors) {
                $user->update_password(password_hash($new_password, PASSWORD_BCRYPT));
                $this->redirect("user", "profile");
            }

        }
        (new View("change_password"))->show([
            'show_back' => true,
            'back_url' => 'user/profile',
            'page_title' => 'Change Password',
            'show_save' => true,
            'user' => $user,
            'current' => $current_password,
            'new_password' => $new_password,
            'confirm_new_password' => $confirm_new_password,
            'errors' => $errors
        ]);
    }

    public function edit_profile(): void {
        $user = $this->get_user_or_false();
        if (!$user) {
            $this->redirect("user", "login");
            return;
        }

        $errors = [];
        $values = [
            "full_name" => $user->get_full_name(),
            "pseudo" => $user->get_pseudo(),
            "mail" => $user->get_mail(),
            "iban" => $user->get_iban()
        ];

        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            $full_name = trim($_POST["full_name"] ?? "");
            $pseudo = trim($_POST["pseudo"] ?? "");
            $mail = trim($_POST["mail"] ?? "");
            $iban = trim($_POST["iban"] ?? "");

            $values = [
                "full_name" => $full_name,
                "pseudo" => $pseudo,
                "mail" => $mail,
                "iban" => $iban
            ];

            if (mb_strlen($full_name) < 3) {
                $errors["full_name"] = "Full name must be at least 3 characters.";
            }

            if (mb_strlen($pseudo) < 3 || mb_strlen($pseudo) > 30) {
                $errors["pseudo"] = "Pseudo must be between 3 and 30 characters.";
            }

            if (!filter_var($mail, FILTER_VALIDATE_EMAIL)) {
                $errors["mail"] = "Invalid email format.";
            }

            if ($iban !== "") {
                $iban_no_spaces = str_replace(" ", "", strtoupper($iban));
                if (!preg_match('/^BE\d{14}$/', $iban_no_spaces)) {
                    $errors["iban"] = "IBAN must have format BE99 9999 9999 9999.";
                } else {
                    $iban = substr($iban_no_spaces, 0, 4) . " " .
                        substr($iban_no_spaces, 4, 4) . " " .
                        substr($iban_no_spaces, 8, 4) . " " .
                        substr($iban_no_spaces, 12, 4);
                    $values["iban"] = $iban;
                }
            } else {
                $iban = null;
            }

            if (empty($errors["mail"]) && !User::is_mail_unique($mail, $user->get_id())) {
                $errors["mail"] = "This email is already used.";
            }

            if (empty($errors["pseudo"]) && !User::is_pseudo_unique($pseudo, $user->get_id())) {
                $errors["pseudo"] = "This pseudo is already used.";
            }

            if (!isset($errors["full_name"]) && User::exists_full_name($full_name)) {
                $errors["full_name"] = "This full name is already used.";
            }

            if (empty($errors)) {
                User::update_profile($user->get_id(), $full_name, $pseudo, $mail, $iban);

                $updated = User::get_by_id($user->get_id());
                if ($updated) {
                    $this->log_user($updated);
                }
                $this->redirect("user", "profile");
                return;
            }
        }

        (new View("edit_profile"))->show([
            "user" => $user,
            "errors" => $errors,
            "values" => $values,
            "show_back" => true,
            "page_title" => "Edit profile",
            "show_save" => false
        ]);
    }

    public function profile_picture(): void {
        $user = $this->get_user_or_false();
        if (!$user) {
            $this->redirect("user", "login");
            return;
        }

        $errors = [];

        if ($_SERVER["REQUEST_METHOD"] === "POST") {

            if (isset($_POST["delete_picture"])) {
                User::delete_profile_picture($user->get_id());
                
                $updated = User::get_by_id($user->get_id());
                if ($updated) {
                    $_SESSION["user"] = $updated;
                }

                $this->redirect("user", "profile_picture");
                return;
            }

            if (isset($_POST["upload_picture"])) {
                if (!isset($_FILES["picture"]) || $_FILES["picture"]["error"] !== UPLOAD_ERR_OK) {
                    $errors["picture"] = "Please select an image.";
                } else {
                    $tmp = $_FILES["picture"]["tmp_name"];
                    $name = $_FILES["picture"]["name"];

                    if (User::update_profile_picture($user->get_id(), $tmp, $name, $errors)) {
                        $updated = User::get_by_id($user->get_id());
                        if ($updated) {
                            $_SESSION["user"] = $updated;
                        }
                        $this->redirect("user", "profile_picture");
                        return;
                    }
                }
            }
        }

        (new View("profile_picture"))->show([
            "user" => $user,
            "errors" => $errors,
            "show_back" => true,
            "back_url" => "user/profile",
            "page_title" => "Manage profile picture",
            "show_save" => false
        ]);
    }



}