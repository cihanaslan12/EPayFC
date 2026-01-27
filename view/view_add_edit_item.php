<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= (isset($_GET['param1']) ? 'Edit Item' : 'Add item') ?></title>
    <base href="<?= Configuration::get("web_root") ?>">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
</head>

<body>
    <header>
        <?php require 'header_menu.php'; ?>
    </header>

    <main>
        <form id="form" action="item/add" method="POST">
        <div class="card m-5 p-5">
            <div class="card">
                <div class="card-header">Basic Information</div>
                <div class="card-body">
                    <label for="title">Item Title *</label>
                    <input type="text" name="title" id="title" value="" class="form-control" placeholder="Ex: Iphone 13 Pro Max 256GB">
                    <p class="text-danger"><?php if (isset($errors['title'])) echo $errors['title'] ?></p>
                    <label for="description">Description</label>
                    <textarea name="description" id="description" value="" class="form-control" placeholder="Describe your item in detail..."></textarea>
                    <p class="text-danger"><?php if (isset($errors['description'])) echo $errors['description'] ?></p>
                    <label for="duration">Sale Duration(days)*</label>
                    <input type="number" name="duration" id="duration" value="" class="form-control">
                </div>
            </div>
            <div class="card">
                <div class="card-header">Sale Type</div>
                <div class="card-body">
                    <div class="card-header"><i class="bi bi-hammer"></i> Option 1: Auction</div>
                    <div class="card-body">
                        <label for="starting_bid">Starting bid</label>
                        <div class="input-group">
                            <input type="number" class="form-control" name="start_bid" id="starting_bid" placeholder="e.g., 50.00">
                            <span class="input-group-text">€</span>
                        </div>
                            <p class="text-danger"><?php if(isset($errors['price'])) echo $errors['price'] ?></p>
                        <label for="instant_purchased_price">Instant Purchased Price (optional)</label>
                        <div class="input-group">
                            <input type="number" class="form-control" name="inst_purch_price" id="instant_purchased_price" placeholder="e.g., 200.00">
                            <span class="input-group-text">€</span>
                        </div>
                            <p class="text-danger"><?php if(isset($errors['auction'])) echo $errors['auction'] ?></p>
                    </div>
                    <div class="card-header"><i class="bi bi-cart"></i> 2: Direct Sale</div>
                    <div class="card-body">
                        <label for="direct_sale_price">Sale Price</label>
                        <div class="input-group">
                            <input type="number" class="form-control" name="dir_sale_price" id="direct_sale_price" placeholder="e.g., 150.00">
                            <span class="input-group-text">€</span>
                        </div>
                        <p class="text-danger"><?php if(isset($errors['price'])) echo $errors['price'] ?></p>
                    </div>
                </div>
            </div>
        </div>
        </form>
    </main>
    <footer>
        <?php require 'footer_menu.php'; ?>
    </footer>
</body>