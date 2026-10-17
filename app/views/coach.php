<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="theme-color" content="#0d9488" />
    <title>SeshBank – Coach Dashboard</title>
    <meta name="description" content="Manage your clients, plans, and progress." />
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
          <span class="brand__badge">Coach Side</span>
        </a>
        <nav class="nav" aria-label="Primary">
          <a href="index.php?route=coach" aria-current="page">Clients</a>
          <a href="error.html">Schedule</a>
          <a href="error.html">Stats</a>
          <a href="error.html">Settings</a>
          <?php require __DIR__ . '/logout.php'; ?>
        </nav>
      </div>
    </header>

    <main class="page page--coach">
      <aside class="card coach-profile">
        <div class="coach-profile__cover">
          <img src="images/cover.png" alt="" />
        </div>
        <div class="avatar coach-profile__avatar">
          <img src="images/avatar-marcus.png" alt="Coach Marcus Vance" />
        </div>
        <h2 class="heading"><?= escape($user['name']) ?></h2>
        <p class="muted">Demo workspace: client and session data below are sample content.</p>
        <p class="coach-profile__role">Yoga &amp; Athletic Prep Spec</p>
        <ul class="coach-profile__list">
          <li>
            <a href="error.html" class="coach-profile__active">
              <span>Active Clients</span>
              <span class="coach-profile__count">12</span>
            </a>
          </li>
          <li><a href="error.html" class="coach-profile__row">3</a></li>
          <li><a href="error.html" class="coach-profile__row">8</a></li>
        </ul>
        <div class="coach-profile__spacer"></div>
      </aside>

      <section class="card clients">
        <div class="clients__head">
          <h2 class="heading">Clients</h2>
          <a href="error.html" class="clients__add">+ Add Client</a>
        </div>
        <label class="search">
          <svg class="icon-sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
          <input type="search" placeholder="Search by name or workout stage..." />
        </label>
        <ul class="clients__list">
          <li>
            <a href="index.php?route=coach" class="client-row client-row--active">
              <span class="client-row__info">
                <span class="avatar"><img src="images/avatar-sasha.png" alt="" /></span>
                <span class="client-row__text">
                  <span class="client-row__name">Sasha Gray</span>
                  <span class="client-row__plan">Pilates Routine</span>
                </span>
              </span>
              <svg class="icon-sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m9 18 6-6-6-6"/></svg>
            </a>
          </li>
          <li>
            <a href="error.html" class="client-row">
              <span class="client-row__info">
                <span class="avatar"><img src="images/avatar-1.png" alt="" /></span>
                <span class="client-row__text"><span class="client-row__plan">Strength Plan B</span></span>
              </span>
              <svg class="icon-sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m9 18 6-6-6-6"/></svg>
            </a>
          </li>
          <li>
            <a href="error.html" class="client-row">
              <span class="client-row__info">
                <span class="avatar"><img src="images/avatar-2.png" alt="" /></span>
                <span class="client-row__text"><span class="client-row__plan">Contortion Basics</span></span>
              </span>
              <svg class="icon-sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m9 18 6-6-6-6"/></svg>
            </a>
          </li>
          <li>
            <a href="error.html" class="client-row">
              <span class="client-row__info">
                <span class="avatar"><img src="images/avatar-3.png" alt="" /></span>
                <span class="client-row__text"><span class="client-row__plan">Ashtanga Core</span></span>
              </span>
              <svg class="icon-sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m9 18 6-6-6-6"/></svg>
            </a>
          </li>
          <li>
            <a href="error.html" class="client-row">
              <span class="client-row__info">
                <span class="avatar"><img src="images/avatar-4.png" alt="" /></span>
                <span class="client-row__text"><span class="client-row__plan">Advanced Acrobatics</span></span>
              </span>
              <svg class="icon-sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m9 18 6-6-6-6"/></svg>
            </a>
          </li>
          <li>
            <a href="error.html" class="client-row">
              <span class="client-row__info">
                <span class="avatar"><img src="images/avatar-5.png" alt="" /></span>
                <span class="client-row__text"><span class="client-row__plan">Rehab Stage 2</span></span>
              </span>
              <svg class="icon-sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m9 18 6-6-6-6"/></svg>
            </a>
          </li>
          <li>
            <a href="error.html" class="client-row">
              <span class="client-row__info">
                <span class="avatar"><img src="images/avatar-1.png" alt="" /></span>
                <span class="client-row__text"><span class="client-row__plan">Hypertrophy Week 4</span></span>
              </span>
              <svg class="icon-sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m9 18 6-6-6-6"/></svg>
            </a>
          </li>
          <li>
            <a href="error.html" class="client-row">
              <span class="client-row__info">
                <span class="avatar"><img src="images/avatar-3.png" alt="" /></span>
                <span class="client-row__text"><span class="client-row__plan">Vocal Breathwork</span></span>
              </span>
              <svg class="icon-sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m9 18 6-6-6-6"/></svg>
            </a>
          </li>
          <li>
            <a href="error.html" class="client-row">
              <span class="client-row__info">
                <span class="avatar"><img src="images/avatar-5.png" alt="" /></span>
                <span class="client-row__text"><span class="client-row__plan">Pop Choreography Prep</span></span>
              </span>
              <svg class="icon-sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m9 18 6-6-6-6"/></svg>
            </a>
          </li>
        </ul>
      </section>

      <section class="card detail">
        <div class="detail__head">
          <div class="avatar"><img src="images/avatar-sasha.png" alt="Sasha Gray" /></div>
          <div>
            <h2 class="heading">Sasha Gray</h2>
            <p>Active since Oct 2025 • Package Gold Tier</p>
          </div>
        </div>

        <dl class="detail__grid">
          <div><dt>Email</dt><dd>sasha.g@seshbank.com</dd></div>
          <div><dt>Phone</dt><dd>+1 (555) 349-2342</dd></div>
          <div><dt>Recent weight</dt><dd>62.4 kg</dd></div>
          <div><dt>Weekly goal</dt><dd>3 Intense Workouts</dd></div>
        </dl>

        <hr class="divider" />

        <h3 class="heading">Active Plans</h3>
        <div class="plans">
          <a href="error.html" class="plan">
            <img src="images/gym.png" alt="Gym with squat rack" />
            <span class="plan__label">Workout</span>
          </a>
          <a href="error.html" class="plan">
            <img src="images/dance.png" alt="Bright dance studio" />
            <span class="plan__label">Dance</span>
          </a>
        </div>

        <hr class="divider" />

        <div class="progress-head">
          <span class="muted">Monthly Target Completion</span>
          <strong>72%</strong>
        </div>
        <div class="progress" role="progressbar" aria-valuenow="72" aria-valuemin="0" aria-valuemax="100" aria-label="Monthly target completion">
          <div class="progress__bar"></div>
        </div>

        <a href="error.html" class="btn-primary">Advance Mode</a>
      </section>
    </main>
  </body>
</html>
