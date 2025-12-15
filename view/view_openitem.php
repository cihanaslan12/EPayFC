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
