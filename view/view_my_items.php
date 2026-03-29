<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Items</title>
    <base href="<?= Configuration::get("web_root") ?>">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= Configuration::get("web_root") ?>css/css_browse_items.css">
</head>

<body>
<header>
    <?php require 'header_menu.php';?>
</header>

<main class="p-3 m-3">
    <?php if (isset($active_items) && count($active_items) > 0): ?>
        <h2 class="pt-5">Active Items</h2>
        <div class="row row-cols-md-4 g-4">
            <?php foreach ($active_items as $item): ?>
                <?php $open_from = 'my_items'; ?>
                <?php require 'item_card.php'; ?>
            <?php endforeach;?>
        </div>
    <?php endif; ?>

    <?php if (isset($closed_unsold_items) && count($closed_unsold_items) > 0): ?>
        <h2 class="pt-3">Closed Unsold Items</h2>
        <div class="row row-cols-md-4 g-4">
            <?php foreach ($closed_unsold_items as $item): ?>
                <?php $open_from = 'my_items'; ?>
                <?php require 'item_card.php'; ?>
            <?php endforeach;?>
        </div>
    <?php endif; ?>

    <?php if (isset($sold_items) && count($sold_items) > 0): ?>
        <h2 class="pt-3">Sold Items</h2>
        <div class="row row-cols-md-4 g-4 pb-5">
            <?php foreach ($sold_items as $item): ?>
                <?php $open_from = 'my_items'; ?>
                <?php require 'item_card.php'; ?>
            <?php endforeach;?>
        </div>
    <?php endif; ?>
</main>

<footer>
    <?php require 'footer_menu.php'; ?>
</footer>
</body>
</html>
