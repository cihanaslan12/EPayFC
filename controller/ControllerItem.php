<?php

require_once "framework/Controller.php";
require_once "model/User.php";
require_once "model/Item.php";
require_once "model/ItemPicture.php";
require_once "model/Category.php";
require_once "utils/AppTime.php";
require_once "utils/Functions.php";

class ControllerItem extends Controller
{
    public function index(): void
    {
        $this->get_user_or_redirect();
        $this->browse();
    }

    public function open(): void
    {
        $id = isset($_GET["param1"]) ? intval($_GET["param1"]) : 0;
        $encoded_filter = $_GET["param3"] ?? "";

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


        (new View("openitem"))->show($this->build_openitem_view_data($item, $user, $encoded_filter));
    }

    private function build_openitem_view_data(
        Item    $item,
                $user,
        ?string $encoded_filter,
        ?array  $errors = null,
        ?string $postedAmount = null
    ): array
    {
        $pictures = $item->get_pictures();

        $param2 = $_GET["param2"] ?? null;
        $selectedPriority = (is_numeric($param2)) ? intval($param2) : 0;

        $placeholderPicture = 'img/item_placeholder/item_placeholder.jpg';
        $mainPicture = count($pictures) > 0 ? $pictures[0]->get_picture_path() : $placeholderPicture;
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
        $categories = Category::get_by_item_alphabetically((int)$item->get_id());

        $defaultBid = null;
        if ($item->get_is_auction() === 1 && $isOpen && !$isOwner) {
            if ($item->get_max_bid() !== null) {
                $defaultBid = (string)((float)$item->get_max_bid() + 1);
            } else {
                $defaultBid = $item->get_starting_bid();
            }
        }

        $from = $this->get_open_from();
        $source_back_url = $this->get_back_url_from_source($from);

        $back_url_with_details = $source_back_url;
        if ($encoded_filter !== '' && $encoded_filter != null) {
            $back_url_with_details .= '/' . $encoded_filter;
        }

        $data = [
            'show_back' => true,
            'back_url' => $back_url_with_details,
            'from' => $from,
            'back_filter' => $encoded_filter,
            'url' => $from . ($encoded_filter ? '/' . $encoded_filter : ''),
            'page_title' => "Item open",
            'show_save' => false,

            "item" => $item,
            "user" => $user,

            "pictures" => $pictures,
            "mainPicture" => $mainPicture,

            "bids" => $bids,
            "highestBid" => $highestBid,
            "categories" => $categories,

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

    private function get_back_url_from_source(string $from): string
    {
        return match ($from) {
            'my_items' => 'item/my_items',
            'sales' => 'item/sales',
            'purchases' => 'item/purchases',
            default => 'item/browse',
        };
    }


    public function place_bid(): void
    {
        $user = $this->get_user_or_false();
        if (!$user) {
            $this->redirect("user", "login");
            return;
        }

        $this->require_post();

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
        $encoded_filter = ($_GET['param3'] ?? '');
        $amount = trim($_POST["amount"] ?? "");
        $errors = [];

        if (Bid::place_bid($user, $item, $amount, $now, $errors)) {
            $from = $this->get_open_from();
            $this->redirect_to_open($id, $from, $encoded_filter);
            return;
        }

        (new View("openitem"))->show(
            $this->build_openitem_view_data($item, $user, $encoded_filter, $errors, $amount)
        );
    }


    public function buy_now(): void
    {
        $user = $this->get_user_or_false();
        if (!$user) {
            $this->redirect("user", "login");
            return;
        }

        $this->require_post();

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
        $encoded_filter = $_GET['param3'] ?? '';
        $errors = [];

        if (Bid::buy_now($user, $item, $now, $errors)) {
            $from = $this->get_open_from();
            $this->redirect_to_open($id, $from, $encoded_filter);
            return;
        }

        (new View("openitem"))->show(
            $this->build_openitem_view_data($item, $user, $encoded_filter, $errors, null)
        );
    }


    public function browse(): void
    {
        $user = $this->get_user_or_false();
        $encoded_filter = $_GET['param1'] ?? '';
        if ($user) {
            $my_participations = $user->get_participating_items();
            $others_available = $user->get_other_available_items();
            $browse_view = [
                'user' => $user,
                'my_participations' => $my_participations,
                'others_available' => $others_available,
                'categories' => Category::get_all_by_priority(),
                'encoded_filter' => $encoded_filter,
                'show_back' => false,
                'page_title' => "Browse",
                'show_save' => false
            ];
        } else {
            $all_available_items = Item::get_all_available_items_for_guest();
            $browse_view = [
                'user' => null,
                'all_available_items' => $all_available_items,
                'categories' => Category::get_all_by_priority(),
                'encoded_filter' => $encoded_filter,
                'show_back' => false,
                'page_title' => "Browse",
                'show_save' => false
            ];
        }
        (new View("browse_items"))->show($browse_view);
    }

    public function my_items(): void
    {
        $user = $this->get_user_or_false();
        if (!$user) {
            $this->redirect("user", "login");
            return;
        }

        $encoded_filter = $_GET['param1'] ?? '';

        $active_items = Item::get_my_active_items($user);
        $closed_unsold_items = Item::get_my_closed_unsold_items($user);
        $sold_items = Item::get_my_sold_items($user);

        (new View("my_items"))->show([
            "user" => $user,
            "active_items" => $active_items,
            "closed_unsold_items" => $closed_unsold_items,
            "sold_items" => $sold_items,
            "categories" => Category::get_all_by_priority(),
            "encoded_filter" => $encoded_filter,

            "show_back" => false,
            "page_title" => "My Items",
            "show_save" => false
        ]);
    }

    private function json_response(array $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    private function require_post(bool $json = false): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            if ($json) {
                $this->json_response(['success' => false, 'error' => 'Invalid request method.'], 405);
            }

            $this->error_response("Invalid request method.", 405);
        }
    }

    private function error_response(string $message, int $status = 403): void
    {
        http_response_code($status);
        (new View("error"))->show(["error" => $message]);
        exit;
    }

    private function get_item_or_error(int $item_id): Item
    {
        if ($item_id <= 0) {
            $this->error_response("Item not found.", 404);
        }

        $item = Item::get_by_id($item_id);
        if (!$item) {
            $this->error_response("Item not found.", 404);
        }

        return $item;
    }

    private function require_item_manager(User $user, Item $item): void
    {
        if ((int)$item->get_owner() !== (int)$user->get_id()) {
            $this->error_response("You are not allowed to manage this item.", 403);
        }

        if ((int)($item->get_has_bids() ?? 0) !== 0) {
            $this->error_response("This item can no longer be modified or deleted because bids have been placed.", 403);
        }
    }

    private function json_require_item_manager(User $user, Item $item): void
    {
        if ((int)$item->get_owner() !== (int)$user->get_id()) {
            $this->json_response(['success' => false, 'error' => 'Forbidden.'], 403);
        }

        if ((int)($item->get_has_bids() ?? 0) !== 0) {
            $this->json_response(['success' => false, 'error' => 'This item can no longer be modified because bids have been placed.'], 403);
        }
    }

    private function item_to_search_array(Item $item): array
    {
        return [
            'id' => $item->get_id(),
            'title' => $item->get_title(),
            'owner_pseudo' => $item->get_owner_pseudo(),
            'time_left' => $item->get_time_left(),
            'buy_now_price' => $item->get_buy_now_price(),
            'starting_bid' => $item->get_starting_bid(),
            'has_bids' => (int)($item->get_has_bids() ?? 0),
            'max_bid' => $item->get_max_bid(),
            'is_auction' => (int)($item->get_is_auction() ?? 0),
            'thumbnail' => $item->get_thumbnail() ?: 'img/item_placeholder/item_placeholder.jpg',
            'bidder' => (bool)($item->get_bidder() ?? false),
            'highest_bidder' => (bool)($item->get_highest_bidder() ?? false),
            'picture_count' => count($item->get_item_pictures())
        ];
    }

    private function items_to_search_array(array $items): array
    {
        $result = [];
        foreach ($items as $item) {
            $result[] = $this->item_to_search_array($item);
        }
        return $result;
    }

    public function search_browse(): void
    {
        $query = trim($_POST['query'] ?? '');
        $category_id = max(0, (int)($_POST['category'] ?? 0));
        $user = $this->get_user_or_false();

        if ($user) {
            $sections = [
                [
                    'title' => "Items I'm Participating In",
                    'items' => $this->items_to_search_array(Item::search_participating_items($user, $query, $category_id))
                ],
                [
                    'title' => "Other Available Items",
                    'items' => $this->items_to_search_array(Item::search_other_available_items($user, $query, $category_id))
                ]
            ];
        } else {
            $sections = [
                [
                    'title' => "Available Items",
                    'items' => $this->items_to_search_array(Item::search_available_items_for_guest($query, $category_id))
                ]
            ];
        }

        $this->json_response(['sections' => $sections]);
    }

    public function search_my_items(): void
    {
        $user = $this->get_user_or_false();
        if (!$user) {
            $this->json_response(['error' => 'Authentication required.'], 401);
        }

        $query = trim($_POST['query'] ?? '');
        $category_id = max(0, (int)($_POST['category'] ?? 0));

        $sections = [
            [
                'title' => 'Active Items',
                'items' => $this->items_to_search_array(Item::search_my_active_items($user, $query, $category_id))
            ],
            [
                'title' => 'Closed Unsold Items',
                'items' => $this->items_to_search_array(Item::search_my_closed_unsold_items($user, $query, $category_id))
            ],
            [
                'title' => 'Sold Items',
                'items' => $this->items_to_search_array(Item::search_my_sold_items($user, $query, $category_id))
            ]
        ];

        $this->json_response(['sections' => $sections]);
    }


    public function manage_images(): void
    {
        $user = $this->get_user_or_false();
        if (!$user) {
            $this->redirect("user", "login");
            return;
        }

        $item_id = isset($_GET['param1']) ? (int)$_GET['param1'] : 0;
        $item = $this->get_item_or_error($item_id);
        $this->require_item_manager($user, $item);

        $error = null;
        $from = $_GET['param2'] ?? '';
        $encoded_filter = $_GET['param3'] ?? '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_images'])) {
            if (isset($_FILES['image']) && is_array($_FILES['image']['name'])) {
                $files = $_FILES['image']['name'];

                foreach ($files as $index => $name) {
                    $file_name = $_FILES['image']['name'][$index];
                    $tmp_name = $_FILES['image']['tmp_name'][$index];
                    $size = $_FILES['image']['size'][$index];
                    $file_error = $_FILES['image']['error'][$index];

                    if ($file_error === UPLOAD_ERR_OK) {
                        $extension = Uploader::check_extension($file_name);
                        $valid_size = Uploader::check_size($size);

                        if (!$extension) {
                            $error = "Unsupported image format : jpg/jpeg, png, gif or webp !";
                        } else if (!$valid_size) {
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
            'back_url' => 'item/open/' . $item->get_id() . '/' . $from . ($encoded_filter !== '' ? '/' . $encoded_filter : ''),
            'from' => $from,
            'back_filter' => $encoded_filter,
            'page_title' => "Manage Images",
            'show_save' => false,
            'item' => $item,
            'error' => $error,
            'images' => $images,
        ];

        (new View("manage_images"))->show($manage_images);
    }

    public function move_picture(): void
    {
        $user = $this->get_user_or_false();
        if (!$user) {
            $this->redirect("user", "login");
            return;
        }

        $this->require_post();

        $item_id = isset($_GET['param1']) ? (int)$_GET['param1'] : 0;
        $item = $this->get_item_or_error($item_id);
        $this->require_item_manager($user, $item);

        $posted_item_id = isset($_POST['item']) ? (int)$_POST['item'] : 0;
        if ($posted_item_id !== $item_id) {
            $this->error_response("Invalid item.", 400);
        }

        $priority = isset($_POST['priority']) ? (int)$_POST['priority'] : 0;
        if ($priority <= 0) {
            $this->error_response("Invalid picture.", 400);
        }

        $picture = ItemPicture::get_by_item_and_priority($item_id, $priority);
        if (!$picture) {
            $this->error_response("Picture not found.", 404);
        }

        $from = $_GET['param2'] ?? '';
        $encoded_filter = $_GET['param3'] ?? '';

        if (isset($_POST['btn-left'])) {
            $picture->priority_minus();
        } else if (isset($_POST['btn-right'])) {
            $picture->priority_plus();
        } else if (isset($_POST['btn-delete'])) {
            $picture->delete_picture();
        }

        $this->redirect("item", "manage_images", (string)$item_id, $from, $encoded_filter);
    }

    public function reorder_pictures(): void
    {
        $user = $this->get_user_or_false();
        if (!$user) {
            $this->json_response(['success' => false, 'error' => 'Authentication required.'], 401);
        }

        $this->require_post(true);

        $item_id = isset($_POST['item_id']) ? (int)$_POST['item_id'] : 0;
        $ordered_paths = $_POST['ordered_paths'] ?? [];

        $item = Item::get_by_id($item_id);
        if (!$item) {
            $this->json_response(['success' => false, 'error' => 'Item not found.'], 404);
        }

        $this->json_require_item_manager($user, $item);

        if (!is_array($ordered_paths) || empty($ordered_paths)) {
            $this->json_response(['success' => false, 'error' => 'Invalid image order.'], 400);
        }

        try {
            ItemPicture::reorder_for_item($item_id, $ordered_paths);
        } catch (Throwable $e) {
            $this->json_response(['success' => false, 'error' => 'Invalid image order.'], 400);
        }

        $this->json_response(['success' => true]);
    }

    public function add(): void
    {
        $user = $this->get_user_or_false();
        if (!$user) {
            $this->redirect("user", "login");
            return;
        }
        $user_id = $user->get_id();
        $title = '';
        $description = '';
        $duration = 7;
        $starting_bid = 0.0;
        $instant_purchase_price = 0.0;
        $direct_sale_price = 0.0;
        $instant_or_direct = 0.0;
        $selected_category_ids = [];
        $errors = [];

        if (isset($_POST['save'])) {
            $title = $_POST['title'];
            $description = $_POST['description'];
            $duration = $_POST['duration'];
            $starting_bid = (float)$_POST['start_bid'];
            $instant_purchase_price = (float)$_POST['inst_purch_price'];
            $direct_sale_price = (float)$_POST['dir_sale_price'];
            $selected_category_ids = Category::normalize_ids($_POST['categories'] ?? []);

            $errors = Item::validations($user_id, $title, $description, $duration, $starting_bid, $instant_purchase_price, $direct_sale_price);
            if ($category_error = Category::validate_item_categories($selected_category_ids)) {
                $errors['categories'] = $category_error;
            }
            if (empty($errors)) {
                if ($direct_sale_price && !$instant_purchase_price)
                    $instant_or_direct = $direct_sale_price;
                else if ($instant_purchase_price && !$direct_sale_price)
                    $instant_or_direct = $instant_purchase_price;
                $new_item_id = Item::insert_into_db($user_id, $title, $description, $duration, $starting_bid, $instant_or_direct);
                Category::set_for_item($new_item_id, $selected_category_ids);
                $this->redirect_to_open($new_item_id, 'my_items', null);
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
            'categories' => Category::get_all_by_priority(),
            'selected_category_ids' => $selected_category_ids,
            'errors' => $errors,
        ];

        (new View("add_edit_item"))->show($add_item);
    }

    public function edit(): void
    {
        $user = $this->get_user_or_false();
        if (!$user) {
            $this->redirect("user", "login");
            return;
        }

        $item_id = isset($_GET['param1']) ? (int)$_GET['param1'] : 0;
        $item = $this->get_item_or_error($item_id);
        $this->require_item_manager($user, $item);

        $from = $_GET['param2'] ?? 'my_items';
        $encoded_filter = $_GET['param3'] ?? '';

        $title = $item->get_title();
        $description = $item->get_description();
        $duration = $item->get_duration_days();
        $starting_bid = $item->get_starting_bid();
        $selected_category_ids = Category::get_ids_for_item((int)$item_id);

        $instant_purchase_price = 0.0;
        $direct_sale_price = 0.0;
        $instant_or_direct = 0.0;

        $buy_now_price = $item->get_buy_now_price();
        if ($buy_now_price > 0) {
            if ($item->get_is_auction() === 1) {
                $instant_purchase_price = $buy_now_price;
            } else if ($item->get_is_direct_sale() === 1) {
                $direct_sale_price = $buy_now_price;
            }
        }

        $errors = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save'])) {
            $title = $_POST['title'] ?? '';
            $description = $_POST['description'] ?? '';
            $duration = $_POST['duration'] ?? '';
            $starting_bid = (float)($_POST['start_bid'] ?? 0);
            $instant_purchase_price = (float)($_POST['inst_purch_price'] ?? 0);
            $direct_sale_price = (float)($_POST['dir_sale_price'] ?? 0);
            $selected_category_ids = Category::normalize_ids($_POST['categories'] ?? []);

            $errors = Item::validations(
                $user->get_id(),
                $title,
                $description,
                $duration,
                $starting_bid,
                $instant_purchase_price,
                $direct_sale_price,
                $item_id
            );
            if ($category_error = Category::validate_item_categories($selected_category_ids)) {
                $errors['categories'] = $category_error;
            }

            if (empty($errors)) {
                if ($direct_sale_price && !$instant_purchase_price) {
                    $instant_or_direct = $direct_sale_price;
                } else if ($instant_purchase_price && !$direct_sale_price) {
                    $instant_or_direct = $instant_purchase_price;
                }
                Category::set_for_item((int)$item_id, $selected_category_ids);

                Item::update_into_db($item_id, $title, $description, (int)$duration, $starting_bid, $instant_or_direct);
                $this->redirect_to_open($item_id, $from, $encoded_filter);
                return;
            }
        }

        (new View("add_edit_item"))->show([
            'user' => $user,
            'show_back' => true,
            'back_url' => 'item/open/' . $item_id . '/' . $from . ($encoded_filter !== '' ? '/' . $encoded_filter : ''),
            'from' => $from,
            'back_filter' => $encoded_filter,
            'page_title' => "Edit item",
            'show_save' => true,
            'item' => $item,
            'title' => $title,
            'description' => $description,
            'duration' => $duration,
            'starting_bid' => $starting_bid,
            'instant_purchase_price' => $instant_purchase_price,
            'direct_sale_price' => $direct_sale_price,
            'categories' => Category::get_all_by_priority(),
            'selected_category_ids' => $selected_category_ids,
            'errors' => $errors,
        ]);
    }

    public function validation_config(): void {
        $this->json_response([
            'title' => [
                'min' => (int) Configuration::get('TITLE_MIN_LENGTH'),
                'max' => (int) Configuration::get('TITLE_MAX_LENGTH'),
            ],
            'description' => [
                'min' => (int) Configuration::get('DESCR_MIN_LENGTH'),
            ],
            'duration' => [
                'min' => (int) Configuration::get('DURATION_MIN'),
                'max' => (int) Configuration::get('DURATION_MAX'),
            ],
            'price' => [
                'min' => (float) Configuration::get('PRICE_MIN'),
            ],
        ]);
    }

    public function validate_title(): void {
        $user = $this->get_user_or_false();
        if (!$user) {
            $this->json_response(['error' => 'Authentication required.'], 401);
        }

        $title = trim($_POST['title'] ?? '');
        $item_id = isset($_POST['item_id']) && $_POST['item_id'] !== ''
            ? (int) $_POST['item_id']
            : null;

        $errors = Item::validate_title_uniqueness($user->get_id(), $title, $item_id);

        $this->json_response([
            'valid' => empty($errors),
            'errors' => $errors
        ]);
    }

    public function delete(): void
    {
        $user = $this->get_user_or_false();
        if (!$user) {
            $this->redirect("user", "login");
            return;
        }

        $item_id = isset($_GET['param1']) ? (int)$_GET['param1'] : 0;
        $item = $this->get_item_or_error($item_id);
        $this->require_item_manager($user, $item);

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete'])) {
            $item->delete_item_with_dependencies();
            $this->redirect("item", "my_items");
            return;
        }

        (new View("delete_confirm"))->show(['item' => $item]);
    }

    public function sales(): void {
        $user = $this->get_user_or_redirect();
        if (!$user) {
            throw new Exception("Veuillez vous connecter pour visualiser vos ventes");
        } else {
            $sales = $user->get_my_sold_items();
            $total = $user->get_my_sold_items_total();
            $average = $user->get_average_ticket();
            $loyal = $user->get_loyal_bidder();
            usort($sales, function(Item $a, Item $b) {
                return strcmp($b->get_finish_time(), $a->get_finish_time());
            });
            (new View("sales"))->show([
                'user' => $user,
                'show_back' => true,
                'back_url' => 'user/profile',
                'page_title' => 'Sales',
                'show_save' => false,
                'sales' => $sales,
                'total' => $total,
                'average' => $average,
                'loyal' => $loyal
            ]);
        }
    }

    public function purchases(): void {
        $user = $this->get_user_or_false();
        if (!$user) {
            $this->redirect("user", "login");
            return;
        }

        $purchases = Item::get_purchases($user);

        $stats = Item::get_purchase_stats($user);
        usort($purchases, function(Item $a, Item $b) {
            return strcmp($b->get_finish_time(), $a->get_finish_time());
        });
        (new View("purchases"))->show([
            "user" => $user,
            "purchases" => $purchases,
            "show_back" => true,
            "back_url" => 'user/profile',
            "page_title" => "Purchases",
            "show_save" => false,
            "stats" => $stats
        ]);
    }

    private function get_open_from(): string {
        $allowed = ['browse', 'my_items', 'sales', 'purchases'];

        $param2 = $_GET['param2'] ?? null;
        $param3 = $_GET['param3'] ?? null;
        $postFrom = $_POST['from'] ?? null;

        if (is_string($param3) && in_array($param3, $allowed, true)) {
            return $param3;
        }

        if (is_string($param2) && in_array($param2, $allowed, true)) {
            return $param2;
        }

        if (is_string($postFrom) && in_array($postFrom, $allowed, true)) {
            return $postFrom;
        }

        return 'browse';
    }

    private function redirect_to_open(int $itemId, string $from, ?string $encoded_filter, ?int $priority = null): void {
        $web_root = Configuration::get("web_root");

        $url = $web_root . "item/open/" . $itemId;

        if ($priority !== null) {
            $url .= "/" . $priority . "/" . $from;
        } else {
            $url .= "/" . $from;
            if ($encoded_filter !== null) {
                $url .= "/" . $encoded_filter;
            }
        }

        header("Location: $url", true, 303);
        die();
    }

    public function get_pictures_service(): void
    {
        $item_id = isset($_GET['param1']) ? (int)$_GET['param1'] : 0;
        $item = Item::get_by_id($item_id);

        if (!$item) {
            $this->json_response(['error' => 'Item not found.'], 404);
        }

        $pictures_paths = [];
        foreach ($item->get_item_pictures() as $picture) {
            $pictures_paths[] = $picture->get_picture_path();
        }

        $this->json_response($pictures_paths);
    }

    public function encode_filter_service(): void
    {
        $query = trim($_POST['query'] ?? ($_POST['filter'] ?? ''));
        $category = max(0, (int)($_POST['category'] ?? 0));

        if ($query === '' && $category === 0) {
            $this->json_response(['encoded' => '']);
        }

        $filter_state = [
            'query' => $query,
            'category' => $category
        ];

        $encoded = Functions::url_safe_encode($filter_state);
        $this->json_response(['encoded' => $encoded]);
    }

    public function decode_filter_service(): void
    {
        $encoded = $_POST['encoded_filter'] ?? '';

        if ($encoded === '') {
            $this->json_response([
                'decoded' => [
                    'query' => '',
                    'category' => 0
                ]
            ]);
        }

        $decoded = Functions::url_safe_decode($encoded);

        if (is_string($decoded)) {
            $decoded = [
                'query' => $decoded,
                'category' => 0
            ];
        }

        if (!is_array($decoded)) {
            $decoded = [
                'query' => '',
                'category' => 0
            ];
        }

        $decoded = [
            'query' => trim((string)($decoded['query'] ?? '')),
            'category' => max(0, (int)($decoded['category'] ?? 0))
        ];

        $this->json_response(['decoded' => $decoded]);
    }
}