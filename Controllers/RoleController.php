<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../Models/Role.php';
require_once __DIR__ . '/../Models/UserStatus.php';
require_once __DIR__ . '/AuthController.php';

/**
 * Controllers/RoleController.php
 * Role & Account Status management (SQL JOINs + CSRF + Admin Protection)
 */
class RoleController
{
    private PDO $db;
    private AuthController $auth;

    public function __construct(?AuthController $auth = null)
    {
        $this->db   = Database::getConnexion();
        $this->auth = $auth ?? new AuthController();
    }

    /**
     * @return Role[]
     */
    public function getAllRoles(): array
    {
        $sql = '
            SELECT r.id, r.name, COUNT(u.id) AS users_count
            FROM roles r
            LEFT JOIN users u ON u.role_id = r.id
            GROUP BY r.id, r.name
            ORDER BY r.id ASC
        ';
        $rows = $this->db->query($sql)->fetchAll();
        return array_map(static fn(array $row): Role => Role::fromArray($row), $rows);
    }

    /**
     * @return UserStatus[]
     */
    public function getAllStatuses(): array
    {
        $sql = '
            SELECT s.id, s.name, COUNT(u.id) AS users_count
            FROM user_statuses s
            LEFT JOIN users u ON u.status_id = s.id
            GROUP BY s.id, s.name
            ORDER BY s.id ASC
        ';
        $rows = $this->db->query($sql)->fetchAll();
        return array_map(static fn(array $row): UserStatus => UserStatus::fromArray($row), $rows);
    }

    /**
     * Updates a user's role and/or status from Backoffice -> Manage Roles.
     */
    public function updateUserRoleAndStatus(int $userId, int $roleId, int $statusId, int $currentAdminId, ?string $csrfToken): array
    {
        if (!$this->auth->verifyCsrfToken($csrfToken)) {
            return ['Invalid security token. Please try again.'];
        }
        if ($userId <= 0) {
            return ['Invalid user ID.'];
        }

        // Validate target user exists
        $uStmt = $this->db->prepare('SELECT id FROM users WHERE id = :id LIMIT 1');
        $uStmt->execute(['id' => $userId]);
        if (!$uStmt->fetch()) {
            return ['User account not found.'];
        }

        // Validate role & status exist
        $rStmt = $this->db->prepare('SELECT name FROM roles WHERE id = :id LIMIT 1');
        $rStmt->execute(['id' => $roleId]);
        $roleName = $rStmt->fetchColumn();
        if (!$roleName) {
            return ['Selected role is invalid.'];
        }

        $sStmt = $this->db->prepare('SELECT name FROM user_statuses WHERE id = :id LIMIT 1');
        $sStmt->execute(['id' => $statusId]);
        $statusName = $sStmt->fetchColumn();
        if (!$statusName) {
            return ['Selected account status is invalid.'];
        }

        // Protect currently logged-in admin from self-demotion or self-suspension
        if ($userId === $currentAdminId) {
            if ($roleName !== 'admin') {
                return ['You cannot remove your own administrator role.'];
            }
            if ($statusName !== 'active') {
                return ['You cannot suspend your own administrator account.'];
            }
        }

        $upd = $this->db->prepare('
            UPDATE users
            SET role_id = :role_id, status_id = :status_id
            WHERE id = :id
        ');
        $ok = $upd->execute([
            'role_id'   => $roleId,
            'status_id' => $statusId,
            'id'        => $userId,
        ]);

        return $ok ? [] : ['Failed to update user role and status.'];
    }
}
