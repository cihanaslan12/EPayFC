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
    <div id="item-search-config"
         data-search-url="item/search_my_items"
         data-open-from="my_items"></div>

    <div id="item-search-box" class="d-none pt-5 search-box-wrapper">
        <input
                type="text"
                id="item-search-input"
                class="form-control"
                placeholder="Search my items...">
    </div>

    <div id="item-search-empty" class="d-none no-item-found mt-4">
        No item found.
    </div>

    <div id="item-search-sections">

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
    </div>
</main>

<footer>
    <?php require 'footer_menu.php'; ?>
</footer>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="<?= Configuration::get("web_root") ?>js/item_search.js"></script>
</body>
</html>
