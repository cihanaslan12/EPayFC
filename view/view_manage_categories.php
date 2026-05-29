<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Categories</title>
    <base href="<?= Configuration::get("web_root") ?>">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= Configuration::get("web_root") ?>css/css_manage_categories.css">
</head>
<body>
<header>
    <?php require 'header_menu.php'; ?>
</header>

<main class="container py-5 my-4">
    <?php if (isset($errors['delete'])): ?>
        <div class="alert alert-danger">
            <?= htmlspecialchars($errors['delete']) ?>
        </div>
    <?php endif; ?>

    <div class="card category-card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span><i class="bi bi-tags"></i> Categories</span>
            <span class="small text-white-50">Priority order</span>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-dark table-hover align-middle mb-0">
                    <thead>
                    <tr>
                        <th class="text-center">Order</th>
                        <th>Name</th>
                        <th class="text-center">Items</th>
                        <th class="text-center">Move</th>
                        <th class="text-center">Save</th>
                        <th class="text-center">Delete</th>
                    </tr>
                    </thead>

                    <tbody>
                    <?php foreach ($categories as $index => $category): ?>
                        <?php
                        $category_id = (int)$category->get_id();
                        $category_errors = $errors['edit'][$category_id] ?? [];
                        $input_value = $submitted_names[$category_id] ?? $category->get_name();
                        $is_first = $index === 0;
                        $is_last = $index === count($categories) - 1;
                        $item_count = (int)$category->get_item_count();
                        ?>

                        <tr>
                            <td class="text-center fw-bold">
                                <?= (int)$category->get_priority() ?>
                            </td>

                            <td>
                                <form id="edit-category-<?= $category_id ?>" action="category/update/<?= $category_id ?>" method="POST">
                                    <input
                                        type="text"
                                        name="category_name"
                                        class="form-control <?= !empty($category_errors) ? 'is-invalid' : '' ?>"
                                        value="<?= htmlspecialchars($input_value) ?>">

                                    <?php if (!empty($category_errors)): ?>
                                        <div class="text-danger small mt-1">
                                            <?php foreach ($category_errors as $error): ?>
                                                <div><?= htmlspecialchars($error) ?></div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </form>
                            </td>

                            <td class="text-center">
                                <span class="badge text-bg-secondary"><?= $item_count ?></span>
                            </td>

                            <td class="text-center move-buttons">
                                <form action="category/move_up/<?= $category_id ?>" method="POST" class="d-inline">
                                    <button type="submit" class="btn btn-sm btn-outline-light" <?= $is_first ? 'disabled' : '' ?>>
                                        <i class="bi bi-arrow-up"></i>
                                    </button>
                                </form>

                                <form action="category/move_down/<?= $category_id ?>" method="POST" class="d-inline">
                                    <button type="submit" class="btn btn-sm btn-outline-light" <?= $is_last ? 'disabled' : '' ?>>
                                        <i class="bi bi-arrow-down"></i>
                                    </button>
                                </form>
                            </td>

                            <td class="text-center">
                                <button type="submit" form="edit-category-<?= $category_id ?>" class="btn btn-sm btn-primary">
                                    <i class="bi bi-save"></i>
                                </button>
                            </td>

                            <td class="text-center">
                                <?php if ($item_count === 0): ?>
                                    <a href="category/delete_confirm/<?= $category_id ?>" class="btn btn-sm btn-danger">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                <?php else: ?>
                                    <button type="button" class="btn btn-sm btn-danger" disabled title="Category contains items">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>

                    <tr class="add-row">
                        <td class="text-center">
                            <i class="bi bi-plus-circle"></i>
                        </td>

                        <td colspan="3">
                            <form id="add-category-form" action="category/add" method="POST">
                                <input
                                    type="text"
                                    name="category_name"
                                    class="form-control <?= !empty($errors['add']) ? 'is-invalid' : '' ?>"
                                    value="<?= htmlspecialchars($add_name ?? '') ?>"
                                    placeholder="New category name">

                                <?php if (!empty($errors['add'])): ?>
                                    <div class="text-danger small mt-1">
                                        <?php foreach ($errors['add'] as $error): ?>
                                            <div><?= htmlspecialchars($error) ?></div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </form>
                        </td>

                        <td class="text-center" colspan="2">
                            <button type="submit" form="add-category-form" class="btn btn-sm btn-success">
                                <i class="bi bi-plus"></i> Add
                            </button>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<footer>
    <?php require 'footer_menu.php'; ?>
</footer>
</body>
</html>
