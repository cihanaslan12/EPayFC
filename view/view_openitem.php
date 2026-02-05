<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Open item</title>
    <base href="<?= $web_root ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
</head>
<style>
    body { padding-top: 50px; padding-bottom: 140px;}
</style>
<body>
<header>
    <?php require 'header_menu.php';?>
</header>

<main class="container py-4 pt-5">
    <?php
    $errors = $errors ?? [];
    $postedAmount = $postedAmount ?? null;
    $isGuest = !$user;
    ?>

    <div class="d-flex align-items-start justify-content-between flex-wrap gap-2 mb-3">
        <div>
            <h1 class="mb-1"><?= $item->get_title() ?></h1>

            <div class="d-flex align-items-center gap-2">
                <?php if ($item->get_seller_picture_path()): ?>
                    <img src="<?= $item->get_seller_picture_path() ?>"
                         alt="Seller picture"
                         class="rounded-circle"
                         style="width:36px;height:36px;object-fit:cover;">
                <?php endif; ?>

                <div class="text-muted">
                    <span>Seller:</span>
                    <strong class="text-body"><?= $item->get_seller_pseudo() ?></strong>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Photos -->
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title mb-3">Photos</h5>

                    <?php if ($mainPicture): ?>
                        <img src="<?= $mainPicture ?>"
                             alt="Main picture"
                             class="img-fluid rounded border mb-3"
                             style="width:100%;max-height:480px;object-fit:contain;">

                        <div class="bg-dark text-white p-3 rounded">
                            <div class="d-flex justify-content-between align-items-start gap-2">
                                <div>
                                    <div class="fw-bold"><?= $item->get_title() ?></div>
                                    <div class="small text-white-50">
                                        <?= $item->get_description() ?>
                                    </div>
                                </div>

                                <?php if ($item->get_is_auction() === 1): ?>
                                    <span class="badge text-bg-secondary">Auction</span>
                                <?php else: ?>
                                    <span class="badge text-bg-secondary">Direct sale</span>
                                <?php endif; ?>
                            </div>

                            <div class="small mt-2">
                                <div><strong>Start:</strong> <?= $item->get_created_at() ?></div>
                                <div><strong>Ends:</strong> <?= $item->get_end_at() ?></div>
                            </div>
                        </div>
                    <?php else: ?>
                        <p class="text-muted mb-0"><em>No pictures for this item.</em></p>
                    <?php endif; ?>
                </div>
            </div>
            <?php if (!empty($pictures)): ?>
                <div class="card mt-4">
                    <div class="card-body">
                        <h5 class="card-title mb-3">Additional images</h5>

                        <div class="d-flex gap-2 flex-wrap">
                            <?php foreach ($pictures as $pic): ?>
                                <?php
                                $thumb = $pic->get_thumbnail_path();
                                $prio  = $pic->get_priority();
                                ?>
                                <a href="item/open/<?= (int)$item->get_id() ?>/<?= $prio ?>"
                                   class="d-inline-block border rounded p-1 bg-light">
                                    <img src="<?= $thumb ?>"
                                         alt="thumbnail <?= $prio ?>"
                                         style="width:90px;height:90px;object-fit:cover;display:block;">
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

        </div>


        <div class="col-lg-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title mb-3">Pricing</h5>

                    <div class="text-muted"></div>
                </div>
            </div>
        </div>
    </div>
</main>


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

            <?php if ($isGuest): ?>
                <p class="text-muted"><em>Login required to place a bid.</em></p>
                <div class="mb-3">
                    <label for="amount"><strong>Your bid:</strong></label><br>
                    <input type="text" id="amount" class="form-control" value="<?= (string)$defaultBid ?>" disabled>
                </div>
                <button class="btn btn-secondary" type="button" disabled>Place bid</button>
            <?php else: ?>
                <form method="post" action="item/place_bid/<?= $item->get_id() ?>">
                    <label for="amount"><strong>Your bid:</strong></label><br>

                    <input
                            type="text"
                            id="amount"
                            name="amount"
                            class="form-control"
                            value="<?= $postedAmount !== null ? $postedAmount : (string)$defaultBid ?>">

                    <?php if (isset($errors["amount"])): ?>
                        <p class="text-danger"><?= $errors["amount"] ?></p>
                    <?php endif; ?>

                    <?php if (isset($errors["bid"])): ?>
                        <p class="text-danger"><?= $errors["bid"] ?></p>
                    <?php endif; ?>

                    <button class="btn btn-primary mt-2" type="submit">Place bid</button>
                </form>
            <?php endif; ?>
        <?php endif; ?>

        <?php if ($item->get_has_buy_now() === 1): ?>
            <p>Buy now price: <?= (string)$item->get_buy_now_price() ?></p>

            <?php if ($isGuest): ?>
                <p class="text-muted"><em>Login required to buy now.</em></p>
                <button class="btn btn-secondary" type="button" disabled>Buy now</button>
            <?php else: ?>
                <form method="post" action="item/buy_now/<?= $item->get_id() ?>">
                    <?php if (isset($errors["buy_now"])): ?>
                        <p class="text-danger"><?= $errors["buy_now"] ?></p>
                    <?php endif; ?>

                    <button class="btn btn-success" type="submit">Buy now</button>
                </form>
            <?php endif; ?>
        <?php endif; ?>
    <?php endif; ?>
<?php endif; ?>

<?php if ($isOwner): ?>
    <h2>Manage your item</h2>

    <?php if ($canManage): ?>
        <ul>
            <li><a href="item/edit/<?= $item->get_id() ?>">Edit item details</a></li>
            <li><a href="item/manage_images/<?= $item->get_id() ?>">Manage images</a></li>
            <li><a href="item/delete/<?= $item->get_id() ?>">Delete item</a></li>
        </ul>
    <?php else: ?>
        <p><em>This item can no longer be modified or deleted because bids have been placed.</em></p>
    <?php endif; ?>
<?php endif; ?>


<p><strong>Description:</strong><br>
    <?= nl2br($item->get_description() ?? "") ?>
</p>

<hr>

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

<footer>
    <?php require 'footer_menu.php'; ?>
</footer>
</body>
</html>
