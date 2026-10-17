(function () {
  var roleConfig = {
    coach: {
      subtitle: 'Access your coaching workspace',
      create: 'Create your coaching workspace',
      email: 'coach@seshbank.com',
      name: 'Coach Marcus Vance',
      href: 'coach.html',
    },
    client: {
      subtitle: 'Access your client log',
      create: 'Create your client log',
      email: 'client@seshbank.com',
      name: 'Sasha Gray',
      href: 'client.html',
    },
  };

  var dropdowns = document.querySelectorAll('.dropdown');

  function closeAll(except) {
    dropdowns.forEach(function (dd) {
      if (dd !== except) {
        dd.dataset.open = 'false';
        dd.querySelector('.dropdown__trigger').setAttribute('aria-expanded', 'false');
      }
    });
  }

  dropdowns.forEach(function (dd) {
    var trigger = dd.querySelector('.dropdown__trigger');
    var labelEl = dd.querySelector('.dropdown__value');
    var options = dd.querySelectorAll('.dropdown__option');

    trigger.addEventListener('click', function () {
      var isOpen = dd.dataset.open === 'true';
      closeAll(dd);
      dd.dataset.open = isOpen ? 'false' : 'true';
      trigger.setAttribute('aria-expanded', String(!isOpen));
    });

    options.forEach(function (opt) {
      opt.addEventListener('click', function () {
        options.forEach(function (o) {
          o.setAttribute('aria-selected', 'false');
        });
        opt.setAttribute('aria-selected', 'true');
        labelEl.textContent = opt.querySelector('span').textContent;
        dd.dataset.value = opt.dataset.value;
        dd.dataset.open = 'false';
        trigger.setAttribute('aria-expanded', 'false');
        dd.dispatchEvent(new CustomEvent('change', { detail: opt.dataset.value }));
      });
    });
  });

  document.addEventListener('pointerdown', function (e) {
    if (!e.target.closest('.dropdown')) closeAll(null);
  });

  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') closeAll(null);
  });

  var login = document.getElementById('login');
  if (!login) return;

  var modeDd = document.getElementById('mode-dropdown');
  var roleDd = document.getElementById('role-dropdown');
  var subtitle = document.getElementById('login-subtitle');
  var email = document.getElementById('email');
  var name = document.getElementById('name');
  var submit = document.getElementById('login-submit');
  var toggle = document.getElementById('password-toggle');
  var password = document.getElementById('password');

  function render() {
    var mode = modeDd.dataset.value;
    var role = roleDd.dataset.value;
    var cfg = roleConfig[role];
    login.dataset.mode = mode;
    subtitle.textContent = mode === 'login' ? cfg.subtitle : cfg.create;
    submit.textContent = mode === 'login' ? 'Log in' : 'Sign in';
    name.placeholder = cfg.name;
  }

  modeDd.addEventListener('change', render);
  roleDd.addEventListener('change', function (e) {
    email.value = roleConfig[e.detail].email;
    render();
  });

  toggle.addEventListener('click', function () {
    var show = toggle.getAttribute('aria-pressed') !== 'true';
    toggle.setAttribute('aria-pressed', String(show));
    toggle.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
    password.type = show ? 'text' : 'password';
  });

  login.addEventListener('submit', function (e) {
    e.preventDefault();
    window.location.href = roleConfig[roleDd.dataset.value].href;
  });
})();
