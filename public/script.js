(function () {
  var form = document.getElementById('login');
  if (!form) return;

  var mode = document.getElementById('mode');
  var role = document.getElementById('role');
  var password = document.getElementById('password');
  var confirm = document.getElementById('confirm');
  var name = document.getElementById('name');
  var toggle = document.getElementById('password-toggle');

  function render() {
    var registering = mode.value === 'register';
    form.dataset.mode = registering ? 'register' : 'login';
    document.getElementById('login-subtitle').textContent =
      (registering ? 'Create your ' : 'Access your ') +
      (role.value === 'coach' ? 'coaching workspace' : 'client log');
    document.getElementById('login-submit').textContent = registering ? 'Create account' : 'Log in';
    name.required = registering;
    confirm.required = registering;
    password.minLength = registering ? 12 : 1;
    password.autocomplete = registering ? 'new-password' : 'current-password';
  }

  mode.addEventListener('change', render);
  role.addEventListener('change', render);
  toggle.addEventListener('click', function () {
    var show = toggle.getAttribute('aria-pressed') !== 'true';
    toggle.setAttribute('aria-pressed', String(show));
    toggle.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
    password.type = show ? 'text' : 'password';
  });
  render();
})();
