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
        <main class="pt-5 bg-secondary text-light">
            <h2>Items I'm Participating In</h2>
                <?php if(isset($my_participations)): ?>
                    <p><?= count($my_participations) ?></p>
                    <?php foreach ($my_participations as $item): ?>
                        <div class="item-card">
                            <h5><?= $item->get_title() ?></h5>
                    <?php endforeach;?>
                <?php endif; ?>

            <h2>Other Available Items</h2>
                <?php if(isset($others_available)): ?>
                   <p><?= count($others_available)?></p>
                    <?php foreach ($others_available as $item): ?>
                        <div class="item-card">
                            <h5><?= $item->get_title() ?></h5>
                        </div>
                    <?php endforeach;?>
                <?php endif; ?>
        </main>
        <footer>
            <?php require 'footer_menu.php'; ?>
        </footer>
    </body>
</html>