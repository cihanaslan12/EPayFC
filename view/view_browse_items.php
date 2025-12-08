<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Browse items</title>
        <base href="<?= Configuration::get("web_root") ?>">
    </head>

    <body>

        <div class="header-navbar">include(menu.php)</div>

        <h2>Items I'm Participating In</h2>
            <?php if(isset($my_participations)): ?>
                <p><?= count($my_participations) ?></p>
                <?php foreach ($my_participations as $item): ?>
                    <div class="item-card">
                        <h5><?= htmlspecialchars($item['title']) ?></h5>
                <?php endforeach;?>
            <?php endif; ?>

        <h2>Other Available Items</h2>
            <?php if(isset($others_available)): ?>
               <p><?= count($others_available)?></p>
                <?php foreach ($others_available as $item): ?>
                    <div class="item-card">
                        <h5><?= htmlspecialchars($item['title']) ?></h5>
                <?php endforeach;?>
            <?php endif; ?>

    </body>
</html>