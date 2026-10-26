<?php
declare(strict_types=1);

namespace KTouch\Modules\Chaba\V1\Repositories;

use mysqli;
use RuntimeException;

/** Queue writes shared by application modules that still use mysqli. */
final class ChabaQueueRepository
{
    public static function ensureSchema(mysqli $connection): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS kp_print_queue (
            queue_id INT NOT NULL AUTO_INCREMENT, sale_id INT NOT NULL DEFAULT 0,
            ref_id INT NOT NULL DEFAULT 0, print_type VARCHAR(55) NOT NULL DEFAULT 'bill',
            label VARCHAR(120) NOT NULL DEFAULT '', cntr_id INT NOT NULL DEFAULT 0,
            route_path VARCHAR(255) NOT NULL DEFAULT '', route_query TEXT NULL,
            requested_by INT NOT NULL DEFAULT 0, reprint_of INT NOT NULL DEFAULT 0,
            status TINYINT NOT NULL DEFAULT 0, created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            shift VARCHAR(60) NOT NULL DEFAULT '', payload_json LONGTEXT NULL, items_json LONGTEXT NULL,
            print_attempts TINYINT UNSIGNED NOT NULL DEFAULT 0, last_error VARCHAR(255) NOT NULL DEFAULT '',
            printed_at DATETIME NULL, claimed_at DATETIME NULL, lease_until DATETIME NULL,
            PRIMARY KEY (queue_id), KEY idx_queue_status_created (status,created_at), KEY idx_counter_status (cntr_id,status)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        if (!mysqli_query($connection, $sql)) throw new RuntimeException('Could not prepare Chaba print queue');
        foreach ([
            'claimed_at' => 'DATETIME NULL', 'lease_until' => 'DATETIME NULL',
            'print_attempts' => 'TINYINT UNSIGNED NOT NULL DEFAULT 0', 'last_error' => "VARCHAR(255) NOT NULL DEFAULT ''",
            'shift' => "VARCHAR(60) NOT NULL DEFAULT ''", 'payload_json' => 'LONGTEXT NULL', 'items_json' => 'LONGTEXT NULL',
        ] as $column => $definition) {
            $name = mysqli_real_escape_string($connection, $column);
            $check = mysqli_query($connection, "SHOW COLUMNS FROM kp_print_queue LIKE '{$name}'");
            if ($check && mysqli_num_rows($check) === 0 && !mysqli_query($connection, "ALTER TABLE kp_print_queue ADD COLUMN `{$name}` {$definition}")) {
                throw new RuntimeException('Could not heal Chaba queue column: ' . $column);
            }
        }
    }

    public static function enqueue(mysqli $connection, array $job): int
    {
        self::ensureSchema($connection);
        $payload = $job['payload'] ?? $job['payload_json'] ?? [];
        if (!is_array($payload)) $payload = json_decode((string)$payload, true);
        if (!is_array($payload) || empty($payload)) throw new RuntimeException('A complete print payload is required');
        $payload['payload_version'] = (int)($payload['payload_version'] ?? 1);
        $payload['document_type'] = strtoupper((string)($payload['document_type'] ?? $job['print_type'] ?? ''));
        if ($payload['document_type'] === '') throw new RuntimeException('Print document type is required');

        $saleId = (int)($job['sale_id'] ?? 0);
        $refId = (int)($job['ref_id'] ?? $saleId);
        $type = mysqli_real_escape_string($connection, strtolower($payload['document_type']));
        $label = mysqli_real_escape_string($connection, (string)($job['label'] ?? strtoupper($payload['document_type'])));
        $counterId = (int)($job['cntr_id'] ?? 0);
        $routePath = mysqli_real_escape_string($connection, (string)($job['route_path'] ?? ''));
        $routeQuery = mysqli_real_escape_string($connection, (string)($job['route_query'] ?? ''));
        $requestedBy = (int)($job['requested_by'] ?? 0);
        $shift = mysqli_real_escape_string($connection, (string)($job['shift'] ?? ($payload['header']['shift'] ?? '')));
        $payloadJson = mysqli_real_escape_string($connection, (string)json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        $items = is_array($payload['items'] ?? null) ? $payload['items'] : [];
        $itemsJson = mysqli_real_escape_string($connection, (string)json_encode($items, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        $sql = "INSERT INTO kp_print_queue (sale_id,ref_id,print_type,label,cntr_id,route_path,route_query,requested_by,reprint_of,status,shift,payload_json,items_json) VALUES ({$saleId},{$refId},'{$type}','{$label}',{$counterId},'{$routePath}','{$routeQuery}',{$requestedBy},0,0,'{$shift}','{$payloadJson}','{$itemsJson}')";
        if (!mysqli_query($connection, $sql)) throw new RuntimeException('Could not enqueue print document');
        return (int)mysqli_insert_id($connection);
    }

}
