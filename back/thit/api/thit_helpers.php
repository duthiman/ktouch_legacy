<?php
declare(strict_types=1);

function thit_json(array $payload, int $status = 200): never
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function thit_table_exists(mysqli $db, string $table): bool
{
    $table = $db->real_escape_string($table);
    try {
        $result = $db->query("SHOW TABLES LIKE '{$table}'");
        return $result instanceof mysqli_result && $result->num_rows > 0;
    } catch (Throwable $e) {
        return false;
    }
}

function thit_column_exists(mysqli $db, string $table, string $column): bool
{
    if (!thit_table_exists($db, $table)) return false;
    $table = str_replace('`', '', $table);
    $column = $db->real_escape_string($column);
    try {
        $result = $db->query("SHOW COLUMNS FROM `{$table}` LIKE '{$column}'");
        return $result instanceof mysqli_result && $result->num_rows > 0;
    } catch (Throwable $e) {
        return false;
    }
}

function thit_query(mysqli $db, string $sql): ?mysqli_result
{
    try {
        $result = $db->query($sql);
        return $result instanceof mysqli_result ? $result : null;
    } catch (Throwable $e) {
        return null;
    }
}

function thit_scalar(mysqli $db, string $sql, mixed $default = 0): mixed
{
    $result = thit_query($db, $sql);
    if (!$result) return $default;
    $row = $result->fetch_row();
    return $row === null || $row[0] === null ? $default : $row[0];
}

function thit_rows(mysqli $db, string $sql): array
{
    $result = thit_query($db, $sql);
    if (!$result) return [];
    $rows = [];
    while ($row = $result->fetch_assoc()) $rows[] = $row;
    return $rows;
}

function thit_number(mixed $value): float
{
    return is_numeric($value) ? (float)$value : 0.0;
}

function thit_money(float $value): string
{
    return number_format($value, 2, '.', ',');
}
