<nav class="navbar fixed-top p-3 mb-2 bg-dark">
    <div class="container-fluid">
            <?php if (isset($show_back) && $show_back): ?>
                <a href="<?= $backUrl ?? '#' ?>" class="btn back-btn">
                    <i class="bi bi-arrow-left text-primary"></i>
                </a>
            <?php endif; ?>

        <span class="navbar-brand mx-auto mb-0 h1 text-primary">
            <?= $page_title ?? "EPayFC" ?> <i class="bi bi-cart4 text-light"></i>
        </span>

        <?php if (isset($show_save) && $show_save): ?>
            <button type="submit" form="form" class="btn save-btn">
                    <i class="bi bi-airplane text-primary"></i>
                </button>
            <?php endif; ?>
    </div>
</nav>