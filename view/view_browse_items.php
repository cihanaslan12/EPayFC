<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Browse items</title>
        <base href="<?= Configuration::get("web_root") ?>">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    </head>

    <body>
        <header>
            <?php require 'header_menu.php';?>
        </header>

        <main class="p-3 m-3">
            <h2 class="pt-5">Items I'm Participating In</h2>
            <div class="row row-cols-md-4 g-4">
                <?php if(isset($my_participations)): ?>
                    <?php foreach ($my_participations as $item): ?>
                        <div class="card">
                            <svg aria-label="Placeholder: Image cap" class="bd-placeholder-img card-img-top" height="140" preserveAspectRatio="xMidYMid slice" role="img" width="100%" xmlns="http://www.w3.org/2000/svg">
                                <title>Placeholder</title><rect width="100%" height="100%" fill="#868e96"></rect><text x="50%" y="50%" fill="#dee2e6" dy=".3em">Vignette image</text></svg>
                            <div class="card-body">
                                <h6 class="card-title"><?= $item->get_title() ?></h6>
                                <p class="card-owner">by <?= $item->get_owner() ?></p>
                            <div class="row row-cols-md-2 border border-danger">
                                <?php if(isset($get_buy_now_price)) : ?>
                                    <p class="card-price">€ <?= $item->get_buy_now_price() ?></p>
                                <?php else : ?>
                                    <p class="card-price">€ <?= $item->get_starting_bid() ?></p>
                                <?php endif; ?>
                                L'enchère la plus élevée, s'il y a au moins une offre d'achat.
                                <?php if($has_bids == 1): ?>
                                    <p class="card-price">Current bid € <?= $item->get_max_bid() ?></p>
                                <?php endif; ?>
                            </div>
                                <p class="card-left_time"><i class="bi bi-clock"></i> <?= $item->get_end_at() ?> left</p>
                            </div>
                        </div>
                    <?php endforeach;?>
                <?php endif; ?>
            </div>

            <h2 class="p-3">Other Available Items</h2>
            <div class="row row-cols-md-4 g-4">
                <?php if(isset($others_available)): ?>
                    <?php foreach ($others_available as $item): ?>
                        <div class="card">
                            <svg aria-label="Placeholder: Image cap" class="bd-placeholder-img card-img-top" height="140" preserveAspectRatio="xMidYMid slice" role="img" width="100%" xmlns="http://www.w3.org/2000/svg">
                                <title>Placeholder</title><rect width="100%" height="100%" fill="#868e96"></rect><text x="50%" y="50%" fill="#dee2e6" dy=".3em">Vignette image</text></svg>
                            <div class="card-body">
                                <h6 class="card-title"><?= $item->get_title() ?></h6>
                                <p class="card-owner">by <?= $item->get_owner() ?></p>
                                <p class="card-price">€ <?= $item->get_buy_now_price() ?></p>
                                <p class="card-current_bid">Current bid</p>
                                <p class="card-price">€ <?= $item->get_max_bid() ?></p>
                                <p class="card-left_time"><i class="bi bi-clock"></i><?= $item->get_end_at() ?> left</p>
                            </div>
                        </div>
                    <?php endforeach;?>
                <?php endif; ?>
            </div>
        </main>

        <footer>
            <?php require 'footer_menu.php'; ?>
        </footer>
    </body>
</html>