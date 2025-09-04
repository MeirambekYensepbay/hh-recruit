<?php

return [
    'host'    => env('OLLAMA_HOST', 'http://127.0.0.1:11434'),
    'model'   => env('OLLAMA_MODEL', 'llama3'),
    'timeout' => (int) env('OLLAMA_TIMEOUT', 120),
];
