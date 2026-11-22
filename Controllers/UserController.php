<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../Models/User.php';
require_once __DIR__ . '/AuthController.php';

/**
 * Controllers/UserController.php
 * Complete User CRUD with Prepared PDO Statements, SQL JOINs, Validation & CSRF
 */
class UserController
{
    private PDO $db;
    private AuthController $auth;

    public function __construct(?AuthController $auth = null)
    {
        $this->db   = Database::getConnexion();
        $this->auth = $auth ?? new AuthController();
    }

    // =========================================================================
    // READ (with explicit JOIN on roles & user_statuses + Search & Filters)
    // =========================================================================

    /**
     * @return User[]
     */
    public function getAllUsers(string $search = '', ?int $roleId = null, ?int $statusId = null): array
    {
        $sql = '
            SELECT u.id, u.full_name, u.email, u.phone, u.role_id, u.status_id,
                   u.created_at, u.updated_at,
                   r.name AS role_name,
                   s.name AS status_name
            FROM users u
            INNER JOIN roles r ON u.role_id = r.id
            INNER JOIN user_statuses s ON u.status_id = s.id
            WHERE 1 = 1
        ';
        $params = [];

        $search = trim($search);
        if ($search !== '') {
            $sql .= ' AND (u.full_name LIKE :search_name OR u.email LIKE :search_email)';
            $params['search_name']  = '%' . $search . '%';
            $params['search_email'] = '%' . $search . '%';
        }
        if ($roleId !== null && $roleId > 0) {
            $sql .= ' AND u.role_id = :role_id';
            $params['role_id'] = $roleId;
        }
        if ($statusId !== null && $statusId > 0) {
            $sql .= ' AND u.status_id = :status_id';
            $params['status_id'] = $statusId;
        }

        $sql .= ' ORDER BY u.id DESC';

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll();

        return array_map(static fn(array $row): User => User::fromArray($row), $rows);
    }

    public function getUserById(int $id): ?User
    {
        if ($id <= 0) {
            return null;
        }
        $sql = '
            SELECT u.id, u.full_name, u.email, u.phone, u.role_id, u.status_id,
                   u.created_at, u.updated_at,
                   r.name AS role_name,
                   s.name AS status_name
            FROM users u
            INNER JOIN roles r ON u.role_id = r.id
            INNER JOIN user_statuses s ON u.status_id = s.id
            WHERE u.id = :id
            LIMIT 1
        ';
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ? User::fromArray($row) : null;
    }

    /**
     * Aggregated user statistics for Backoffice dashboard (via JOIN)
     */
    public function getStats(): array
    {
        $sql = "
            SELECT
                COUNT(*) AS total_users,
                SUM(CASE WHEN r.name = 'admin' THEN 1 ELSE 0 END) AS total_admins,
                SUM(CASE WHEN r.name = 'coach' THEN 1 ELSE 0 END) AS total_coaches,
                SUM(CASE WHEN r.name = 'user' THEN 1 ELSE 0 END) AS total_regular,
                SUM(CASE WHEN s.name = 'active' THEN 1 ELSE 0 END) AS total_active,
                SUM(CASE WHEN s.name = 'suspended' THEN 1 ELSE 0 END) AS total_suspended
            FROM users u
            INNER JOIN roles r ON u.role_id = r.id
            INNER JOIN user_statuses s ON u.status_id = s.id
        ";
        $row = $this->db->query($sql)->fetch() ?: [];
        return [
            'total_users'     => (int)($row['total_users'] ?? 0),
            'total_admins'    => (int)($row['total_admins'] ?? 0),
            'total_coaches'   => (int)($row['total_coaches'] ?? 0),
            'total_regular'   => (int)($row['total_regular'] ?? 0),
            'total_active'    => (int)($row['total_active'] ?? 0),
            'total_suspended' => (int)($row['total_suspended'] ?? 0),
        ];
    }

    // =========================================================================
    // VALIDATION HELPERS
    // =========================================================================
    private function emailExists(string $email, ?int $excludeId = null): bool
    {
        if ($excludeId !== null && $excludeId > 0) {
            $stmt = $this->db->prepare('SELECT COUNT(*) FROM users WHERE email = :email AND id <> :id');
            $stmt->execute(['email' => $email, 'id' => $excludeId]);
        } else {
            $stmt = $this->db->prepare('SELECT COUNT(*) FROM users WHERE email = :email');
            $stmt->execute(['email' => $email]);
        }
        return (int)$stmt->fetchColumn() > 0;
    }

