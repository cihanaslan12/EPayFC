<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <base href="<?= Configuration::get("web_root") ?>">
    <link rel="stylesheet" href="<?= Configuration::get("web_root") ?>css/css_login.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
</head>

<body>
<h2 class="title">
    <i class="fa-solid fa-cart-shopping"></i> EPayFC
</h2>
<div class="container-login">
    <h3>Sign In</h3>
    <form action="user/login" method="post">
        <div class="container-input">
            <div class="input-wrapper">
                <i class="fa-regular fa-user" id="icons"></i>
                <input type="email" name="email" placeholder="Mail" value="<?= $mail ?>">
            </div>
            <?php if(isset($errors['mail'])) :?>
                <span class="error"><?= $errors['mail'] ?></span>
            <?php endif; ?>
            <div class="input-wrapper">
                <i class="fa-solid fa-key" id="icons"></i>
                <input type="password" name="password" placeholder="******" value="<?= $password ?>">
            </div>
            <?php if(isset($errors['password'])) :?>
                <span class="error"><?= $errors['password'] ?></span>
            <?php endif; ?>
        </div>
        <div class="container-submit">
            <button type="submit" name="login_user" id="btn-login">Login</button>
            <button type="submit" name="login_guest" id="btn-guest">Continue as guest</button>
            <a href="user/signup" id="signup">New here ? Click here to subscribe !</a>
        </div>
    </form>

    <div class="container-link">
        <?php if (Configuration::is_dev()): ?>
            <p id="orange">For Debug Purpose</p>
            <div class="user-link">
                <a href="user/debug/boris" id="user-link">Login as <b>boverhaegen@epfc.eu</b></a>
                <a href="user/debug/marc" id="user-link">Login as <b>mamichel@epfc.eu</b></a>
                <a href="user/debug/quentin" id="user-link">Login as <b>quhouben@epfc.eu</b></a>
                <a href="user/debug/xavier" id="user-link">Login as <b>xapigeolet@epfc.eu</b></a>
            </div>
            <a href="setup/install" id="green">Restore original data</a>
            <a href="setup/export" id="orange">Backup personal data</a>
            <a href="setup/install" id="orange">Restore personal data</a>
        <?php endif; ?>
    </div>
</div>
</body>
</html>