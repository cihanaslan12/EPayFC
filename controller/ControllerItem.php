<?php

require_once "framework/Controller.php";
require_once "model/Item.php";

class ControllerItem extends Controller {
    public function index(): void {
        $this->browse();
    }

    public function browse(): void {
        $user = $this->get_user_or_redirect();
        $participated = Item::get_participating_items($user->get_id());
        $others = Item::get_other_available_items();

        foreach ($participated as $item){
            $items_data[] = [
                'title' => $item->get_title(),
                'owner' => $item->get_owner(),
            ];
        }
        (new View("browse_items"))->show([$items_data]);
    }
}