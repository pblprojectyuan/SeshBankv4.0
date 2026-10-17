<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Register – SeshBank</title>
    <link rel="stylesheet" href="styles.css" />
  </head>
  <body>
    <main class="card">
      <p class="brand">SeshBank</p>
      <h1>Register</h1>
      <?php if ($error !== ''): ?>
        <p role="alert"><?= escape($error) ?></p>
      <?php endif; ?>
      <form method="post" action="index.php?route=register">
        <input type="hidden" name="csrf_token" value="<?= escape(\SeshBank\Security\Csrf::token()) ?>" />
        <label for="name">Full name</label>
        <input id="name" name="name" type="text" autocomplete="name" maxlength="100" required value="<?= escape($name) ?>" />
        <label for="email">Email address</label>
        <input id="email" name="email" type="email" autocomplete="username" maxlength="254" required value="<?= escape($email) ?>" />
        <label for="password">Password</label>
        <input id="password" name="password" type="password" autocomplete="new-password" minlength="12" maxlength="72" required aria-describedby="password-help" />
        <p id="password-help">Use 12–72 bytes (ASCII characters use one byte).</p>
        <label for="confirm">Confirm password</label>
        <input id="confirm" name="confirm_password" type="password" autocomplete="new-password" maxlength="72" required />
        <button type="submit">Register</button>
      </form>
      <p>Already registered? <a href="index.php?route=login">Login</a></p>
    </main>
  </body>
</html>
