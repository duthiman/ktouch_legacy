<?php
session_start();
include('../../inc/connection.php');
include_once('../../inc/gen_funcs.php');
require_once('../../qz/common.php');
$chaba_print_mode = function_exists('get_print_mode') ? (int)get_print_mode() : 1;
$mode_override = (int)($_GET['mode_override'] ?? 0);
if (in_array($mode_override, [2, 4], true)) {
  $chaba_print_mode = $mode_override;
}
$qzTrustConfigured = function_exists('kp_qz_trust_is_configured') ? kp_qz_trust_is_configured() : false;
$qzSecurityBaseUrl = '../../qz/security.php';
$worker_title = 'K-TOUCH Print Server';
if ($chaba_print_mode === 4) {
  $worker_title = 'K-TOUCH QZ Tray Worker';
} elseif ($chaba_print_mode === 2) {
  $worker_title = 'K-TOUCH Browser Tab Worker';
}
$qzFit = function_exists('kp_qz_fit_settings') ? kp_qz_fit_settings($connection) : ['paper_width_mm' => 80, 'margin_mm' => 0, 'content_scale_pct' => 80];
$receiptDividerSettings = function_exists('kp_receipt_divider_settings') ? kp_receipt_divider_settings($connection) : ['symbol' => '*', 'count' => 50];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?php echo htmlspecialchars($worker_title); ?></title>
  <link rel="stylesheet" href="../../assets/plugins/bootstrap/4.0.0/css/bootstrap.min.css">
  <link rel="stylesheet" href="../../assets/plugins/font-awesome/5.0/css/fontawesome-all.min.css">
  <!-- <script src="https://cdn.jsdelivr.net/npm/qz-tray@2.2.5/qz-tray.js"></script> -->
  <script src="../../qz/qz-tray.js"></script>
  <script src="../../qz/ktouch-qz-security.js"></script>
  <script>
    if (!window.KTouchQzSecurity) {
      window.KTouchQzSecurity = (function () {
        function normalizeError(err, fallback) {
          if (!err) return fallback;
          if (err.message) return err.message;
          return String(err);
        }
        function createLogger(logger) {
          return typeof logger === 'function' ? logger : function () {};
        }
        function create(options) {
          options = options || {};
          var qzObject = window.qz;
          var log = createLogger(options.logger);
          var certificateUrl = String(options.certificateUrl || '');
          var signUrl = String(options.signUrl || '');
          var certificateCache = null;
          var setupDone = false;

          function fetchCertificate(resolve, reject) {
            if (certificateCache !== null) {
              resolve(certificateCache);
              return;
            }
            fetch(certificateUrl, { method: 'GET', credentials: 'same-origin', cache: 'no-store' })
              .then(function (response) {
                if (!response.ok) throw new Error('Certificate request failed with HTTP ' + response.status);
                return response.text();
              })
              .then(function (text) {
                var certificate = String(text || '').trim();
                if (!certificate) throw new Error('Certificate response was empty');
                certificateCache = certificate;
                resolve(certificateCache);
              })
              .catch(function (err) {
                var message = normalizeError(err, 'Certificate request failed');
                log('certificate_error', message);
                reject(new Error(message));
              });
          }

          function fetchSignature(toSign, resolve, reject) {
            var body = new URLSearchParams();
            body.set('request', toSign);
            fetch(signUrl, {
              method: 'POST',
              credentials: 'same-origin',
              cache: 'no-store',
              headers: {
                'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
                'X-Requested-With': 'XMLHttpRequest'
              },
              body: body.toString()
            }).then(function (response) {
              return response.json().catch(function () {
                throw new Error('Signing response was not valid JSON');
              }).then(function (payload) {
                if (!response.ok || !payload || !payload.ok || !payload.signature) {
                  throw new Error((payload && payload.message) ? payload.message : ('Signing request failed with HTTP ' + response.status));
                }
                return payload.signature;
              });
            }).then(function (signature) {
              resolve(signature);
            }).catch(function (err) {
              var message = normalizeError(err, 'Signing request failed');
              log('sign_error', message);
              reject(new Error(message));
            });
          }

          return {
            configure: function () {
              if (setupDone) return Promise.resolve();
              return new Promise(function (resolve, reject) {
                if (!qzObject) {
                  reject(new Error('QZ Tray JavaScript library not loaded'));
                  return;
                }
                if (!certificateUrl || !signUrl) {
                  reject(new Error('QZ security endpoints are not configured'));
                  return;
                }
                qzObject.security.setCertificatePromise(function (certResolve, certReject) {
                  fetchCertificate(certResolve, certReject);
                });
                if (typeof qzObject.security.setSignatureAlgorithm === 'function') {
                  qzObject.security.setSignatureAlgorithm('SHA512');
                }
                qzObject.security.setSignaturePromise(function (toSign) {
                  return function (signResolve, signReject) {
                    fetchSignature(toSign, signResolve, signReject);
                  };
                });
                setupDone = true;
                log('configured', 'QZ trusted signing configured');
                resolve();
              });
            },
            ensureReady: function () {
              return this.configure().then(function () {
                if (qzObject.websocket.isActive()) return null;
                return qzObject.websocket.connect();
              });
            }
          };
        }
        return { create: create };
      }());
    }
  </script>
  <style>
    body { background:#1a1a2e; color:#eee; font-family:Arial,sans-serif; }
    .ps-card { background:#16213e; border-radius:8px; padding:24px; margin-top:40px; }
    h2 { color:#e94560; }
    #status-dot { display:inline-block; width:12px; height:12px; border-radius:50%; background:#aaa; margin-right:6px; vertical-align:middle; }
    #status-dot.ok  { background:#28a745; }
    #status-dot.err { background:#dc3545; }
    #status-dot.busy{ background:#ffc107; }
    #job-tbody tr td { color:#ccc; font-size:13px; }
    .badge-bill { background:#0d6efd; }
    .badge-cashier_order { background:#6f42c1; }
    .badge-bot  { background:#fd7e14; }
    .badge-kot  { background:#198754; }
    .badge-got  { background:#20c997; }
    .badge-ret  { background:#dc3545; }
    .badge-kug  { background:#6c757d; }
    .badge-gwitia { background:#9c6644; }
    .badge-tots { background:#7c3aed; }
    .badge-variance { background:#d63384; }
    .badge-summary, .badge-shift_sum, .badge-staff_sum, .badge-counter_sum { background:#0dcaf0; color:#111; }
    select.form-control, input.form-control { background:#0f3460; color:#eee; border:1px solid #444; }
    #print-frames { position:fixed; top:-9999px; left:-9999px; width:1px; height:1px; overflow:hidden; }
    iframe { width:82mm; height:600px; border:none; }
    .btn-ps { background:#e94560; color:#fff; border:none; }
    .btn-ps:hover { background:#c73652; color:#fff; }
    .qz-note { margin-top:10px; font-size:12px; color:#f3d9ff; background:rgba(126,34,206,.18); border:1px solid rgba(192,132,252,.35); border-radius:8px; padding:10px 12px; }
    .qz-fit-box { margin-top:12px; margin-bottom:14px; font-size:12px; color:#d9eef9; background:rgba(13,110,253,.14); border:1px solid rgba(125,211,252,.28); border-radius:8px; padding:12px; }
    .qz-fit-box .form-control { height:32px; padding:4px 8px; font-size:12px; }
    .qz-preview-frame { width:100%; height:560px; border:1px solid rgba(191,231,255,.22); border-radius:8px; background:#fff; }
    .qz-fit-summary { margin-top:12px; margin-bottom:14px; font-size:12px; color:#d9eef9; background:rgba(13,110,253,.14); border:1px solid rgba(125,211,252,.28); border-radius:8px; padding:12px; }
    .qz-fit-modal {
      display:none;
      position:fixed;
      inset:0;
      z-index:1050;
      background:rgba(8,15,30,.78);
      padding:16px;
      overflow:auto;
    }
    .qz-fit-modal.is-open { display:block; }
    .qz-fit-modal-dialog {
      width:min(980px, 96vw);
      margin:20px auto;
      background:#16213e;
      border:1px solid rgba(125,211,252,.24);
      border-radius:12px;
      box-shadow:0 18px 52px rgba(0,0,0,.45);
      overflow:hidden;
    }
    .qz-fit-modal-head {
      display:flex;
      align-items:center;
      justify-content:space-between;
      gap:12px;
      padding:14px 16px;
      background:#0f3460;
      color:#fff;
    }
    .qz-fit-modal-head h4 { margin:0; font-size:17px; font-weight:700; }
    .qz-fit-modal-body { padding:16px; }
    .qz-fit-close {
      background:transparent;
      border:1px solid rgba(255,255,255,.25);
      color:#fff;
      border-radius:6px;
      padding:4px 10px;
    }
  </style>
</head>
<body>
<div class="container" style="max-width:680px;">
  <div class="ps-card">
    <h2><i class="fas fa-print"></i> <?php echo htmlspecialchars($worker_title); ?></h2>
    <hr style="border-color:#444;">

    <!-- Setup panel -->
    <div id="setup-panel">
      <p style="color:#aaa;" id="setup-copy">Select which counter this machine belongs to. The server will print all pending jobs for that counter.</p>
      <div class="form-group">
        <label id="counter-label">Counter / Printer Station</label>
        <select id="counter-select" class="form-control">
          <option value="0">-- Loading... --</option>
        </select>
      </div>
      <button id="start-btn" class="btn btn-ps btn-block" style="margin-top:12px;">
        Start Print Server
      </button>
    </div>

    <!-- Running panel -->
    <div id="server-panel" style="display:none;">
      <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:14px;">
        <div>
          <span id="status-dot" class="ok"></span>
          <strong id="server-name"></strong>
        </div>
        <button id="stop-btn" class="btn btn-xs btn-default">Change Counter</button>
      </div>

      <p style="margin-bottom:4px; color:#aaa; font-size:13px;">
        Status: <span id="status-text">Waiting for jobs...</span>
      </p>
      <p style="color:#888; font-size:11px; margin-bottom:14px;">
        Keep this tab open. Polling every 3 seconds.
        <span id="mode-badge" class="label label-default" style="margin-left:6px;"></span>
      </p>
      <div id="qz-note" class="qz-note" style="display:none;">
        <strong>QZ Tray mode:</strong> keep QZ Tray open on this Windows machine and allow this page to connect to it. Each job will be sent straight to the mapped Windows printer.
        <div id="qz-trust-note" style="margin-top:6px;"></div>
      </div>
      <div id="qz-fit-summary" class="qz-fit-summary" style="display:none;">
        <div style="display:flex;align-items:center;justify-content:space-between;gap:10px;flex-wrap:wrap;">
          <div>
            <div style="font-weight:700; margin-bottom:4px;">Thermal Fit And Preview</div>
            <div id="qz-fit-current" style="color:#bfe7ff;"></div>
          </div>
          <button id="qz-fit-open" class="btn btn-sm btn-ps">Open Print Settings</button>
        </div>
      </div>

      <div id="qz-fit-modal" class="qz-fit-modal" aria-hidden="true">
        <div class="qz-fit-modal-dialog">
          <div class="qz-fit-modal-head">
            <h4>Thermal Fit Settings And Preview</h4>
            <button type="button" id="qz-fit-close" class="qz-fit-close">Close</button>
          </div>
          <div class="qz-fit-modal-body">
      <div id="qz-fit-box" class="qz-fit-box" style="display:block;margin-top:0;margin-bottom:0;">
        <div style="font-weight:700; margin-bottom:6px;">Thermal Fit Settings</div>
        <div style="margin-bottom:8px;">Save here, then print again immediately from this same page.</div>
        <div id="qz-fit-current-modal" style="margin-bottom:8px;color:#bfe7ff;"></div>
        <div id="qz-fit-msg" style="margin-bottom:8px;"></div>
        <div class="row">
          <div class="col-sm-4">
            <label style="font-size:11px;">Paper Width (mm)</label>
            <input type="number" step="0.1" min="50" max="120" class="form-control" id="qz-fit-paper" value="">
          </div>
          <div class="col-sm-4">
            <label style="font-size:11px;">Page Margin (mm)</label>
            <input type="number" step="0.1" min="0" max="10" class="form-control" id="qz-fit-margin" value="">
          </div>
          <div class="col-sm-4">
            <label style="font-size:11px;">Content Scale (%)</label>
            <input type="number" step="1" min="40" max="120" class="form-control" id="qz-fit-scale" value="">
          </div>
        </div>
        <div class="row" style="margin-top:10px;">
          <div class="col-sm-6">
            <label style="font-size:11px;">Divider Symbol</label>
            <input type="text" maxlength="4" class="form-control" id="qz-divider-symbol" value="">
          </div>
          <div class="col-sm-6">
            <label style="font-size:11px;">Divider Repeat Count</label>
            <input type="number" step="1" min="5" max="120" class="form-control" id="qz-divider-count" value="">
          </div>
        </div>
        <div style="margin-top:10px;">
          <button id="qz-fit-save" class="btn btn-sm btn-ps">Save Fit Settings</button>
        </div>
        <hr style="border-color:rgba(191,231,255,.15);margin:14px 0;">
        <div style="font-weight:700; margin-bottom:6px;">Preview And Test Print</div>
        <div style="margin-bottom:8px;">Use the active receipt template here before you print a real job.</div>
        <div class="row">
          <div class="col-sm-4">
            <label style="font-size:11px;">Template Type</label>
            <select class="form-control" id="qz-preview-type">
              <option value="bill">Bill / Receipt</option>
              <option value="cashier_order">Cashier Order</option>
              <option value="bot">BOT</option>
              <option value="kot">KOT</option>
              <option value="got">GOT</option>
              <option value="ret">Return</option>
              <option value="kug">KUG</option>
              <option value="gwitia">Requisition</option>
              <option value="tots">Totals</option>
              <option value="variance">Variance</option>
              <option value="summary">Shift Summary</option>
              <option value="staff_sum">Staff Summary</option>
              <option value="counter_sum">Counter Summary</option>
            </select>
          </div>
          <div class="col-sm-4">
            <label id="qz-preview-ref-label" style="font-size:11px;">Sale / Reference ID</label>
            <input type="number" min="1" step="1" class="form-control" id="qz-preview-ref" value="">
          </div>
          <div class="col-sm-4">
            <label style="font-size:11px;">Test Printer Counter</label>
            <select class="form-control" id="qz-preview-counter">
              <option value="0">-- Select mapped printer --</option>
            </select>
          </div>
        </div>
        <div style="margin-top:10px;">
          <button id="qz-preview-load" class="btn btn-sm btn-default">Show Preview</button>
          <button id="qz-preview-print" class="btn btn-sm btn-ps">Print Test Page</button>
          <a id="qz-preview-open" href="#" target="_blank" class="btn btn-sm btn-info">Open Template</a>
        </div>
        <div id="qz-preview-msg" style="margin-top:8px;"></div>
        <div style="margin-top:10px;">
          <iframe id="qz-preview-frame" class="qz-preview-frame" title="Live print preview"></iframe>
        </div>
      </div>
          </div>
        </div>
      </div>

      <table class="table table-condensed" style="background:#0f3460; border-radius:4px;">
        <thead>
          <tr style="color:#aaa; font-size:12px;">
            <th>#</th><th>Type</th><th>Ref</th><th>Time</th><th>Result</th>
          </tr>
        </thead>
        <tbody id="job-tbody"></tbody>
      </table>
    </div>
  </div>
</div>

<div id="print-frames"></div>

<script src="../../assets/plugins/jquery/jquery-3.2.1.min.js"></script>
<script>
(function () {
  // This page is the print-server UI.
  // It polls the shared queue for the selected counter, then loads the
  // matching render page in a hidden iframe or sends it to QZ Tray.
  var PRINT_MODE = <?php echo (int)$chaba_print_mode; ?>;
  var POLL_MS   = 3000;
  var API_URL   = '../../gen/print_api.php';
  var SETTINGS_URL = '../../back/settings/printer_set.php';
  var pollTimer = null;
  var printing  = false;
  var cntrId    = 0;
  var jobSeq    = 0;
  var allCounters = [];
  var isGlobalQzMode = (PRINT_MODE === 4);
  var MAX_ATTEMPTS = 5;
  var qzFit = <?php echo json_encode($qzFit); ?>;
  var receiptDividerSettings = <?php echo json_encode($receiptDividerSettings); ?>;
  var qzTrustConfigured = <?php echo $qzTrustConfigured ? 'true' : 'false'; ?>;
  var QZ_FIT_DRAFT_KEY = 'ktouch_qz_fit_draft';
  var QZ_FIT_MODAL_KEY = 'ktouch_qz_fit_modal_open';
  var QZ_PREVIEW_DRAFT_KEY = 'ktouch_qz_preview_draft';
  var previewState = {
    url: '',
    html: '',
    job: null
  };
  var qzSecurity = window.KTouchQzSecurity.create({
    certificateUrl: '<?php echo htmlspecialchars($qzSecurityBaseUrl, ENT_QUOTES); ?>?action=certificate',
    signUrl: '<?php echo htmlspecialchars($qzSecurityBaseUrl, ENT_QUOTES); ?>?action=sign',
    logger: function (eventName, detail) {
      if (window.console && console.warn) {
        console.warn('[QZ Security][' + eventName + ']', detail);
      }
    }
  });

  // Detect HTTPS vs HTTP
  var isHttps = (location.protocol === 'https:');
  var modeText = PRINT_MODE === 4 ? 'QZ Tray mode' : (isHttps ? 'HTTPS mode' : 'HTTP mode');
  $('#mode-badge').text(modeText);
  if (PRINT_MODE === 4) {
    $('#qz-note').show();
    $('#qz-fit-summary').show();
    $('#setup-copy').text('QZ Tray global worker mode: one tab can handle all queued jobs and send each one to its mapped printer automatically.');
    $('#counter-label').text('Worker Mode');
    $('#counter-select').html('<option value="1">Global QZ Tray Worker - All queued printers</option>').val('1');
    $('#qz-trust-note').html(
      qzTrustConfigured
        ? '<span style="color:#d1fae5;">Trusted signing is configured for this site.</span>'
        : '<span style="color:#fecaca;">Trusted signing files are missing. QZ Tray may keep asking for Allow/Block until the certificate and key are added.</span>'
    );
    syncQzFitInputs();
    restoreQzFitDraft();
  }

  // ── Load counter list ─────────────────────────────────────────────
  $.getJSON(API_URL + '?action=counters', function (data) {
    allCounters = Array.isArray(data) ? data : [];
    var sel = $('#counter-select').empty();
    syncPreviewCounterOptions(allCounters);
    if (isGlobalQzMode) {
      sel.append('<option value="1">Global QZ Tray Worker - All queued printers</option>');
      var savedGlobal = localStorage.getItem('ps_cntr_id');
      if (savedGlobal === '1') {
        setTimeout(function () {
          $('#start-btn').trigger('click');
        }, 300);
      }
      return;
    }
    sel.append('<option value="0">-- Select counter --</option>');
    if (!Array.isArray(data)) {
      sel.append('<option value="0">-- No counters found --</option>');
      return;
    }
    $.each(data, function (i, c) {
      var counterName = (c && c.name) ? c.name : ('Counter ' + ((c && c.cntr_id) ? c.cntr_id : ''));
      var label = counterName;
      if (c && c.printer_name) label += '  [' + c.printer_name + ']';
      sel.append('<option value="' + c.cntr_id + '">' + label + '</option>');
    });
    var saved = localStorage.getItem('ps_cntr_id');
    if (saved) {
      sel.val(saved);
      // Auto-start if we had a previously selected counter
      setTimeout(function () {
        if (parseInt(sel.val(), 10) > 0) $('#start-btn').trigger('click');
      }, 300);
    }
  });

  // ── Start ─────────────────────────────────────────────────────────
  $('#start-btn').on('click', function () {
    cntrId = parseInt($('#counter-select').val(), 10);
    if (!cntrId) { alert('Please select a counter first.'); return; }
    var name = isGlobalQzMode ? 'Global QZ Tray Worker' : $('#counter-select option:selected').text().trim();
    localStorage.setItem('ps_cntr_id', cntrId);
    $('#setup-panel').hide();
    $('#server-panel').show();
    $('#server-name').text(name);
    startPolling();
  });

  $('#stop-btn').on('click', function () {
    stopPolling();
    $('#server-panel').hide();
    $('#setup-panel').show();
  });
  $('#qz-fit-save').on('click', saveQzFitFromWorker);
  $('#qz-fit-open').on('click', openQzFitModal);
  $('#qz-fit-close').on('click', closeQzFitModal);
  $('#qz-fit-modal').on('click', function (e) {
    if (e.target === this) {
      closeQzFitModal();
    }
  });
  $('#qz-fit-paper,#qz-fit-margin,#qz-fit-scale,#qz-divider-symbol,#qz-divider-count').on('input change', persistQzFitDraft);
  $('#qz-preview-type,#qz-preview-ref,#qz-preview-counter').on('input change', persistQzPreviewDraft);
  $('#qz-preview-load').on('click', function () {
    loadTemplatePreview(false);
  });
  $('#qz-preview-print').on('click', function () {
    printTemplateTest();
  });
  $('#qz-preview-type').on('change', syncPreviewRefLabel);

  // ── Polling ───────────────────────────────────────────────────────
  function startPolling() { poll(); pollTimer = setInterval(poll, POLL_MS); }
  function stopPolling()  { if (pollTimer) { clearInterval(pollTimer); pollTimer = null; } }

  function poll() {
    if (printing) return;
    // Pull the oldest pending print jobs for this counter and process one at a time.
    var url = isGlobalQzMode
      ? (API_URL + '?action=pending&all=1')
      : (API_URL + '?action=pending&cntr_id=' + cntrId);
    $.getJSON(url)
      .done(function (jobs) {
        setDot('ok');
        if (!jobs || jobs.length === 0) {
          setStatus(isGlobalQzMode ? 'Waiting for jobs on all mapped printers...' : 'Waiting for jobs...');
          return;
        }
        processJob(jobs[0]);
      })
      .fail(function () {
        setDot('err');
        setStatus('API error – check server connection.');
      });
  }

  // ── Process one job ───────────────────────────────────────────────
  function processJob(job) {
    // In QZ mode we do not use the browser print dialog.
    if (PRINT_MODE === 4) {
      processQzJob(job);
      return;
    }

    printing = true;
    jobSeq++;
    var seq = jobSeq;
    var url = getRenderUrl(job);
    // Each job type resolves to a dedicated render page.
    // For bills this is `render_bill.php?sale_id=...`.
    if (!url) { logJob(seq, job, 'Unknown type - skipped'); printing = false; return; }

    claimJob(job.queue_id).done(function (claimed) {
      if (!claimed) {
        printing = false;
        return;
      }

      setDot('busy');
      setStatus('Printing ' + job.print_type.toUpperCase() + ' - Ref #' + (job.ref_id || job.sale_id || job.queue_id) + '...');

      var iframe = $('<iframe>').attr('src', url).attr('id', 'pf_' + seq);
      $('#print-frames').append(iframe);

      var finished = false;

      function finishOk(result) {
        if (finished) return;
        finished = true;
        callDone(job.queue_id);
        logJob(seq, job, result);
        setTimeout(function () { iframe.remove(); printing = false; }, 500);
      }

      function finishFail(message) {
        if (finished) return;
        finished = true;
        failJob(job.queue_id, message);
        logJob(seq, job, message);
        setDot('err');
        setStatus(message);
        setTimeout(function () { iframe.remove(); printing = false; }, 500);
      }

      iframe[0].onload = function () {
        try {
          var win = iframe[0].contentWindow;
          win.focus();
          win.print();

          win.addEventListener('afterprint', function () {
            setTimeout(function () { finishOk('OK'); }, 200);
          });

          setTimeout(function () { finishOk('OK (timeout)'); }, 15000);
        } catch (e) {
          finishFail('Print error: ' + e.message);
        }
      };

      setTimeout(function () { finishFail('Skipped (load timeout)'); }, 20000);
    }).fail(function () {
      printing = false;
    });
  }

  function processQzJob(job) {
    printing = true;
    jobSeq++;
    var seq = jobSeq;
    var url = getRenderUrl(job);
    var printerName = (job && job.printer_name) ? String(job.printer_name).trim() : '';
    if (!url) {
      logJob(seq, job, 'Unknown type - skipped');
      printing = false;
      return;
    }
    claimJob(job.queue_id).done(function (claimed) {
      if (!claimed) {
        printing = false;
        return;
      }
      if (!printerName) {
        var noPrinterMsg = 'No mapped Windows printer for this counter.';
        failJob(job.queue_id, noPrinterMsg);
        logJob(seq, job, noPrinterMsg);
        setDot('err');
        setStatus(noPrinterMsg);
        printing = false;
        return;
      }

      setDot('busy');
      setStatus('Sending ' + job.print_type.toUpperCase() + ' to QZ Tray - ' + printerName + '...');

      $.get(url, function (html) {
        html = applyQzThermalFit(html, job);
        ensureQzReady().then(function () {
          return qz.printers.find(printerName);
        }).then(function (printer) {
          var config = qz.configs.create(printer, {
            jobName: 'KTouch ' + String(job.print_type || '').toUpperCase() + ' #' + (job.ref_id || job.sale_id || job.queue_id),
            units: 'mm',
            margins: {
              top: 0,
              right: 0,
              bottom: 0,
              left: 0
            },
            scaleContent: false
          });
          return qz.print(config, [{
            type: 'pixel',
            format: 'html',
            flavor: 'plain',
            data: html
          }]);
        }).then(function () {
          callDone(job.queue_id);
          logJob(seq, job, 'QZ sent to ' + printerName);
          setDot('ok');
          setStatus(isGlobalQzMode ? 'Waiting for jobs on all mapped printers...' : 'Waiting for jobs...');
        }).catch(function (err) {
          var msg = (err && err.message) ? err.message : String(err);
          failJob(job.queue_id, 'QZ Tray error - ' + msg);
          logJob(seq, job, 'QZ error: ' + msg);
          setDot('err');
          setStatus('QZ Tray error - ' + msg);
        }).then(function () {
          printing = false;
        }, function () {
          printing = false;
        });
      }).fail(function () {
        failJob(job.queue_id, 'Could not load print page for QZ Tray.');
        logJob(seq, job, 'Could not load print page');
        setDot('err');
        setStatus('Could not load print page for QZ Tray.');
        printing = false;
      });
    }).fail(function () {
      printing = false;
    });
  }

  function ensureQzReady() {
    return qzSecurity.ensureReady().catch(function (err) {
      var message = (err && err.message) ? err.message : String(err);
      throw new Error('Could not connect to trusted QZ Tray session. ' + message);
    });
  }

  function syncQzFitInputs() {
    $('#qz-fit-paper').val(qzFit.paper_width_mm);
    $('#qz-fit-margin').val(qzFit.margin_mm);
    $('#qz-fit-scale').val(qzFit.content_scale_pct);
    $('#qz-divider-symbol').val(receiptDividerSettings.symbol);
    $('#qz-divider-count').val(receiptDividerSettings.count);
    var summary = 'Current fit: <strong>' + qzFit.paper_width_mm + 'mm</strong> paper, ' +
      '<strong>' + qzFit.margin_mm + 'mm</strong> margin, ' +
      '<strong>' + qzFit.content_scale_pct + '%</strong> scale. Divider: <strong>' +
      escapeHtml(String(receiptDividerSettings.symbol)) + '</strong> x <strong>' + receiptDividerSettings.count + '</strong>.';
    $('#qz-fit-current').html(summary);
    $('#qz-fit-current-modal').html(summary);
  }

  function persistQzFitDraft() {
    qzFit.paper_width_mm = parseFloat($('#qz-fit-paper').val() || qzFit.paper_width_mm) || qzFit.paper_width_mm;
    qzFit.margin_mm = parseFloat($('#qz-fit-margin').val() || qzFit.margin_mm) || qzFit.margin_mm;
    qzFit.content_scale_pct = parseFloat($('#qz-fit-scale').val() || qzFit.content_scale_pct) || qzFit.content_scale_pct;
    receiptDividerSettings.symbol = String($('#qz-divider-symbol').val() || receiptDividerSettings.symbol || '*').substring(0, 4);
    receiptDividerSettings.count = parseInt($('#qz-divider-count').val(), 10) || receiptDividerSettings.count || 50;
    try {
      localStorage.setItem(QZ_FIT_DRAFT_KEY, JSON.stringify({
        paper: $('#qz-fit-paper').val(),
        margin: $('#qz-fit-margin').val(),
        scale: $('#qz-fit-scale').val(),
        divider_symbol: $('#qz-divider-symbol').val(),
        divider_count: $('#qz-divider-count').val()
      }));
    } catch (e) {}
  }

  function persistQzPreviewDraft() {
    try {
      localStorage.setItem(QZ_PREVIEW_DRAFT_KEY, JSON.stringify({
        type: $('#qz-preview-type').val(),
        ref: $('#qz-preview-ref').val(),
        counter: $('#qz-preview-counter').val()
      }));
    } catch (e) {}
  }

  function restoreQzPreviewDraft() {
    try {
      var raw = localStorage.getItem(QZ_PREVIEW_DRAFT_KEY);
      if (!raw) return;
      var draft = JSON.parse(raw);
      if (draft && typeof draft === 'object') {
        if (draft.type !== undefined) $('#qz-preview-type').val(draft.type);
        if (draft.ref !== undefined) $('#qz-preview-ref').val(draft.ref);
        if (draft.counter !== undefined) $('#qz-preview-counter').val(draft.counter);
      }
    } catch (e) {}
    syncPreviewRefLabel();
  }

  function restoreQzFitDraft() {
    try {
      var raw = localStorage.getItem(QZ_FIT_DRAFT_KEY);
      if (raw) {
        var draft = JSON.parse(raw);
        if (draft && typeof draft === 'object') {
          if (draft.paper !== undefined) $('#qz-fit-paper').val(draft.paper);
          if (draft.margin !== undefined) $('#qz-fit-margin').val(draft.margin);
          if (draft.scale !== undefined) $('#qz-fit-scale').val(draft.scale);
          if (draft.divider_symbol !== undefined) $('#qz-divider-symbol').val(draft.divider_symbol);
          if (draft.divider_count !== undefined) $('#qz-divider-count').val(draft.divider_count);
          qzFit.paper_width_mm = parseFloat(draft.paper || qzFit.paper_width_mm) || qzFit.paper_width_mm;
          qzFit.margin_mm = parseFloat(draft.margin || qzFit.margin_mm) || qzFit.margin_mm;
          qzFit.content_scale_pct = parseFloat(draft.scale || qzFit.content_scale_pct) || qzFit.content_scale_pct;
          receiptDividerSettings.symbol = String(draft.divider_symbol || receiptDividerSettings.symbol || '*').substring(0, 4);
          receiptDividerSettings.count = parseInt(draft.divider_count, 10) || receiptDividerSettings.count || 50;
        }
      }
    } catch (e) {}
    syncQzFitInputs();
    restoreQzPreviewDraft();
    if (localStorage.getItem(QZ_FIT_MODAL_KEY) === '1') {
      openQzFitModal();
    }
  }

  function openQzFitModal() {
    $('#qz-fit-modal').addClass('is-open').attr('aria-hidden', 'false');
    $('body').css('overflow', 'hidden');
    try { localStorage.setItem(QZ_FIT_MODAL_KEY, '1'); } catch (e) {}
  }

  function closeQzFitModal() {
    $('#qz-fit-modal').removeClass('is-open').attr('aria-hidden', 'true');
    $('body').css('overflow', '');
    try { localStorage.setItem(QZ_FIT_MODAL_KEY, '0'); } catch (e) {}
  }

  function syncPreviewCounterOptions(counters) {
    var $sel = $('#qz-preview-counter');
    if (!$sel.length) {
      return;
    }
    $sel.empty().append('<option value="0">-- Select mapped printer --</option>');
    $.each(counters || [], function (i, c) {
      var counterId = parseInt(c.cntr_id, 10) || 0;
      var printerName = c && c.printer_name ? String(c.printer_name).trim() : '';
      if (!counterId || !printerName) {
        return;
      }
      var label = (c.name || ('Counter ' + counterId)) + ' [' + printerName + ']';
      $sel.append('<option value="' + counterId + '">' + label + '</option>');
    });
    if (!$sel.val() || $sel.val() === '0') {
      var remembered = localStorage.getItem('ps_cntr_id');
      if (remembered && remembered !== '1') {
        $sel.val(remembered);
      }
    }
    restoreQzPreviewDraft();
  }

  function syncPreviewRefLabel() {
    var type = String($('#qz-preview-type').val() || 'bill').toLowerCase();
    var usesSale = {
      bill: true,
      cashier_order: true,
      bot: true,
      kot: true,
      got: true,
      ret: true,
      kug: true
    };
    var text = usesSale[type] ? 'Sale ID' : 'Reference ID';
    $('#qz-preview-ref-label').text(text);
  }

  function qzPreviewMessage(html, kind) {
    var bg = kind === 'danger' ? 'rgba(220,53,69,.18)' : (kind === 'warning' ? 'rgba(255,193,7,.18)' : 'rgba(40,167,69,.18)');
    var border = kind === 'danger' ? 'rgba(248,113,113,.45)' : (kind === 'warning' ? 'rgba(252,211,77,.35)' : 'rgba(134,239,172,.35)');
    $('#qz-preview-msg').html('<div style="padding:8px 10px;border-radius:6px;background:' + bg + ';border:1px solid ' + border + ';">' + html + '</div>');
  }

  function buildPreviewJob() {
    var type = String($('#qz-preview-type').val() || 'bill').toLowerCase();
    var ref = parseInt($('#qz-preview-ref').val(), 10) || 0;
    var saleTypes = {
      bill: true,
      cashier_order: true,
      bot: true,
      kot: true,
      got: true,
      ret: true,
      kug: true
    };
    return {
      print_type: type,
      label: type.toUpperCase(),
      sale_id: saleTypes[type] ? ref : 0,
      ref_id: saleTypes[type] ? ref : ref,
      queue_id: 0
    };
  }

  function findPreviewCounter() {
    var counterId = parseInt($('#qz-preview-counter').val(), 10) || 0;
    var found = null;
    $.each(allCounters || [], function (i, c) {
      if ((parseInt(c.cntr_id, 10) || 0) === counterId) {
        found = c;
        return false;
      }
    });
    return found;
  }

  function setPreviewFrameHtml(html) {
    var frame = document.getElementById('qz-preview-frame');
    if (!frame) {
      return;
    }
    if ('srcdoc' in frame) {
      frame.srcdoc = html;
      return;
    }
    var doc = frame.contentWindow ? frame.contentWindow.document : frame.contentDocument;
    if (!doc) {
      return;
    }
    doc.open();
    doc.write(html);
    doc.close();
  }

  function loadTemplatePreview(silent) {
    var job = buildPreviewJob();
    var refNo = job.sale_id || job.ref_id || 0;
    var url = getRenderUrl(job);
    if (!refNo || !url) {
      if (!silent) {
        qzPreviewMessage('Choose a template type and enter a valid sale/reference ID first.', 'warning');
      }
      return $.Deferred().reject('missing-ref').promise();
    }
    previewState.job = job;
    previewState.url = url;
    $('#qz-preview-open').attr('href', url);
    if (!silent) {
      qzPreviewMessage('Loading preview from the active print template...', 'success');
    }
    return $.get(url).then(function (html) {
      var fitted = applyQzThermalFit(html, job);
      previewState.html = fitted;
      setPreviewFrameHtml(fitted);
      if (!silent) {
        qzPreviewMessage('Preview loaded from the current ' + job.print_type.toUpperCase() + ' template.', 'success');
      }
      return fitted;
    }, function () {
      previewState.html = '';
      if (!silent) {
        qzPreviewMessage('Could not load the selected template preview.', 'danger');
      }
      return $.Deferred().reject('load-failed').promise();
    });
  }

  function printTemplateTest() {
    var counter = findPreviewCounter();
    if (!counter || !counter.printer_name) {
      qzPreviewMessage('Select the counter whose mapped printer should receive the test page.', 'warning');
      return;
    }
    loadTemplatePreview(true).then(function (html) {
      qzPreviewMessage('Sending test page to ' + counter.printer_name + '...', 'success');
      return ensureQzReady().then(function () {
        return qz.printers.find(String(counter.printer_name).trim());
      }).then(function (printer) {
        var job = previewState.job || buildPreviewJob();
        var config = qz.configs.create(printer, {
          jobName: 'KTouch TEST ' + String(job.print_type || 'PRINT').toUpperCase() + ' #' + (job.ref_id || job.sale_id || 'PREVIEW'),
          units: 'mm',
          margins: {
            top: 0,
            right: 0,
            bottom: 0,
            left: 0
          },
          scaleContent: false
        });
        return qz.print(config, [{
          type: 'pixel',
          format: 'html',
          flavor: 'plain',
          data: html
        }]);
      }).then(function () {
        qzPreviewMessage('Test page sent to ' + counter.printer_name + '.', 'success');
      });
    }, function (err) {
      var msg = (err && err.message) ? err.message : String(err || 'Test print failed');
      qzPreviewMessage('Test print failed. ' + msg, 'danger');
    });
  }

  function qzFitMessage(html, kind) {
    var bg = kind === 'danger' ? 'rgba(220,53,69,.18)' : 'rgba(40,167,69,.18)';
    var border = kind === 'danger' ? 'rgba(248,113,113,.45)' : 'rgba(134,239,172,.35)';
    $('#qz-fit-msg').html('<div style="padding:8px 10px;border-radius:6px;background:' + bg + ';border:1px solid ' + border + ';">' + html + '</div>');
  }

  function escapeHtml(value) {
    return String(value || '')
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#39;');
  }

  function saveQzFitFromWorker() {
    var payload = {
      action: 'save_qz_fit',
      qz_paper_width_mm: $('#qz-fit-paper').val(),
      qz_margin_mm: $('#qz-fit-margin').val(),
      qz_scale_pct: $('#qz-fit-scale').val(),
      receipt_divider_symbol: $('#qz-divider-symbol').val(),
      receipt_divider_count: $('#qz-divider-count').val()
    };
    var $btn = $('#qz-fit-save').prop('disabled', true);
    $.post(SETTINGS_URL, payload, function (res) {
      if (res && res.ok) {
        qzFit.paper_width_mm = parseFloat(payload.qz_paper_width_mm || qzFit.paper_width_mm);
        qzFit.margin_mm = parseFloat(payload.qz_margin_mm || qzFit.margin_mm);
        qzFit.content_scale_pct = parseFloat(payload.qz_scale_pct || qzFit.content_scale_pct);
        receiptDividerSettings.symbol = String(payload.receipt_divider_symbol || receiptDividerSettings.symbol || '*').substring(0, 4);
        receiptDividerSettings.count = parseInt(payload.receipt_divider_count, 10) || receiptDividerSettings.count || 50;
        syncQzFitInputs();
        persistQzFitDraft();
        qzFitMessage('Saved. Print again from this page to test the new fit.', 'success');
        if (previewState.url) {
          loadTemplatePreview(true);
        }
      } else {
        qzFitMessage((res && res.message) ? res.message : 'Could not save fit settings.', 'danger');
      }
    }, 'json').fail(function () {
      qzFitMessage('Could not save fit settings.', 'danger');
    }).always(function () {
      $btn.prop('disabled', false);
    });
  }

  function applyQzThermalFit(html, job) {
    var source = String(html || '');
    if (!source) {
      return source;
    }

    var type = String((job && job.print_type) || '').toLowerCase();
    var thermalTypes = {
      bill: true,
      cashier_order: true,
      bot: true,
      kot: true,
      got: true,
      ret: true,
      kug: true,
      gwitia: true,
      tots: true,
      variance: true,
      summary: true,
      shift_sum: true,
      staff_sum: true,
      counter_sum: true
    };
    if (!thermalTypes[type]) {
      return source;
    }

    var paperWidth = Math.max(50, parseFloat(qzFit.paper_width_mm || 80) || 80);
    var marginMm = Math.max(0, parseFloat(qzFit.margin_mm || 0) || 0);
    var scalePct = Math.max(40, parseFloat(qzFit.content_scale_pct || 80) || 80);
    var scaleFactor = scalePct / 100;
    var safeContentWidth = Math.max(20, paperWidth - (marginMm * 2));
    var wrapperWidth = safeContentWidth.toFixed(3);

    var fitCss = ''
      + '<style id="ktouch-qz-fit">'
      + '@page{size:' + paperWidth + 'mm auto;margin:0 !important;}'
      + 'html,body{margin:0 !important;padding:0 !important;background:#fff !important;}'
      + 'body{width:' + paperWidth + 'mm !important;min-width:' + paperWidth + 'mm !important;max-width:' + paperWidth + 'mm !important;overflow:visible !important;padding:' + marginMm + 'mm !important;}'
      + '.ktouch-qz-fit-wrap{display:block !important;width:' + wrapperWidth + 'mm !important;max-width:' + wrapperWidth + 'mm !important;margin:0 !important;padding:0 !important;transform:scale(' + scaleFactor + ');transform-origin:top left;}'
      + '</style>';

    if (source.indexOf('ktouch-qz-fit') !== -1) {
      return source;
    }

    if (/<\/head>/i.test(source)) {
      source = source.replace(/<\/head>/i, fitCss + '</head>');
    } else {
      source = fitCss + source;
    }

    if (/<body[^>]*>/i.test(source)) {
      source = source.replace(/<body([^>]*)>/i, '<body$1><div class="ktouch-qz-fit-wrap">');
      source = source.replace(/<\/body>/i, '</div></body>');
      return source;
    }

    return '<div class="ktouch-qz-fit-wrap">' + source + '</div>';
  }

  // ── Helpers ───────────────────────────────────────────────────────
  function getRenderUrl(job) {
    if (job.route_path) {
      var url = String(job.route_path);
      if (job.route_query) {
        url += (url.indexOf('?') === -1 ? '?' : '&') + String(job.route_query);
      }
      return url;
    }
    var type = String(job.print_type || '').toLowerCase();
    var saleId = job.sale_id || job.ref_id || 0;
    var refId = job.ref_id || job.sale_id || 0;
    if (type === 'bill' && saleId) return 'render_bill.php?sale_id=' + encodeURIComponent(saleId);
    if (type === 'cashier_order' && saleId) return 'render_cashier_order.php?sale_id=' + encodeURIComponent(saleId);
    if (type === 'bot' && saleId) return 'render_bot.php?sale_id=' + encodeURIComponent(saleId);
    if (type === 'kot' && saleId) return 'render_kot.php?sale_id=' + encodeURIComponent(saleId);
    if (type === 'got' && saleId) return 'render_got.php?sale_id=' + encodeURIComponent(saleId);
    if (type === 'ret' && saleId) return 'render_ret.php?sale_id=' + encodeURIComponent(saleId);
    if (type === 'kug' && saleId) return 'render_kug.php?sale_id=' + encodeURIComponent(saleId);
    if (type === 'gwitia' && refId) return 'render_req.php?id=' + encodeURIComponent(refId);
    if (type === 'tots' && refId) return 'render_tots.php?id=' + encodeURIComponent(refId);
    if (type === 'variance' && refId) return 'render_variance.php?cntr_id=' + encodeURIComponent(refId);
    if ((type === 'summary' || type === 'shift_sum') && refId) return 'render_shift_sum.php?id=' + encodeURIComponent(refId);
    if (type === 'staff_sum' && refId) return 'render_staff_summary.php?id=' + encodeURIComponent(refId);
    if (type === 'counter_sum' && refId) return 'render_counter_sum.php?summary_id=' + encodeURIComponent(refId);
    return '';
  }

  function callDone(queueId) {
    $.getJSON(API_URL + '?action=done&queue_id=' + queueId);
  }

  function claimJob(queueId) {
    return $.getJSON(API_URL + '?action=claim&queue_id=' + queueId).then(function (res) {
      return !!(res && res.ok);
    });
  }

  function failJob(queueId, message) {
    $.getJSON(API_URL + '?action=fail&queue_id=' + queueId + '&max_attempts=' + MAX_ATTEMPTS + '&error=' + encodeURIComponent(message || 'Print job failed'));
  }

  function logJob(seq, job, result) {
    var now  = new Date().toLocaleTimeString();
    var badgeClass = String(job.print_type || 'bill').replace(/[^a-z0-9_]/gi, '_').toLowerCase();
    var badge = '<span class="label badge-' + badgeClass + '">' + String(job.label || job.print_type || 'print').toUpperCase() + '</span>';
    var refNo = job.ref_id || job.sale_id || job.queue_id;
    $('#job-tbody').prepend(
      '<tr><td>' + seq + '</td><td>' + badge + '</td><td>#' + refNo +
      '</td><td>' + now + '</td><td>' + result + '</td></tr>'
    );
    // keep log to 50 rows
    var rows = $('#job-tbody tr');
    if (rows.length > 50) rows.last().remove();
  }

  function setStatus(msg) { $('#status-text').text(msg); }
  function setDot(state)  { $('#status-dot').removeClass('ok err busy').addClass(state); }
  // ── Warn before tab close ─────────────────────────────────────────
  window.addEventListener('beforeunload', function (e) {
    if (pollTimer) {
      e.preventDefault();
      e.returnValue = 'The print server is running. Closing this tab will stop printing. Are you sure?';
      return e.returnValue;
    }
  });

})();
</script>
</body>
</html>
