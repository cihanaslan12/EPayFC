<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Category</title>
    <base href="<?= Configuration::get("web_root") ?>">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= Configuration::get("web_root") ?>css/css_delete_category_confirm.css">
</head>

<body>
<header>
    <?php require 'header_menu.php'; ?>
</header>

<main class="delete-category-page">
    <form action="category/delete_confirm/<?= $category->get_id() ?>" method="POST">
        <div class="card delete-category-card text-center">
            <div class="card-body">
                <i class="bi bi-trash delete-category-icon"></i>

                <h5 class="card-title">Are you sure?</h5>

                <p class="card-text">
                    Do you want to delete category
                    <strong>"<?= htmlspecialchars($category->get_name()) ?>"</strong>?
                </p>

                <p class="text-white-50">This process cannot be undone.</p>

                <div class="d-flex justify-content-center gap-2">
                    <a href="category/manage_categories" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-danger" name="delete">Delete</button>
                </div>
            </div>
        </div>
    </form>
</main>

<footer>
    <?php require 'footer_menu.php'; ?>
</footer>
</body>
</html>