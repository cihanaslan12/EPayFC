<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Browse items</title>
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
            <?php if(isset($user)): ?>
                <?php if(isset($my_participations) && count($my_participations) > 0): ?>
                    <h2 class="pt-5">Items I'm Participating In</h2>
                        <div class="row row-cols-md-4 g-4">
                            <?php foreach ($my_participations as $item): ?>
                                <?php $open_from = 'browse'; ?>
                                <?php require 'item_card.php'; ?>
                            <?php endforeach;?>
                        </div>
                <?php endif; ?>
                <?php if(isset($others_available) && count($others_available) > 0): ?>
                    <h2 class="pt-3">Other Available Items</h2>
                        <div class="row row-cols-md-4 g-4 pb-5">
                            <?php foreach ($others_available as $item): ?>
                                <?php $open_from = 'browse'; ?>
                                <?php require 'item_card.php'; ?>
                            <?php endforeach;?>
                        </div>
                <?php endif; ?>
            <?php else: ?>
                <?php if(isset($all_available_items) && count($all_available_items) > 0): ?>
                    <h2 class="pt-5">Available Items</h2>
                        <div class="row row-cols-md-4 g-4">
                            <?php foreach ($all_available_items as $item): ?>
                                <?php $open_from = 'browse'; ?>
                                <?php require 'item_card.php'; ?>
                            <?php endforeach;?>
                        </div>
                <?php endif; ?>
            <?php endif; ?>
        </main>

        <footer>
            <?php require 'footer_menu.php'; ?>
        </footer>
    </body>
</html>