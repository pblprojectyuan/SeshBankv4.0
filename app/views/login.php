<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Login – SeshBank</title>
    <link rel="stylesheet" href="styles.css" />
  </head>
  <body>
    <main class="card">
      <p class="brand">SeshBank</p>
      <h1>Login</h1>
      <?php if ($error !== ''): ?>
        <p role="alert"><?= escape($error) ?></p>
      <?php endif; ?>
      <form method="post" action="index.php?route=login">
        <input type="hidden" name="csrf_token" value="<?= escape(\SeshBank\Security\Csrf::token()) ?>" />
        <label for="email">Email address</label>
        <input id="email" name="email" type="email" autocomplete="username" maxlength="254" required value="<?= escape($email) ?>" />
        <label for="password">Password</label>
        <input id="password" name="password" type="password" autocomplete="current-password" maxlength="72" required />
        <button type="submit">Log in</button>
      </form>
      <p>No account? <a href="index.php?route=register">Register</a></p>
    </main>
  </body>
</html>
