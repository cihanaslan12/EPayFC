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
}