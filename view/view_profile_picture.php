<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage profile picture</title>
    <base href="<?= Configuration::get("web_root") ?>">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
</head>
<style>
    body { padding-top: 60px; padding-bottom: 100px;}
</style>
<body>
<header>
    <?php require 'header_menu.php'; ?>
</header>

<main class="container py-4">
    <h1 class="mb-4">Manage profile picture</h1>

    <?php $errors = $errors ?? []; ?>

    <div class="card mb-4">
        <div class="card-header"><strong>Current Profile Picture</strong></div>
        <div class="card-body text-center">
            <?php if ($user->get_picture_path()): ?>
                <img src="<?= $user->get_picture_path() ?>"
                     alt="Profile picture"
                     style="width:140px;height:140px;object-fit:cover;border-radius:50%;"
                     class="mb-3">
            <?php else: ?>
                <div class="mx-auto mb-3" style="width:140px;height:140px;border-radius:50%;background:#ddd;"></div>
            <?php endif; ?>

            <div class="fw-semibold"><?= $user->get_full_name() ?></div>
            <div class="text-muted">@<?= $user->get_pseudo() ?></div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header"><strong>Upload New Picture</strong></div>
        <div class="card-body">
            <form method="post" enctype="multipart/form-data">
                <div class="mb-2">
                    <label class="form-label fw-semibold">Select Image *</label>
                    <input type="file" name="picture" class="form-control">
                    <div class="form-text">
                        Supported formats: JPG, PNG, GIF, WebP. This will replace your current profile picture.
                    </div>
                </div>

                <?php if (isset($errors["picture"])): ?>
                    <div class="text-danger small mb-2"><?= $errors["picture"] ?></div>
                <?php endif; ?>

                <button type="submit" name="upload_picture" class="btn btn-primary">
                    Upload Picture
                </button>
            </form>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header"><strong>Remove Current Picture</strong></div>
        <div class="card-body">
            <div class="alert alert-warning mb-3">
                This will permanently delete your current profile picture. You can upload a new one anytime.
            </div>

            <form method="post">
                <button type="submit" name="delete_picture" class="btn btn-danger">
                    Delete Picture
                </button>
            </form>
        </div>
    </div>
</main>

<footer>
    <?php require 'footer_menu.php'; ?>
</footer>
</body>
</html>
