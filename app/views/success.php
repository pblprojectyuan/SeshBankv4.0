<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Success – SeshBank</title>
    <link rel="stylesheet" href="styles.css" />
  </head>
  <body>
    <main class="card">
      <p class="brand">SeshBank</p>
      <h1><?= !empty($_SESSION['registered']) ? 'Registration successful' : 'Login successful' ?></h1>
      <p>Welcome, <?= escape($user['name']) ?>. You are logged in.</p>
      <form method="post" action="index.php?route=logout">
        <input type="hidden" name="csrf_token" value="<?= escape(\SeshBank\Security\Csrf::token()) ?>" />
        <button type="submit">Log out</button>
      </form>
    </main>
  </body>
</html>
