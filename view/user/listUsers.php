<?php
require_once __DIR__ . '/../../controller/AuthController.php';
$currentUser = AuthController::requireAdmin();

$userC = new UserController();
$users = $userC->listUsers();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Users — List (HB FITNESS)</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/fitness.css">
</head>
<body>
<div class="app-wrapper">
    <?php
    $basePrefix = '../../';
    $activeNav  = 'users';
    include __DIR__ . '/../partials/navbar.php';
    ?>

    <div class="container">
        <section class="crud-page-header">
            <div>
                <div class="breadcrumb-trail">
                    <a href="../../index.php?page=admin">Admin Dashboard</a> <span>/</span> <span>Users</span>
                </div>
                <h1><i class="fa-solid fa-users-gear"></i> Athlete & Coach Accounts Registry</h1>
                <p>Manage coach administrators and registered athletes.</p>
            </div>
            <a href="createUser.php" class="btn btn-green" style="z-index:1;">
                <i class="fa-solid fa-user-plus"></i> Create New User
            </a>
        </section>

        <div class="full-card">
            <div class="card-header">
                <div class="card-title"><i class="fa-solid fa-users"></i> All Users (<?= count($users) ?>)</div>
                <div class="search-input-wrap">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="text" class="form-control" placeholder="Search users..." data-search-target=".user-row">
                </div>
            </div>
            <div class="table-responsive">
                <table class="smart-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Full Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Phone</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $u): ?>
                        <tr class="user-row">
                            <td><strong>#<?= (int)$u['id'] ?></strong></td>
                            <td><strong><?= htmlspecialchars($u['full_name']) ?></strong></td>
                            <td><?= htmlspecialchars($u['email']) ?></td>
                            <td>
                                <?php
                                $roleBadge = $u['role'] === 'admin' ? 'badge-orange' : ($u['role'] === 'coach' ? 'badge-navy' : 'badge-green');
                                ?>
                                <span class="badge <?= $roleBadge ?>"><?= strtoupper(htmlspecialchars($u['role'])) ?></span>
                            </td>
                            <td><?= htmlspecialchars($u['phone'] ?? '—') ?></td>
                            <td style="display:flex;gap:0.4rem;">
                                <a href="updateUser.php?id=<?= (int)$u['id'] ?>" class="btn btn-outline btn-sm"><i class="fa-solid fa-pen"></i> Edit</a>
                                <?php if ((int)$u['id'] !== (int)$currentUser['id']): ?>
                                    <a href="deleteUser.php?id=<?= (int)$u['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this user account?');"><i class="fa-solid fa-trash"></i> Delete</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<script src="../assets/js/fitness.js"></script>
</body>
</html>
