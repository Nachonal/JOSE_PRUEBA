<?php

return [
    'paths' => ['api/*'],          // qué rutas exponen CORS
    'allowed_methods' => ['*'],    // GET, POST, PUT, DELETE, OPTIONS, etc.
    'allowed_origins' => [         // orígenes que podrán consumir la API
        'http://localhost:5173',
        'http://127.0.0.1:5173',
        'http://myproject.test',
    ],
    'allowed_headers' => ['*'],    // qué headers aceptas
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => false, // pon true solo si vas a enviar cookies/credenciales
];
