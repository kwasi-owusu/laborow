<?php

http_response_code(404);
header('Content-Type: application/json');
echo json_encode([
    'error' => true,
    'message' => 'Notifications API is not available yet.'
]);
