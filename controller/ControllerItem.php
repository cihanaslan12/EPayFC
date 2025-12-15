<?php

require_once "framework/Controller.php";
require_once "model/User.php";
require_once "model/Item.php";

class ControllerItem extends Controller {
    public function index(): void {
        $this->browse();
    }

    public function open(): void {
        $id = isset($_GET["param1"]) ? intval($_GET["param1"]) : 0;

        if ($id <= 0) {
            http_response_code(404);
            (new View("error"))->show(["error" => "Item not found."]);
            return;
        }

        $item = Item::get_open_item_with_seller($id);
        if (!$item) {
            http_response_code(404);
            (new View("error"))->show(["error" => "Item not found."]);
            return;
        }

        $user = $this->get_user_or_false(); // guest autorisé
        (new View("openitem"))->show([
            "item" => $item,
            "user" => $user
        ]);
    }

    public function browse(): void {
        $user = $this->get_user_or_redirect();
        $my_participations = $user->get_participating_items();
        $others_available = $user->get_other_available_items();

        (new View("browse_items"))->show(['my_participations' => $my_participations, 'others_available' => $others_available]);
    }
}