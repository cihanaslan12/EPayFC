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
                            <?php require 'item_card.php'; ?>
                        <?php endforeach;?>
                    <?php endif; ?>
                </div>
            <h2 class="p-3">Other Available Items</h2>
                <div class="row row-cols-md-4 g-4">
                    <?php if(isset($others_available)): ?>
                        <?php foreach ($others_available as $item): ?>
                            <?php require 'item_card.php'; ?>
                        <?php endforeach;?>
                    <?php endif; ?>
                </div>
        </main>

        <footer>
            <?php require 'footer_menu.php'; ?>
        </footer>
    </body>
</html>