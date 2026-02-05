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
        $pseudo = '';
        $password = '';
        $password_confirm = '';
        $errors = [];

        if(isset($_POST['pseudo']) && isset($_POST['password']) && isset($_POST['password_confirm'])) {
            $pseudo = $_POST['pseudo'];
            $password = $_POST['password'];
            $password_confirm = $_POST['password_confirm'];

            $user = new User($pseudo, password_hash($password, PASSWORD_BCRYPT));
            $errors = User::validate_unicity($pseudo);
            $errors = array_merge($errors, $user->validate());
            $errors = array_merge($errors, User::validate_passwords($password, $password_confirm));

            if (count($errors) == 0) {
                $user->persist();
                $this->log_user($user);
            }
        }
        (new View("signup"))->show(['pseudo' => $pseudo, "password" => $password, "password_confirm" => $password_confirm, "errors" => $errors]);
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
            'backUrl' => 'user/profile',
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
            $this->redirect("main", "login");
            return;
        }

        (new View("edit_profile"))->show([
            "user" => $user,
            "errors" => [],
            "show_back" => true,
            "page_title" => "Edit profile",
            "show_save" => false
        ]);
    }

}