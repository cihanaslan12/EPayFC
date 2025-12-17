<?php

require_once "framework/Controller.php";
require_once "model/User.php";
require_once "model/Item.php";
require_once "model/ItemPicture.php";

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

        $bids = $item->get_bids();

        $pictures = $item->get_pictures();

        $selectedPriority = isset($_GET["param2"]) ? intval($_GET["param2"]) : 0;

        $mainPicture = count($pictures) > 0 ? $pictures[0]->get_picture_path() : null;
        if ($selectedPriority > 0) {
            foreach ($pictures as $pic) {
                if ($pic->get_priority() === $selectedPriority) {
                    $mainPicture = $pic->get_picture_path();
                }
            }
        }


        $user = $this->get_user_or_false(); // guest autorisé
        (new View("openitem"))->show([
            "item" => $item,
            "user" => $user,
            "pictures" => $pictures,
            "mainPicture" => $mainPicture,
            "bids" => $bids
        ]);
    }

    public function browse(): void {
        $user = $this->get_user_or_redirect();
        $my_participations = $user->get_participating_items();
        $others_available = $user->get_other_available_items();

        (new View("browse_items"))->show(['my_participations' => $my_participations, 'others_available' => $others_available]);
    }
}