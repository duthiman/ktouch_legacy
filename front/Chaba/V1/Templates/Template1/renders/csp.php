<?php
/**
 * Counter Summary Print (CSP) V1 entry point.
 *
 * ChabaService prepares the database connection and query string before this
 * renderer is loaded. It emits the complete counter-summary thermal document
 * and does not enqueue, claim, or complete queue jobs.
 */
$counterSummaryLayout = dirname(__DIR__, 6) . '/chaba-legacy/render_counter_sum.php';
if (!is_file($counterSummaryLayout)) {
    throw new RuntimeException('Counter summary layout is unavailable');
}
$previousDirectory = getcwd();
chdir(dirname($counterSummaryLayout));
try {
    require $counterSummaryLayout;
} finally {
    if (is_string($previousDirectory) && $previousDirectory !== '') {
        chdir($previousDirectory);
    }
}
