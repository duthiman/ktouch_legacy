<?php
declare(strict_types=1);

function counter_log(string $event, array $data = []): void
{
    error_log('counter ' . json_encode(['event' => $event, 'data' => $data, 'time' => date('c')], JSON_UNESCAPED_SLASHES));
}
