/* Michael Tourism - partial save / resume.
 *
 * Forms opt in with:
 *   data-draft-key="crud:clients:0"
 *   data-draft-route="/clients/new"
 *   data-draft-label="New client database"
 *
 * The server stores the URL-encoded form payload in partial_saves. On the
 * next sign-in, index.php reopens the latest draft with ?resume=1.
 */
(function () {
  'use strict';

  var body = document.body;
  var saveUrl = body.getAttribute('data-draft-save-url');
  var discardUrl = body.getAttribute('data-draft-discard-url');
  if (!saveUrl || !discardUrl) return;

  var csrfMeta = document.querySelector('meta[name="csrf-token"]');
  var csrf = csrfMeta ? csrfMeta.getAttribute('content') : '';
  var forms = Array.prototype.slice.call(document.querySelectorAll('form[data-draft-key]'));
  if (!forms.length) return;

  var AUTOSAVE_MS = 45000;              // keep drafts fresh while the user works
  var NINE_MINUTES_MS = 9 * 60 * 1000;  // explicit checkpoint requested by the desk

  function serialize(form) {
    var params = new URLSearchParams();
    var data = new FormData(form);
    data.forEach(function (value, key) {
      if (key === '_draft_payload' || key === '_draft_key' ||
          key === '_draft_route' || key === '_draft_label') return;
      params.append(key, value);
    });
    return params.toString();
  }

  function bodyFor(form, payload) {
    var body = new URLSearchParams();
    body.set('_csrf', csrf);
    body.set('_draft_key', form.getAttribute('data-draft-key') || '');
    body.set('_draft_route', form.getAttribute('data-draft-route') || '');
    body.set('_draft_label', form.getAttribute('data-draft-label') || '');
    body.set('_draft_payload', payload);
    return body;
  }

  function status(form, text, kind) {
    var el = form.querySelector('[data-draft-status]');
    if (!el) return;
    el.textContent = text;
    el.className = 'draft-status' + (kind ? ' ' + kind : '');
  }

  function markDirty(form) {
    if (!form) return;
    form.dataset.draftDirty = '1';
  }

  function save(form, force) {
    var key = form.getAttribute('data-draft-key');
    var route = form.getAttribute('data-draft-route');
    if (!key || !route) return Promise.resolve(false);
    if (form.dataset.draftSubmitting === '1') return Promise.resolve(true);
    if (!force && form.dataset.draftDirty !== '1') return Promise.resolve(true);

    var payload = serialize(form);
    if (!force && payload === form.dataset.draftLast) {
      form.dataset.draftDirty = '';
      return Promise.resolve(true);
    }

    status(form, 'Saving partial save...', '');
    return fetch(saveUrl, {
      method: 'POST',
      credentials: 'same-origin',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8' },
      body: bodyFor(form, payload).toString()
    }).then(function (res) {
      return res.json().catch(function () { return { ok: false }; });
    }).then(function (json) {
      if (json && json.ok) {
        form.dataset.draftLast = payload;
        // If the user kept typing while the request was in flight, keep the
        // form dirty so the next autosave stores the newer values too.
        form.dataset.draftDirty = serialize(form) === payload ? '' : '1';
        status(form, 'Partial save saved at ' + (json.saved_at || 'now') + '.', 'ok');
        return true;
      }
      status(form, 'Partial save could not be stored. Run the Phase 2 migration.', 'bad');
      return false;
    }).catch(function () {
      status(form, 'Partial save could not be stored. Check the connection.', 'bad');
      return false;
    });
  }

  function beacon(form) {
    if (!form || form.dataset.draftSubmitting === '1' || form.dataset.draftDirty !== '1') return;
    var payload = serialize(form);
    if (payload === form.dataset.draftLast) return;
    var data = bodyFor(form, payload);
    if (navigator.sendBeacon) {
      navigator.sendBeacon(saveUrl, data);
    } else {
      fetch(saveUrl, {
        method: 'POST',
        credentials: 'same-origin',
        keepalive: true,
        headers: { 'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8' },
        body: data.toString()
      });
    }
  }

  function formOf(el) {
    return el && el.closest ? el.closest('form[data-draft-key]') : null;
  }

  /* Initialise: remember the current values so an untouched form is not saved. */
  forms.forEach(function (form) {
    form.dataset.draftLast = serialize(form);
    form.dataset.draftDirty = '';
  });

  document.addEventListener('input', function (e) {
    markDirty(formOf(e.target));
  }, true);
  document.addEventListener('change', function (e) {
    markDirty(formOf(e.target));
  }, true);
  document.addEventListener('click', function (e) {
    if (e.target.closest && e.target.closest('#add-day, #add-item, .remove-row')) {
      markDirty(formOf(e.target));
    }
  }, true);

  /* A real record save clears the draft on the server. Stop pagehide/beacon
     from recreating it while the POST is in flight. */
  document.addEventListener('submit', function (e) {
    var form = e.target;
    if (form && form.matches && form.matches('form[data-draft-key]')) {
      form.dataset.draftDirty = '';
      form.dataset.draftSubmitting = '1';
    }
  }, true);

  /* Keep a draft current during long sessions. */
  window.setInterval(function () {
    forms.forEach(function (form) { save(form, false); });
  }, AUTOSAVE_MS);

  /* A visible checkpoint at the nine-minute mark if there are unsaved edits. */
  window.setTimeout(function () {
    forms.forEach(function (form) { save(form, false); });
  }, NINE_MINUTES_MS);

  /* Flush on tab close, refresh, or switching away. */
  window.addEventListener('pagehide', function () {
    forms.forEach(beacon);
  });
  document.addEventListener('visibilitychange', function () {
    if (document.visibilityState === 'hidden') forms.forEach(beacon);
  });

  /* Explicit "Save partial" button. */
  document.querySelectorAll('[data-draft-save]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var form = formOf(btn);
      if (form) save(form, true);
    });
  });

  /* Discard the restored draft without touching the record. */
  document.querySelectorAll('[data-draft-discard]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var form = formOf(btn);
      if (!form) return;
      if (!window.confirm('Discard this partial save? The saved record will not be changed.')) return;

      var data = new URLSearchParams();
      data.set('_csrf', csrf);
      data.set('_draft_key', form.getAttribute('data-draft-key') || '');

      fetch(discardUrl, {
        method: 'POST',
        credentials: 'same-origin',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8' },
        body: data.toString()
      }).then(function (res) {
        return res.json().catch(function () { return { ok: false }; });
      }).then(function (json) {
        if (!json || !json.ok) {
          status(form, 'Could not discard the partial save.', 'bad');
          return;
        }
        // Reload so the form shows the saved record instead of the draft values.
        window.location.reload();
      });
    });
  });

  /* Save before the Sign out link navigates away. */
  document.querySelectorAll('a[data-draft-flush]').forEach(function (link) {
    link.addEventListener('click', function (e) {
      if (!forms.length) return;
      e.preventDefault();
      var href = link.href;
      var saving = Promise.all(forms.map(function (form) { return save(form, false); }));
      var timeout = new Promise(function (resolve) { window.setTimeout(resolve, 3000); });
      Promise.race([saving, timeout]).then(function () { window.location.href = href; });
    });
  });
})();
