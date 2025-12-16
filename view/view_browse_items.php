<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Browse items</title>
        <base href="<?= Configuration::get("web_root") ?>">
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    </head>

    <body>
        <?php require 'header_menu.php'; ?>

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
                <?php endforeach;?>
            <?php endif; ?>

    </body>
</html>