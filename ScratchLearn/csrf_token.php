<?php
// Gives the pages of this site their CSRF token (see security.php). Needs no database.
require_once __DIR__ . '/security.php';
send_api_headers();
echo json_encode(['token' => csrf_token()]);
