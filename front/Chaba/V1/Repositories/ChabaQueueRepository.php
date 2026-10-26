<?php
declare(strict_types=1);

namespace KTouch\Modules\Chaba\V1\Repositories;

use mysqli;
use RuntimeException;

/** Queue writes shared by application modules that still use mysqli. */
final class ChabaQueueRepository
{
    public static function enqueue(mysqli $connection, array $job): int
    {
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
