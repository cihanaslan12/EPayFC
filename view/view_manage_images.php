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

        <main>
            <h2>Manage images for "<?= $item->get_title(); ?>"</h2>

            <div>
                <form action="item/manage_images" method="POST">
                    <div class="card-upload">
                        <div class="card-header">
                            Add New Images
                            <div class="card-body">
                                <div class="file mb-3">
                                    <label for="formFileMultiple" class="form-label">Select Images</label>
                                    <input type="file" class="form-control" id="formFileMultiple" multiple>
                                    <p>You can select multiple images (JPG, PNG, GIF, WebP). Images will be added to the end of your current list</p>
                                </div>
                                <a href="#" class="btn btn-primary">Upload Images</a>
                            </div>
                        </div>
                    </div>
                    <div class="card-current-images">
                        <?php if(isset($images) && count($images) > 0): ?>
                            <?php foreach ($images as $image): ?>
                                <div class="card-header">
                                    Current Images
                                    <div class="card-body">
                                        <img src="<?= $image->get_picture_thumbnail() ?>" alt="item_thumbnail">
                                        <button class="arrow-btn"><i class="bi bi-arrow-left"></i></button>
                                        <button class="arrow-btn"><i class="bi bi-arrow-right"></i></button>
                                        <button class="delete-btn"><i class="bi bi-x"></i></button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p>No images yet for this item. Upload some images above to get started.</p>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </main>
    </body>
</html>