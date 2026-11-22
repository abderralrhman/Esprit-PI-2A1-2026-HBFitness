<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../model/User.php';

class UserController {
    public function listUsers(): array {
        $db = Config::getConnexion();
        $stmt = $db->query("SELECT * FROM users ORDER BY id DESC");
        return $stmt->fetchAll();
    }

    public function listUsersWithStats(): array {
        $db = Config::getConnexion();
        $sql = "
            SELECT u.*,
                   (SELECT COUNT(*) FROM articles a WHERE a.user_id = u.id) AS articles_count,
                   (SELECT COUNT(*) FROM comments c WHERE c.user_id = u.id) AS comments_count
            FROM users u
            ORDER BY u.id DESC
        ";
        return $db->query($sql)->fetchAll();
    }

    public function getUserById(int $id): ?array {
        $db = Config::getConnexion();
        $stmt = $db->prepare("SELECT * FROM users WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function authenticate(string $email, string $password): ?array {
        $db = Config::getConnexion();
        $stmt = $db->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();
        if ($user && password_verify($password, $user['password'])) {
            return $user;
        }
        return null;
    }

    public function addUser(User $user): bool {
        $db = Config::getConnexion();
        $hashedPassword = password_hash($user->getPassword(), PASSWORD_DEFAULT);
        $stmt = $db->prepare("
            INSERT INTO users (full_name, email, password, role, phone, avatar_color)
            VALUES (:full_name, :email, :password, :role, :phone, :avatar_color)
        ");
        return $stmt->execute([
            'full_name' => $user->getFullName(),
            'email' => $user->getEmail(),
            'password' => $hashedPassword,
            'role' => $user->getRole(),
            'phone' => $user->getPhone(),
            'avatar_color' => $user->getAvatarColor()
        ]);
    }

    public function updateUser(User $user, int $id): bool {
        $db = Config::getConnexion();
        if (!empty($user->getPassword())) {
            $hashedPassword = password_hash($user->getPassword(), PASSWORD_DEFAULT);
            $stmt = $db->prepare("
                UPDATE users SET
                    full_name = :full_name,
                    email = :email,
                    password = :password,
                    role = :role,
                    phone = :phone,
                    avatar_color = :avatar_color
                WHERE id = :id
            ");
            return $stmt->execute([
                'full_name' => $user->getFullName(),
                'email' => $user->getEmail(),
                'password' => $hashedPassword,
                'role' => $user->getRole(),
                'phone' => $user->getPhone(),
                'avatar_color' => $user->getAvatarColor(),
                'id' => $id
            ]);
        }

        $stmt = $db->prepare("
            UPDATE users SET
                full_name = :full_name,
                email = :email,
                role = :role,
                phone = :phone,
                avatar_color = :avatar_color
            WHERE id = :id
        ");
        return $stmt->execute([
            'full_name' => $user->getFullName(),
            'email' => $user->getEmail(),
            'role' => $user->getRole(),
            'phone' => $user->getPhone(),
            'avatar_color' => $user->getAvatarColor(),
            'id' => $id
        ]);
    }

    public function getUserByEmail(string $email): ?array {
        $db = Config::getConnexion();
        $stmt = $db->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
        $stmt->execute(['email' => $email]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function emailExists(string $email, ?int $excludeId = null): bool {
        $db = Config::getConnexion();
        if ($excludeId !== null) {
            $stmt = $db->prepare("SELECT COUNT(*) FROM users WHERE email = :email AND id <> :id");
            $stmt->execute(['email' => $email, 'id' => $excludeId]);
        } else {
            $stmt = $db->prepare("SELECT COUNT(*) FROM users WHERE email = :email");
            $stmt->execute(['email' => $email]);
        }
        return (int)$stmt->fetchColumn() > 0;
    }

    public function deleteUser(int $id): bool {
        $db = Config::getConnexion();
        $stmt = $db->prepare("DELETE FROM users WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }
}
?>
