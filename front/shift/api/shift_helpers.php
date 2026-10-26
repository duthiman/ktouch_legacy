<?php
declare(strict_types=1);

function shift_api_int(array $data, string $key, int $default = 0): int
{
    $value = $data[$key] ?? $default;
    return filter_var($value, FILTER_VALIDATE_INT) === false ? $default : (int)$value;
}

function shift_api_response(bool $ok, string $status, string $message, array $data = []): array
{
    return ['ok' => $ok, 'status' => $status, 'message' => $message, 'data' => $data];
}

function shift_api_json(array $response): never
{
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($response, JSON_UNESCAPED_SLASHES);
    exit;
}
