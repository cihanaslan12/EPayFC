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

<body>
<header>
    <?php require 'header_menu.php';?>
</header>

<main class="p-3 m-3">
    <h2 class="pt-3">Purchases</h2>

    <?php if (isset($stats)): ?>
        <div class="card p-3 mb-3">
            <p class="mb-1"><strong>Total spent:</strong> <?= $stats["total_spent"] ?></p>
            <p class="mb-1"><strong>Average price:</strong> <?= $stats["avg_spent"] ?></p>

            <?php if ($stats["top_seller_pseudo"] !== null): ?>
                <p class="mb-0"><strong>Most loyal seller:</strong> <?= $stats["top_seller_pseudo"] ?> (<?= $stats["top_seller_count"] ?>)</p>
            <?php else: ?>
                <p class="mb-0"><strong>Most loyal seller:</strong> -</p>
            <?php endif; ?>
        </div>
    <?php endif; ?>


    <?php if (!empty($purchases)): ?>
        <div class="row row-cols-md-4 g-4 pb-5">
            <?php foreach ($purchases as $item): ?>
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

