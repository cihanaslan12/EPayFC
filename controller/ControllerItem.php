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
        $participated = Item::get_participating_items($user->get_id());
        $others = Item::get_other_available_items($user->get_id());

        $my_participations = [];
        $others_available = [];
        foreach ($participated as $item){
            $my_participations[] = [
                'id' => $item->get_id(),
                'title' => $item->get_title(),
                'description' => $item->get_description(),
                'owner' => $item->get_owner(),
                'created_at' => $item->get_created_at(),
                'duration_days' => $item->get_duration_days(),
                'buy_now_price' => $item->get_buy_now_price(),
                'starting_bid' => $item->get_starting_bid(),
            ];
        }
        foreach ($others as $item){
            $others_available[] = [
                'id' => $item->get_id(),
                'title' => $item->get_title(),
                'description' => $item->get_description(),
                'owner' => $item->get_owner(),
                'created_at' => $item->get_created_at(),
                'duration_days' => $item->get_duration_days(),
                'buy_now_price' => $item->get_buy_now_price(),
                'starting_bid' => $item->get_starting_bid(),
            ];
        }

        (new View("browse_items"))->show(['my_participations' => $my_participations, 'others_available' => $others_available]);
    }
}