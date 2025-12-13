<?php

require_once "framework/Controller.php";
require_once "model/User.php";
require_once "model/Item.php";

class ControllerItem extends Controller {
    public function index(): void {
        $this->browse();
    }

    public function open() : void{

    }
    public function browse(): void {
        $user = $this->get_user_or_redirect();
        $my_participations = $user->get_participating_items();
        $others_available = $user->get_other_available_items();

        (new View("browse_items"))->show(['my_participations' => $my_participations, 'others_available' => $others_available]);
    }
}