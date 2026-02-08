<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Signup</title>
    <base href="<?= Configuration::get("web_root") ?>">
    <link rel="stylesheet" href="<?= Configuration::get("web_root") ?>css/css_login.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
</head>

<body>
<h2 class="title">
    <i class="fa-solid fa-cart-shopping"></i> EPayFC
</h2>

<div class="container-login">
    <h3>Sign Up</h3>

    <form action="user/signup" method="post">
        <div class="container-input">
            <div class="input-wrapper">
                <i class="fa-regular fa-id-card" id="icons"></i>
                <input type="text" name="full_name" placeholder="Full name" value="<?= $full_name ?? "" ?>">
            </div>
            <?php if(isset($errors['full_name'])) :?>
                <span class="error"><?= $errors['full_name'] ?></span>
            <?php endif; ?>

            <div class="input-wrapper">
                <i class="fa-regular fa-envelope" id="icons"></i>
                <input type="email" name="mail" placeholder="Mail" value="<?= $mail ?? "" ?>">
            </div>
            <?php if(isset($errors['mail'])) :?>
                <span class="error"><?= $errors['mail'] ?></span>
            <?php endif; ?>

            <div class="input-wrapper">
                <i class="fa-regular fa-user" id="icons"></i>
                <input type="text" name="pseudo" placeholder="Pseudo" value="<?= $pseudo ?? "" ?>">
            </div>
            <?php if(isset($errors['pseudo'])) :?>
                <span class="error"><?= $errors['pseudo'] ?></span>
            <?php endif; ?>

            <div class="input-wrapper">
                <i class="fa-solid fa-key" id="icons"></i>
                <input type="password" name="password" placeholder="******" value="<?= $password ?? "" ?>">
            </div>
            <?php if(isset($errors['password'])) :?>
                <span class="error"><?= $errors['password'] ?></span>
            <?php endif; ?>

            <div class="input-wrapper">
                <i class="fa-solid fa-key" id="icons"></i>
                <input type="password" name="password_confirm" placeholder="Confirm password" value="<?= $password_confirm ?? "" ?>">
            </div>
            <?php if(isset($errors['password_confirm'])) :?>
                <span class="error"><?= $errors['password_confirm'] ?></span>
            <?php endif; ?>
        </div>

        <div class="container-submit">
            <button type="submit" id="btn-login">Create account</button>
            <a href="user/login" id="signup">Already have an account? Login</a>
        </div>
    </form>
</div>
</body>
</html>
