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

        $user = $this->get_user_or_false(); // guest autorisé


        (new View("openitem"))->show($this->build_openitem_view_data($item, $user));
    }

    private function build_openitem_view_data(
        Item $item,
             $user,
        ?array $errors = null,
        ?string $postedAmount = null
    ): array {
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

        $now = AppTime::get_current_datetime();
        $isOpen = $item->is_open($now);

        $isOwner = $user && $user->get_id() === $item->get_owner();
        $canManage = $isOwner && ((int)($item->get_bid_count() ?? 0) === 0);

        $bids = $item->get_bids();
        $highestBid = Bid::get_highest_for_item($item->get_id());

        $defaultBid = null;
        if ($item->get_is_auction() === 1 && $isOpen && !$isOwner) {
            if ($item->get_max_bid() !== null) {
                $defaultBid = (string)((float)$item->get_max_bid() + 1);
            } else {
                $defaultBid = $item->get_starting_bid();
            }
        }

        $data = [
            'show_back' => true,
            'backUrl' => 'item/browse',
            'page_title' => "Item open",
            'show_save' => false,

            "item" => $item,
            "user" => $user,

            "pictures" => $pictures,
            "mainPicture" => $mainPicture,

            "bids" => $bids,
            "highestBid" => $highestBid,

            "now" => $now,
            "isOpen" => $isOpen,
            "isOwner" => $isOwner,
            "canManage" => $canManage,
            "defaultBid" => $defaultBid,
        ];

        if ($errors !== null) {
            $data["errors"] = $errors;
        }
        if ($postedAmount !== null) {
            $data["postedAmount"] = $postedAmount;
        }

        return $data;
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

        (new View("openitem"))->show(
            $this->build_openitem_view_data($item, $user, $errors, $amount)
        );
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

        if (Bid::buy_now($user, $item, $now, $errors)) {
            $this->redirect("item", "open", $id);
            return;
        }

        (new View("openitem"))->show(
            $this->build_openitem_view_data($item, $user, $errors, null)
        );
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
        $user = $this->get_user_or_redirect();
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
            'user' => $user,
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

    public function add(): void {
        $user = $this->get_user_or_false();
        $user_id = $user->get_id();
        $title = '';
        $description = '';
        $duration = 7;
        $starting_bid = 0.0;
        $instant_purchase_price = 0.0;
        $direct_sale_price = 0.0;
        $instant_or_direct = 0.0;
        $errors = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $title = $_POST['title'];
            $description = $_POST['description'];
            $duration = $_POST['duration'];
            $starting_bid = (float)$_POST['start_bid'];
            $instant_purchase_price = (float)$_POST['inst_purch_price'];
            $direct_sale_price = (float)$_POST['dir_sale_price'];

            $errors = Item::validations($title,$description, $starting_bid, $instant_purchase_price, $direct_sale_price);
            if (empty($errors)) {
                if ($direct_sale_price && !$instant_purchase_price)
                    $instant_or_direct = $direct_sale_price;
                else if ($instant_purchase_price && !$direct_sale_price)
                    $instant_or_direct = $instant_purchase_price;
                $new_item_id = Item::insert_into_db($user_id, $title, $description, $duration, $starting_bid, $instant_or_direct);
                $this->redirect("item", "open", $new_item_id);
            }
        }
        $add_item = [
            'user' => $user,
            'show_back' => true,
            'page_title' => "Add item",
            'show_save' => true,
            'title' => $title,
            'description' => $description,
            'duration' => $duration,
            'starting_bid' => $starting_bid,
            'instant_purchase_price' => $instant_purchase_price,
            'direct_sale_price' => $direct_sale_price,
            'errors' => $errors,
        ];

        (new View("add_edit_item"))->show($add_item);
    }

    public function edit(): void {
        $user = $this->get_user_or_false();
        $item_id = $_GET['param1'];
        $item = Item::get_by_id($item_id);

        $title = $item->get_title();
        $description = $item->get_description();
        $duration = $item->get_duration_days();
        $starting_bid = $item->get_starting_bid();

        $instant_purchase_price = 0.0;
        $direct_sale_price = 0.0;
        $instant_or_direct = 0.0;

        $buy_now_price = $item->get_buy_now_price();
        $is_auction = $item->get_is_auction();
        $is_direct_sale = $item->get_is_direct_sale();
        if ($buy_now_price > 0) {
            if ($is_auction === 1) {
                $instant_purchase_price = $buy_now_price;
            } else if ($is_direct_sale === 1) {
                $direct_sale_price = $buy_now_price;
            }
        }
        $errors = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $title = $_POST['title'];
            $description = $_POST['description'];
            $duration = $_POST['duration'];
            $starting_bid = (float)$_POST['start_bid'];
            $instant_purchase_price = (float)$_POST['inst_purch_price'];
            $direct_sale_price = (float)$_POST['dir_sale_price'];

            $errors = Item::validations($title, $description, $starting_bid, $instant_purchase_price, $direct_sale_price);
            if (empty($errors)) {
                if ($direct_sale_price && !$instant_purchase_price)
                    $instant_or_direct = $direct_sale_price;
                else if ($instant_purchase_price && !$direct_sale_price)
                    $instant_or_direct = $instant_purchase_price;
                Item::update_into_db($item_id, $title, $description, $duration, $starting_bid, $instant_or_direct);
                $this->redirect("item", "open", $item_id);
            }
        }

        $edit_item = [
            'user' => $user,
            'show_back' => true,
            'page_title' => "Edit item",
            'show_save' => true,
            'item' => $item,
            'title' => $title,
            'description' => $description,
            'duration' => $duration,
            'starting_bid' => $starting_bid,
            'instant_purchase_price' => $instant_purchase_price,
            'direct_sale_price' => $direct_sale_price,
            'errors' => $errors,
        ];
        (new View("add_edit_item"))->show($edit_item);
    }

    public function delete(): void {
        $item_id = $_GET['param1'];
        $item = Item::get_by_id($item_id);
        (new View("delete_confirm"))->show(['item' => $item]);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $item->delete_item_with_dependencies();
            $this->redirect("item", "my_items");
        }
    }

    public function sales(): void {
        $user = $this->get_user_or_redirect();
        $sales = $user->get_my_sold_items();
        $total = $user->get_my_sold_items_total();
        $average = $user->get_average_ticket();
        $loyal = $user->get_loyal_bidder();
        (new View("sales"))->show([
            'user' => $user,
            'show_back' => true,
            'backUrl' => 'user/profile',
            'page_title' => 'Sales',
            'show_save' => false,
            'sales' => $sales,
            'total' => $total,
            'average' => $average,
            'loyal' => $loyal
        ]);
    }

    public function purchases(): void {
        $user = $this->get_user_or_false();
        if (!$user) {
            $this->redirect("main", "login");
            return;
        }

        $purchases = Item::get_purchases($user);

        $stats = Item::get_purchase_stats($user);

        (new View("purchases"))->show([
            "user" => $user,
            "purchases" => $purchases,
            "show_back" => true,
            'backUrl' => 'user/profile',
            "page_title" => "Purchases",
            "show_save" => false,
            "stats" => $stats
        ]);
    }

}