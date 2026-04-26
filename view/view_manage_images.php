<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Manage Images - <?= $item->get_title() ?></title>
        <base href="<?= Configuration::get("web_root") ?>">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
        <link rel="stylesheet" href="<?= Configuration::get("web_root") ?>css/css_manage_images.css">
    </head>

    <body>
        <header>
            <?php require 'header_menu.php'; ?>
        </header>

        <main>
            <h3>Manage images for "<?= $item->get_title(); ?>"</h3>
            <div class="card my-4">
                <div class="card-header">Add New Images</div>
                <div class="card-body">
                    <div class="file">
                        <form action="item/manage_images/<?= $item->get_id() ?>/<?= $from ?><?= $back_filter ? '/' . $back_filter : ''?>" method="POST" enctype="multipart/form-data">
                            <label for="formFileMultiple" class="form-label">Select Images</label>
                            <input type="file" name="image[]" class="form-control" id="formFileMultiple" multiple>
                            <p>You can select multiple images (JPG, PNG, GIF, WebP). Images will be added to the end of your current list</p>
                            <?php if(isset($error)): ?>
                                <p class="text-danger"><?= $error ?></p>
                            <?php endif; ?>
                            <button type="submit" class="btn btn-primary" name="upload_images">Upload Images</button>
                        </form>
                    </div>
                </div>
            </div>
            <div id="manage-images-config"
                 data-reorder-url="item/reorder_pictures"
                 data-item-id="<?= $item->get_id() ?>">
            </div>
            <div class="card">
                <div class="card-header">Current Images</div>
                <div class="card-body">
                    <?php if(isset($images) && count($images) > 0): ?>
                        <div class="row row-cols-md-4 m-2" id="sortable-images">
                        <?php foreach ($images as $image): ?>
                            <div class="card card-thumb m-2 p-2 sortable-image-card"
                                 data-picture-path="<?= htmlspecialchars($image->get_picture_path(), ENT_QUOTES) ?>">
                                <img src="<?= $image->get_picture_thumbnail() ?>" alt="item_thumbnail">
                                <div class="card-btn m-2 p-2">
                                    <form action="item/move_picture/<?= $item->get_id() ?>/<?= $from ?><?= $back_filter ? '/' . $back_filter : ''?>" method="POST">
                                        <input type="hidden" name="item" value="<?= $image->get_item() ?>">
                                        <input type="hidden" name="priority" value="<?= $image->get_priority() ?>">
                                        <button type="submit" class="arrow-btn" name="btn-left" <?php if($image->get_priority() == 1): ?>disabled<?php endif; ?>>
                                            <i class="bi bi-arrow-left"></i>
                                        </button>
                                        <button type="submit" class="arrow-btn" name="btn-right" <?php if($image->get_priority() == ItemPicture::get_pictures_priority_max($item->get_id())): ?>disabled<?php endif; ?>>
                                            <i class="bi bi-arrow-right"></i>
                                        </button>
                                        <button type="submit" class="delete-btn" name="btn-delete"><i class="bi bi-x"></i></button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p>No images yet for this item. Upload some images above to get started.</p>
                    <?php endif; ?>
                </div>
            </div>
        </main>
        <footer>
            <?php require 'footer_menu.php'; ?>
        </footer>
        <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
        <script src="https://code.jquery.com/ui/1.14.1/jquery-ui.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui-touch-punch/0.2.3/jquery.ui.touch-punch.min.js"></script>
        <script src="<?= Configuration::get("web_root") ?>js/manage_images_sortable.js?v=<?= filemtime("js/manage_images_sortable.js") ?>"></script>
    </body>
</html>