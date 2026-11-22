<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/Models/MarketplaceRepository.php';
require_once __DIR__ . '/Controllers/ApiController.php';

try {
    (new ApiController(new MarketplaceRepository(database())))->handle();
} catch (Throwable $error) {
    error_log(sprintf('[Atelier Market API] %s: %s', get_class($error), $error->getMessage()));
    header('Content-Type: application/json; charset=utf-8');
    http_response_code(500);
    echo json_encode(['error' => 'Unable to connect to the database.'], JSON_UNESCAPED_UNICODE);
}
