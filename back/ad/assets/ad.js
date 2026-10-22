(function () {
  'use strict';

  if (typeof App !== 'undefined' && typeof App.init === 'function') {
    App.init();
  }

  function notify(message) {
    var old = document.querySelector('.ad-toast');
    if (old) old.remove();

    var toast = document.createElement('div');
    toast.className = 'ad-toast';
    toast.textContent = '✓ ' + message;
    toast.style.cssText = 'position:fixed;right:24px;top:85px;z-index:1100;padding:10px 16px;border-radius:5px;background:#173a62;color:#fff;box-shadow:0 5px 16px rgba(0,0,0,.2);';
    document.body.appendChild(toast);
    window.setTimeout(function () { toast.remove(); }, 1800);
  }

  function setModalContent(selector, html) {
    var element = document.querySelector(selector);
    if (element) element.innerHTML = html;
  }

  function showModal(modalSelector, contentSelector, message) {
    setModalContent(contentSelector,
      '<div class="modal-content"><div class="modal-body text-center">' +
      '<i class="fa fa-spinner fa-spin"></i> ' + message +
      '</div></div>'
    );

    if (window.jQuery && typeof window.jQuery(modalSelector).modal === 'function') {
      window.jQuery(modalSelector).modal('show');
    }
  }

  function escapeHtml(value) {
    return String(value == null ? '' : value)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;');
  }

  function setViewContext(title, description, toolbarVisible) {
    var page = document.querySelector('.ad-page');
    if (!page) return;
    var titleNode = page.querySelector('.title span');
    if (titleNode) titleNode.textContent = title;
    var bannerTitle = page.querySelector('.banner h2');
    if (bannerTitle) bannerTitle.textContent = title;
    var bannerText = page.querySelector('.banner p');
    if (bannerText) bannerText.textContent = description;
    var shellToolbar = page.querySelector('.main > .toolbar');
    if (shellToolbar) shellToolbar.style.display = toolbarVisible ? '' : 'none';
  }

  function makeTableSortable(selector) {
    var table = document.querySelector(selector);
    if (!table || table.getAttribute('data-sort-ready') === '1') return;
    table.setAttribute('data-sort-ready', '1');
    table.querySelectorAll('thead th').forEach(function (header, columnIndex) {
      header.style.cursor = 'pointer';
      header.title = 'Click to sort';
      header.addEventListener('click', function () {
        var body = table.tBodies[0];
        if (!body) return;
        var ascending = header.getAttribute('data-sort-direction') !== 'asc';
        table.querySelectorAll('thead th').forEach(function (item) { item.removeAttribute('data-sort-direction'); });
        header.setAttribute('data-sort-direction', ascending ? 'asc' : 'desc');
        var rows = Array.prototype.slice.call(body.rows);
        rows.sort(function (left, right) {
          var a = (left.cells[columnIndex] ? left.cells[columnIndex].textContent : '').trim();
          var b = (right.cells[columnIndex] ? right.cells[columnIndex].textContent : '').trim();
          var aNumeric = a.replace(/[,\s]|KES|%/gi, '');
          var bNumeric = b.replace(/[,\s]|KES|%/gi, '');
          var an = parseFloat(aNumeric);
          var bn = parseFloat(bNumeric);
          var bothNumbers = /^-?\d+(\.\d+)?$/.test(aNumeric) && /^-?\d+(\.\d+)?$/.test(bNumeric);
          var result = bothNumbers ? an - bn : a.localeCompare(b, undefined, { numeric: true, sensitivity: 'base' });
          return ascending ? result : -result;
        });
        rows.forEach(function (row) { body.appendChild(row); });
      });
    });
  }

  function renderStaffRows(records) {
    var body = document.querySelector('#staff-table-body');
    if (!body) return;
    if (!records.length) {
      body.innerHTML = '<tr><td colspan="8" style="text-align:center;padding:20px;">No staff records found.</td></tr>';
      return;
    }

    body.innerHTML = records.map(function (record, index) {
      var summary = record.summary || {};
      var check = (record.profile_checks || [])[0] || {};
      var employment = (record.employment || [])[0] || {};
      var completion = Math.max(0, Math.min(100, Number(check.completion_percent || 0)));
      var status = summary.work_status || employment.work_status || 'Active';
      var access = Number(summary.user_status || 0) > 0 ? 'System User' : 'No User';
      return '<tr>' +
        '<td>' + (index + 1) + '</td>' +
        '<td><b>' + escapeHtml(summary.name || 'Unnamed staff') + '</b><br><small>' + escapeHtml(summary.alias || '') + '</small></td>' +
        '<td>' + escapeHtml(summary.phone || '—') + '<br><small>' + escapeHtml(summary.email || '—') + '</small></td>' +
        '<td>' + escapeHtml(summary.location || 'No residence') + '<br><small>' + escapeHtml(employment.department_name || 'No department') + '</small></td>' +
        '<td><b>' + completion + '%</b><div class="progress"><i style="width:' + completion + '%"></i></div><small>Profile checks: ' + escapeHtml(String(record.documents ? record.documents.length : 0)) + ' documents</small></td>' +
        '<td><span class="pill">● ' + escapeHtml(status) + '</span></td>' +
        '<td><span class="pill access">◆ ' + escapeHtml(access) + '</span></td>' +
        '<td><button class="open" data-staff-id="' + Number(summary.staff_id || 0) + '">◉ Open</button></td>' +
        '</tr>';
    }).join('');
    makeTableSortable('#staff-table');

    var visible = document.querySelector('#staff-visible');
    var total = document.querySelector('#staff-total');
    if (visible) visible.textContent = records.length;
    if (total) total.textContent = records.length;

    body.querySelectorAll('.open').forEach(function (button) {
      button.addEventListener('click', function () {
        showModal('#modal', '#load', 'Loading staff record...');
        notify('Staff #' + button.getAttribute('data-staff-id'));
      });
    });
  }

  function loadStaffShell() {
    var container = document.querySelector('#staff-list');
    if (!container) return Promise.resolve();
    setViewContext('STAFF DIRECTORY', 'Manage staff records, employment data, user access, documents, and next of kin from one place.', true);

    return fetch('sta/stalist.php', { headers: { Accept: 'text/html' } })
      .then(function (response) {
        if (!response.ok) throw new Error('Staff shell request failed');
        return response.text();
      })
      .then(function (html) {
        container.innerHTML = html;
        var legacyTable = container.nextElementSibling;
        if (legacyTable && legacyTable.classList.contains('table-card')) legacyTable.remove();
      });
  }

  function loadUserList(mode) {
    mode = mode || 'all';
    setViewContext(mode === 'suspended' ? 'SUSPENDED USERS' : 'SYSTEM USERS', mode === 'suspended' ? 'Review and restore accounts that are currently suspended.' : 'Manage user accounts, credentials, roles, permissions, and targets.', false);
    var container = document.querySelector('#ad-user-list');
    if (!container) return;
    document.querySelector('#staff-list').style.display = 'none';
    container.style.display = 'block';
    container.innerHTML = '<div class="table-card" style="padding:20px;text-align:center">Loading users...</div>';
    fetch('user/userlist.php', { headers: { Accept: 'text/html' } })
      .then(function (response) { return response.text(); })
      .then(function (html) { container.innerHTML = html; return fetch('api/ad_api.php?action=users', { headers: { Accept: 'application/json' } }); })
      .then(function (response) { return response.json(); })
      .then(function (response) { if (!response.ok) throw new Error(response.message || 'Users unavailable'); var users = (response.data && response.data.users) || []; if (mode === 'suspended') users = users.filter(function (user) { return Number(user.work_status || 2) === 1; }); renderUserRows(users); var add = document.querySelector('#ad-new-user'); if (add) add.style.display = mode === 'suspended' ? 'none' : ''; })
      .catch(function () { container.innerHTML = '<div class="table-card" style="padding:20px;color:#c33">User list unavailable.</div>'; });
  }

  function rolePanel(value) {
    return Number(value) === 1 ? 'Back End' : (Number(value) === 2 ? 'Front End' : 'Both Panels');
  }

  function loadRoleList() {
    setViewContext('USER ROLES & ACCESS', 'Define access panels, dashboard access, and front-end permissions for every system role.', false);
    var roles = document.querySelector('#ad-role-list');
    if (!roles) return;
    document.querySelector('#staff-list').style.display = 'none';
    var users = document.querySelector('#ad-user-list');
    if (users) users.style.display = 'none';
    roles.style.display = 'block';
    roles.innerHTML = '<div class="table-card" style="padding:20px;text-align:center">Loading roles...</div>';
    fetch('roles/rolelist.php', { headers: { Accept: 'text/html' } }).then(function (response) { return response.text(); }).then(function (html) {
      roles.innerHTML = html;
      return fetch('api/ad_api.php?action=role_list', { headers: { Accept: 'application/json' } });
    }).then(function (response) { return response.json(); }).then(function (response) {
      if (!response.ok) throw new Error(response.message || 'Roles unavailable');
      renderRoleRows((response.data && response.data.roles) || []);
      var add = document.querySelector('#ad-new-role'); if (add) add.addEventListener('click', function () { openRoleModal(0); });
      var refresh = document.querySelector('#ad-refresh-roles'); if (refresh) refresh.addEventListener('click', loadRoleList);
      var search = document.querySelector('#ad-role-search'); if (search) search.addEventListener('input', function () { renderRoleRows((response.data.roles || []).filter(function (role) { return (role.role_name + ' ' + role.role_description).toLowerCase().indexOf(search.value.toLowerCase()) !== -1; })); });
    }).catch(function () { roles.innerHTML = '<div class="table-card" style="padding:20px;color:#c33">Role list unavailable.</div>'; });
  }

  function renderRoleRows(roles) {
    var body = document.querySelector('#ad-role-table-body'); if (!body) return;
    body.innerHTML = roles.length ? roles.map(function (role, index) {
      return '<tr><td>' + (index + 1) + '</td><td><b>' + escapeHtml(role.role_name || '') + '</b><br><small>ID #' + Number(role.role_id) + '</small></td><td>' + escapeHtml(role.role_description || 'No description') + '</td><td><span class="pill access">' + rolePanel(role.role_access_panel) + '</span></td><td>' + (Number(role.access_dash) ? 'Yes' : 'No') + '</td><td>' + escapeHtml(role.front_perm || 'None defined') + '</td><td><small>' + escapeHtml(role.role_creation_date || '—') + '<br>' + escapeHtml(role.role_last_update || '—') + '</small></td><td><button type="button" class="open ad-role-edit" data-role-id="' + Number(role.role_id) + '">Edit</button></td></tr>';
    }).join('') : '<tr><td colspan="8" style="text-align:center;padding:20px">No roles found.</td></tr>';
    makeTableSortable('#ad-role-table-card table');
    var count = document.querySelector('#ad-role-count'); if (count) count.textContent = roles.length + ' role' + (roles.length === 1 ? '' : 's');
    body.querySelectorAll('.ad-role-edit').forEach(function (button) { button.addEventListener('click', function () { openRoleModal(Number(button.getAttribute('data-role-id'))); }); });
  }

  function openRoleModal(roleId) {
    showModal('#modal', '#load', 'Loading role form...');
    var request = roleId ? fetch('api/ad_api.php?action=role_get&role_id=' + roleId).then(function (response) { return response.json(); }) : Promise.resolve({ data: { role: {} } });
    request.then(function (response) { var role = (response.data && response.data.role) || {}; return fetch('roles/rolenew.php').then(function (formResponse) { return formResponse.text(); }).then(function (html) { setModalContent('#load', html); document.querySelector('#ad-role-modal-title').textContent = roleId ? 'EDIT ROLE' : 'NEW ROLE'; document.querySelector('#ad-role-id').value = roleId || ''; document.querySelector('#ad-role-name').value = role.role_name || ''; document.querySelector('#ad-role-description').value = role.role_description || ''; document.querySelector('#ad-role-panel').value = role.role_access_panel || ''; document.querySelector('#ad-role-dashboard').value = role.access_dash || '0'; document.querySelector('#ad-role-front').value = role.front_perm || ''; document.querySelector('#ad-save-role').addEventListener('click', function () { var form = document.querySelector('#ad-role-form'); if (!form.checkValidity()) { form.reportValidity(); return; } var body = new FormData(form); fetch('api/ad_api.php?action=' + (roleId ? 'role_update' : 'role_create'), { method: 'POST', body: body, headers: { Accept: 'application/json' } }).then(function (saveResponse) { return saveResponse.json(); }).then(function (saveResponse) { if (!saveResponse.ok) throw new Error(saveResponse.message); if (window.jQuery) window.jQuery('#modal').modal('hide'); notify(saveResponse.message); loadRoleList(); }).catch(function (error) { document.querySelector('#ad-role-message').innerHTML = '<div class="alert alert-danger">' + escapeHtml(error.message) + '</div>'; }); }); }); });
  }

  function renderUserRows(users) {
    var body = document.querySelector('#ad-user-table-body');
    if (!body) return;
    body.innerHTML = users.length ? users.map(function (user, index) {
      var panel = Number(user.role_access_panel || 0);
      var credentials = (panel === 1 || panel === 3 ? '<button type="button" class="ad-outline ad-user-action ad-pass-btn" data-user-action="password" data-user-id="' + Number(user.user_id) + '">✎ Change Pass</button>' : '') + (panel === 2 || panel === 3 ? ' <button type="button" class="ad-outline ad-user-action ad-pin-btn" data-user-action="pin" data-user-id="' + Number(user.user_id) + '">✎ Change PIN</button>' : '');
      var staff = [user.staff_name || '', user.surname || ''].join(' ').trim() || 'Unlinked';
      var suspended = Number(user.work_status || 2) === 1;
      var status = suspended ? '<span class="pill" style="background:#ffe0e0;color:#c62828">● Suspended</span>' : '<span class="pill">● Active</span>';
      var statusAction = suspended ? 'unsuspend' : 'suspend';
      var statusLabel = suspended ? 'Unsuspend' : 'Suspend';
      return '<tr><td>' + Number(user.user_id) + '</td><td>' + escapeHtml(staff) + '</td><td><b>' + escapeHtml(user.username || '') + '</b></td><td>KES ' + Number(user.max_sale || 0).toFixed(2) + '</td><td class="text-center">' + credentials + '</td><td>' + escapeHtml(user.role_name || 'No role') + '</td><td>' + (panel === 1 ? 'Back End' : (panel === 2 ? 'Front End' : 'Both')) + '</td><td>' + status + '<br><button type="button" class="ad-outline ad-user-action ad-suspend-btn" data-user-action="' + statusAction + '" data-user-id="' + Number(user.user_id) + '">' + statusLabel + '</button></td><td><button type="button" class="ad-outline ad-user-action ad-edit-btn" data-user-action="edit" data-user-id="' + Number(user.user_id) + '">Edit</button> <button type="button" class="ad-outline ad-user-action ad-perm-btn" data-user-action="permissions" data-user-id="' + Number(user.user_id) + '">Set Permission</button> <button type="button" class="ad-outline ad-user-action ad-target-btn" data-user-action="target" data-user-id="' + Number(user.user_id) + '">Max Sale</button></td></tr>';
    }).join('') : '<tr><td colspan="9" style="text-align:center;padding:20px">No users found.</td></tr>';
    makeTableSortable('#ad-user-table');
    body.querySelectorAll('.ad-user-action').forEach(function (button) { button.addEventListener('click', function () { openUserAction(button.getAttribute('data-user-action'), Number(button.getAttribute('data-user-id'))); }); });
    var newUser = document.querySelector('#ad-new-user'); if (newUser) newUser.addEventListener('click', openNewUser);
  }

  function userModal(title, body, footer) {
    setModalContent('#load', '<div class="modal-content"><div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button><h4 class="modal-title">' + title + '</h4></div><div class="modal-body">' + body + '</div><div class="modal-footer">' + footer + '</div></div>');
    if (window.jQuery) window.jQuery('#modal').modal('show');
  }

  function openNewUser() {
    fetch('user/usernew.php', { headers: { Accept: 'text/html' } }).then(function (response) { return response.text(); }).then(function (html) {
      setModalContent('#load', html); if (window.jQuery) window.jQuery('#modal').modal('show');
      var role = document.querySelector('#ad-new-user-role');
      fetch('api/ad_api.php?action=roles', { headers: { Accept: 'application/json' } }).then(function (response) { return response.json(); }).then(function (response) { var roles = response.data.roles || []; role.innerHTML = '<option value="">Select role</option>' + roles.map(function (item) { return '<option value="' + Number(item.role_id) + '" data-panel="' + Number(item.role_access_panel) + '">' + escapeHtml(item.role_name) + '</option>'; }).join(''); });
      role.onchange = function () { var panel = Number(role.options[role.selectedIndex].getAttribute('data-panel') || 0); document.querySelector('#ad-new-pass-wrap').style.display = panel === 1 || panel === 3 ? 'block' : 'none'; document.querySelector('#ad-new-pin-wrap').style.display = panel === 2 || panel === 3 ? 'block' : 'none'; };
      document.querySelector('#ad-create-user').onclick = function () { var body = new URLSearchParams({ staff_id: document.querySelector('#ad-new-user-staff').value, username: document.querySelector('#ad-new-user-name').value, role_id: role.value, drole: document.querySelector('#ad-new-user-drole').value, password: document.querySelector('#ad-new-user-password').value, pin: document.querySelector('#ad-new-user-pin').value }); fetch('api/ad_api.php?action=user_create', { method: 'POST', body: body, headers: { Accept: 'application/json' } }).then(function (response) { return response.json(); }).then(function (response) { if (!response.ok) throw new Error(response.message); if (window.jQuery) window.jQuery('#modal').modal('hide'); notify(response.message); loadUserList(); }).catch(function (error) { document.querySelector('#ad-new-user-message').innerHTML = '<div class="alert alert-danger">' + escapeHtml(error.message) + '</div>'; }); };
    }).catch(function () { userModal('Create New User', '<div class="alert alert-danger">Could not load user form.</div>', ''); });
  }

  function openUserAction(action, userId) {
    if (action === 'suspend') {
      userModal('Suspend User', '<div id="ad-user-message"></div><div class="alert alert-warning">This will prevent the user from using the account.</div><div class="ad-field"><label>Reason</label><textarea id="ad-suspend-reason" class="form-control" rows="4" required placeholder="Enter the reason for suspension"></textarea></div>', '<button type="button" class="btn btn-danger" data-dismiss="modal">Close</button><button type="button" class="btn btn-danger" id="ad-confirm-suspend">Suspend User</button>');
      document.querySelector('#ad-confirm-suspend').onclick = function () { postUserAction('user_suspend', { user_id: userId, reason: document.querySelector('#ad-suspend-reason').value }); };
      return;
    }
    if (action === 'unsuspend') {
      userModal('Unsuspend User', '<div id="ad-user-message"></div><div class="alert alert-info">This will restore the user account.</div>', '<button type="button" class="btn btn-danger" data-dismiss="modal">Close</button><button type="button" class="btn btn-success" id="ad-confirm-unsuspend">Unsuspend User</button>');
      document.querySelector('#ad-confirm-unsuspend').onclick = function () { postUserAction('user_unsuspend', { user_id: userId }); };
      return;
    }
    showModal('#modal', '#load', 'Loading user action...');
    Promise.all([fetch('api/ad_api.php?action=user_get&user_id=' + userId).then(function (r) { return r.json(); }), action === 'edit' ? fetch('api/ad_api.php?action=roles').then(function (r) { return r.json(); }) : Promise.resolve(null), action === 'permissions' ? fetch('api/ad_api.php?action=user_permissions_get&user_id=' + userId).then(function (r) { return r.json(); }) : Promise.resolve(null)])
      .then(function (responses) {
        var user = responses[0].data.user || {}; var roles = responses[1] && responses[1].data ? responses[1].data.roles || [] : []; var perms = responses[2] && responses[2].data ? responses[2].data : {};
        if (action === 'edit') { userModal('Edit User Details', '<div id="ad-user-message"></div><div class="ad-wizard-grid two"><div class="ad-field"><label>Username</label><input id="ad-edit-username" value="' + escapeHtml(user.username || '') + '"></div><div class="ad-field"><label>Role</label><select id="ad-edit-role">' + roles.map(function (role) { return '<option value="' + Number(role.role_id) + '" ' + (Number(role.role_id) === Number(user.role_id) ? 'selected' : '') + '>' + escapeHtml(role.role_name) + '</option>'; }).join('') + '</select></div><div class="ad-field"><label>Default role</label><input id="ad-edit-drole" type="number" value="' + Number(user.drole || 0) + '"></div></div>', '<button type="button" class="btn btn-danger" data-dismiss="modal">Close</button><button type="button" class="btn btn-success" id="ad-save-user-edit">Save</button>'); document.querySelector('#ad-save-user-edit').onclick = function () { postUserAction('user_edit', { user_id: userId, username: document.querySelector('#ad-edit-username').value, role_id: document.querySelector('#ad-edit-role').value, drole: document.querySelector('#ad-edit-drole').value }); }; }
        if (action === 'password' || action === 'pin') { var label = action === 'password' ? 'Password' : 'PIN'; userModal('Change ' + label, '<div id="ad-user-message"></div><div class="form-group"><label>Previous ' + label + '</label><input type="password" class="form-control" id="ad-previous-credential" placeholder="Previous ' + label + '"></div><div class="form-group"><label>New ' + label + '</label><input type="password" class="form-control" id="ad-credential" placeholder="New ' + label + '"></div><div class="form-group"><label>Confirm ' + label + '</label><input type="password" class="form-control" id="ad-credential-confirm" placeholder="Confirm ' + label + '"></div>', '<button type="button" class="btn btn-danger btn-sm" data-dismiss="modal">Close</button><button type="button" class="btn btn-success btn-sm" id="ad-save-credential">Submit</button>'); document.querySelector('#ad-save-credential').onclick = function () { postUserAction(action === 'password' ? 'user_change_password' : 'user_change_pin', { user_id: userId, value: document.querySelector('#ad-credential').value, confirm: document.querySelector('#ad-credential-confirm').value }); }; }
        if (action === 'target') { userModal('Set User Target', '<div id="ad-user-message"></div><div class="ad-field"><label>Maximum sale target</label><input type="number" min="0" step="0.01" id="ad-user-target" value="' + Number(user.max_sale || 0).toFixed(2) + '"></div>', '<button type="button" class="btn btn-danger" data-dismiss="modal">Close</button><button type="button" class="btn btn-success" id="ad-save-target">Save</button>'); document.querySelector('#ad-save-target').onclick = function () { postUserAction('user_target', { user_id: userId, max_sale: document.querySelector('#ad-user-target').value }); }; }
        if (action === 'permissions') { userModal('Set Permissions for ' + escapeHtml(user.username || ''), '<div id="ad-user-message"></div><div id="ad-permission-list">' + (perms.permissions || []).map(function (item) { return '<label style="display:block;margin:7px"><input type="checkbox" name="ad-permission" value="' + Number(item.menu_id) + '" ' + ((perms.selected || []).indexOf(Number(item.menu_id)) >= 0 ? 'checked' : '') + '> ' + escapeHtml(item.name) + '</label>'; }).join('') + '</div>', '<button type="button" class="btn btn-danger" data-dismiss="modal">Close</button><button type="button" class="btn btn-success" id="ad-save-permissions">Save</button>'); document.querySelector('#ad-save-permissions').onclick = function () { postUserAction('user_permissions_save', { user_id: userId, permissions: Array.prototype.map.call(document.querySelectorAll('[name="ad-permission"]:checked'), function (item) { return item.value; }) }); }; }
      }).catch(function () { setModalContent('#load', '<div class="modal-content"><div class="modal-body">Could not load user action.</div></div>'); });
  }

  function postUserAction(action, data) { var body = new URLSearchParams(); Object.keys(data).forEach(function (key) { if (Array.isArray(data[key])) data[key].forEach(function (value) { body.append('permissions[]', value); }); else body.append(key, data[key]); }); fetch('api/ad_api.php?action=' + action, { method: 'POST', body: body, headers: { Accept: 'application/json' } }).then(function (response) { return response.json(); }).then(function (response) { if (!response.ok) throw new Error(response.message); notify(response.message); if (window.jQuery) window.jQuery('#modal').modal('hide'); loadUserList(); }).catch(function (error) { var message = document.querySelector('#ad-user-message'); if (message) message.innerHTML = '<div class="alert alert-danger">' + escapeHtml(error.message) + '</div>'; }); }

  function loadStaffList(search) {
    var url = 'api/ad_api.php?action=list&limit=50';
    if (search) url += '&search=' + encodeURIComponent(search);
    fetch(url, { headers: { Accept: 'application/json' } })
      .then(function (response) {
        if (!response.ok) throw new Error('Staff request failed');
        return response.json();
      })
      .then(function (response) {
        if (!response.ok) throw new Error(response.message || 'Staff request failed');
        var records = (response.data && response.data.staff) || [];
        renderStaffRows(records);
        var total = response.data && response.data.pagination ? response.data.pagination.total : records.length;
        var totalCard = document.querySelector('.stats .stat strong');
        if (totalCard) totalCard.textContent = total;
      })
      .catch(function () {
        notify('Staff list unavailable');
      });
  }

  function buildStaffReview(form) {
    var get = function (name) {
      var field = form.querySelector('[name="' + name + '"]');
      return field && field.value ? field.value : 'Not provided';
    };
    var review = form.querySelector('#ad-wizard-review');
    if (!review) return;
    review.innerHTML = '<div class="ad-review-card"><h5>User details</h5><p><b>Name:</b> ' + escapeHtml(get('staff_name') + ' ' + get('surname')) + '</p><p><b>Phone:</b> ' + escapeHtml(get('staff_phone')) + '</p><p><b>Residence:</b> ' + escapeHtml(get('staff_residence')) + '</p></div>' +
      '<div class="ad-review-card"><h5>Next of kin</h5><p><b>Name:</b> ' + escapeHtml(get('nok_name')) + '</p><p><b>Phone:</b> ' + escapeHtml(get('nok_phone')) + '</p><p><b>Relationship:</b> ' + escapeHtml(get('nok_relationship')) + '</p></div>' +
      '<div class="ad-review-card"><h5>Employment</h5><p><b>Department:</b> ' + escapeHtml(get('department_name')) + '</p><p><b>Job title:</b> ' + escapeHtml(get('job_title')) + '</p><p><b>Status:</b> ' + escapeHtml(get('work_status')) + '</p></div>' +
      '<div class="ad-review-card"><h5>Documents</h5><p>Files selected: ' + Array.prototype.filter.call(form.querySelectorAll('input[type="file"]'), function (field) { return field.files && field.files.length; }).length + '</p><p>Profile image: ' + (form.querySelector('[name="staff_photo"]').files.length ? 'Selected' : 'Not selected') + '</p></div>' +
      '<div class="ad-review-card"><h5>System user</h5><p><b>Account:</b> ' + (form.querySelector('[name="make_user"]').checked ? 'Will be created' : 'Not requested') + '</p><p><b>Username:</b> ' + escapeHtml(get('username')) + '</p></div>';
  }

  function initStaffWizard() {
    var wizard = document.querySelector('#ad-staff-wizard');
    if (!wizard) return;
    var form = wizard.querySelector('#ad-staff-form');
    var panels = wizard.querySelectorAll('[data-wizard-panel]');
    var steps = wizard.querySelectorAll('[data-wizard-step]');
    var back = wizard.querySelector('#ad-wizard-back');
    var next = wizard.querySelector('#ad-wizard-next');
    var save = wizard.querySelector('#ad-wizard-save');
    var status = wizard.querySelector('#ad-wizard-status');
    var error = wizard.querySelector('#ad-wizard-error');
    var current = 0;

    function showStep(index) {
      current = Math.max(0, Math.min(panels.length - 1, index));
      panels.forEach(function (panel, i) { panel.classList.toggle('active', i === current); });
      steps.forEach(function (step, i) { step.classList.toggle('active', i === current); step.classList.toggle('done', i < current); });
      back.classList.toggle('visible', current > 0);
      next.style.display = current === panels.length - 1 ? 'none' : 'inline-block';
      save.style.display = current === panels.length - 1 ? 'inline-block' : 'none';
      status.textContent = 'Step ' + (current + 1) + ' of ' + panels.length;
      if (current === panels.length - 1) buildStaffReview(form);
      if (error) error.classList.remove('show');
    }

    function validateStep() {
      var fields = panels[current].querySelectorAll('input,select,textarea');
      for (var i = 0; i < fields.length; i++) {
        if (!fields[i].checkValidity()) {
          fields[i].reportValidity();
          return false;
        }
      }
      return true;
    }

    next.addEventListener('click', function () { if (validateStep()) showStep(current + 1); });
    back.addEventListener('click', function () { showStep(current - 1); });
    steps.forEach(function (step, index) { step.addEventListener('click', function () { if (index <= current || validateStep()) showStep(index); }); });
    var photo = wizard.querySelector('#ad-staff-photo');
    if (photo) photo.addEventListener('change', function () { var preview = wizard.querySelector('#ad-photo-preview'); var file = photo.files[0]; if (!file || !preview) return; var reader = new FileReader(); reader.onload = function (event) { preview.innerHTML = '<img src="' + event.target.result + '" alt="Staff preview">'; }; reader.readAsDataURL(file); });
    var relationshipSelect = wizard.querySelector('#ad-nok-relationship');
    if (relationshipSelect) {
      fetch('api/ad_api.php?action=relationships', { headers: { Accept: 'application/json' } })
        .then(function (response) { return response.json(); })
        .then(function (response) { var relationships = response.data && response.data.relationships ? response.data.relationships : []; relationshipSelect.innerHTML = '<option value="">Select relationship</option>' + relationships.map(function (item) { return '<option value="' + Number(item.id) + '" data-name="' + escapeHtml(item.name) + '">' + escapeHtml(item.name) + '</option>'; }).join(''); })
        .catch(function () { relationshipSelect.innerHTML = '<option value="">Unable to load relationships</option>'; });
    }
    var makeUser = wizard.querySelector('#ad-make-user');
    var userFields = wizard.querySelector('#ad-user-fields');
    var roleSelect = wizard.querySelector('#ad-user-role');
    function setRequired(selector, required) { var field = wizard.querySelector(selector); if (field) field.required = required; }
    function updateUserFields() {
      var enabled = !!(makeUser && makeUser.checked);
      if (userFields) userFields.style.display = enabled ? 'block' : 'none';
      setRequired('#ad-user-name', enabled); setRequired('#ad-user-role', enabled);
      if (!enabled) { ['#ad-user-password','#ad-user-password-confirm','#ad-user-pin','#ad-user-pin-confirm'].forEach(function (selector) { setRequired(selector, false); }); return; }
      updateRoleFields();
    }
    function updateRoleFields() {
      var option = roleSelect && roleSelect.options[roleSelect.selectedIndex];
      var panel = option ? Number(option.getAttribute('data-access-panel') || 0) : 0;
      var password = panel === 1 || panel === 3;
      var pin = panel === 2 || panel === 3;
      ['#ad-password-field','#ad-password-confirm-field'].forEach(function (selector) { var field = wizard.querySelector(selector); if (field) field.style.display = password ? 'block' : 'none'; });
      ['#ad-pin-field','#ad-pin-confirm-field'].forEach(function (selector) { var field = wizard.querySelector(selector); if (field) field.style.display = pin ? 'block' : 'none'; });
      var defaultRole = wizard.querySelector('#ad-default-role-field'); if (defaultRole) defaultRole.style.display = pin ? 'block' : 'none';
      setRequired('#ad-user-password', password); setRequired('#ad-user-password-confirm', password); setRequired('#ad-user-pin', pin); setRequired('#ad-user-pin-confirm', pin);
    }
    if (makeUser) makeUser.addEventListener('change', updateUserFields);
    if (roleSelect) roleSelect.addEventListener('change', updateRoleFields);
    updateUserFields();
    if (roleSelect) {
      fetch('api/ad_api.php?action=roles', { headers: { Accept: 'application/json' } })
        .then(function (response) { return response.json(); })
        .then(function (response) { var roles = response.data && response.data.roles ? response.data.roles : []; roleSelect.innerHTML = '<option value="">Select user role</option>' + roles.map(function (role) { return '<option value="' + Number(role.role_id) + '" data-access-panel="' + Number(role.role_access_panel || 0) + '">' + escapeHtml(role.role_name) + '</option>'; }).join(''); updateUserFields(); })
        .catch(function () { roleSelect.innerHTML = '<option value="">Unable to load roles</option>'; });
    }
    save.addEventListener('click', function () {
      if (!validateStep()) return;
      var formData = new FormData(form);
      var selectedRelationship = form.querySelector('[name="nok_relationship"] option:checked');
      if (selectedRelationship) formData.set('nok_relationship', selectedRelationship.getAttribute('data-name') || selectedRelationship.textContent);
      save.disabled = true;
      save.textContent = 'Saving...';
      fetch('api/ad_api.php?action=save_staff', { method: 'POST', body: formData, headers: { Accept: 'application/json' } })
        .then(function (response) { return response.json(); })
        .then(function (response) { if (!response.ok) throw new Error(response.message || 'Could not save staff.'); if (window.jQuery) window.jQuery('#modal').modal('hide'); notify('Staff saved successfully'); loadStaffList(''); })
        .catch(function (saveError) { if (error) { error.textContent = saveError.message; error.classList.add('show'); } })
        .finally(function () { save.disabled = false; save.textContent = '✓ Save Staff'; });
    });
    showStep(0);
  }

  function openNewStaffWizard() {
    showModal('#modal', '#load', 'Loading new staff wizard...');
    fetch('sta/stanew.php', { headers: { Accept: 'text/html' } })
      .then(function (response) { if (!response.ok) throw new Error('Wizard request failed'); return response.text(); })
      .then(function (html) { setModalContent('#load', html); initStaffWizard(); })
      .catch(function () { setModalContent('#load', '<div class="modal-content"><div class="modal-body"><div class="alert alert-danger">Could not load the new staff wizard.</div></div></div>'); });
  }

  function bindShellActions() {
    var page = document.querySelector('.ad-page');
    if (!page) return;

    var userCard = page.querySelector('#ad-user-card');
    if (userCard) { userCard.addEventListener('click', loadUserList); userCard.addEventListener('keydown', function (event) { if (event.key === 'Enter' || event.key === ' ') loadUserList(); }); }
    var roleCard = page.querySelector('#ad-role-card');
    if (roleCard) { roleCard.addEventListener('click', loadRoleList); roleCard.addEventListener('keydown', function (event) { if (event.key === 'Enter' || event.key === ' ') loadRoleList(); }); }
    page.querySelectorAll('.stats .stat').forEach(function (card) { var label = card.querySelector('small'); if (label && label.textContent.trim().toLowerCase() === 'user roles' && !roleCard) { card.style.cursor = 'pointer'; card.addEventListener('click', loadRoleList); } });
    page.querySelectorAll('.stats .stat').forEach(function (card) { var label = card.querySelector('small'); if (label && ['total staff', 'active staff'].indexOf(label.textContent.trim().toLowerCase()) !== -1) { card.style.cursor = 'pointer'; card.addEventListener('click', function () { document.querySelector('#ad-user-list').style.display = 'none'; document.querySelector('#ad-role-list').style.display = 'none'; document.querySelector('#staff-list').style.display = 'block'; loadStaffShell().then(function () { loadStaffList(''); }); }); } });

    page.querySelectorAll('a[href="#"]').forEach(function (link) {
      link.addEventListener('click', function (event) {
        event.preventDefault();
      });
    });

    page.querySelectorAll('.side-actions button').forEach(function (button, index) {
      button.addEventListener('click', function () {
        if (index === 1) window.location.reload();
      });
    });

    page.querySelectorAll('.toolbar button:not(.new), .open').forEach(function (button) {
      button.addEventListener('click', function (event) {
        event.preventDefault();
        notify(button.textContent.trim().replace(/\s+/g, ' '));
      });
    });

    page.querySelectorAll('.toolbar .new, .side a').forEach(function (button) {
      button.addEventListener('click', function (event) {
        event.preventDefault();
        var label = button.textContent.trim().toLowerCase();
        if (label.indexOf('all staff') !== -1) { document.querySelector('#ad-user-list').style.display = 'none'; document.querySelector('#ad-role-list').style.display = 'none'; document.querySelector('#staff-list').style.display = 'block'; loadStaffShell().then(function () { loadStaffList(''); }); return; }
        if (label.indexOf('suspended users') !== -1) { loadUserList('suspended'); return; }
        if (label.indexOf('all users') !== -1) { loadUserList('all'); return; }
        if (label.indexOf('user roles') !== -1 || label.indexOf('new role') !== -1) { if (label.indexOf('new role') !== -1) { loadRoleList(); setTimeout(function () { openRoleModal(0); }, 150); } else loadRoleList(); return; }
        openNewStaffWizard();
      });
    });

    var search = page.querySelector('.search');
    if (search) {
      search.addEventListener('input', function () {
        loadStaffList(search.value.trim());
      });
    }

    loadStaffShell()
      .then(function () { loadStaffList(''); })
      .catch(function () { notify('Staff table shell unavailable'); });
  }

  document.addEventListener('DOMContentLoaded', bindShellActions);
}());
