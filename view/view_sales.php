<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Sales</title>
        <base href="<?= Configuration::get("web_root") ?>">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
        <link rel="stylesheet" href="<?= Configuration::get("web_root") ?>css/css_sales.css">
    </head>

    <body>
    <header>
        <?php require 'header_menu.php';?>
    </header>

    <main>
        <h2 class="pt-5">Completed Sales</h2>
        <p>A snapshot of the deals you've wrapped up.</p>
        <label class="badge rounded-pill text-bg-success text-white"><?= count($sales) ?> sales</label>

        <div class="container px-4 text-center">
            <div class="row gx-5">
                <div class="col">
                    <div class="card">
                        <h5>TOTAL REVENUE</h5>
                        <p id="res">€ <?= number_format($total, 2, ',', ' ') ?></p>
                        <p id="sales-infos">Accross <?= count($sales) ?> sales</p>
                    </div>
                </div>
                <div class="col">
                    <div class="card">
                        <h5>AVERAGE TICKET</h5>
                        <p id="res">€ <?= number_format($average, 2, ',', ' ') ?></p>
                        <p id="sales-infos">Median buyer appetite indicator</p>
                    </div>
                </div>
                <div class="col">
                    <div class="card">
                        <h5>LOYAL BIDDER</h5>
                        <p id="bidder"><?= $loyal->get_pseudo() ?></p>
                        <p id="sales-infos">Most recurring winning bidder</p>
                    </div>
                </div>
            </div>
        </div>

        <?php if (isset($sales) && count($sales) > 0): ?>
            <div class="row row-cols-md-4 g-4">
                <?php foreach ($sales as $item): ?>
                    <div class="card-item">
                        <?php $open_from = 'sales'; ?>
                        <?php require 'item_card.php'; ?>
                        <div id="item-infos">
                            <p class="text-secondary"><i class="bi bi-currency-dollar"></i> Final price € <?= $item->get_max_bid() ?></p>
                            <p class="text-secondary"><i class="bi bi-trophy"></i> <?= $item->get_winner() ?></p>
                            <p class="text-secondary"><i class="bi bi-clock"></i> Closed on <?= $item->get_end_at() ?></p>
                        </div>
                    </div>
                <?php endforeach;?>
            </div>
        <?php endif; ?>
    </main>

    <footer>
        <?php require 'footer_menu.php'; ?>
    </footer>
    </body>
</html>