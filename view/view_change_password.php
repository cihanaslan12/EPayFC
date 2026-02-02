<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password</title>
    <base href="<?= Configuration::get("web_root") ?>">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= Configuration::get("web_root") ?>css/css_browse_items.css">
</head>

<body>
<header>
    <?php require 'header_menu.php';?>
</header>

<main class="card p-5 m-5">
    <form action="user/change_password" method="POST"></form>
    <div class="card text-light">
        <div class="card-header">Current Password</div>
        <div class="card-body">
            <label for="current">Current Password *</label>
            <input type="password" name="current" id="current" value="<?= $current_password ?? ''?>" class="form-control" placeholder="Enter your current password">
            <p class="text-danger"><?php if (isset($errors['current'])) echo $errors['current'] ?></p>
            <p>Enter your current password to verify your identity</p>
        </div>
    </div>
    <div class="card text-light">
        <div class="card-header">New Password</div>
        <div class="card-body">
            <label for="new_password">New Password *</label>
            <input type="password" name="new_password" id="new_password" value="<?= $new_password ?? '' ?>" class="form-control" placeholder="Enter your new password">
            <p class="text-danger"><?php if (isset($errors['new_password'])) echo $errors['new_password'] ?></p>
            <p>Password must be 8-16 characters with uppercase, number, and punctuation</p>
            <label for="confirm_new_password">Confirm New Password *</label>
            <input type="password" name="confirm_new_password" id="confirm_new_password" value="<?= $confirm_new_password ?? '' ?>" class="form-control" placeholder="Confirm your new password">
            <p class="text-danger"><?php if (isset($errors['confirm_new_password'])) echo $errors['confirm_new_password'] ?></p>
            <p>Re-enter your new password to confirm it matches</p>
        </div>
    </div>
</main>

<footer>
    <?php require 'footer_menu.php'; ?>
</footer>
</body>
</html>
