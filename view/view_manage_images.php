<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Manage Images - <?= $item->get_title() ?></title>
        <base href="<?= Configuration::get("web_root") ?>">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    </head>

    <body>
        <header>
            <?php require 'header_menu.php'; ?>
        </header>

        <main class="p-3 m-3">
            <h2>Manage images for "<?= $item->get_title(); ?>"</h2>

            <div>
                <div class="card-upload">
                    <div class="card-header">
                        Add New Images
                        <div class="card-body">
                            <div class="file mb-3">
                                <form action="item/manage_images/<?= $item->get_id() ?>" method="POST" enctype="multipart/form-data">
                                    <label for="formFileMultiple" class="form-label">Select Images</label>
                                    <input type="file" name="image[]" class="form-control" id="formFileMultiple" multiple>
                                    <p>You can select multiple images (JPG, PNG, GIF, WebP). Images will be added to the end of your current list</p>
                                    <?php if(isset($error)): ?>
                                        <p class="text-danger"><?= $error ?></p>
                                    <?php endif; ?>
                                    <button type="submit" class="btn btn-primary" id="image">Upload Images</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-current-images card border-primary m-2">
                    <div class="card-header card border-success m-2">Current Images</div>
                    <?php if(isset($images) && count($images) > 0): ?>
                        <div class="row row-cols-md-4 card border-danger m-2">
                        <?php foreach ($images as $image): ?>
                            <div class="card-thumb card border-warning m-2 p-2">
                                <img src="<?= $image->get_picture_thumbnail() ?>" alt="item_thumbnail">
                                <div class="card-btn card border-info m-2 p-2">
                                    <form action="item/move_picture" method="POST">
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
    </body>
</html>