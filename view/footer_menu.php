<nav class="navbar fixed-bottom bg-dark text-light mb-5" id="navbar">
    <?php if(isset($user)): ?>
        <div class="container-fluid" id="footer-href">
            <a href="item/browse" class="text-decoration-none link-light"><i class="bi bi-search"></i><br>Browse</a>
            <a href="item/my_items" class="text-decoration-none link-light"><i class="bi bi-house-door"></i><br>My Items</a>
            <a href="item/add" class="text-decoration-none link-light"><i class="bi bi-plus"></i><br>Add Offer</a>
            <?php if ($user->get_role() === 'admin'): ?>
                <a href="category/manage_categories" class="text-decoration-none link-light">
                    <i class="bi bi-tags"></i><br>Categories
                </a>
            <?php endif; ?>
            <a href="user/profile" class="text-decoration-none link-light"><i class="bi bi-gear"></i><br>Profile</a>
        </div>

    <?php else: ?>
        <div>
            <a href="item/browse" class="text-decoration-none link-light"><i class="bi bi-search"></i><br>Browse</a>
            <a href="user/signup" class="text-decoration-none link-light"><i class="bi bi-cake2"></i><br>Join us</a>
        </div>
    <?php endif; ?>

    <?php if (Configuration::is_dev()) :?> <?php require 'footer_time.php'; ?> <?php endif; ?>
</nav>
