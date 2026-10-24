<?php

return [
    'url' => env('CBS_SERVICE_URL', 'http://127.0.0.1:8001'),
    'timeout' => (int) env('CBS_SERVICE_TIMEOUT', 10),
    'retry_times' => (int) env('CBS_SERVICE_RETRY_TIMES', 2),
    'retry_sleep' => (int) env('CBS_SERVICE_RETRY_SLEEP', 100),
];
