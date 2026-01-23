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
        $item = Item::get_by_id($_GET['param1']);       // param1 !!! -> id de open item?
        $images = $item->get_item_pictures();
        $error = null;

        if(isset($_FILES['image']) && is_array($_FILES['image']['name'])) {
            $files = $_FILES['image']['name'];

            foreach ($files as $index => $name) {
                $file_name = $_FILES['image']['name'][$index];
                $tmp_name = $_FILES['image']['tmp_name'][$index];
                $size = $_FILES['image']['size'][$index];
                $file_error = $_FILES['image']['error'][$index];

                if ($file_error === 0) {
                    $extension = Uploader::check_extension($file_name);
                    $size = Uploader::check_size($size);
                    if (!$extension) {
                        $error = "Unsupported image format : jpg/jpeg, png, gif or webp !";
                    } else if (!$size) {
                        $error = "Image size is max 5MB";
                    } else {
                        $item->add_pictures($tmp_name, $file_name);
                    }
                }
            }
        } else {
            $error = "Error while uploading file.";
        }

        $manage_images = [
            'show_back' => true,
            'backUrl' => 'item/openitem',
            'page_title' => "Manage Images",
            'show_save' => false,
            'item' => $item,
            'error' => $error,
            'images' => $images,
        ];
        (new View("manage_images"))->show($manage_images);
    }

    public function move_picture(): void {
        $picture_id = $_POST['image_id'];
        $picture = ItemPicture::get_by_id($picture_id);
        $item = $picture['item'];
        if (isset($_POST['btn-left'])) {
            $picture->priority_minus();
        } else if (isset($_POST['btn-right'])) {
            $picture->priority_plus();
        } else if (isset($_POST['btn-delete'])) {
            $picture->delete_picture();
        }
        $this->redirect("item", "manage_images", $item);
    }
}