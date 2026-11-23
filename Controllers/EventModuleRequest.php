<?php
declare(strict_types=1);

/** HTTP and form handling shared by the two Module 4 controllers. */
final class EventModuleRequest
{
    public const MAX_UNSIGNED_ID = 4294967295;

    public static function allow(string ...$methods): string
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        if (!in_array($method, $methods, true)) {
            header('Allow: ' . implode(', ', $methods));
            self::fail(405, 'This action does not support that request method.');
        }

        return $method;
    }

    public static function id(): int
    {
        $id = self::unsignedInt($_GET['id'] ?? null);
        if ($id === null || $id === 0) {
            self::fail(404, 'The requested record could not be found.');
        }
        return $id;
    }

    public static function unsignedInt(mixed $value): ?int
    {
        if (!is_string($value) || !preg_match('/\A[0-9]{1,10}\z/', $value)) {
            return null;
        }
        if ((float) $value > min((float) PHP_INT_MAX, self::MAX_UNSIGNED_ID)) {
            return null;
        }
        return (int) $value;
    }

    public static function requireCsrf(AuthController $auth, User $user): void
    {
        $token = $_POST['csrf_token'] ?? null;
        if (!is_string($token) || !$auth->verifyCsrfToken($token)) {
            self::fail(403, 'Your form expired. Return to the page and try again.', $user);
        }
    }

    /** Accept scalar form values and retain field-specific errors for invalid input. */
    public static function values(array $defaults, array &$errors): array
    {
        $values = [];
        foreach (array_keys($defaults) as $field) {
            $value = $_POST[$field] ?? '';
            if (!is_string($value) || !mb_check_encoding($value, 'UTF-8')) {
                $errors[$field] = 'Enter a valid text value.';
                $value = '';
            }
            $values[$field] = trim($value);
        }
        return $values;
    }

    public static function validWebsite(string $value): bool
    {
        return filter_var($value, FILTER_VALIDATE_URL) !== false
            && in_array(strtolower((string) parse_url($value, PHP_URL_SCHEME)), ['http', 'https'], true);
    }

    public static function validLogo(string $value): bool
    {
        return (bool) preg_match(
            '~\AViews/assets/images/[A-Za-z0-9][A-Za-z0-9._-]*\.(?:png|jpe?g|webp|gif|svg)\z~i',
            $value
        ) && is_file(__DIR__ . '/../' . $value);
    }

    public static function redirect(string $action, ?int $id = null): never
    {
        $url = 'index.php?action=' . $action;
        if ($id !== null) {
            $url .= '&id=' . $id;
        }
        header('Location: ' . $url, true, 303);
        exit;
    }

    public static function fail(int $status, string $message, ?User $currentUser = null): never
    {
        http_response_code($status);
        $title = 'Events & Sponsors — HB FITNESS';
        $currentPage = 'events';
        ob_start();
        ?>
        <div class="container py-5">
            <div class="hb-card mx-auto" style="max-width: 42rem">
                <h1 class="h4">Events &amp; Sponsors</h1>
                <p><?= htmlspecialchars($message, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></p>
                <a class="btn btn-hb-dark" href="index.php?action=events">Back to events</a>
            </div>
        </div>
        <?php
        $content = (string) ob_get_clean();
        require __DIR__ . '/../Views/layout.php';
        exit;
    }
}
