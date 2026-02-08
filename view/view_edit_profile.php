<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit profile</title>
    <base href="<?= Configuration::get("web_root") ?>">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
</head>
<style>
body { padding-top: 50px;}
</style>
<body>
<header>
    <?php require 'header_menu.php'; ?>
</header>

<main class="container py-4 pt-5">
    <h1 class="mb-4">Edit profile</h1>

    <?php
    $errors = $errors ?? [];
    $values = $values ?? [
            "full_name" => $user->get_full_name(),
            "pseudo" => $user->get_pseudo(),
            "mail" => $user->get_mail(),
            "iban" => $user->get_iban()
    ];
    ?>

    <form method="post" action="user/edit_profile">
        <div class="mb-3">
            <label class="form-label">Full name</label>
            <input class="form-control" name="full_name" value="<?= $values["full_name"] ?>">
            <?php if (!empty($errors["full_name"])): ?>
                <div class="text-danger small"><?= $errors["full_name"] ?></div>
            <?php endif; ?>
        </div>

        <div class="mb-3">
            <label class="form-label">Pseudo</label>
            <input class="form-control" name="pseudo" value="<?= $values["pseudo"] ?>">
            <?php if (!empty($errors["pseudo"])): ?>
                <div class="text-danger small"><?= $errors["pseudo"] ?></div>
            <?php endif; ?>
        </div>

        <div class="mb-3">
            <label class="form-label">Email</label>
            <input class="form-control" name="mail" value="<?= $values["mail"] ?>">
            <?php if (!empty($errors["mail"])): ?>
                <div class="text-danger small"><?= $errors["mail"] ?></div>
            <?php endif; ?>
        </div>

        <div class="mb-3">
            <label class="form-label">IBAN (optional)</label>
            <input class="form-control" name="iban" value="<?= $values["iban"] ?>">
            <?php if (!empty($errors["iban"])): ?>
                <div class="text-danger small"><?= $errors["iban"] ?></div>
            <?php endif; ?>
        </div>

        <button class="btn btn-primary" type="submit">Save</button>
        <a class="btn btn-secondary" href="user/profile">Cancel</a>
    </form>
</main>

<footer>
    <?php require 'footer_menu.php'; ?>
</footer>
</body>
</html>
