<a href="item/open/<?=$item->get_id() ?>" class="text-decoration-none">
    <div class="card">
        <?php if ($item->get_highest_bidder()): ?>
            <label class="badge rounded-pill text-bg-success text-white">
                <i class="bi bi-trophy"></i> Highest Bidder
            </label>
        <?php elseif($item->get_bidder()): ?>
            <label class="badge rounded-pill text-bg-secondary text-white">
                <i class="bi bi-hammer"></i> Bidder
            </label>
        <?php endif; ?>
        <?php if(!empty($item->get_thumbnail()) && ($item->get_thumbnail() !== "")): ?>
            <img src="<?= $item->get_thumbnail() ?>" alt="item_thumbnail">
        <?php else: ?>
            <svg aria-label="Placeholder: Image cap" class="bd-placeholder-img card-img-top" height="140" preserveAspectRatio="xMidYMid slice" role="img" width="100%" xmlns="http://www.w3.org/2000/svg">
                <title>Placeholder</title><rect width="100%" height="100%" fill="#868e96"></rect><text x="50%" y="50%" fill="#dee2e6" dy=".3em">Vignette image</text></svg>
        <?php endif; ?>
        <?php if(count($item->get_item_pictures()) > 1): ?>
            <label class="badge rounded-pill text-bg-dark opacity-75" for="nb-pics">
                <i class="bi bi-images" id="nb-pics"></i> <?= count($item->get_item_pictures()) ?> images
            </label>
        <?php endif; ?>
        <div class="card-body">
        <?php if(($item->get_buy_now_price() !== null)): ?>
            <label class="badge rounded-pill text-bg-primary">
                <i class="bi bi-bag"></i> Buy Now
            </label>
        <?php endif; ?>
        <?php if($item->get_is_auction() == 1): ?>
            <label class="badge rounded-pill text-bg-warning text-white">
                <i class="bi bi-hammer"></i> Auction
            </label>
        <?php endif; ?>
            <h6 class="card-title"><?= $item->get_title() ?></h6>
            <p class="card-owner">by <?= $item->get_owner_pseudo() ?></p>
        <div class="row row-cols-md-2">
            <?php if(($item->get_buy_now_price() != null)) : ?>
                <p class="card-price text-primary">€ <?= $item->get_buy_now_price() ?></p>
            <?php else : ?>
                <p class="card-price text-warning">€ <?= $item->get_starting_bid() ?></p>
            <?php endif; ?>
            <div>
                <?php if($item->get_has_bids() == 1): ?>
                    <label for="current-bid">Current bid</label>
                    <p class="card-price text-success" id="current-bid">€ <?= $item->get_max_bid() ?></p>
                <?php endif; ?>
            </div>
        </div>
            <p class="card-left_time"><i class="bi bi-clock"></i> <?= $item->get_time_left() ?></p>
        </div>
    </div>
</a>