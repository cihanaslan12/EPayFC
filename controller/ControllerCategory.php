<?php

require_once "framework/Controller.php";
require_once "model/User.php";
require_once "model/Category.php";

class ControllerCategory extends Controller
{
    public function index(): void
    {
        $this->manage_categories();
    }

    private function get_admin_or_stop(): User
    {
        $user = $this->get_user_or_false();

        if (!$user) {
            $this->redirect("user", "login");
        }

        if ($user->get_role() !== 'admin') {
            http_response_code(403);
            (new View("error"))->show([
                "error" => "You must be an administrator to manage categories."
            ]);
            exit;
        }

        return $user;
    }

    private function show_manage_categories(User $user, array $extra = []): void
    {
        $data = array_merge([
            'user' => $user,
            'categories' => Category::get_all_by_priority(),
            'errors' => [],
            'submitted_names' => [],
            'add_name' => '',
            'show_back' => false,
            'page_title' => 'Manage Categories',
            'show_save' => false
        ], $extra);

        (new View("manage_categories"))->show($data);
    }

    public function manage_categories(): void
    {
        $user = $this->get_admin_or_stop();
        $this->show_manage_categories($user);
    }

    public function add(): void
    {
        $user = $this->get_admin_or_stop();

        $name = trim($_POST['category_name'] ?? '');
        $name_errors = Category::validate_name($name);

        if (!empty($name_errors)) {
            $this->show_manage_categories($user, [
                'errors' => ['add' => $name_errors],
                'add_name' => $name
            ]);
            return;
        }

        Category::create($name);
        $this->redirect("category", "manage_categories");
    }

    public function update(): void
    {
        $user = $this->get_admin_or_stop();

        $id = isset($_GET['param1']) ? (int)$_GET['param1'] : 0;
        $category = Category::get_by_id($id);

        if (!$category) {
            http_response_code(404);
            (new View("error"))->show(["error" => "Category not found."]);
            return;
        }

        $name = trim($_POST['category_name'] ?? '');
        $name_errors = Category::validate_name($name, $id);

        if (!empty($name_errors)) {
            $this->show_manage_categories($user, [
                'errors' => ['edit' => [$id => $name_errors]],
                'submitted_names' => [$id => $name]
            ]);
            return;
        }

        Category::update_name($id, $name);
        $this->redirect("category", "manage_categories");
    }

    public function move_up(): void
    {
        $this->get_admin_or_stop();

        $id = isset($_GET['param1']) ? (int)$_GET['param1'] : 0;
        Category::move_up($id);

        $this->redirect("category", "manage_categories");
    }

    public function move_down(): void
    {
        $this->get_admin_or_stop();

        $id = isset($_GET['param1']) ? (int)$_GET['param1'] : 0;
        Category::move_down($id);

        $this->redirect("category", "manage_categories");
    }

    public function delete_confirm(): void
    {
        $user = $this->get_admin_or_stop();

        $id = isset($_GET['param1']) ? (int)$_GET['param1'] : 0;
        $category = Category::get_by_id($id);

        if (!$category) {
            http_response_code(404);
            (new View("error"))->show(["error" => "Category not found."]);
            return;
        }

        if ((int)$category->get_item_count() > 0) {
            $this->show_manage_categories($user, [
                'errors' => ['delete' => "You cannot delete a category that contains items."]
            ]);
            return;
        }

        if (isset($_POST['delete'])) {
            Category::delete_by_id($id);
            $this->redirect("category", "manage_categories");
        }

        (new View("delete_category_confirm"))->show([
            'user' => $user,
            'category' => $category,
            'show_back' => true,
            'back_url' => 'category/manage_categories',
            'page_title' => 'Delete Category',
            'show_save' => false
        ]);
    }
}