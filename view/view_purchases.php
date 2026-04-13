<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Purchases</title>
    <base href="<?= Configuration::get("web_root") ?>">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= Configuration::get("web_root") ?>css/css_browse_items.css">
</head>
<style>
    body {padding-bottom: 50px;}
</style>
<body>
<header>
    <?php require 'header_menu.php';?>
</header>

<main class="p-3 m-3 pt-5">
    <h2 class="pt-3">Purchases</h2>

    <span class="badge text-bg-light border mb-2">
        <?= (int)$stats["count_purchases"] ?> purchase(s)
    </span>

    <?php if (isset($stats)): ?>
        <div class="card mb-3">
            <div class="card-body">
                <div class="d-flex flex-wrap gap-2 align-items-center justify-content-between">
                <span class="badge text-bg-primary p-2">
                    Total spent: <?= number_format((float)$stats["total_spent"], 2, '.', '') ?>
                </span>

                    <span class="badge text-bg-secondary p-2">
                    Average price: <?= number_format((float)$stats["avg_spent"], 2, '.', '') ?>
                </span>

                    <span class="badge text-bg-success p-2">
                    Most loyal seller:
                    <?php if ($stats["top_seller_pseudo"] !== null): ?>
                        <?= $stats["top_seller_pseudo"] ?> (<?= (int)$stats["top_seller_count"] ?>)
                    <?php else: ?>
                        -
                    <?php endif; ?>
                </span>
                </div>
            </div>
        </div>
    <?php endif; ?>


    <?php if (!empty($purchases)): ?>
        <div class="row row-cols-md-4 g-4 pb-5">
            <?php foreach ($purchases as $item): ?>
                <?php $open_from = 'purchases'; ?>
                <?php require 'item_card.php'; ?>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p class="mt-3"><em>No purchases yet.</em></p>
    <?php endif; ?>
</main>

<footer>
    <?php require 'footer_menu.php'; ?>
</footer>
</body>
</html>

