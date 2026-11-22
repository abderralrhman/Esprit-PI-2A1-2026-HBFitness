<?php
declare(strict_types=1);

require_once __DIR__ . '/../../db.php';
require_once __DIR__ . '/../Models/MarketplaceRepository.php';

final class PageController
{
    public function __construct(private MarketplaceRepository $marketplace)
    {
    }

    public function handle(): void
    {
        $page = (string) ($_GET['page'] ?? 'admin');
        if ($page === 'admin') {
            require __DIR__ . '/../Views/admin/dashboard.php';
            return;
        }
        if ($page !== 'store') {
            http_response_code(404);
            echo 'Page not found.';
            return;
        }

        $this->renderStore();
    }

    private function renderStore(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $flashMessage = $_SESSION['marketplace_flash'] ?? null;
        unset($_SESSION['marketplace_flash']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'buy') {
            $listingId = filter_var($_POST['listing_id'] ?? null, FILTER_VALIDATE_INT);
            $buyerName = trim((string) ($_POST['buyer_name'] ?? ''));
            $buyerEmail = trim((string) ($_POST['buyer_email'] ?? ''));
            $token = (string) ($_POST['csrf_token'] ?? '');

            if (!hash_equals((string) ($_SESSION['csrf_token'] ?? ''), $token)) {
                $flashMessage = ['type' => 'error', 'text' => 'Your form has expired. Reload the page and try again.'];
            } elseif ($listingId === false || $listingId < 1 || $buyerName === '' || mb_strlen($buyerName) > 100 || !filter_var($buyerEmail, FILTER_VALIDATE_EMAIL)) {
                $flashMessage = ['type' => 'error', 'text' => 'Check your name and email address.'];
            } else {
                try {
                    $this->marketplace->createOrder($listingId, $buyerName, $buyerEmail);
                    $flashMessage = ['type' => 'success', 'text' => 'Your reservation request has been submitted.'];
                } catch (DomainException $error) {
                    $flashMessage = ['type' => 'error', 'text' => $error->getMessage()];
                } catch (Throwable $error) {
                    error_log(sprintf('[Atelier Market storefront] %s: %s', get_class($error), $error->getMessage()));
                    $flashMessage = ['type' => 'error', 'text' => 'Reservations are temporarily unavailable.'];
                }
            }

            $_SESSION['marketplace_flash'] = $flashMessage;
            $destination = 'index.php?page=store';
            if ($listingId !== false && $listingId > 0) {
                $destination .= '&listing=' . $listingId;
            }
            header('Location: ' . $destination, true, 303);
            return;
        }

        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        $categories = $this->marketplace->categories();
        $activeCategory = trim((string) ($_GET['category'] ?? ''));
        $search = trim((string) ($_GET['q'] ?? ''));
        $selectedListing = null;
        $selectedId = filter_var($_GET['listing'] ?? null, FILTER_VALIDATE_INT);
        if ($selectedId !== false && $selectedId > 0) {
            $selectedListing = $this->marketplace->listing($selectedId);
            if ($selectedListing === null) {
                http_response_code(404);
            }
        }
        $listings = $this->marketplace->listings(true, $activeCategory, $search);
        $csrfToken = $_SESSION['csrf_token'];
        require __DIR__ . '/../Views/front/store.php';
    }
}
