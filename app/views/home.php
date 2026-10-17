<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="theme-color" content="#0d9488" />
    <title>SeshBank – Professional tools for wellness leaders</title>
    <meta name="description" content="Coaching workspace and client log for wellness professionals." />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
      href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Outfit:wght@500;600&display=swap"
      rel="stylesheet"
    />
    <link rel="stylesheet" href="styles.css" />
  </head>
  <body>
    <header class="site-header">
      <div class="site-header__inner">
        <a href="index.php" class="brand">
          <span class="brand__mark">
            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 6c.6.5 1.2 1 2.5 1C7 7 7 5 9.5 5c2.6 0 2.4 2 5 2 2.5 0 2.5-2 5-2 1.3 0 1.9.5 2.5 1"/><path d="M2 12c.6.5 1.2 1 2.5 1 2.5 0 2.5-2 5-2 2.6 0 2.4 2 5 2 2.5 0 2.5-2 5-2 1.3 0 1.9.5 2.5 1"/><path d="M2 18c.6.5 1.2 1 2.5 1 2.5 0 2.5-2 5-2 2.6 0 2.4 2 5 2 2.5 0 2.5-2 5-2 1.3 0 1.9.5 2.5 1"/></svg>
          </span>
          <span class="brand__name">SeshBank</span>
          <span class="brand__badge">Welcome</span>
        </a>
        <nav class="nav" aria-label="Primary">
          <a href="index.php" aria-current="page">Home</a>
          <a href="error.html">Features</a>
          <a href="error.html">Pricing</a>
        </nav>
      </div>
    </header>

    <main class="page page--landing">
      <section class="stack">
        <div class="hero-image">
          <img src="images/studio.png" alt="Bright minimalist wellness studio" />
          <p class="hero-image__caption">Professional tools for wellness leaders.</p>
        </div>
        <div class="card scale-card">
          <h2 class="heading">Built for Scale</h2>
          <ul>
            <li>
              <svg class="icon-sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg>
              Effortless scheduling directly in client calendars
            </li>
            <li>
              <svg class="icon-sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg>
              Integrated finance and custom milestone metrics
            </li>
            <li>
              <svg class="icon-sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg>
              Branded experience to match your wellness boutique
            </li>
          </ul>
          <div class="scale-card__image">
            <img src="images/notebook.png" alt="Open notebook with pencil and latte" />
          </div>
        </div>
      </section>

      <section class="integrations">
        <h2 class="heading">Key Integrations</h2>
        <div class="integrations__list">
          <article class="card integration">
            <h3>Calorie &amp; Nutrition Mapping</h3>
            <div class="integration__body">
              <p>Import seamless meal plans and metric logs directly to client files.</p>
              <div class="integration__image">
                <img src="images/avocado.png" alt="Avocado toast with poached egg and tomatoes" />
              </div>
            </div>
          </article>
          <article class="card integration">
            <h3>Video Motion Assessment</h3>
            <div class="integration__body">
              <p>Record form, post clips, and annotate key postures for dance &amp; weight training.</p>
              <div class="integration__image integration__image--contain">
                <img src="images/silhouette.png" alt="Silhouette of a person in a lunge pose" />
              </div>
            </div>
          </article>
          <article class="card integration">
            <h3>Automatic Billing &amp; Ledgers</h3>
            <div class="integration__body">
              <p>Never chase another invoice. Auto-charge after session completion.</p>
              <div class="integration__image">
                <img src="images/skyline.png" alt="Abstract blue city skyline" />
              </div>
            </div>
          </article>
        </div>
      </section>

      <section>
        <form id="login" class="card login" data-mode="<?= escape($mode) ?>" method="post" action="index.php?route=auth">
          <input type="hidden" name="csrf_token" value="<?= escape(\SeshBank\Security\Csrf::token()) ?>" />
          <label for="mode" class="field-label">Welcome to SeshBank</label>
          <select id="mode" name="mode" class="field">
            <option value="login" <?= $mode === 'login' ? 'selected' : '' ?>>Log in</option>
            <option value="register" <?= $mode === 'register' ? 'selected' : '' ?>>Create account</option>
          </select>
          <p id="login-subtitle" class="login__subtitle">Access your coaching workspace</p>
          <?php if ($error !== ''): ?>
            <p role="alert"><?= escape($error) ?></p>
          <?php endif; ?>
          <noscript><style>.signin-only { display: block !important; }</style><p>For registration, fill in all fields below. For login, only email, password, and role are needed.</p></noscript>

          <div class="login__fields">
            <div>
              <label for="role" class="field-label">Select role</label>
              <select id="role" name="role" class="field">
                <option value="coach" <?= $role === 'coach' ? 'selected' : '' ?>>Professional Coach</option>
                <option value="client" <?= $role === 'client' ? 'selected' : '' ?>>Client</option>
              </select>
            </div>

            <div class="signin-only">
              <label for="name" class="field-label">Full name</label>
              <input id="name" name="name" type="text" class="field" placeholder="Your full name" autocomplete="name" maxlength="100" value="<?= escape($name) ?>" />
            </div>

            <div>
              <label for="email" class="field-label">Email address</label>
              <input id="email" name="email" type="email" class="field" autocomplete="username" maxlength="254" required value="<?= escape($email) ?>" />
            </div>

            <div>
              <label for="password" class="field-label">Password</label>
              <div class="password-wrap">
                <input id="password" name="password" type="password" class="field field--password" autocomplete="current-password" maxlength="72" required aria-describedby="password-help" />
                <button id="password-toggle" type="button" class="password-toggle" aria-pressed="false" aria-label="Show password">
                  <svg class="icon icon-eye" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2.062 12.348a1 1 0 0 1 0-.696 10.75 10.75 0 0 1 19.876 0 1 1 0 0 1 0 .696 10.75 10.75 0 0 1-19.876 0"/><circle cx="12" cy="12" r="3"/></svg>
                  <svg class="icon icon-eye-off" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M10.733 5.076a10.744 10.744 0 0 1 11.205 6.575 1 1 0 0 1 0 .696 10.747 10.747 0 0 1-1.444 2.49"/><path d="M14.084 14.158a3 3 0 0 1-4.242-4.242"/><path d="M17.479 17.499a10.75 10.75 0 0 1-15.417-5.151 1 1 0 0 1 0-.696 10.75 10.75 0 0 1 4.446-5.143"/><path d="m2 2 20 20"/></svg>
                </button>
              </div>
              <p id="password-help" class="muted">New passwords must be 12–72 bytes long (ASCII characters use one byte).</p>
            </div>

            <div class="signin-only">
              <label for="confirm" class="field-label">Confirm password</label>
              <input id="confirm" name="confirm_password" type="password" class="field field--password" autocomplete="new-password" maxlength="72" />
            </div>

            <button id="login-submit" type="submit" class="btn-primary login__submit">Log in</button>
          </div>
        </form>
      </section>
    </main>

    <script src="script.js"></script>
  </body>
</html>
