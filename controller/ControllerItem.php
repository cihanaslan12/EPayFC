<?php

require_once "framework/Controller.php";
require_once "model/Item.php";
require_once "model/User.php";
require_once "utils/Uploader.php";

class ControllerItem extends Controller {
    public function index(): void {
        $this->browse();
    }

    public function browse(): void {
        $user = $this->get_user_or_false();
        if ($user) {
            $my_participations = $user->get_participating_items();
            $others_available = $user->get_other_available_items();
            $browse_view = [
                'user' => $user,
                'my_participations' => $my_participations,
                'others_available' => $others_available,
                'show_back' => false,
                'page_title' => "Browse",
                'show_save' => false
            ];
        } else {
            $all_available_items = Item::get_all_available_items_for_guest();
            $browse_view = [
                'user' => null,
                'all_available_items' => $all_available_items,
                'show_back' => false,
                'page_title' => "Browse",
                'show_save' => false
            ];
        }
        (new View("browse_items"))->show($browse_view);
    }

    public function manage_images(): void {
        $error = null;
        if(isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
            $extension = Uploader::check_extension($_FILES['image']['name']);
            $size = Uploader::check_size($_FILES['image']['size']);
            if (!$extension) {
                $error = "Unsupported image format : jpg/jpeg, png, gif or webp !";
            } else if (!$size) {
                $error = "Image size is max 5MB";
            } else {
                add_pictures();
            }
        } else {
            $errors = "Error while uploading file.";
        }
        $item = Item::get_by_id($_GET['id']);
        $pictures = $item->get_item_pictures();
        (new View("manage_images"))->show(['error' => $error, 'pictures' => $pictures]);
    }
}