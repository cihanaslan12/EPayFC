<?php

require_once "framework/Controller.php";
require_once "model/Item.php";
require_once "model/User.php";

class ControllerItem extends Controller {
    public function index(): void {
        $this->browse();
    }

    public function browse(): void {
        $user = $this->get_user_or_redirect();
        $my_participations = $user->get_participating_items();
        $others_available = $user->get_other_available_items();
        $browse_view = [
            'my_participations' => $my_participations,
            'others_available' => $others_available,
            'show_back' => false,
            'page_title' => "Browse",
            'show_save' => false
        ];
        (new View("browse_items"))->show($browse_view);
    }
}