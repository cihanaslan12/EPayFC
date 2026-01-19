<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Open item</title>
    <base href="<?= $web_root ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>

<h1><?= $item->get_title() ?></h1>

<p><strong>Seller:</strong> <?= $item->get_seller_pseudo() ?></p>

<?php
$errors = $errors ?? [];
$postedAmount = $postedAmount ?? null;
?>

<h2>Photos</h2>

<?php if ($mainPicture): ?>
    <div style="max-width: 700px;">
        <img
                src="<?= $mainPicture ?>"
                alt="Main picture"
                style="width: 100%; height: auto; display:block; border:1px solid #ccc;">

        <div style="margin-top: 10px; display:flex; gap:10px; flex-wrap:wrap;">
            <?php foreach ($pictures as $pic): ?>
                <?php
                $thumb = $pic->get_thumbnail_path();
                $prio  = $pic->get_priority();
                ?>
                <a href="item/open/<?= (int)$item->get_id() ?>/<?= $prio ?>">
                <img
                            src="<?= $thumb ?>"
                            alt="thumbnail <?= $prio ?>"
                            style="width:120px; height:auto; border:1px solid #ccc;">
                </a>
            <?php endforeach; ?>
        </div>
    </div>
<?php else: ?>
    <p><em>No pictures for this item.</em></p>
<?php endif; ?>

<h2>Pricing</h2>

<?php if (!$isOpen): ?>
    <p><strong>Status:</strong> Closed</p>

    <?php if ($item->get_is_auction() === 1): ?>
        <?php if ($highestBid !== null): ?>
            <p>Final price: <?= $highestBid->get_amount() ?></p>

            <?php if ($user && $highestBid->get_owner_id() === $user->get_id()): ?>
                <p><strong>You won this auction.</strong></p>

            <?php elseif ($isOwner): ?>
                <p>Sold to: <?= $highestBid->get_owner_pseudo() ?></p>

            <?php else: ?>
                <p>Winner: <?= $highestBid->get_owner_pseudo() ?></p>
            <?php endif; ?>
        <?php else: ?>
            <p>This auction ended with no bids.</p>
        <?php endif; ?>
    <?php else: ?>
        <p>This item is no longer available.</p>
    <?php endif; ?>

<?php else: ?>
    <p><strong>Status:</strong> Open</p>

    <?php if ($isOwner): ?>
        <p>You are the owner of this item.</p>

    <?php else: ?>
        <?php if ($item->get_is_auction() === 1): ?>
            <p>Current highest bid: <?= (string)$item->get_max_bid() ?></p>

            <form method="post" action="item/place_bid/<?= $item->get_id() ?>">
                <label for="amount"><strong>Your bid:</strong></label><br>

                <input
                        type="text"
                        id="amount"
                        name="amount"
                        value="<?= $postedAmount !== null ? $postedAmount : (string)$defaultBid ?>">

                <?php if (isset($errors["amount"])): ?>
                    <p style="color:red;"><?= $errors["amount"] ?></p>
                <?php endif; ?>

                <?php if (isset($errors["bid"])): ?>
                    <p style="color:red;"><?= $errors["bid"] ?></p>
                <?php endif; ?>

                <button type="submit">Place bid</button>
            </form>
        <?php endif; ?>

        <?php if ($item->get_has_buy_now() === 1): ?>
            <p>Buy now price: <?= (string)$item->get_buy_now_price() ?></p>

            <form method="post" action="item/buy_now/<?= $item->get_id() ?>">
                <?php if (isset($errors["buy_now"])): ?>
                    <p style="color:red;"><?= $errors["buy_now"] ?></p>
                <?php endif; ?>

                <button type="submit">Buy now</button>
            </form>
        <?php endif; ?>
    <?php endif; ?>
<?php endif; ?>

<p><strong>Description:</strong><br>
    <?= nl2br($item->get_description() ?? "") ?>
</p>

<hr>

<p><strong>Start:</strong> <?= $item->get_created_at() ?></p>
<p><strong>End:</strong> <?= $item->get_end_at() ?></p>

<hr>

<ul>
    <li><strong>Starting bid:</strong> <?= (string)$item->get_starting_bid() ?></li>
    <li><strong>Buy now price:</strong> <?= (string)$item->get_buy_now_price() ?></li>
    <li><strong>Max bid:</strong> <?= (string)$item->get_max_bid() ?></li>
    <li><strong>Bid count:</strong> <?= (string)$item->get_bid_count() ?></li>
</ul>

<hr>

<h2>Bid history</h2>

<?php if (!empty($bids)): ?>
    <table border="1" cellpadding="6" cellspacing="0">
        <thead>
        <tr>
            <th>Bidder</th>
            <th>Date/Time</th>
            <th>Amount</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($bids as $bid): ?>
            <tr>
                <td><?= $bid->get_owner_pseudo() ?></td>
                <td><?= $bid->get_created_at() ?></td>
                <td><?= $bid->get_amount() ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <p><em>No bids yet.</em></p>
<?php endif; ?>


</body>
</html>
