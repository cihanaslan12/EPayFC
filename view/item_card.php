<a href="item/open/<?= $item->get_id() ?>/<?= urlencode($open_from ?? 'browse') ?>"
   data-base-url="item/open/<?= $item->get_id() ?>/<?= urlencode($open_from ?? 'browse') ?>"
   class="text-decoration-none item-card-link">
    <div class="card">
        <div class="position-relative">
            <?php
            $thumbnail = $item->get_thumbnail();
            $displayThumbnail = (!empty($thumbnail) && $thumbnail !== "")
                    ? $thumbnail
                    : 'img//item_placeholder/item_placeholder.jpg';
            ?>

            <img src="<?= $displayThumbnail ?>"
                 alt="item_thumbnail"
                 class="card-img-top w-100">

            <?php if ($item->get_highest_bidder()): ?>
                <span class="position-absolute top-O start-0 m-2 badge rounded-pill text-bg-success text-white">
                    <i class="bi bi-trophy"></i> Highest Bidder
                </span>
            <?php elseif($item->get_bidder()): ?>
                <span class="position-absolute top-O start-0 m-2 badge rounded-pill text-bg-secondary text-white">
                    <i class="bi bi-hammer"></i> Bidder
                </span>
            <?php endif; ?>

            <?php if($item->get_is_auction()): ?>
                <span class="position-absolute top-O end-0 m-2 badge rounded-pill text-bg-warning text-white">
                    <i class="bi bi-hammer"></i> Auction
                </span>
                <?php if(($item->get_buy_now_price() !== null)): ?>
                    <span class="position-absolute end-0 me-2 mt-5 badge rounded-pill text-bg-primary">
                    <i class="bi bi-bag"></i> Buy Now
                </span>
                <?php endif; ?>
            <?php else: ?>
                <?php if(($item->get_buy_now_price() !== null)): ?>
                    <span class="position-absolute top-0 end-0 m-2 badge rounded-pill text-bg-primary">
                        <i class="bi bi-bag"></i> Buy Now
                    </span>
                <?php endif; ?>
            <?php endif; ?>

            <?php if(count($item->get_item_pictures()) > 1): ?>
                <span class="position-absolute bottom-0 end-0 m-2 badge rounded-pill text-bg-dark opacity-75">
                    <i class="bi bi-images" id="nb-pics"></i> <?= count($item->get_item_pictures()) ?> images
                </span>
            <?php endif; ?>
        </div>

        <div class="card-body">
            <h6 class="card-title"><?= $item->get_title() ?></h6>
            <p class="card-owner">by <?= $item->get_owner_pseudo() ?></p>
        <div class="row row-cols-md-2">
            <?php if(($item->get_buy_now_price() != null)) : ?>
                <p class="get-buy-now-price">€ <?= $item->get_buy_now_price() ?></p>
            <?php else : ?>
                <p class="starting-bid">€ <?= $item->get_starting_bid() ?></p>
            <?php endif; ?>
            <div>
                <?php if($item->get_has_bids() == 1): ?>
                    <label class="current-bid-label" for="current-bid">Current bid</label>
                    <p class="card-price" id="current-bid">€ <?= $item->get_max_bid() ?></p>
                <?php endif; ?>
            </div>
        </div>
            <p class="card-left_time"><i class="bi bi-clock"></i> <?= $item->get_time_left() ?></p>
        </div>
    </div>
</a>