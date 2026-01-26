<?php

require_once "framework/Controller.php";
require_once "model/User.php";
require_once "model/Item.php";
require_once "model/ItemPicture.php";
require_once "utils/AppTime.php";

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

        $now = AppTime::get_current_datetime();
        $isOpen = $item->is_open($now);

        $isOwner = $user && $user->get_id() === $item->get_owner();

        $canManage = $isOwner && ((int)($item->get_bid_count() ?? 0) === 0);

        $highestBid = Bid::get_highest_for_item($item->get_id());

        $defaultBid = null;
        if ($item->get_is_auction() === 1 && $isOpen && !$isOwner) {
            if ($item->get_max_bid() !== null) {
                $defaultBid = (string)((float)$item->get_max_bid() + 1);
            } else {
                $defaultBid = $item->get_starting_bid();
            }
        }

        (new View("openitem"))->show([
            "item" => $item,
            "user" => $user,
            "pictures" => $pictures,
            "mainPicture" => $mainPicture,
            "bids" => $bids,
            "now" => $now,
            "isOpen" => $isOpen,
            "isOwner" => $isOwner,
            "defaultBid" => $defaultBid,
            "highestBid" => $highestBid,
            "canManage" => $canManage
        ]);
    }

    public function place_bid(): void {
        $user = $this->get_user_or_false();
        if (!$user) {
            $this->redirect("user", "login");
            return;
        }

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

        $now = AppTime::get_current_datetime();

        $amount = trim($_POST["amount"] ?? "");
        $errors = [];

        if (Bid::place_bid($user, $item, $amount, $now, $errors)) {
            $this->redirect("item", "open", $id);
            return;
        }

        $pictures = $item->get_pictures();

        $selectedPriority = isset($_GET["param2"]) ? intval($_GET["param2"]) : 0;
        $mainPicture = null;
        if (!empty($pictures)) {
            $mainPicture = $pictures[0]->get_picture_path();
            if ($selectedPriority > 0) {
                foreach ($pictures as $pic) {
                    if ($pic->get_priority() === $selectedPriority) {
                        $mainPicture = $pic->get_picture_path();
                    }
                }
            }
        }

        $bids = $item->get_bids();
        $highestBid = Bid::get_highest_for_item($item->get_id());

        $isOpen = $item->is_open($now);
        $isOwner = $user->get_id() === $item->get_owner();
        $canManage = $isOwner && ((int)($item->get_bid_count() ?? 0) === 0);


        $defaultBid = null;
        if ($item->get_is_auction() === 1 && $isOpen && !$isOwner) {
            if ($item->get_max_bid() !== null) {
                $defaultBid = (string)((float)$item->get_max_bid() + 1);
            } else {
                $defaultBid = $item->get_starting_bid();
            }
        }

        (new View("openitem"))->show([
            "item" => $item,
            "user" => $user,
            "pictures" => $pictures,
            "mainPicture" => $mainPicture,
            "bids" => $bids,
            "highestBid" => $highestBid,
            "now" => $now,
            "isOpen" => $isOpen,
            "isOwner" => $isOwner,
            "defaultBid" => $defaultBid,
            "canManage" => $canManage,

            "errors" => $errors,
            "postedAmount" => $amount
        ]);
    }


    public function buy_now(): void {
        $user = $this->get_user_or_false();
        if (!$user) {
            $this->redirect("user", "login");
            return;
        }

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

        $now = AppTime::get_current_datetime();

        $errors = [];

        if ($item->get_has_buy_now() !== 1 || $item->get_buy_now_price() === null) {
            $errors["buy_now"] = "Buy now is not available for this item.";
        } else {
            $amount = (string)$item->get_buy_now_price();
            if (Bid::place_bid($user, $item, $amount, $now, $errors)) {
                $this->redirect("item", "open", $id);
                return;
            }
        }

        $pictures = $item->get_pictures();

        $selectedPriority = isset($_GET["param2"]) ? intval($_GET["param2"]) : 0;
        $mainPicture = null;
        if (!empty($pictures)) {
            $mainPicture = $pictures[0]->get_picture_path();
            if ($selectedPriority > 0) {
                foreach ($pictures as $pic) {
                    if ($pic->get_priority() === $selectedPriority) {
                        $mainPicture = $pic->get_picture_path();
                    }
                }
            }
        }

        $bids = $item->get_bids();
        $highestBid = Bid::get_highest_for_item($item->get_id());

        $isOpen = $item->is_open($now);
        $isOwner = $user->get_id() === $item->get_owner();
        $canManage = $isOwner && ((int)($item->get_bid_count() ?? 0) === 0);


        $defaultBid = null;
        if ($item->get_is_auction() === 1 && $isOpen && !$isOwner) {
            if ($item->get_max_bid() !== null) {
                $defaultBid = (string)((float)$item->get_max_bid() + 1);
            } else {
                $defaultBid = $item->get_starting_bid();
            }
        }

        (new View("openitem"))->show([
            "item" => $item,
            "user" => $user,
            "pictures" => $pictures,
            "mainPicture" => $mainPicture,
            "bids" => $bids,
            "highestBid" => $highestBid,
            "now" => $now,
            "isOpen" => $isOpen,
            "isOwner" => $isOwner,
            "defaultBid" => $defaultBid,
            "canManage" => $canManage,

            // erreurs buy now
            "errors" => $errors,
            "postedAmount" => null
        ]);
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

    public function my_items(): void {
        $user = $this->get_user_or_false();
        if (!$user) {
            $this->redirect("user", "login");
            return;
        }

        $active_items = Item::get_my_active_items($user);
        $closed_unsold_items = Item::get_my_closed_unsold_items($user);
        $sold_items = Item::get_my_sold_items($user);

        (new View("my_items"))->show([
            "user" => $user,
            "active_items" => $active_items,
            "closed_unsold_items" => $closed_unsold_items,
            "sold_items" => $sold_items,

            "show_back" => false,
            "page_title" => "My Items",
            "show_save" => false
        ]);
    }


    public function manage_images(): void {
        $item = Item::get_by_id($_GET['param1']);       // param1 !!! -> id de open item?
        $error = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_FILES['image']) && is_array($_FILES['image']['name'])) {
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
        }

        $images = $item->get_item_pictures();

        $manage_images = [
            'show_back' => true,
            'backUrl' => 'item/open/' . $item->get_id(),
            'page_title' => "Manage Images",
            'show_save' => false,
            'item' => $item,
            'error' => $error,
            'images' => $images,
        ];
        (new View("manage_images"))->show($manage_images);
    }

    public function move_picture(): void {
        $item_id = $_POST['item'];
        $priority = $_POST['priority'];
        $picture = ItemPicture::get_by_item_and_priority($item_id, $priority);

        if($picture) {
            if (isset($_POST['btn-left'])) {
                $picture->priority_minus();
            } else if (isset($_POST['btn-right'])) {
                $picture->priority_plus();
            } else if (isset($_POST['btn-delete'])) {
                $picture->delete_picture();
            }
        }
        $this->redirect("item", "manage_images", $item_id);
    }
}