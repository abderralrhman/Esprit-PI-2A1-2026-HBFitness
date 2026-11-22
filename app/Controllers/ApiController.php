<?php
declare(strict_types=1);

require_once __DIR__ . '/../Models/MarketplaceRepository.php';

final class ApiController
{
    public function __construct(private MarketplaceRepository $marketplace)
    {
    }

    public function handle(): never
    {
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store');

        $resource = (string) ($_GET['resource'] ?? '');
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

        try {
            $this->dispatch($resource, $method);
        } catch (DomainException $error) {
            $status = $resource === 'orders' || str_contains($error->getMessage(), 'already exists') ? 409 : 422;
            $this->respond(['error' => $error->getMessage()], $status);
        } catch (Throwable $error) {
            error_log(sprintf('[Atelier Market API] %s: %s', get_class($error), $error->getMessage()));
            $this->respond(['error' => 'A server error occurred. Check the PHP error log.'], 500);
        }

        $this->respond(['error' => 'Resource not found.'], 404);
    }

    private function dispatch(string $resource, string $method): void
    {
        if ($method === 'GET' && $resource === 'dashboard') {
            $this->respond([
                'listings' => $this->marketplace->listings(),
                'orders' => $this->marketplace->orders(),
                'categories' => $this->marketplace->categories(),
            ]);
        }
        if ($method === 'GET' && $resource === 'listings') {
            $this->respond(['data' => $this->marketplace->listings()]);
        }
        if ($method === 'GET' && $resource === 'orders') {
            $this->respond(['data' => $this->marketplace->orders()]);
        }
        if ($method === 'GET' && $resource === 'categories') {
            $this->respond(['data' => $this->marketplace->categories()]);
        }
        if ($method === 'POST' && $resource === 'listings') {
            $data = $this->requestData();
            $title = trim((string) ($data['title'] ?? ''));
            $category = trim((string) ($data['category'] ?? ''));
            $seller = trim((string) ($data['seller'] ?? ''));
            $condition = trim((string) ($data['condition'] ?? 'Good'));
            $price = filter_var($data['price'] ?? null, FILTER_VALIDATE_FLOAT);

            if ($title === '' || mb_strlen($title) > 120 || $category === '' || $seller === '' || mb_strlen($seller) > 100) {
                $this->respond(['error' => 'Enter a title (up to 120 characters), a category, and a seller.'], 422);
            }
            if ($price === false || $price <= 0 || $price > 999999.99) {
                $this->respond(['error' => 'Price must be greater than 0 and less than €1,000,000.'], 422);
            }
            if (!in_array($condition, ['Like new', 'Very good', 'Good', 'Needs repair'], true)) {
                $this->respond(['error' => 'Invalid item condition.'], 422);
            }

            $id = $this->marketplace->createListing([
                'title' => $title,
                'category' => $category,
                'seller' => $seller,
                'condition' => $condition,
                'price' => $price,
            ]);
            $this->respond(['id' => $id, 'message' => 'Listing created.'], 201);
        }
        if ($resource === 'listings' && isset($_GET['id']) && $method === 'PATCH') {
            $id = $this->resourceId();
            $data = $this->requestData();
            if (isset($data['title']) || isset($data['category']) || isset($data['seller']) || isset($data['price']) || isset($data['condition'])) {
                $listing = $this->validateListingData($data);
                if (!$this->marketplace->updateListing($id, $listing)) {
                    $this->respond(['error' => 'Listing not found.'], 404);
                }
                $this->respond(['message' => 'Listing updated.']);
            }
            if (!$this->marketplace->updateListingStatus($id, (string) ($data['status'] ?? ''))) {
                $this->respond(['error' => 'Listing not found.'], 404);
            }
            $this->respond(['message' => 'Listing status updated.']);
        }
        if ($resource === 'listings' && isset($_GET['id']) && $method === 'DELETE') {
            if (!$this->marketplace->deleteListing($this->resourceId())) {
                $this->respond(['error' => 'Listing not found.'], 404);
            }
            $this->respond(['message' => 'Listing deleted.']);
        }
        if ($method === 'POST' && $resource === 'categories') {
            $data = $this->requestData();
            $name = $this->categoryName($data);
            $id = $this->marketplace->createCategory($name);
            $this->respond(['id' => $id, 'message' => 'Category created.'], 201);
        }
        if ($resource === 'categories' && isset($_GET['id']) && $method === 'PATCH') {
            $data = $this->requestData();
            $name = $this->categoryName($data);
            if (!$this->marketplace->updateCategory($this->resourceId(), $name)) {
                $this->respond(['error' => 'Category not found.'], 404);
            }
            $this->respond(['message' => 'Category updated.']);
        }
        if ($resource === 'categories' && isset($_GET['id']) && $method === 'DELETE') {
            if (!$this->marketplace->deleteCategory($this->resourceId())) {
                $this->respond(['error' => 'Category not found.'], 404);
            }
            $this->respond(['message' => 'Category deleted.']);
        }
        if ($resource === 'orders' && isset($_GET['id']) && $method === 'PATCH') {
            $data = $this->requestData();
            if (!$this->marketplace->advanceOrder($this->resourceId(), (string) ($data['status'] ?? ''))) {
                $this->respond(['error' => 'Order not found.'], 404);
            }
            $this->respond(['message' => 'Order status updated.']);
        }
        if ($resource === 'orders' && isset($_GET['id']) && $method === 'DELETE') {
            if (!$this->marketplace->deleteOrder($this->resourceId())) {
                $this->respond(['error' => 'Order not found.'], 404);
            }
            $this->respond(['message' => 'Order deleted.']);
        }

        if (!in_array($method, ['GET', 'POST', 'PATCH', 'DELETE'], true)) {
            header('Allow: GET, POST, PATCH, DELETE');
            $this->respond(['error' => 'HTTP method not allowed.'], 405);
        }
        if (in_array($resource, ['listings', 'orders'], true) && in_array($method, ['PATCH', 'DELETE'], true) && !isset($_GET['id'])) {
            $this->respond(['error' => 'Invalid resource ID.'], 400);
        }
    }

