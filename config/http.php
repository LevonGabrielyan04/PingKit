<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | HTTP Errors CSV Export Columns
    |--------------------------------------------------------------------------
    |
    | Header labels keyed by attribute paths on failed HTTP check log records
    | returned by HttpCheckLogRepository::exportFailedQuery().
    |
    */

    'errors_export_columns' => [
        'Checked at' => 'created_at',
        'Target' => 'target',
        'Status' => 'status_code',
        'Total (ms)' => 'response_time_ms',
        'DNS (ms)' => 'dns_time_ms',
        'TCP (ms)' => 'tcp_time_ms',
        'TLS (ms)' => 'tls_time_ms',
        'Error' => 'error_message',
        'Headers' => 'response_headers',
    ],

];
