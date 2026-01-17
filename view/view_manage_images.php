<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Manage Images - <?= $item->get_id() ?></title>
        <base href="<?= Configuration::get("web_root") ?>">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    </head>

    <body>
        <header>
            <?php require 'header_menu.php'; ?>
        </header>

        <main>
            <h2><?= $item->get_title(); ?></h2>

            <div>
                <form action="item/manage_images" method="POST">
                    <div class="card-upload">
                        <div class="card-header">
                            Add New Images
                            <div class="card-body">
                                <h5 class="card-title">Select Images</h5>
                                <div class="file mb-3">
                                    <label for="formFileMultiple" class="form-label"></label>
                                    <input type="file" class="form-control" id="formFileMultiple" multiple>
                                </div>
                                <a href="#" class="btn btn-primary">Upload Images</a>
                            </div>
                        </div>
                    </div>
                    <div class="card-current-images">

                    </div>
                </form>
            </div>
        </main>
    </body>
</html>