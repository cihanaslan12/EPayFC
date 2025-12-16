<nav class="navbar">
    <div class="navbar-left">
        <?php if (isset($show_back) && $show_back): ?>
            <a href="<?= $backUrl ?? '#' ?>" class="back-button">
                <i class="fa-solid fa-arrow-left"></i>
            </a>
        <?php endif; ?>
    </div>

    <div class="navbar-center">
        <span class="page-title">
            <?= $page_title ?? "EPayFC" ?>
        </span>
    </div>

    <div class="navbar-right">
        <?php if (isset($show_save) && $show_save): ?>
            <button type="submit" form="main-form" class="save-button">
                <i class="fa-regular fa-floppy-disk"></i>
            </button>
        <?php endif; ?>
    </div>
</nav>