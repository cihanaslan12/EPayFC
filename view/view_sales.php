<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Sales</title>
        <base href="<?= Configuration::get("web_root") ?>">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    </head>

    <body>
    <header>
        <?php require 'header_menu.php';?>
    </header>

    <main class="p-3 m-5">
        <div class="card">
            <h2 class="pt-5">Completed Sales</h2>
            <p>A snapshot of the deals you've wrapped up.</p>
            <label class="badge rounded-pill text-bg-success text-white"><?= count($sales) ?> sales</label>
        </div>

        <div class="card">
            <h2>TOTAL REVENUE</h2>
            <p>€ <?= number_format($total, 2, ',', ' ') ?></p>
            <p>Accross <?= count($sales) ?> sales</p>
        </div>

        <div class="card">
            <h2>AVERAGE TICKET</h2>
            <p>€ <?= number_format($average, 2, ',', ' ') ?></p>
            <p>Median buyer appetite indicator</p>
        </div>

        <div class="card">
            <h2>LOYAL BIDDER</h2>
            <p>€ <?= $loyal->get_pseudo() ?></p>
            <p>Most recurring winning bidder</p>
        </div>

        <?php if (isset($sales) && count($sales) > 0): ?>
            <div class="row row-cols-md-4 g-4">
                <?php foreach ($sales as $item): ?>
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