    private function validateListingData(array $data): array
    {
        $title = trim((string) ($data['title'] ?? ''));
        $category = trim((string) ($data['category'] ?? ''));
        $seller = trim((string) ($data['seller'] ?? ''));
        $condition = trim((string) ($data['condition'] ?? 'Good'));
        $price = filter_var($data['price'] ?? null, FILTER_VALIDATE_FLOAT);

        if ($title === '' || mb_strlen($title) > 120 || $category === '' || $seller === '' || mb_strlen($seller) > 100) {
            $this->respond(['error' => 'Enter a title (up to 120 characters), a category, and a seller.'], 422);
        }
        if ($price === false || $price <= 0 || $price > 999999.99) {
            $this->respond(['error' => 'Price must be greater than 0 and less than €1,000,000.'], 422);
        }
        if (!in_array($condition, ['Like new', 'Very good', 'Good', 'Needs repair'], true)) {
            $this->respond(['error' => 'Invalid item condition.'], 422);
        }

        return compact('title', 'category', 'seller', 'condition', 'price');
    }

    private function categoryName(array $data): string
    {
        $name = trim((string) ($data['name'] ?? ''));
        if ($name === '' || mb_strlen($name) > 60) {
            $this->respond(['error' => 'Category name is required (up to 60 characters).'], 422);
        }
        return $name;
    }

    private function resourceId(): int
    {
        $id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);
        if ($id === false || $id < 1) {
            $this->respond(['error' => 'Invalid ID.'], 400);
        }

        return $id;
    }

    private function requestData(): array
    {
        $raw = file_get_contents('php://input');
        if ($raw === false || $raw === '') {
            return [];
        }

        try {
            $data = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            $this->respond(['error' => 'The submitted JSON is invalid.'], 400);
        }

        if (!is_array($data)) {
            $this->respond(['error' => 'The request body must be a JSON object.'], 400);
        }

        return $data;
    }

    private function respond(array $data, int $status = 200): never
    {
        http_response_code($status);
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }
}
