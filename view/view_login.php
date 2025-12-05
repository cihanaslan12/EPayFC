<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Login</title>
        <base href="<?= Configuration::get("web_root") ?>">
        <link rel="stylesheet" href="">
    </head>

    <body>
        <h3 class="title">
            <img src="" alt="Logo">EPayFC
        </h3>
        <article>
            <h4>Sign In</h4>
            <hr>
            <form action="user/login" method="post">
                <input type="email" name="email" placeholder="Mail" value="<?= $mail ?>">
                <br>
                <?php if(isset($errors['mail'])) echo $errors['mail'] ?>
                <br>
                <input type="password" name="password" placeholder="******" value="<?= $password ?>">
                <br>
                <?php if(isset($errors['password'])) echo $errors['password'] ?>
                <br>
                <button type="submit" name="login_user">Login</button>
                <br>
                <button type="submit" name="login_guest">Continue as guest</button>
            </form>
            <br>
            <a href="user/signup">New here ? Click here to subscribe !</a>

            <?php if (Configuration::is_dev()): ?>
                <hr>
                <h5>For Debug Purpose</h5>
                <a href="user/debug/boris">Login as <b>boverhaegen@epfc.eu</b></a>
                <br>
                <a href="user/debug/marc">Login as <b>mamichel@epfc.eu</b></a>
                <br>
                <a href="user/debug/quentin">Login as <b>quhouben@epfc.eu</b></a>
                <br>
                <a href="user/debug/xavier">Login as <b>xapigeolet@epfc.eu</b></a>
                <br>
                <a href="setup/install">Restore original data</a>
                <br>
                <a href="setup/export">Backup personal data</a>
                <br>
                <a href="setup/install">Restore personal data</a>
            <?php endif; ?>
        </article>
    </body>
</html>