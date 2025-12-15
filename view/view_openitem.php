<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Open item</title>
    <base href="<?= $web_root ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>

<h1><?= htmlspecialchars($item["title"]) ?></h1>

<p><strong>Seller:</strong> <?= htmlspecialchars($item["seller_pseudo"]) ?></p>

<?php
function thumbnail_path(string $path): string {
    $dot = strrpos($path, '.');
    if ($dot === false) return $path;
    return substr($path, 0, $dot) . "_thumbnail" . substr($path, $dot);
}
?>

<h2>Photos</h2>

<?php if ($mainPicture): ?>
    <div style="max-width: 700px;">
        <img
                src="<?= htmlspecialchars($mainPicture) ?>"
                alt="Main picture"
                style="width: 100%; height: auto; display:block; border:1px solid #ccc;">

        <div style="margin-top: 10px; display:flex; gap:10px; flex-wrap:wrap;">
            <?php foreach ($pictures as $pic): ?>
                <?php
                $full = $pic["picture_path"];
                $thumb = thumbnail_path($full);
                $prio = (int)$pic["priority"];
                ?>
                <a href="item/open/<?= (int)$item["id"] ?>/<?= $prio ?>">
                <img
                            src="<?= htmlspecialchars($thumb) ?>"
                            alt="thumbnail <?= $prio ?>"
                            style="width:120px; height:auto; border:1px solid #ccc;">
                </a>
            <?php endforeach; ?>
        </div>
    </div>
<?php else: ?>
    <p><em>No pictures for this item.</em></p>
<?php endif; ?>


<p><strong>Description:</strong><br>
    <?= nl2br(htmlspecialchars($item["description"] ?? "")) ?>
</p>

<hr>

<p><strong>Start:</strong> <?= htmlspecialchars($item["created_at"]) ?></p>
<p><strong>End:</strong> <?= htmlspecialchars($item["end_at"]) ?></p>

<hr>

<ul>
    <li><strong>Starting bid:</strong> <?= htmlspecialchars((string)$item["starting_bid"]) ?></li>
    <li><strong>Buy now price:</strong> <?= htmlspecialchars((string)$item["buy_now_price"]) ?></li>
    <li><strong>Max bid:</strong> <?= htmlspecialchars((string)$item["max_bid"]) ?></li>
    <li><strong>Bid count:</strong> <?= htmlspecialchars((string)$item["bid_count"]) ?></li>
</ul>

<hr>

</body>
</html>
