<?php
declare(strict_types=1);

namespace KTouch\Modules\Chaba\V1\Repositories;

use mysqli;
use RuntimeException;

/** Queue writes shared by application modules that still use mysqli. */
final class ChabaQueueRepository
{
    public static function queueCsp(mysqli $connection, int $summaryId, int $counterId, string $shift, int $userId, array $document = []): int
    {
        $escShift = mysqli_real_escape_string($connection, $shift);
        $query = mysqli_real_escape_string($connection, "summary_id={$summaryId}&cntr_id={$counterId}&shift=" . urlencode($shift));
        $payload = mysqli_real_escape_string($connection, (string)json_encode($document, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        $sql = "INSERT INTO kp_print_queue (sale_id,ref_id,print_type,label,cntr_id,route_path,route_query,requested_by,status,shift,payload_json) VALUES (0,{$summaryId},'csp','COUNTER SUMMARY'," . (int)$counterId . ",'csp.php','{$query}'," . (int)$userId . ",0,'{$escShift}','{$payload}')";
        if (!mysqli_query($connection, $sql)) throw new RuntimeException('Could not queue counter summary');
        return (int)mysqli_insert_id($connection);
    }

    public static function queueSsp(mysqli $connection, string $shift, int $userId, array $document = []): int
    {
        $escShift = mysqli_real_escape_string($connection, $shift);
        $payload = mysqli_real_escape_string($connection, (string)json_encode($document, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        $sql = "INSERT INTO kp_print_queue (sale_id,ref_id,print_type,label,cntr_id,route_path,route_query,requested_by,status,shift,payload_json) VALUES (0,0,'ssp','SHIFT SUMMARY',0,'ssp.php','shift=" . urlencode($shift) . "'," . (int)$userId . ",0,'{$escShift}','{$payload}')";
        if (!mysqli_query($connection, $sql)) throw new RuntimeException('Could not queue shift summary');
        return (int)mysqli_insert_id($connection);
    }
}
