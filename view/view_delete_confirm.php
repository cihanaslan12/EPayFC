<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete "<?= $item->get_title() ?>"</title>
    <base href="<?= Configuration::get("web_root") ?>">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
</head>

<body>
    <form action="item/delete/<?=$item->get_id() ?>" method="POST">
        <div class="card text-center m-5 p-5">
            <div class="card-body">
                <i class="bi bi-trash"></i>
                <h5 class="card-title">Are you sure?</h5>
                <p class="card-text">Do you want to delete item <strong>"<?= $item->get_title() ?>"</strong> by <?= $item->get_owner_full_name() ?> and all of its dependencies?</p>
                <p>This process cannot be undone.</p>
                <a href="item/open/<?= $item->get_id() ?>" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-danger">Delete</button>
            </div>
        </div>
    </form>
</body>