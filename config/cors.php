<?php

// Same effect as FastAPI's CORSMiddleware(allow_origins=["*"]) in backend/main.py.
// Tighten this before any real deployment.
return [
    'paths' => ['api/*', 'storage/*'],
    'allowed_methods' => ['*'],
    'allowed_origins' => ['*'],
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => false,
];
