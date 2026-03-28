(function () {
  var form = document.getElementById('dispatch-form');
  var btn = document.getElementById('dispatch-btn');
  if (!form || !btn) return;

  form.addEventListener('submit', function () {
    btn.classList.add('is-loading');
    btn.setAttribute('disabled', 'disabled');
  });
})();
