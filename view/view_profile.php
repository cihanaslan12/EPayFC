<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile</title>
    <base href="<?= Configuration::get("web_root") ?>">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
</head>

<body>
<header>
    <?php require 'header_menu.php'; ?>
</header>

<main class="container py-4">
    <h1 class="mb-4">Profile</h1>

    <div class="card p-3 mb-4">
        <div class="d-flex align-items-center gap-3">
            <?php if ($user->get_picture_path()): ?>
                <img src="<?= $user->get_picture_path() ?>" alt="Profile picture"
                     style="width:90px;height:90px;object-fit:cover;border-radius:50%;">
            <?php else: ?>
                <div style="width:90px;height:90px;border-radius:50%;background:#ddd;"></div>
            <?php endif; ?>

            <div>
                <p class="mb-1"><strong>Full name:</strong> <?= $user->get_full_name() ?></p>
                <p class="mb-1"><strong>Pseudo:</strong> <?= $user->get_pseudo() ?></p>
                <p class="mb-0"><strong>Email:</strong> <?= $user->get_mail() ?></p>
            </div>
        </div>
    </div>

    <div class="list-group">
        <a class="list-group-item list-group-item-action" href="user/sales">Sales</a>
        <a class="list-group-item list-group-item-action" href="user/purchases">Purchases</a>
        <a class="list-group-item list-group-item-action" href="user/edit_profile">Edit profile</a>
        <a class="list-group-item list-group-item-action" href="user/change_password">Change password</a>
        <a class="list-group-item list-group-item-action" href="user/profile_picture">Profile picture</a>
    </div>

    <form class="mt-4" method="post" action="user/logout">
        <button type="submit" class="btn btn-danger">Logout</button>
    </form>
</main>

<footer>
    <?php require 'footer_menu.php'; ?>
</footer>
</body>
</html>
