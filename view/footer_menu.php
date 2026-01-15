<nav class="navbar fixed-bottom m-5 bg-dark">
    <?php if(isset($user)): ?>
        <div class="container-fluid">
            <a href="item/browse" class="text-decoration-none link-light"><i class="bi bi-search"></i><br>Browse</a>
            <a href="#" class="text-decoration-none link-light"><i class="bi bi-house-door"></i><br>My Items</a>
            <a href="#" class="text-decoration-none link-light"><i class="bi bi-plus"></i><br>Add Offer</a>
            <a href="#" class="text-decoration-none link-light"><i class="bi bi-gear"></i><br>Profile</a>
        </div>

    <?php else: ?>
        <div>
            <a href="item/browse" class="text-decoration-none link-light"><i class="bi bi-search"></i><br>Browse</a>
            <a href="#" class="text-decoration-none link-light"><i class="bi bi-cake2"></i><br>Join us</a>
        </div>
    <?php endif; ?>

    <?php if (Configuration::is_dev()) :?> <?php require 'footer_time.php'; ?> <?php endif; ?>
</nav>
