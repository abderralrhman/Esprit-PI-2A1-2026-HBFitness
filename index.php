<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/app/Models/MarketplaceRepository.php';
require_once __DIR__ . '/app/Controllers/PageController.php';

try {
    (new PageController(new MarketplaceRepository(database())))->handle();
} catch (Throwable $error) {
    error_log(sprintf('[Atelier Market pages] %s: %s', get_class($error), $error->getMessage()));
    http_response_code(500);
    echo 'Unable to connect to the database. Check the MySQL configuration.';
}
