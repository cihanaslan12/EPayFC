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


    <div class="row g-4">
        <!-- Photos -->
        <div class="col-lg-8">
            <div class="card">

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

            <div class="card mt-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="card-title mb-0">Bid History</h5>
                        <span class="badge text-bg-light border"><?= count($bids) ?> entries</span>
                    </div>

                    <?php if (!empty($bids)): ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($bids as $bid): ?>
                                <div class="list-group-item d-flex justify-content-between align-items-start">
                                    <div>
                                        <div class="fw-semibold"><?= $bid->get_owner_pseudo() ?></div>
                                        <div class="text-muted small"><?= $bid->get_created_at() ?></div>
                                    </div>
                                    <div class="fw-bold text-success"><?= $bid->get_amount() ?></div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-muted mb-0"><em>No bids yet.</em></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>


        <div class="col-lg-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title mb-3">Pricing</h5>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <?php if ($isOpen): ?>
                            <span class="badge text-bg-success">Open</span>
                        <?php else: ?>
                            <span class="badge text-bg-secondary">Closed</span>
                        <?php endif; ?>
                    </div>

                    <?php if (!$isOpen): ?>

                        <?php if ($item->get_is_auction() === 1): ?>
                            <?php if ($highestBid !== null): ?>
                                <div class="mb-2">
                                    <div class="text-muted small">Final price</div>
                                    <div class="fw-bold text-success"><?= $highestBid->get_amount() ?></div>
                                </div>

                                <?php if ($user && $highestBid->get_owner_id() === $user->get_id()): ?>
                                    <div class="alert alert-success py-2 mb-0">You won this auction.</div>
                                <?php elseif ($isOwner): ?>
                                    <div class="text-muted">Sold to: <strong><?= $highestBid->get_owner_pseudo() ?></strong></div>
                                <?php else: ?>
                                    <div class="text-muted">Winner: <strong><?= $highestBid->get_owner_pseudo() ?></strong></div>
                                <?php endif; ?>

                            <?php else: ?>
                                <div class="alert alert-secondary py-2 mb-0">This auction ended with no bids.</div>
                            <?php endif; ?>
                        <?php else: ?>
                            <?php if ($highestBid !== null): ?>
                                <div class="mb-2">
                                    <div class="text-muted small">Final price</div>
                                    <div class="fw-bold text-success"><?= $highestBid->get_amount() ?></div>
                                </div>

                                <?php if ($user && $highestBid->get_owner_id() === $user->get_id()): ?>
                                    <div class="alert alert-success py-2 mb-0">You bought this item.</div>

                                <?php elseif ($isOwner): ?>
                                    <div class="text-muted">Sold to: <strong><?= $highestBid->get_owner_pseudo() ?></strong></div>

                                <?php else: ?>
                                    <div class="alert alert-secondary py-2 mb-0">This item has been sold.</div>
                                <?php endif; ?>

                            <?php else: ?>
                                <div class="alert alert-secondary py-2 mb-0">This item is no longer available.</div>
                            <?php endif; ?>
                        <?php endif; ?>
                    <?php else: ?>

                        <?php if ($isOwner): ?>
                            <div class="alert alert-info py-2">You are the owner of this item.</div>
                        <?php else: ?>

                            <?php if ($item->get_is_auction() === 1): ?>
                                <div class="mb-2">
                                    <div class="text-muted small">Current bid</div>
                                    <div class="fw-bold text-success"><?= (string)$item->get_max_bid() ?></div>
                                </div>

                                <?php if ($isGuest): ?>
                                    <input class="form-control mb-2" value="<?= (string)$defaultBid ?>" disabled>
                                    <button class="btn btn-secondary w-100 mb-2" type="button" disabled>Place bid</button>
                                    <div class="text-muted small"><em>Login required to place a bid.</em></div>
                                <?php else: ?>
                                    <form method="post" action="item/place_bid/<?= $item->get_id() ?>">
                                        <input
                                                type="text"
                                                name="amount"
                                                class="form-control mb-2"
                                                value="<?= $postedAmount !== null ? $postedAmount : (string)$defaultBid ?>">

                                        <?php if (isset($errors["amount"])): ?>
                                            <div class="text-danger small mb-1"><?= $errors["amount"] ?></div>
                                        <?php endif; ?>
                                        <?php if (isset($errors["bid"])): ?>
                                            <div class="text-danger small mb-1"><?= $errors["bid"] ?></div>
                                        <?php endif; ?>

                                        <button class="btn btn-success w-100 mb-2" type="submit">Place bid</button>
                                    </form>
                                <?php endif; ?>
                            <?php endif; ?>

                            <?php if ($item->get_has_buy_now() === 1): ?>
                                <div class="mb-2">
                                    <div class="text-muted small">Buy Now</div>
                                    <div class="fw-bold"><?= (string)$item->get_buy_now_price() ?></div>
                                </div>

                                <?php if ($isGuest): ?>
                                    <button class="btn btn-secondary w-100" type="button" disabled>
                                        Buy Now at <?= (string)$item->get_buy_now_price() ?>
                                    </button>
                                    <div class="text-muted small mt-1"><em>Login required to buy now.</em></div>
                                <?php else: ?>
                                    <form method="post" action="item/buy_now/<?= $item->get_id() ?>">
                                        <?php if (isset($errors["buy_now"])): ?>
                                            <div class="text-danger small mb-1"><?= $errors["buy_now"] ?></div>
                                        <?php endif; ?>
                                        <button class="btn btn-outline-success w-100" type="submit">
                                            Buy Now at <?= (string)$item->get_buy_now_price() ?>
                                        </button>
                                    </form>
                                <?php endif; ?>
                            <?php endif; ?>

                        <?php endif; ?>
                    <?php endif; ?>
                </div>

                <div class="card mt-4">
                    <div class="card-body">
                        <h5 class="card-title mb-3">Seller information</h5>

                        <div class="d-flex align-items-center gap-2">
                            <?php if ($item->get_seller_picture_path()): ?>
                                <img src="<?= $item->get_seller_picture_path() ?>"
                                     alt="Seller"
                                     class="rounded-circle"
                                     style="width:42px;height:42px;object-fit:cover;">
                            <?php else: ?>
                                <div class="rounded-circle bg-secondary" style="width:42px;height:42px;"></div>
                            <?php endif; ?>

                            <div>
                                <div class="fw-semibold"><?= $item->get_seller_pseudo() ?></div>
                                <div class="text-muted small">Member</div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php if ($isOwner): ?>
                    <div class="card mt-4">
                        <div class="card-body">
                            <h5 class="card-title mb-3">Manage your item</h5>

                            <?php if ($canManage): ?>
                                <div class="d-grid gap-2">
                                    <a class="btn btn-outline-primary" href="item/edit/<?= $item->get_id() ?>">
                                        Edit item details
                                    </a>
                                    <a class="btn btn-outline-primary" href="item/manage_images/<?= $item->get_id() ?>">
                                        Manage images
                                    </a>
                                    <a class="btn btn-outline-danger" href="item/delete/<?= $item->get_id() ?>">
                                        Delete item
                                    </a>
                                </div>
                            <?php else: ?>
                                <div class="alert alert-warning mb-0">
                                    This item can no longer be modified or deleted because bids have been placed.
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>

            </div>
        </div>
    </div>
</main>

<footer>
    <?php require 'footer_menu.php'; ?>
</footer>
</body>
</html>
