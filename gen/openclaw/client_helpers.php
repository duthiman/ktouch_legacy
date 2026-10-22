<?php

function oc_client_http_request(string $method, string $url, array $headers, string $body = '', int $timeout = 30): array
{
    $startedAt = microtime(true);
    $timeout = max(5, min(120, $timeout));

    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        if ($ch === false) {
            return [
                'ok' => false,
                'http_code' => 0,
                'body' => '',
                'error' => 'curl_init_failed',
                'duration_ms' => 0,
            ];
        }

        $options = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_TIMEOUT => $timeout,
            CURLOPT_CONNECTTIMEOUT => min(10, $timeout),
            CURLOPT_CUSTOMREQUEST => $method,
        ];

        if ($method === 'POST') {
            $options[CURLOPT_POST] = true;
            $options[CURLOPT_POSTFIELDS] = $body;
        }

        curl_setopt_array($ch, $options);
        $response = curl_exec($ch);
        $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = (string)curl_error($ch);
        curl_close($ch);

        return [
            'ok' => ($error === '' && $httpCode >= 200 && $httpCode < 300),
            'http_code' => $httpCode,
            'body' => is_string($response) ? $response : '',
            'error' => $error,
            'duration_ms' => (int)round((microtime(true) - $startedAt) * 1000),
        ];
    }

    $context = stream_context_create([
        'http' => [
            'method' => $method,
            'header' => implode("\r\n", $headers) . "\r\n",
            'content' => $body,
            'timeout' => $timeout,
            'ignore_errors' => true,
        ],
    ]);

    $response = @file_get_contents($url, false, $context);
    $httpCode = 0;
    if (isset($http_response_header) && is_array($http_response_header) && isset($http_response_header[0])) {
        if (preg_match('/\s(\d{3})\s/', (string)$http_response_header[0], $matches)) {
            $httpCode = (int)$matches[1];
        }
    }

    return [
        'ok' => ($httpCode >= 200 && $httpCode < 300),
        'http_code' => $httpCode,
        'body' => is_string($response) ? $response : '',
        'error' => '',
        'duration_ms' => (int)round((microtime(true) - $startedAt) * 1000),
    ];
}

function oc_client_request_headers(string $apiKey): array
{
    $headers = [
        'Accept: application/json',
        'X-API-Key: ' . $apiKey,
    ];

    $sessionName = session_name();
    $sessionId = session_id();
    if ($sessionName !== '' && $sessionId !== '') {
        $headers[] = 'Cookie: ' . $sessionName . '=' . $sessionId;
    }

    return $headers;
}

function oc_client_normalize_path(string $path): string
{
    $parts = explode('/', str_replace('\\', '/', $path));
    $normalized = [];

    foreach ($parts as $part) {
        if ($part === '' || $part === '.') {
            continue;
        }

        if ($part === '..') {
            array_pop($normalized);
            continue;
        }

        $normalized[] = $part;
    }

    return '/' . implode('/', $normalized);
}

function oc_client_absolute_url(string $endpoint): string
{
    if (preg_match('/^https?:\/\//i', $endpoint)) {
        return $endpoint;
    }

    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = (string)($_SERVER['HTTP_HOST'] ?? 'localhost');
    $scriptName = str_replace('\\', '/', (string)($_SERVER['SCRIPT_NAME'] ?? ''));
    $currentDir = str_replace('\\', '/', dirname($scriptName));
    $projectBase = dirname(dirname($currentDir));

    if (strpos($endpoint, '/') === 0) {
        $resolvedPath = $endpoint;
    } elseif (strpos($endpoint, 'gen/') === 0) {
        $resolvedPath = rtrim($projectBase, '/') . '/' . ltrim($endpoint, '/');
    } else {
        $resolvedPath = rtrim($currentDir, '/') . '/' . ltrim($endpoint, '/');
    }

    return $scheme . '://' . $host . oc_client_normalize_path($resolvedPath);
}
