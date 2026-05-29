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
            <div id="manage-categories-config"
                 data-add-url="category/add_service"
                 data-update-url="category/update_service"
                 data-delete-url="category/delete_service"
                 data-reorder-url="category/reorder_service"
                 data-name-min="<?= (int)Configuration::get('CATEGORY_NAME_MIN_LENGTH', '3') ?>"
                 data-name-max="<?= (int)Configuration::get('CATEGORY_NAME_MAX_LENGTH', '25') ?>">
            </div>

            <div id="categories-js" class="d-none">

                <div id="categories-list">
                    <?php foreach ($categories as $category): ?>
                        <?php
                        $category_id = (int)$category->get_id();
                        $item_count = (int)$category->get_item_count();
                        ?>

                        <div class="category-js-row"
                             data-category-id="<?= $category_id ?>"
                             data-item-count="<?= $item_count ?>">
                            <div class="category-drag-handle">
                                <i class="bi bi-grip-vertical"></i>
                            </div>

                            <div class="category-main">
                                <div class="category-name-display">
                                    <?= htmlspecialchars($category->get_name()) ?>
                                </div>

                                <input type="text"
                                       class="form-control category-name-input d-none"
                                       value="<?= htmlspecialchars($category->get_name()) ?>">

                                <div class="category-error text-danger small mt-1"></div>
                            </div>

                            <div class="category-count">
                                <span class="badge text-bg-secondary"><?= $item_count ?></span>
                            </div>

                            <div class="category-actions">
                                <button type="button" class="btn btn-sm btn-primary edit-category-btn">
                                    <i class="bi bi-pencil"></i>
                                </button>

                                <?php if ($item_count === 0): ?>
                                    <button type="button" class="btn btn-sm btn-danger delete-category-btn">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                <?php else: ?>
                                    <button type="button" class="btn btn-sm btn-danger" disabled title="Category contains items">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="d-flex justify-content-end mb-3">
                    <button type="button" class="btn btn-success" id="show-add-category">
                        <i class="bi bi-plus"></i> Add category
                    </button>
                </div>

                <div class="category-js-row d-none" id="add-category-row">
                    <div class="category-drag-handle invisible">
                        <i class="bi bi-grip-vertical"></i>
                    </div>

                    <div class="category-main">
                        <input type="text"
                               class="form-control"
                               id="new-category-name"
                               placeholder="New category name">
                        <div class="category-error text-danger small mt-1"></div>
                    </div>

                    <div class="category-count">
                        <span class="badge text-bg-secondary">0</span>
                    </div>

                    <div class="category-actions">
                        <button type="button" class="btn btn-sm btn-secondary" id="cancel-add-category">
                            Cancel
                        </button>
                    </div>
                </div>

            </div>

            <div class="table-responsive" id="categories-nojs">
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

<div class="modal fade" id="deleteCategoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-dark text-white">
            <div class="modal-header">
                <h5 class="modal-title">Delete category</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <p>
                    Do you really want to delete category
                    <strong id="delete-category-name"></strong>?
                </p>
                <p class="text-white-50 mb-0">This action cannot be undone.</p>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    Cancel
                </button>
                <button type="button" class="btn btn-danger" id="confirm-delete-category">
                    Delete
                </button>
            </div>
        </div>
    </div>
</div>

<footer>
    <?php require 'footer_menu.php'; ?>
</footer>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://code.jquery.com/ui/1.14.1/jquery-ui.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui-touch-punch/0.2.3/jquery.ui.touch-punch.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.min.js"></script>
<script src="<?= Configuration::get("web_root") ?>js/manage_categories.js"></script>
</body>
</html>
