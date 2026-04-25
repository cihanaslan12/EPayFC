<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= (isset($_GET['param1']) ? 'Edit Item' : 'Add item') ?></title>
    <base href="<?= Configuration::get("web_root") ?>">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= Configuration::get("web_root") ?>css/css_add_edit_item.css">
</head>

<body>
    <header>
        <?php require 'header_menu.php'; ?>
    </header>

    <main>
        <form id="form" action="<?= isset($_GET['param1']) ? "item/edit/" . $item->get_id() . '/' . $from . ($back_filter ? '/' . $back_filter : '') : 'item/add' ?>" method="POST">
            <input type="hidden" name="from" value="<?= $from ?>">
            <input type="hidden" name="back_filter" value="<?= $back_filter ?>">
            <div class="card mb-2" id="basic-info">
                <div class="card-header">Basic Information</div>
                <div class="card-body">
                    <label for="title">Item Title *</label>
                    <input type="text" name="title" id="title" value="<?= $title ?>" class="form-control" placeholder="Ex: Iphone 13 Pro Max 256GB">
                    <p class="text-danger"><?php if (isset($errors['title'])) echo "<li>".$errors['title']."</li>" ?></p>
                    <p class="text-danger"><?php if (isset($errors['unicity'])) echo "<li>".$errors['unicity']."</li>" ?></p>
                    <label for="description">Description</label>
                    <textarea name="description" id="description" class="form-control" placeholder="Describe your item in detail..."><?= $description ?></textarea>
                    <p class="text-danger"><?php if (isset($errors['description'])) echo "<li>".$errors['description']."</li>" ?></p>
                    <label for="duration">Sale Duration(days)*</label>
                    <input type="number" name="duration" id="duration" value="<?= $duration ?>" min="1" max="365" class="form-control">
                </div>
            </div>
            <div class="card" id="sale-type">
                <div class="card-header">Sale Type</div>
                <div class="card-body">
                    <div class="auction-div">
                        <div class="card-header text-primary" id="auction-header"><i class="bi bi-hammer"></i> Option 1: Auction</div>
                        <div class="card-body">
                            <label for="starting_bid">Starting bid</label>
                            <div class="input-group">
                                <input type="number" class="form-control" name="start_bid" id="starting_bid" value="<?= ($starting_bid > 0) ? $starting_bid : '' ?>" min="1" placeholder="e.g., 50.00">
                                <span class="input-group-text">€</span>
                            </div>
                                <p class="text-danger"><?php if(isset($errors['price'])) echo "<li>".$errors['price']."</li>" ?></p>
                            <label for="instant_purchase_price">Instant Purchase Price (optional)</label>
                            <div class="input-group">
                                <input type="number" class="form-control" name="inst_purch_price" id="instant_purchase_price" value="<?= ($instant_purchase_price > 0) ? $instant_purchase_price : '' ?>" min="1" placeholder="e.g., 200.00">
                                <span class="input-group-text">€</span>
                            </div>
                                <p class="text-danger"><?php if(isset($errors['auction'])) echo "<li>".$errors['auction']."</li>" ?></p>
                        </div>
                    </div>
                    <div class="direct-sale-div">
                        <div class="card-header bg-opacity-25" id="direct-sale-header"><i class="bi bi-cart"></i> 2: Direct Sale</div>
                        <div class="card-body">
                            <label for="direct_sale_price">Sale Price</label>
                            <div class="input-group">
                                <input type="number" class="form-control" name="dir_sale_price" id="direct_sale_price" value="<?= ($direct_sale_price > 0) ? $direct_sale_price : '' ?>" placeholder="e.g., 150.00">
                                <span class="input-group-text">€</span>
                            </div>
                            <p class="text-danger"><?php if(isset($errors['price'])) echo "<li>".$errors['price']."</li>" ?></p>
                        </div>
                    </div>
                </div>
            </div>

        </form>
    </main>
    <footer>
        <?php require 'footer_menu.php'; ?>
    </footer>
<!--    fenêtre modal -->
    <div class="modal" tabindex="-1" id="confirmationModal">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content bg-dark text-white">
                <div class="modal-header">
                    <h5 class="modal-title">Unsaved changes</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>You have unsaved changes. Leave anyway?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <a href="" type="button" class="btn btn-primary" id="confirmLeave">Leave</a>
                </div>
            </div>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.min.js" integrity="sha384-G/EV+4j2dNv+tEPo3++6LCgdCROaejBqfUeNjuKAiuXbjrxilcCdDz6ZAVfHWe1Y" crossorigin="anonymous"></script>
    <script src="js/modal.js"></script>
</body>