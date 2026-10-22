<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../index.php?msg=6');
    exit;
}

include __DIR__ . '/auth.php';
include __DIR__ . '/client_helpers.php';

function oc_client_read_api_key(): string
{
    return trim(openclaw_api_key());
}

$today = date('Y-m-d');
$monthAgo = date('Y-m-d', strtotime('-30 days'));
$configuredKey = oc_client_read_api_key();

$form = [
    'endpoint' => './workflow_api.php',
    'http_method' => 'GET',
    'datasets' => 'sales',
    'from' => $monthAgo,
    'to' => $today,
    'limit' => '25',
    'page' => '1',
    'customer_id' => '',
    'timeout' => '30',
    'with_totals' => '0',
    'response_mode' => 'compact',
    'auth_mode' => $configuredKey !== '' ? 'configured' : 'manual',
    'api_key' => '',
];

$errorMsg = '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>OpenClaw Pull Test</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link href="../../assets/plugins/bootstrap/4.0.0/css/bootstrap.min.css" rel="stylesheet" />
    <style>
        body {
            margin: 0;
            min-height: 100vh;
            background: linear-gradient(160deg, #eef3e8 0%, #f7f2e8 48%, #edf6f8 100%);
            color: #21313a;
            font-family: "Segoe UI", Tahoma, sans-serif;
        }
        .page-wrap {
            max-width: 1120px;
            margin: 28px auto;
            padding: 0 14px 28px;
        }
        .hero,
        .panel {
            border: 0;
            border-radius: 16px;
            box-shadow: 0 16px 38px rgba(30, 46, 54, 0.10);
        }
        .hero {
            background: linear-gradient(135deg, #17384a, #225c53);
            color: #fff;
        }
        .panel {
            background: #fffdf9;
        }
        .tiny {
            font-size: 12px;
            color: #5b6972;
        }
        .code-box {
            min-height: 240px;
            max-height: 52vh;
            padding: 14px;
            border-radius: 12px;
            background: #13212c;
            color: #d7ecff;
            white-space: pre-wrap;
            word-break: break-word;
            overflow: auto;
            font-family: Consolas, "Courier New", monospace;
            font-size: 13px;
            line-height: 1.55;
        }
        .badge-soft {
            background: #eaf3ec;
            color: #20513f;
            border-radius: 999px;
            padding: 6px 10px;
            display: inline-block;
            font-size: 12px;
        }
        .hidden {
            display: none;
        }
        .panel-tools {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
        }
        .copy-note {
            font-size: 12px;
            color: #5b6972;
            min-height: 18px;
        }
    </style>
</head>
<body>
    <div class="page-wrap">
        <div class="card hero mb-3">
            <div class="card-body py-3">
                <div class="d-flex justify-content-between align-items-center flex-wrap">
                    <div>
                        <h4 class="mb-1">OpenClaw Pull Test</h4>
                        <div class="mb-0">Run a pull against `gen/openclaw_workflow_api.php` and preview the returned JSON.</div>
                    </div>
                    <div class="mt-2 mt-sm-0">
                        <span class="badge-soft mr-2"><?php echo $configuredKey !== '' ? 'Configured key found' : 'Manual key required'; ?></span>
                        <a href="../../index.php" class="btn btn-light btn-sm">Home</a>
                    </div>
                </div>
            </div>
        </div>

        <div id="page-alert"<?php echo $errorMsg === '' ? ' class="alert alert-danger hidden"' : ' class="alert alert-danger"'; ?>><?php echo htmlspecialchars($errorMsg, ENT_QUOTES, 'UTF-8'); ?></div>

        <div class="row">
            <div class="col-lg-4 mb-3">
                <div class="card panel h-100">
                    <div class="card-body">
                        <form id="openclaw-form" method="post">
                            <div class="form-group">
                                <label>Endpoint</label>
                                <input type="text" class="form-control" name="endpoint" value="<?php echo htmlspecialchars($form['endpoint'], ENT_QUOTES, 'UTF-8'); ?>" />
                                <div class="tiny mt-1">Relative path or full URL.</div>
                            </div>

                            <div class="form-row">
                                <div class="form-group col-6">
                                    <label>Mode</label>
                                    <select class="form-control" name="http_method">
                                        <option value="GET"<?php echo $form['http_method'] === 'GET' ? ' selected' : ''; ?>>GET</option>
                                        <option value="POST_JSON"<?php echo $form['http_method'] === 'POST_JSON' ? ' selected' : ''; ?>>POST JSON</option>
                                    </select>
                                </div>
                                <div class="form-group col-6">
                                    <label>Timeout</label>
                                    <input type="number" min="5" max="120" class="form-control" name="timeout" value="<?php echo htmlspecialchars($form['timeout'], ENT_QUOTES, 'UTF-8'); ?>" />
                                </div>
                            </div>

                            <div class="form-group">
                                <label>Datasets</label>
                                <input type="text" class="form-control" name="datasets" value="<?php echo htmlspecialchars($form['datasets'], ENT_QUOTES, 'UTF-8'); ?>" />
                                <div class="tiny mt-1">Example: `sales,sale_items,sale_payments` or `all`.</div>
                            </div>

                            <div class="form-row">
                                <div class="form-group col-6">
                                    <label>From</label>
                                    <input type="date" class="form-control" name="from" value="<?php echo htmlspecialchars($form['from'], ENT_QUOTES, 'UTF-8'); ?>" />
                                </div>
                                <div class="form-group col-6">
                                    <label>To</label>
                                    <input type="date" class="form-control" name="to" value="<?php echo htmlspecialchars($form['to'], ENT_QUOTES, 'UTF-8'); ?>" />
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group col-4">
                                    <label>Limit</label>
                                    <input type="number" class="form-control" name="limit" value="<?php echo htmlspecialchars($form['limit'], ENT_QUOTES, 'UTF-8'); ?>" />
                                </div>
                                <div class="form-group col-4">
                                    <label>Page</label>
                                    <input type="number" class="form-control" name="page" value="<?php echo htmlspecialchars($form['page'], ENT_QUOTES, 'UTF-8'); ?>" />
                                </div>
                                <div class="form-group col-4">
                                    <label>Customer</label>
                                    <input type="number" class="form-control" name="customer_id" value="<?php echo htmlspecialchars($form['customer_id'], ENT_QUOTES, 'UTF-8'); ?>" />
                                </div>
                            </div>

                            <div class="form-group">
                                <label>Speed Mode</label>
                                <select class="form-control" name="with_totals">
                                    <option value="0"<?php echo $form['with_totals'] === '0' ? ' selected' : ''; ?>>Fast pull without totals</option>
                                    <option value="1"<?php echo $form['with_totals'] === '1' ? ' selected' : ''; ?>>Full pagination totals</option>
                                </select>
                                <div class="tiny mt-1">Fast mode skips `COUNT(*)` queries and feels much lighter on large tables.</div>
                            </div>

                            <div class="form-group">
                                <label>Response Shape</label>
                                <select class="form-control" name="response_mode">
                                    <option value="compact"<?php echo $form['response_mode'] === 'compact' ? ' selected' : ''; ?>>Compact and shorter</option>
                                    <option value="full"<?php echo $form['response_mode'] === 'full' ? ' selected' : ''; ?>>Full metadata</option>
                                </select>
                                <div class="tiny mt-1">Compact mode trims wrapper metadata and keeps the JSON much shorter.</div>
                            </div>

                            <div class="form-group">
                                <label>Shared X-API-Key</label>
                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($configuredKey, ENT_QUOTES, 'UTF-8'); ?>" readonly />
                                <div class="tiny mt-1">Hardcoded in `gen/openclaw/auth.php` for reuse by other OpenClaw APIs.</div>
                            </div>

                            <div class="form-group">
                                <label>Auth</label>
                                <select class="form-control" name="auth_mode">
                                    <option value="configured"<?php echo $form['auth_mode'] === 'configured' ? ' selected' : ''; ?>>Configured key</option>
                                    <option value="manual"<?php echo $form['auth_mode'] === 'manual' ? ' selected' : ''; ?>>Manual key</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label>Manual API Key</label>
                                <input type="text" class="form-control" name="api_key" value="<?php echo htmlspecialchars($form['api_key'], ENT_QUOTES, 'UTF-8'); ?>" autocomplete="off" />
                            </div>

                            <button type="submit" id="run-pull-btn" class="btn btn-primary btn-block">Run Pull</button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-8 mb-3">
                <div class="card panel mb-3">
                    <div class="card-body">
                        <div class="panel-tools mb-3">
                            <h5 class="mb-0">Response</h5>
                            <button type="button" id="copy-response-btn" class="btn btn-outline-secondary btn-sm">Copy Response</button>
                        </div>
                        <div id="response-empty" class="alert alert-secondary mb-3">Submit the form to run a pull request.</div>
                        <div id="response-meta" class="mb-2 hidden">
                            <span id="response-http" class="badge mr-2"></span>
                            <span id="response-time" class="badge badge-info mr-2"></span>
                            <span id="response-error" class="badge badge-warning hidden"></span>
                        </div>
                        <div id="response-box" class="code-box"></div>
                        <div id="response-copy-note" class="copy-note mt-2"></div>
                    </div>
                </div>

                <div class="card panel">
                    <div class="card-body">
                        <div class="panel-tools mb-3">
                            <h5 class="mb-0">Request Preview</h5>
                            <button type="button" id="copy-request-btn" class="btn btn-outline-secondary btn-sm">Copy Request</button>
                        </div>
                        <div id="request-box" class="code-box"></div>
                        <div id="request-copy-note" class="copy-note mt-2"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        (function () {
            const form = document.getElementById('openclaw-form');
            const alertBox = document.getElementById('page-alert');
            const submitButton = document.getElementById('run-pull-btn');
            const responseEmpty = document.getElementById('response-empty');
            const responseMeta = document.getElementById('response-meta');
            const responseHttp = document.getElementById('response-http');
            const responseTime = document.getElementById('response-time');
            const responseError = document.getElementById('response-error');
            const responseBox = document.getElementById('response-box');
            const requestBox = document.getElementById('request-box');
            const copyResponseButton = document.getElementById('copy-response-btn');
            const copyRequestButton = document.getElementById('copy-request-btn');
            const responseCopyNote = document.getElementById('response-copy-note');
            const requestCopyNote = document.getElementById('request-copy-note');
            const sharedApiKey = <?php echo json_encode($configuredKey, JSON_UNESCAPED_UNICODE); ?>;

            function setAlert(message) {
                if (!message) {
                    alertBox.textContent = '';
                    alertBox.classList.add('hidden');
                    return;
                }

                alertBox.textContent = message;
                alertBox.classList.remove('hidden');
            }

            function prettyJson(text) {
                try {
                    return JSON.stringify(JSON.parse(text), null, 2);
                } catch (error) {
                    return text;
                }
            }

            function resolveEndpoint(endpoint) {
                return new URL(endpoint || './workflow_api.php', window.location.href).toString();
            }

            async function copyText(text, noteEl, successMessage) {
                if (!text) {
                    noteEl.textContent = 'Nothing to copy yet.';
                    return;
                }

                try {
                    await navigator.clipboard.writeText(text);
                    noteEl.textContent = successMessage;
                } catch (error) {
                    noteEl.textContent = 'Copy failed.';
                }
            }

            copyResponseButton.addEventListener('click', function () {
                copyText(responseBox.textContent, responseCopyNote, 'Response copied.');
            });

            copyRequestButton.addEventListener('click', function () {
                copyText(requestBox.textContent, requestCopyNote, 'Request copied.');
            });

            form.addEventListener('submit', async function (event) {
                event.preventDefault();
                setAlert('');
                responseCopyNote.textContent = '';
                requestCopyNote.textContent = '';

                const formData = new FormData(form);
                const selectedKey = formData.get('auth_mode') === 'configured'
                    ? sharedApiKey
                    : String(formData.get('api_key') || '').trim();

                if (!selectedKey) {
                    setAlert('API key is required. Use the configured key or enter one manually.');
                    return;
                }

                const endpoint = resolveEndpoint(String(formData.get('endpoint') || './workflow_api.php').trim());
                const httpMethod = String(formData.get('http_method') || 'GET').toUpperCase();
                const timeoutSeconds = Math.max(5, Math.min(120, parseInt(String(formData.get('timeout') || '30'), 10) || 30));
                const params = new URLSearchParams();

                ['datasets', 'from', 'to', 'limit', 'page', 'customer_id', 'with_totals', 'response_mode'].forEach(function (key) {
                    const value = String(formData.get(key) || '').trim();
                    if (value !== '') {
                        params.set(key, value);
                    }
                });

                const headers = {
                    'Accept': 'application/json',
                    'X-API-Key': selectedKey
                };

                let url = endpoint;
                let requestBody = '';
                const controller = new AbortController();
                const timeoutHandle = window.setTimeout(function () {
                    controller.abort();
                }, timeoutSeconds * 1000);

                submitButton.disabled = true;
                submitButton.textContent = 'Running...';
                responseEmpty.classList.add('hidden');
                responseMeta.classList.add('hidden');
                responseError.classList.add('hidden');
                responseBox.textContent = 'Loading response...';

                try {
                    const startedAt = performance.now();
                    let fetchOptions = {
                        method: httpMethod === 'POST_JSON' ? 'POST' : 'GET',
                        headers: headers,
                        credentials: 'same-origin',
                        signal: controller.signal
                    };

                    if (fetchOptions.method === 'GET') {
                        const query = params.toString();
                        if (query) {
                            url += (url.indexOf('?') === -1 ? '?' : '&') + query;
                        }
                        requestBox.textContent = url;
                    } else {
                        headers['Content-Type'] = 'application/json';
                        requestBody = JSON.stringify(Object.fromEntries(params.entries()));
                        fetchOptions.body = requestBody;
                        requestBox.textContent = url + '\n\n' + requestBody;
                    }

                    const response = await fetch(url, fetchOptions);
                    const responseText = await response.text();
                    const durationMs = Math.round(performance.now() - startedAt);

                    responseHttp.textContent = 'HTTP ' + response.status;
                    responseHttp.className = 'badge mr-2 badge-' + (response.ok ? 'success' : 'danger');
                    responseTime.textContent = durationMs + ' ms';

                    const parsed = (function () {
                        try {
                            return JSON.parse(responseText);
                        } catch (error) {
                            return null;
                        }
                    }());

                    if (parsed && parsed.error) {
                        responseError.textContent = parsed.error;
                        responseError.classList.remove('hidden');
                    } else {
                        responseError.textContent = '';
                        responseError.classList.add('hidden');
                    }

                    responseMeta.classList.remove('hidden');
                    responseBox.textContent = prettyJson(responseText);
                } catch (error) {
                    const message = error && error.name === 'AbortError'
                        ? 'Request timed out.'
                        : 'Request failed: ' + (error && error.message ? error.message : 'unknown error');
                    setAlert(message);
                    responseBox.textContent = message;
                } finally {
                    window.clearTimeout(timeoutHandle);
                    submitButton.disabled = false;
                    submitButton.textContent = 'Run Pull';
                }
            });
        }());
    </script>
</body>
</html>
