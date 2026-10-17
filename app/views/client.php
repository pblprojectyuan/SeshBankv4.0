<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="theme-color" content="#0d9488" />
    <title>SeshBank – Client Simple Mode</title>
    <meta name="description" content="Your sessions, credits, and coach notes in one place." />
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
          <span class="brand__badge">User Side</span>
        </a>
        <nav class="nav" aria-label="Primary">
          <a href="index.php">Home</a>
          <a href="index.php?route=client" aria-current="page">Simple Mode</a>
          <a href="error.html">Advanced</a>
          <a href="error.html">My History</a>
          <?php require __DIR__ . '/logout.php'; ?>
        </nav>
      </div>
    </header>

    <main class="page page--client">
      <section class="stack client-side">
        <div class="client-hero">
          <img src="images/window.png" alt="Woman sitting in front of a bright window" />
          <div class="client-hero__box">
            <h2 class="heading">Elevate your wellness journey</h2>
            <p>Direct constant access to professional guidance, nutrition matching, and personal kinesiologist archives.</p>
          </div>
        </div>
        <a href="error.html" class="btn-primary">Explore Available Packages</a>
      </section>

      <section class="card client-panel">
        <div class="client-panel__head">
          <div class="avatar"><img src="images/avatar-sasha.png" alt="Sasha Gray" /></div>
          <div>
            <h2 class="heading"><?= escape($user['name']) ?> (Client)</h2>
            <p>Demo workspace: sessions, credits, and coach details below are sample content.</p>
          </div>
        </div>

        <dl class="client-panel__details">
          <div><dt>Assigned specialist</dt><dd>Coach Marcus Vance</dd></div>
          <div><dt>Current balance</dt><dd>14 Credits Remaining</dd></div>
          <div><dt>Next scheduled session</dt><dd>Tomorrow at 09:30 AM</dd></div>
        </dl>

        <div class="filter-row">
          <label class="search">
            <svg class="icon-sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
            <input type="search" placeholder="Filter session notes..." />
          </label>
          <a href="error.html" class="filter-btn" aria-label="Filter options">
            <svg class="icon-sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="21" x2="14" y1="4" y2="4"/><line x1="10" x2="3" y1="4" y2="4"/><line x1="21" x2="12" y1="12" y2="12"/><line x1="8" x2="3" y1="12" y2="12"/><line x1="21" x2="16" y1="20" y2="20"/><line x1="12" x2="3" y1="20" y2="20"/><line x1="14" x2="14" y1="2" y2="6"/><line x1="8" x2="8" y1="10" y2="14"/><line x1="16" x2="16" y1="18" y2="22"/></svg>
          </a>
        </div>

        <h3 class="heading">Completed Sessions &amp; Log Notes</h3>
        <ul class="sessions">
          <li class="session">
            <div class="session__head">
              <h4>Vinyasa Core Align</h4>
              <span>Dec 18, 2025</span>
            </div>
            <p>Spine tracking assessment complete. Recommended additional lower back stretching prior to bed.</p>
            <div class="session__by">
              <span class="avatar"><img src="images/avatar-marcus.png" alt="" /></span>
              Logged by Marcus Vance
            </div>
          </li>
          <li class="session">
            <div class="session__head">
              <h4>Contortion Foundation III</h4>
              <span>Dec 12, 2025</span>
            </div>
            <p>Hip mobility focus. Dorsiflexion was solid. Continued alignment tracking logged.</p>
            <div class="session__by">
              <span class="avatar"><img src="images/avatar-marcus.png" alt="" /></span>
              Logged by Marcus Vance
            </div>
          </li>
          <li class="session">
            <div class="session__head">
              <h4>Dynamic Cardio Assessment</h4>
              <span>Dec 05, 2025</span>
            </div>
            <p>Maximum cardiac performance logged. Recovery is exceptionally fast.</p>
            <div class="session__by">
              <span class="avatar"><img src="images/avatar-marcus.png" alt="" /></span>
              Logged by Marcus Vance
            </div>
          </li>
          <li class="session">
            <div class="session__head">
              <h4>Initial Wellness Intake</h4>
              <span>Nov 28, 2025</span>
            </div>
            <p>Setup plan, customized Ashtanga core structure assigned.</p>
            <div class="session__by">
              <span class="avatar"><img src="images/avatar-marcus.png" alt="" /></span>
              Logged by Marcus Vance
            </div>
          </li>
        </ul>
      </section>
    </main>
  </body>
</html>
