$(function() {
  $(document).ajaxComplete(function(event, xhr) {
    var payload = xhr && xhr.responseJSON ? xhr.responseJSON : null;
    if (!payload && xhr && xhr.responseText) {
      try {
        payload = JSON.parse(xhr.responseText);
      } catch (error) {
        payload = null;
      }
    }
    if (payload && payload.success === false && String(payload.message || '').toLowerCase() === 'unauthorized') {
      window.location = '../log_out.php';
    }
  });

  var dashState = null;
  var saleCapMessage = 'Sale cap reached. Clear your unpaid bills first before placing another order.';
  var templateCache = {
    desktop: '',
    mobile: '',
    alert: '',
    desktopTopAction: '',
    desktopQuickAction: '',
    desktopManagerPanel: '',
    mobileTopAction: '',
    mobileQuickAction: '',
    listItem: '',
    listWrapper: '',
    saleCap: '',
    saleCapStat: '',
    mobileStats: '',
    returnRow: '',
    modalLoading: '',
    modalLock: '',
    loadError: '',
    desktopActionBreak: '',
    mergeBadge: ''
  };

  function escapeHtml(value) {
    return String(value == null ? '' : value)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#39;');
  }

  function formatNumber(value, decimals) {
    var num = Number(value || 0);
    return num.toLocaleString(undefined, {
      minimumFractionDigits: decimals,
      maximumFractionDigits: decimals
    });
  }

  function fillTemplate(template, values) {
    return template.replace(/\{\{(\w+)\}\}/g, function(_, key) {
      return Object.prototype.hasOwnProperty.call(values, key) ? values[key] : '';
    });
  }

  function dashOpenModal(loadingText) {
    $('#modal').modal('show');
    $('#load').html(fillTemplate(templateCache.modalLoading, {
      modal_text: escapeHtml(loadingText || 'Loading...')
    }));
  }

  function dashShowLockMessage() {
    dashOpenModal('Sale cap reached');
    $('#load').html(fillTemplate(templateCache.modalLock, {
      lock_message: escapeHtml(saleCapMessage)
    }));
  }

  function alertClass(type) {
    return type === 'danger' ? 'alert-danger' : 'alert-warning';
  }

  function buildList(items) {
    if (!Array.isArray(items) || !items.length) {
      return '';
    }
    return fillTemplate(templateCache.listWrapper, {
      list_items: items.map(function(item) {
        return fillTemplate(templateCache.listItem, {
          item_text: escapeHtml(item)
        });
      }).join('')
    });
  }

  function renderAlerts(alerts) {
    if (!alerts || !alerts.length) {
      return '';
    }

    return alerts.map(function(alert) {
      return fillTemplate(templateCache.alert, {
        alert_class: alertClass(alert.type),
        alert_message: escapeHtml(alert.message || alert.title || ''),
        alert_items: buildList(alert.items)
      });
    }).join('');
  }

  function renderSaleCap(data) {
    var saleCap = data.sale_cap || {};
    if (!(data.shift && data.shift.name) || Number(saleCap.max_sale || 0) <= 0) {
      return '';
    }

    var locked = !!saleCap.locked;
    var fill = Number(saleCap.percent || 0);
    var angle = (fill * 3.6).toFixed(2);

    var stats = [
      { label: 'Current Unpaid', value: formatNumber(saleCap.current_unpaid_sales, 2) },
      { label: 'Max Sale', value: formatNumber(saleCap.max_sale, 2) },
      { label: 'Remaining Room', value: formatNumber(saleCap.remaining, 2) }
    ];

    return fillTemplate(templateCache.saleCap, {
      sale_cap_state: escapeHtml(saleCap.state || 'safe'),
      sale_cap_stats: stats.map(function(stat) {
        return fillTemplate(templateCache.saleCapStat, {
          stat_label: stat.label,
          stat_value: stat.value
        });
      }).join(''),
      sale_cap_alert_class: locked ? 'locked' : 'ready',
      sale_cap_alert_text: locked
        ? 'You have reached your sale cap. Clear the unpaid bills before placing another order.'
        : 'You can still place orders. Keep an eye on unpaid bills as you approach the cap.',
      sale_cap_percent_label: escapeHtml(saleCap.percent_label || '0.0'),
      sale_cap_state_label: escapeHtml(saleCap.state_label || 'Safe'),
      sale_cap_percent: fill,
      sale_cap_color: escapeHtml(saleCap.color || '#61b846'),
      sale_cap_angle: angle
    });
  }

  function renderMergeBadge(count) {
    if (!(count > 0)) {
      return '';
    }
    return fillTemplate(templateCache.mergeBadge, {
      badge_value: count
    });
  }

  function renderDesktopTopLeft(shift, counters) {
    if (shift.can_open_shift) {
      return fillTemplate(templateCache.desktopTopAction, {
        action_href: '../shift/open_shift.php',
        action_attrs: '',
        action_style: " font-family: 'comic sans ms'; font-size: 12PX; font-weight: bold; color: #02550E !important; padding-top:30%; border-radius: 5px; height: 80px; width:80px; background-color: #FFF;border: 5px solid #02550E;",
        action_class: 'btn btn-sm btn-success m-b-10',
        action_label_top: 'OPEN',
        action_label_break: templateCache.desktopActionBreak,
        action_label_bottom: 'SHIFT'
      });
    }

    if (shift.can_open_counter || Number(counters.total || 0) !== Number(counters.open || 0)) {
      return fillTemplate(templateCache.desktopTopAction, {
        action_href: '#',
        action_attrs: 'data-toggle="modal" data-target="#modal" id="open" name="open"',
        action_style: " font-family: 'comic sans ms'; font-size: 14PX; font-weight: bold; color: #02550E !important;  border-radius: 5px; height: 70px; width:auto; background-color: #FFF;border: 5px solid #02550E; word-wrap: break-word; ",
        action_class: 'btn btn-sm btn-success m-b-10 text-center',
        action_label_top: 'OPEN',
        action_label_break: templateCache.desktopActionBreak,
        action_label_bottom: ' COUNTER'
      });
    }

    return '';
  }

  function renderDesktopActions(data) {
    var shift = data.shift || {};
    var mergeCount = ((data.merge_requests || {}).incoming_count || 0);
    if (!shift.name) {
      return '';
    }

    var actions = [
      {
        href: '#',
        attrs: 'data-toggle="modal" data-target="#modal" id="sell"',
        className: 'btn btn-sm m-b-10 btn-landing ' + ((data.sale_cap || {}).locked ? 'is-locked' : ''),
        label: 'PLACE ORDER',
        badge: '',
        lineBreak: templateCache.desktopActionBreak
      },
      {
        href: '../open/index.php',
        attrs: '',
        className: 'btn btn-sm m-b-10 btn-landing',
        label: 'CONTINUE BILL',
        badge: '',
        lineBreak: ''
      },
      {
        href: '../view/my_sales.php',
        attrs: '',
        className: 'btn btn-sm m-b-10 btn-landing',
        label: ' VIEW ORDERS ',
        badge: renderMergeBadge(mergeCount),
        lineBreak: ''
      },
      {
        href: '#',
        attrs: 'data-toggle="modal" data-target="#modal" id="cpass"',
        className: 'btn btn-sm m-b-10 btn-landing',
        label: 'CHANGE PASS',
        badge: '',
        lineBreak: templateCache.desktopActionBreak
      },
      {
        href: '../kpesa/mpesas.php',
        attrs: 'target="_blank" rel="noopener"',
        className: 'btn btn-sm m-b-10 btn-landing',
        label: 'K-PESA',
        badge: '',
        lineBreak: templateCache.desktopActionBreak
      }
    ];

    return actions.map(function(action) {
      return fillTemplate(templateCache.desktopQuickAction, {
        action_href: action.href,
        action_attrs: action.attrs,
        action_class: action.className,
        action_label: action.label,
        action_badge: action.badge,
        action_break: action.lineBreak
      });
    }).join('');
  }

  function renderDesktopManagerActions(user) {
    if (user.job_id !== 1) {
      return '';
    }

    var actions = [
      { href: '../ret/appr/returns.php', attrs: '', className: 'btn btn-sm m-b-10 btn-landing', label: 'RETURNS', badge: '', lineBreak: '' },
      { href: '../cash', attrs: '', className: 'btn btn-sm m-b-10 btn-landing', label: 'CLEAR BILL', badge: '', lineBreak: '' },
      { href: '../stock/store_dash.php', attrs: '', className: 'btn btn-sm m-b-10 btn-landing', label: 'STOCK', badge: '', lineBreak: '' },
      { href: '#', attrs: 'data-toggle="modal" data-target="#modal" id="close-counter"', className: 'btn btn-sm m-b-10 btn-close', label: 'CLOSE COUNTER', badge: '', lineBreak: '' }
    ];

    return fillTemplate(templateCache.desktopManagerPanel, {
      manager_actions: actions.map(function(action) {
        return fillTemplate(templateCache.desktopQuickAction, {
          action_href: action.href,
          action_attrs: action.attrs,
          action_class: action.className,
          action_label: action.label,
          action_badge: action.badge,
          action_break: action.lineBreak
        });
      }).join('')
    });
  }

  function renderMobileTopActions(shift, counters) {
    var actions = [];
    if (shift.can_open_shift) {
      actions.push({ href: '../shift/open_shift.php', attrs: '', label: 'Open Shift' });
    }
    if (!shift.can_open_shift && (shift.can_open_counter || Number(counters.total || 0) !== Number(counters.open || 0))) {
      actions.push({ href: '#', attrs: 'id="open"', label: 'Open Counter' });
    }
    actions.push({ href: '../log_out.php', attrs: '', label: 'Log Out' });
    return actions.map(function(action) {
      return fillTemplate(templateCache.mobileTopAction, {
        action_href: action.href,
        action_attrs: action.attrs,
        action_label: action.label
      });
    }).join('');
  }

  function renderMobileActions(data) {
    var user = data.user || {};
    var shift = data.shift || {};
    var mergeCount = ((data.merge_requests || {}).incoming_count || 0);
    if (!shift.name) {
      return '';
    }

    var actions = [
      {
        href: '#',
        attrs: 'data-toggle="modal" data-target="#modal" id="sell"',
        className: 'app-btn btn-order ' + ((data.sale_cap || {}).locked ? 'is-locked' : ''),
        kicker: 'Sales',
        iconClass: 'glyphicon glyphicon-shopping-cart',
        text: 'Place Order',
        badge: ''
      },
      {
        href: '../open/index.php',
        attrs: '',
        className: 'app-btn btn-view',
        kicker: 'Continue',
        iconClass: 'glyphicon glyphicon-random',
        text: 'Continue Bill',
        badge: ''
      },
      {
        href: '../view/my_sales.php',
        attrs: '',
        className: 'app-btn btn-view',
        kicker: 'Orders',
        iconClass: 'glyphicon glyphicon-list-alt',
        text: 'View Orders',
        badge: renderMergeBadge(mergeCount)
      },
      {
        href: '#',
        attrs: 'data-toggle="modal" data-target="#modal" id="cpass"',
        className: 'app-btn btn-pass',
        kicker: 'Security',
        iconClass: 'glyphicon glyphicon-lock',
        text: 'Change Pass',
        badge: ''
      }
    ];

    if (user.job_id === 1) {
      actions = actions.concat([
        { href: '../ret/appr/returns.php', attrs: '', className: 'app-btn btn-return', kicker: 'Items', iconClass: 'glyphicon glyphicon-repeat', text: 'Returns', badge: '' },
        { href: '../cash', attrs: '', className: 'app-btn btn-bill', kicker: 'Cash', iconClass: 'glyphicon glyphicon-credit-card', text: 'Clear Bill', badge: '' },
        { href: '../stock/store_dash.php', attrs: '', className: 'app-btn btn-stock', kicker: 'Inventory', iconClass: 'glyphicon glyphicon-briefcase', text: 'Stock', badge: '' },
        { href: '#', attrs: 'data-toggle="modal" data-target="#modal" id="close-counter"', className: 'app-btn btn-closecounter', kicker: 'Counter', iconClass: 'glyphicon glyphicon-off', text: 'Close Counter', badge: '' }
      ]);
    }

    return actions.map(function(action) {
      return fillTemplate(templateCache.mobileQuickAction, {
        action_href: action.href,
        action_attrs: action.attrs,
        action_class: action.className,
        action_kicker: action.kicker,
        action_icon_class: action.iconClass,
        action_text: action.text,
        action_badge: action.badge
      });
    }).join('');
  }

  function renderMobileStats(data) {
    var sales = data.sales || {};
    var returns = data.returns || {};
    var user = data.user || {};
    var returnRows = [
      {
        label: 'Accepted',
        value: formatNumber(returns.accepted, 0),
        wrap: '',
        valueStyle: 'font-size:15px;'
      },
      {
        label: 'Rejected',
        value: formatNumber(returns.rejected, 0),
        wrap: '',
        valueStyle: 'font-size:15px;'
      },
      {
        label: 'Pending',
        value: formatNumber(returns.pending, 0),
        wrap: 'flex-wrap:wrap;',
        valueStyle: 'display:inline-flex;align-items:center;justify-content:center;min-width:34px;padding:3px 10px;border-radius:5px;background:#fff4da;color:#9a6700;font-size:12px;font-weight:900;'
      }
    ];

    if (user.is_barman && Number(returns.counter_pending || 0) > 0) {
      returnRows.push({
        label: 'This Counter',
        value: formatNumber(returns.counter_pending, 0) + ' Pending',
        wrap: 'flex-wrap:wrap;',
        valueStyle: 'display:inline-flex;align-items:center;justify-content:center;padding:3px 10px;border-radius:5px;background:#ffe0e0;color:#b42318;font-size:12px;font-weight:900;'
      });
    }

    return fillTemplate(templateCache.mobileStats, {
      stats_total_bills: formatNumber(sales.total_bills, 0),
      stats_paid_bills: formatNumber(sales.paid_bills, 0),
      stats_paid_amount: formatNumber(sales.paid_amount, 0),
      stats_unpaid_bills: formatNumber(sales.unpaid_bills, 0),
      stats_total_sales: formatNumber(sales.total_sales, 0),
      stats_voided_bills: formatNumber(sales.voided_bills, 0),
      stats_total_mpesa: formatNumber(sales.total_mpesa, 0),
      stats_items_sold: formatNumber(sales.items_sold, 0),
      return_rows: returnRows.map(function(row) {
        return fillTemplate(templateCache.returnRow, {
          row_label: row.label,
          row_value: row.value,
          row_wrap: row.wrap,
          row_value_style: row.valueStyle
        });
      }).join('')
    });
  }

  function templateValues(data) {
    var user = data.user || {};
    var shift = data.shift || {};
    var counters = data.counters || {};
    var tokens = data.tokens || {};
    var sales = data.sales || {};
    var returns = data.returns || {};

    return {
      alerts: renderAlerts(data.alerts || []),
      sale_cap: renderSaleCap(data),
      desktop_top_left: renderDesktopTopLeft(shift, counters),
      desktop_actions: renderDesktopActions(data),
      desktop_manager_actions: renderDesktopManagerActions(user),
      mobile_top_actions: renderMobileTopActions(shift, counters),
      mobile_actions: renderMobileActions(data),
      mobile_stats: renderMobileStats(data),
      mobile_user_line: escapeHtml(user.name || '') + (shift.name ? ' | Shift ' + escapeHtml(shift.name) : ''),
      mobile_open_counters: formatNumber(counters.open, 0) + '/' + formatNumber(counters.total, 0),
      token_total: formatNumber(tokens.total, 2),
      token_month: formatNumber(tokens.month, 2),
      token_today: formatNumber(tokens.today, 2),
      sales_total_bills: formatNumber(sales.total_bills, 0),
      sales_total_sales: formatNumber(sales.total_sales, 2),
      returns_total: formatNumber((returns.accepted || 0) + (returns.rejected || 0) + (returns.pending || 0), 0),
      sales_voided: formatNumber(sales.voided_bills, 0),
      sales_mpesa: formatNumber(sales.total_mpesa, 2),
      sales_items: formatNumber(sales.items_sold, 2)
    };
  }

  function renderDashboard(data) {
    dashState = data || {};
    window.dashSaleCapLocked = !!((dashState.sale_cap || {}).locked);
    window.dashIncomingMergeRequests = parseInt(((dashState.merge_requests || {}).incoming_count || 0), 10) || 0;

    if (dashState.user && dashState.user.redirect_to_cashier) {
      window.location.replace('../cash/');
      return;
    }

    var values = {
      alerts: renderAlerts(dashState.alerts || []),
      sale_cap: renderSaleCap(dashState),
      mobile_stats: renderMobileStats(dashState)
    };
    var shift = dashState.shift || {};
    var counters = dashState.counters || {};
    var user = dashState.user || {};
    var sales = dashState.sales || {};
    var returns = dashState.returns || {};
    var tokens = dashState.tokens || {};
    var canOpenCounter = !shift.can_open_shift && (shift.can_open_counter || Number(counters.total || 0) !== Number(counters.open || 0));
    var hasShift = !!shift.name;
    var isManager = Number(user.job_id) === 1;

    $('.dash-open-shift, .dash-mobile-open-shift').toggle(!!shift.can_open_shift);
    $('.dash-open-counter, .dash-mobile-open-counter').toggle(!!canOpenCounter);
    $('.dash-desktop-actions, .dash-mobile-actions').toggle(hasShift);
    $('.dash-desktop-manager-actions, .dash-mobile-manager-action').toggle(hasShift && isManager);
    $('#dash-desktop-alerts, #dash-mobile-alerts').html(values.alerts);
    $('#dash-desktop-sale-cap, #dash-mobile-sale-cap').html(values.sale_cap);
    $('#dash-mobile-stats').html(values.mobile_stats);
    $('#dash-mobile-user-line').text((user.name || '') + (shift.name ? ' | Shift ' + shift.name : ''));
    $('#dash-mobile-open-counters').text(formatNumber(counters.open, 0) + '/' + formatNumber(counters.total, 0));
    $('#dash-token-total').text(formatNumber(tokens.total, 2));
    $('#dash-token-month').text(formatNumber(tokens.month, 2));
    $('#dash-token-today').text(formatNumber(tokens.today, 2));
    $('#dash-sales-total-bills').text(formatNumber(sales.total_bills, 0));
    $('#dash-sales-total-sales').text(formatNumber(sales.total_sales, 2));
    $('#dash-returns-total').text(formatNumber((returns.accepted || 0) + (returns.rejected || 0) + (returns.pending || 0), 0));
    $('#dash-sales-voided').text(formatNumber(sales.voided_bills, 0));
    $('#dash-sales-mpesa').text(formatNumber(sales.total_mpesa, 2));
    $('#dash-sales-items').text(formatNumber(sales.items_sold, 2));
    $('#sell, #sell-mobile').toggleClass('is-locked', window.dashSaleCapLocked);

    if (window.dashIncomingMergeRequests > 0) {
      setTimeout(function() {
        openDashMergeRequests();
      }, 600);
    }
  }

  function renderLoadError(message) {
    $('#dash-page').html(fillTemplate(templateCache.loadError, {
      error_message: escapeHtml(message)
    }));
  }

  function fetchTemplates() {
    return $.getJSON('templates.php?v=1').then(function(templates) {
      if (!templates || templates.success === false) {
        return $.Deferred().reject().promise();
      }
      Object.keys(templateCache).forEach(function(key) {
        if (Object.prototype.hasOwnProperty.call(templates, key)) {
          templateCache[key] = templates[key];
        }
      });
    });
  }

  function loadDashboard() {
    $.ajax({
      url: '../API/dash/dash.php',
      type: 'GET',
      data: window.location.search.replace(/^\?/, ''),
      dataType: 'json',
      cache: false
    })
    .done(function(res) {
      if (!res || !res.success || !res.data) {
        renderLoadError((res && res.message) ? res.message : 'Could not load dashboard data.');
        return;
      }
      renderDashboard(res.data);
    })
    .fail(function() {
      renderLoadError('Could not load dashboard data.');
    });
  }

  window.openDashMergeRequests = function() {
    dashOpenModal('Loading merge requests...');
    $.ajax({
      url: '../view/API/view_api.php',
      type: 'GET',
      data: { action: 'merge_requests_modal', incoming: 1, pending: 1 },
      dataType: 'html'
    })
    .done(function(html) {
      $('#load').html(html);
    })
    .fail(function() {
      $('#load').html(fillTemplate(templateCache.loadError, {
        error_message: 'Could not load merge requests.'
      }));
    });
  };

  $(document).on('click', '#open, #open-mobile', function(e) {
    e.preventDefault();
    dashOpenModal('Loading counter setup...');
    $.ajax({
      url: '../counter/open.php',
      type: 'POST',
      dataType: 'html'
    })
    .done(function(data) {
      $('#load').html(data);
    })
    .fail(function() {
      $('#load').html(fillTemplate(templateCache.loadError, {
        error_message: 'Something went wrong, please try again.'
      }));
    });
  });

  $(document).on('click', '#sell, #sell-mobile', function(e) {
    if (window.dashSaleCapLocked) {
      e.preventDefault();
      dashShowLockMessage();
      return;
    }
    e.preventDefault();
    dashOpenModal('Loading counter selection...');
    $.ajax({
      url: '../counter/counter_select.php',
      type: 'POST',
      dataType: 'html'
    })
    .done(function(data) {
      $('#load').html(data);
    })
    .fail(function() {
      $('#load').html(fillTemplate(templateCache.loadError, {
        error_message: 'Something went wrong, please try again.'
      }));
    });
  });

  $(document).on('click', '.counter-select-btn', function(e) {
    e.preventDefault();

    var $button = $(this);
    var $modalContent = $button.closest('.modal-content');
    var initApi = $modalContent.data('initApi');
    var initAction = $button.data('action') || $modalContent.data('initAction') || 'initialise';
    var initStatus = parseInt($modalContent.data('initStatus'), 10) || 0;
    var saleId = parseInt($modalContent.data('saleId'), 10) || 0;
    var counter = parseInt($button.data('counter'), 10) || 0;

    if (!initApi || !initAction || !counter || !initStatus) {
      $('#load').html(fillTemplate(templateCache.loadError, {
        error_message: 'Could not initialize this order.'
      }));
      return;
    }

    $('.counter-select-btn').addClass('is-locked').css('pointer-events', 'none');
    dashOpenModal('Initializing order...');

    $.ajax({
      url: initApi,
      type: 'POST',
      dataType: 'json',
      data: {
        action: initAction,
        counter: counter,
        st: initStatus,
        sale_id: saleId
      }
    })
    .done(function(res) {
      var ok = !!(res && (res.success || res.ok));
      var redirect = '';
      if (res) {
        if (res.data && res.data.redirect) {
          redirect = res.data.redirect;
        } else if (res.redirect) {
          redirect = res.redirect;
        }
      }
      var message = res ? (res.message || '') : '';

      if (!ok || !redirect) {
        $('#load').html(fillTemplate(templateCache.loadError, {
          error_message: message || 'Could not initialize this order.'
        }));
        $('.counter-select-btn').removeClass('is-locked').css('pointer-events', '');
        return;
      }

      window.location.href = redirect;
    })
    .fail(function() {
      $('#load').html(fillTemplate(templateCache.loadError, {
        error_message: 'Could not initialize this order.'
      }));
      $('.counter-select-btn').removeClass('is-locked').css('pointer-events', '');
    });
  });

  $(document).on('click', '#cpass, #cpass-mobile', function(e) {
    e.preventDefault();
    dashOpenModal('Loading password change...');
    $.ajax({
      url: '../staff/cpass.php',
      type: 'POST',
      dataType: 'html'
    })
    .done(function(data) {
      $('#load').html(data);
    })
    .fail(function() {
      $('#load').html(fillTemplate(templateCache.loadError, {
        error_message: 'Something went wrong, please try again.'
      }));
    });
  });

  $(document).on('click', '#close-counter, #close-counter-mobile', function(e) {
    e.preventDefault();
    dashOpenModal('Loading close counter check...');
    $.ajax({
      url: '../counter/close_modal.php',
      type: 'POST',
      dataType: 'html'
    })
    .done(function(data) {
      $('#load').html(data);
    })
    .fail(function() {
      $('#load').html(fillTemplate(templateCache.loadError, {
        error_message: 'Something went wrong, please try again.'
      }));
    });
  });

  $(document).on('click', '.accept-merge-request', function() {
    var requestId = $(this).data('requestId');
    $.ajax({
      url: '../view/API/view_api.php',
      type: 'POST',
      data: { action: 'merge_accept', request_id: requestId },
      dataType: 'json'
    })
    .done(function(res) {
      if (!res || !res.ok) {
        alert(res && res.err ? res.err : 'Could not accept the merge request.');
        return;
      }
      loadDashboard();
      $('#modal').modal('hide');
    })
    .fail(function() {
      alert('Could not accept the merge request right now.');
    });
  });

  $(document).on('click', '.reject-merge-request', function() {
    var requestId = $(this).data('requestId');
    $.ajax({
      url: '../view/API/view_api.php',
      type: 'POST',
      data: { action: 'merge_reject', request_id: requestId },
      dataType: 'json'
    })
    .done(function(res) {
      if (!res || !res.ok) {
        alert(res && res.err ? res.err : 'Could not reject the merge request.');
        return;
      }
      loadDashboard();
      $('#modal').modal('hide');
    })
    .fail(function() {
      alert('Could not reject the merge request right now.');
    });
  });

  $(document).on('click', '.close-merge-request', function() {
    $('#modal').modal('hide');
  });

  fetchTemplates()
    .done(function() {
      loadDashboard();
    })
    .fail(function() {
      renderLoadError('Could not load dashboard templates.');
    });
});