    private function roleExists(int $roleId): bool
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM roles WHERE id = :id');
        $stmt->execute(['id' => $roleId]);
        return (int)$stmt->fetchColumn() > 0;
    }

    private function statusExists(int $statusId): bool
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM user_statuses WHERE id = :id');
        $stmt->execute(['id' => $statusId]);
        return (int)$stmt->fetchColumn() > 0;
    }

    private function getRoleNameById(int $roleId): string
    {
        $stmt = $this->db->prepare('SELECT name FROM roles WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $roleId]);
        return (string)($stmt->fetchColumn() ?: '');
    }

    private function getStatusNameById(int $statusId): string
    {
        $stmt = $this->db->prepare('SELECT name FROM user_statuses WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $statusId]);
        return (string)($stmt->fetchColumn() ?: '');
    }

    // =========================================================================
    // CREATE (Admin)
    // =========================================================================
    public function createUser(array $post): array
    {
        if (!$this->auth->verifyCsrfToken($post['csrf_token'] ?? null)) {
            return ['Invalid security token. Please try again.'];
        }

        $fullName = trim((string)($post['full_name'] ?? ''));
        $email    = trim((string)($post['email'] ?? ''));
        $password = (string)($post['password'] ?? '');
        $phone    = trim((string)($post['phone'] ?? ''));
        $roleId   = (int)($post['role_id'] ?? 0);
        $statusId = (int)($post['status_id'] ?? 0);

        $errors = [];
        if ($fullName === '' || mb_strlen($fullName) < 3 || mb_strlen($fullName) > 120 || !preg_match('/^[\p{L}\s\-\'\.]+$/u', $fullName)) {
            $errors[] = 'Full name must contain only letters and spaces (3 to 120 characters).';
        }
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Please enter a valid email address.';
        } elseif ($this->emailExists($email)) {
            $errors[] = 'This email address is already registered.';
        }
        if (mb_strlen($password) < 8 || !preg_match('/[A-Za-z]/', $password) || !preg_match('/[0-9]/', $password)) {
            $errors[] = 'Password must be at least 8 characters and contain both letters and digits.';
        }
        if ($phone !== '' && !preg_match('/^\+?[0-9\s\-]{8,20}$/', $phone)) {
            $errors[] = 'Phone number must contain 8 to 20 digits.';
        }
        if (!$this->roleExists($roleId)) {
            $errors[] = 'Please select a valid role.';
        }
        if (!$this->statusExists($statusId)) {
            $errors[] = 'Please select a valid account status.';
        }

        if (!empty($errors)) {
            return $errors;
        }

        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->db->prepare('
            INSERT INTO users (full_name, email, password_hash, phone, role_id, status_id)
            VALUES (:full_name, :email, :password_hash, :phone, :role_id, :status_id)
        ');
        $ok = $stmt->execute([
            'full_name'     => $fullName,
            'email'         => $email,
            'password_hash' => $hash,
            'phone'         => $phone !== '' ? $phone : null,
            'role_id'       => $roleId,
            'status_id'     => $statusId,
        ]);

        return $ok ? [] : ['Failed to create user account.'];
    }

    // =========================================================================
    // UPDATE (Admin)
    // =========================================================================
    public function updateUser(int $id, array $post, int $currentAdminId): array
    {
        if (!$this->auth->verifyCsrfToken($post['csrf_token'] ?? null)) {
            return ['Invalid security token. Please try again.'];
        }

        $existing = $this->getUserById($id);
        if (!$existing) {
            return ['User account not found.'];
        }

        $fullName = trim((string)($post['full_name'] ?? ''));
        $email    = trim((string)($post['email'] ?? ''));
        $password = (string)($post['password'] ?? '');
        $phone    = trim((string)($post['phone'] ?? ''));
        $roleId   = (int)($post['role_id'] ?? 0);
        $statusId = (int)($post['status_id'] ?? 0);

        $errors = [];
        if ($fullName === '' || mb_strlen($fullName) < 3 || mb_strlen($fullName) > 120 || !preg_match('/^[\p{L}\s\-\'\.]+$/u', $fullName)) {
            $errors[] = 'Full name must contain only letters and spaces (3 to 120 characters).';
        }
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Please enter a valid email address.';
        } elseif ($this->emailExists($email, $id)) {
            $errors[] = 'Another account already uses this email address.';
        }
        if ($password !== '' && (mb_strlen($password) < 8 || !preg_match('/[A-Za-z]/', $password) || !preg_match('/[0-9]/', $password))) {
            $errors[] = 'New password must be at least 8 characters and contain both letters and digits.';
        }
        if ($phone !== '' && !preg_match('/^\+?[0-9\s\-]{8,20}$/', $phone)) {
            $errors[] = 'Phone number must contain 8 to 20 digits.';
        }
        if (!$this->roleExists($roleId)) {
            $errors[] = 'Please select a valid role.';
        }
        if (!$this->statusExists($statusId)) {
            $errors[] = 'Please select a valid account status.';
        }

        // Prevent admin from locking themselves out
        if ($id === $currentAdminId) {
            if ($this->getRoleNameById($roleId) !== 'admin') {
                $errors[] = 'You cannot remove your own administrator role.';
            }
            if ($this->getStatusNameById($statusId) !== 'active') {
                $errors[] = 'You cannot suspend your own administrator account.';
            }
        }

        if (!empty($errors)) {
            return $errors;
        }

        if ($password !== '') {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $this->db->prepare('
                UPDATE users
                SET full_name = :full_name,
                    email = :email,
                    password_hash = :password_hash,
                    phone = :phone,
                    role_id = :role_id,
                    status_id = :status_id
                WHERE id = :id
            ');
            $ok = $stmt->execute([
                'full_name'     => $fullName,
                'email'         => $email,
                'password_hash' => $hash,
                'phone'         => $phone !== '' ? $phone : null,
                'role_id'       => $roleId,
                'status_id'     => $statusId,
                'id'            => $id,
            ]);
        } else {
            $stmt = $this->db->prepare('
                UPDATE users
                SET full_name = :full_name,
                    email = :email,
                    phone = :phone,
                    role_id = :role_id,
                    status_id = :status_id
                WHERE id = :id
            ');
            $ok = $stmt->execute([
                'full_name' => $fullName,
                'email'     => $email,
                'phone'     => $phone !== '' ? $phone : null,
                'role_id'   => $roleId,
                'status_id' => $statusId,
                'id'        => $id,
            ]);
        }

        return $ok ? [] : ['Failed to update user account.'];
    }

    // =========================================================================
    // DELETE (Admin, CSRF-protected POST, self-delete protected)
    // =========================================================================
    public function deleteUser(int $id, int $currentAdminId, ?string $csrfToken): array
    {
        if (!$this->auth->verifyCsrfToken($csrfToken)) {
            return ['Invalid security token. Please try again.'];
        }
        if ($id <= 0) {
            return ['Invalid user ID.'];
        }
        if ($id === $currentAdminId) {
            return ['You cannot delete your own administrator account while logged in.'];
        }
        $existing = $this->getUserById($id);
        if (!$existing) {
            return ['User account not found.'];
        }

        $stmt = $this->db->prepare('DELETE FROM users WHERE id = :id');
        $ok   = $stmt->execute(['id' => $id]);
        return ($ok && $stmt->rowCount() > 0) ? [] : ['Failed to delete user account.'];
    }

    // =========================================================================
    // FRONTOFFICE: UPDATE OWN PROFILE (User / Coach / Admin)
    // =========================================================================
    public function updateOwnProfile(int $userId, array $post): array
    {
        if (!$this->auth->verifyCsrfToken($post['csrf_token'] ?? null)) {
            return ['Invalid security token. Please try again.'];
        }

        $fullName        = trim((string)($post['full_name'] ?? ''));
        $email           = trim((string)($post['email'] ?? ''));
        $phone           = trim((string)($post['phone'] ?? ''));
        $newPassword     = (string)($post['new_password'] ?? '');
        $confirmPassword = (string)($post['new_password_confirm'] ?? '');

        $errors = [];
        if ($fullName === '' || mb_strlen($fullName) < 3 || mb_strlen($fullName) > 120 || !preg_match('/^[\p{L}\s\-\'\.]+$/u', $fullName)) {
            $errors[] = 'Full name must contain only letters and spaces (3 to 120 characters).';
        }
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Please enter a valid email address.';
        } elseif ($this->emailExists($email, $userId)) {
            $errors[] = 'This email address is already in use by another account.';
        }
        if ($phone !== '' && !preg_match('/^\+?[0-9\s\-]{8,20}$/', $phone)) {
            $errors[] = 'Phone number must contain 8 to 20 digits.';
        }
        if ($newPassword !== '') {
            if (mb_strlen($newPassword) < 8 || !preg_match('/[A-Za-z]/', $newPassword) || !preg_match('/[0-9]/', $newPassword)) {
                $errors[] = 'New password must be at least 8 characters and include letters and digits.';
            }
            if ($newPassword !== $confirmPassword) {
                $errors[] = 'New password confirmation does not match.';
            }
        }

        if (!empty($errors)) {
            return $errors;
        }

        if ($newPassword !== '') {
            $stmt = $this->db->prepare('
                UPDATE users
                SET full_name = :full_name, email = :email, phone = :phone, password_hash = :password_hash
                WHERE id = :id
            ');
            $ok = $stmt->execute([
                'full_name'     => $fullName,
                'email'         => $email,
                'phone'         => $phone !== '' ? $phone : null,
                'password_hash' => password_hash($newPassword, PASSWORD_DEFAULT),
                'id'            => $userId,
            ]);
        } else {
            $stmt = $this->db->prepare('
                UPDATE users
                SET full_name = :full_name, email = :email, phone = :phone
                WHERE id = :id
            ');
            $ok = $stmt->execute([
                'full_name' => $fullName,
                'email'     => $email,
                'phone'     => $phone !== '' ? $phone : null,
                'id'        => $userId,
            ]);
        }

        return $ok ? [] : ['Unable to update your profile. Please try again.'];
    }
}
