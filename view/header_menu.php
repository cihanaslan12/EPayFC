<nav class="navbar fixed-top bg-dark p-3 mb-2" id="navbar">
    <div class="container-fluid">
            <?php if (isset($show_back) && $show_back): ?>
                <a href="<?= $back_url ?? '#' ?>" class="btn back-btn">
                    <i class="bi bi-arrow-left text-primary"></i>
                </a>
            <?php endif; ?>

        <span class="navbar-brand mx-auto mb-0 h1 text-primary" id="page-title">
            <?= $page_title ?? "EPayFC" ?> <i class="bi bi-cart4 text-light"></i>
        </span>

        <?php if (isset($show_save) && $show_save): ?>
            <button type="submit" form="form" class="btn save-btn" name="save">
                    <i class="bi bi-save text-primary"></i>
                </button>
            <?php endif; ?>
    </div>
</nav>