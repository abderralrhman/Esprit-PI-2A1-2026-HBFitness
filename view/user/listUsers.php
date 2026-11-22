<?php
require_once __DIR__ . '/../../controller/AuthController.php';

$currentUser = AuthController::requireAdmin();
$userC       = new UserController();

// ------------------------------------------------------------------
// Full CRUD POST Handler (Create / Update / Delete) + Contrôle de saisie
// ------------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $crudAction = $_POST['crud_action'] ?? '';
    $uid        = (int)$currentUser['id'];
    $targetId   = (int)($_POST['id'] ?? 0);

    try {
        if ($crudAction === 'create' || $crudAction === 'update') {
            $fullName    = trim($_POST['full_name'] ?? '');
            $email       = trim($_POST['email'] ?? '');
            $password    = $_POST['password'] ?? '';
            $role        = $_POST['role'] ?? 'user';
            $phone       = trim($_POST['phone'] ?? '');
            $avatarColor = trim($_POST['avatar_color'] ?? '#910329');

            if (!preg_match('/^#[0-9a-fA-F]{6}$/', $avatarColor)) {
                $avatarColor = '#910329';
            }

            $errors = [];
            if ($fullName === '' || mb_strlen($fullName) < 3 || !preg_match('/^[\p{L}\s\-\'\.]+$/u', $fullName)) {
                $errors[] = 'Full Name must contain only letters and spaces (min 3 characters).';
            }
            if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'Please enter a valid email address.';
            } elseif ($userC->emailExists($email, $crudAction === 'update' ? $targetId : null)) {
                $errors[] = 'This email address is already registered.';
            }
            if ($crudAction === 'create' && mb_strlen($password) < 6) {
                $errors[] = 'Password must be at least 6 characters long.';
            }
            if ($crudAction === 'update' && $password !== '' && mb_strlen($password) < 6) {
                $errors[] = 'New password must be at least 6 characters long.';
            }
            if (!in_array($role, ['admin', 'coach', 'user'], true)) {
                $errors[] = 'Role must be admin, coach, or user.';
            }
            if ($crudAction === 'update' && $targetId === $uid && $role !== 'admin') {
                $errors[] = 'You cannot remove your own administrator role.';
            }
            if ($phone !== '' && !preg_match('/^\+?[0-9\s\-]{8,20}$/', $phone)) {
                $errors[] = 'Phone number must contain 8 to 20 digits.';
            }

            if ($errors) {
                throw new Exception(implode(' | ', $errors));
            }

            if ($crudAction === 'create') {
                $newUser = new User(null, $fullName, $email, $password, $role, $phone !== '' ? $phone : null, $avatarColor);
                $userC->addUser($newUser);
                AuthController::flash("User \"$fullName\" created successfully!");
            } else {
                $existing = $userC->getUserById($targetId);
                if (!$existing) {
                    throw new Exception("User #$targetId not found.");
                }
                $updUser = new User($targetId, $fullName, $email, $password, $role, $phone !== '' ? $phone : null, $avatarColor);
                $userC->updateUser($updUser, $targetId);
                AuthController::flash("User #$targetId (\"$fullName\") updated successfully!");
            }
        } elseif ($crudAction === 'delete') {
            if ($targetId <= 0) {
                throw new Exception('Invalid user ID.');
            }
            if ($targetId === $uid) {
                throw new Exception('You cannot delete your own account while logged in.');
            }
            $existing = $userC->getUserById($targetId);
            if (!$existing) {
                throw new Exception("User #$targetId not found.");
            }
            $userC->deleteUser($targetId);
            AuthController::flash("User #$targetId (\"{$existing['full_name']}\") deleted.");
        }
    } catch (PDOException $e) {
        AuthController::flash('Database error: ' . $e->getMessage(), 'error');
    } catch (Exception $e) {
        AuthController::flash($e->getMessage(), 'error');
    }

    header('Location: listUsers.php');
    exit;
}

$users        = $userC->listUsersWithStats();
$totalUsers   = count($users);
$adminCount   = count(array_filter($users, fn($u) => $u['role'] === 'admin'));
$coachCount   = count(array_filter($users, fn($u) => $u['role'] === 'coach'));
$athleteCount = count(array_filter($users, fn($u) => $u['role'] === 'user'));

if (!function_exists('userInitials')) {
    function userInitials(string $name): string {
        $parts = preg_split('/\s+/', trim($name));
        $init  = '';
        foreach (array_slice($parts, 0, 2) as $p) {
            if ($p !== '') {
                $init .= mb_strtoupper(mb_substr($p, 0, 1));
            }
        }
        return $init ?: 'HB';
    }
}

$swatches = ['#910329', '#E8405E', '#171717', '#5A0219', '#52525B'];

// Build clean JSON lookup map for JS modals (100% immune to HTML attribute escaping issues)
$usersMap = [];
foreach ($users as $u) {
    $uidKey = (int)$u['id'];
    $usersMap[$uidKey] = [
        'id'             => $uidKey,
        'full_name'      => (string)$u['full_name'],
        'email'          => (string)$u['email'],
        'role'           => (string)$u['role'],
        'phone'          => (string)($u['phone'] ?? ''),
        'avatar_color'   => (string)($u['avatar_color'] ?: '#910329'),
        'created_at'     => (string)($u['created_at'] ?? ''),
        'articles_count' => (int)$u['articles_count'],
        'comments_count' => (int)$u['comments_count'],
        'initials'       => userInitials((string)$u['full_name']),
    ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management CRUD — HB FITNESS</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/fitness.css?v=3">
</head>
<body>
<div class="app-wrapper">
    <?php
    $basePrefix = '../../';
    $activeNav  = 'users';
    include __DIR__ . '/../partials/navbar.php';
    ?>

    <div class="container">
        <!-- Hero Banner -->
        <section class="crud-page-header">
            <div>
                <div class="breadcrumb-trail">
                    <a href="../../index.php?page=admin">Admin Dashboard</a> <span>/</span> <span>User Management CRUD</span>
                </div>
                <h1><i class="fa-solid fa-users-gear"></i> Personalized User Management</h1>
                <p>Create, inspect, update, and delete Admin, Coach, and Athlete accounts from one unified HB FITNESS console.</p>
            </div>
            <div style="display:flex;gap:0.65rem;flex-wrap:wrap;z-index:1;">
                <button type="button" class="btn btn-primary" data-action="create">
                    <i class="fa-solid fa-user-plus"></i> Create New User
                </button>
            </div>
        </section>

        <!-- 4 KPI Stats Cards -->
        <section class="kpi-grid">
            <div class="kpi-card">
                <div class="kpi-info">
                    <span>Total Accounts</span>
                    <h3 class="counter-value" data-target="<?= $totalUsers ?>">0</h3>
                </div>
                <div class="kpi-icon"><i class="fa-solid fa-users"></i></div>
            </div>
            <div class="kpi-card">
                <div class="kpi-info">
                    <span>Administrators</span>
                    <h3 class="counter-value" data-target="<?= $adminCount ?>">0</h3>
                </div>
                <div class="kpi-icon"><i class="fa-solid fa-shield-halved"></i></div>
            </div>
            <div class="kpi-card">
                <div class="kpi-info">
                    <span>Coaches</span>
                    <h3 class="counter-value" data-target="<?= $coachCount ?>">0</h3>
                </div>
                <div class="kpi-icon"><i class="fa-solid fa-dumbbell"></i></div>
            </div>
            <div class="kpi-card">
                <div class="kpi-info">
                    <span>Athletes (Users)</span>
                    <h3 class="counter-value" data-target="<?= $athleteCount ?>">0</h3>
                </div>
                <div class="kpi-icon"><i class="fa-solid fa-person-running"></i></div>
            </div>
        </section>

        <!-- Role Filter Pills + Search + View Switcher -->
        <div class="crud-subnav" style="justify-content:space-between;">
            <div style="display:flex;gap:0.45rem;flex-wrap:wrap;" id="roleFilterBar">
                <button type="button" class="subnav-pill active" data-role="all"><i class="fa-solid fa-layer-group"></i> All (<?= $totalUsers ?>)</button>
                <button type="button" class="subnav-pill" data-role="admin"><i class="fa-solid fa-shield-halved"></i> Admins (<?= $adminCount ?>)</button>
                <button type="button" class="subnav-pill" data-role="coach"><i class="fa-solid fa-dumbbell"></i> Coaches (<?= $coachCount ?>)</button>
                <button type="button" class="subnav-pill" data-role="user"><i class="fa-solid fa-user"></i> Users (<?= $athleteCount ?>)</button>
            </div>

            <div style="display:flex;gap:0.75rem;align-items:center;flex-wrap:wrap;">
                <div class="search-input-wrap">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="text" id="userLiveSearch" class="form-control" placeholder="Search name, email, phone...">
                </div>
                <div class="view-switcher">
                    <button type="button" class="view-switch-btn active" id="btnTableView"><i class="fa-solid fa-list"></i> Table</button>
                    <button type="button" class="view-switch-btn" id="btnCardView"><i class="fa-solid fa-grip"></i> Cards</button>
                </div>
            </div>
        </div>

        <!-- TABLE VIEW -->
        <div class="full-card" id="usersTableContainer">
            <div class="card-header">
                <div class="card-title"><i class="fa-solid fa-address-book"></i> Registered Users (<span id="visibleCount"><?= $totalUsers ?></span>)</div>
                <button type="button" class="btn btn-green btn-sm" data-action="create">
                    <i class="fa-solid fa-plus"></i> Add User
                </button>
            </div>
            <div class="table-responsive">
                <table class="smart-table">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Role</th>
                            <th>Phone</th>
                            <th>Activity</th>
                            <th>Joined</th>
                            <th>CRUD Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $u):
                            $uidRow    = (int)$u['id'];
                            $isSelf    = $uidRow === (int)$currentUser['id'];
                            $roleBadge = $u['role'] === 'admin' ? 'badge-orange' : ($u['role'] === 'coach' ? 'badge-navy' : 'badge-green');
                            $avatarBg  = htmlspecialchars($u['avatar_color'] ?: '#910329');
                            $initials  = htmlspecialchars(userInitials($u['full_name']));
                        ?>
                        <tr class="user-item" data-role="<?= htmlspecialchars($u['role']) ?>">
                            <td>
                                <div style="display:flex;align-items:center;gap:0.85rem;">
                                    <div class="user-avatar-circle" style="background:<?= $avatarBg ?>;"><?= $initials ?></div>
                                    <div>
                                        <div style="font-weight:700;color:#fff;">
                                            <?= htmlspecialchars($u['full_name']) ?>
                                            <?php if ($isSelf): ?>
                                                <span class="badge badge-orange" style="font-size:0.65rem;padding:0.15rem 0.5rem;margin-left:0.35rem;">YOU</span>
                                            <?php endif; ?>
                                        </div>
                                        <div style="font-size:0.8rem;color:var(--hb-silver);"><?= htmlspecialchars($u['email']) ?> · #<?= $uidRow ?></div>
                                    </div>
                                </div>
                            </td>
                            <td><span class="badge <?= $roleBadge ?>"><?= strtoupper(htmlspecialchars($u['role'])) ?></span></td>
                            <td><?= htmlspecialchars($u['phone'] ?: '—') ?></td>
                            <td>
                                <span style="font-size:0.8rem;color:var(--hb-silver);">
                                    <i class="fa-solid fa-newspaper" style="color:var(--hb-coral);"></i> <?= (int)$u['articles_count'] ?> ·
                                    <i class="fa-solid fa-comment" style="color:#B4B4B4;"></i> <?= (int)$u['comments_count'] ?>
                                </span>
                            </td>
                            <td style="font-size:0.82rem;color:var(--hb-silver);">
                                <?= !empty($u['created_at']) ? date('M d, Y', strtotime($u['created_at'])) : '—' ?>
                            </td>
                            <td>
                                <div style="display:flex;gap:0.38rem;flex-wrap:wrap;">
                                    <button type="button" class="btn btn-navy btn-sm" title="Read / View Details" data-action="read" data-id="<?= $uidRow ?>">
                                        <i class="fa-solid fa-eye"></i> Read
                                    </button>
                                    <button type="button" class="btn btn-outline btn-sm" title="Update User" data-action="edit" data-id="<?= $uidRow ?>">
                                        <i class="fa-solid fa-pen"></i> Update
                                    </button>
                                    <?php if (!$isSelf): ?>
                                        <button type="button" class="btn btn-danger btn-sm" title="Delete User" data-action="delete" data-id="<?= $uidRow ?>">
                                            <i class="fa-solid fa-trash"></i> Delete
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- PROFILE CARDS VIEW -->
        <div class="cards-grid" id="usersCardsContainer" style="display:none;">
            <?php foreach ($users as $u):
                $uidRow    = (int)$u['id'];
                $isSelf    = $uidRow === (int)$currentUser['id'];
                $roleBadge = $u['role'] === 'admin' ? 'badge-orange' : ($u['role'] === 'coach' ? 'badge-navy' : 'badge-green');
                $avatarBg  = htmlspecialchars($u['avatar_color'] ?: '#910329');
                $initials  = htmlspecialchars(userInitials($u['full_name']));
            ?>
            <div class="entity-card user-card-item" data-role="<?= htmlspecialchars($u['role']) ?>">
                <div>
                    <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:0.9rem;">
                        <div style="display:flex;align-items:center;gap:0.85rem;">
                            <div class="user-avatar-circle" style="background:<?= $avatarBg ?>;"><?= $initials ?></div>
                            <div>
                                <h4 style="color:#fff;font-size:1.1rem;margin-bottom:0.15rem;"><?= htmlspecialchars($u['full_name']) ?></h4>
                                <span style="font-size:0.8rem;color:var(--hb-silver);"><?= htmlspecialchars($u['email']) ?></span>
                            </div>
                        </div>
                        <span class="badge <?= $roleBadge ?>"><?= strtoupper(htmlspecialchars($u['role'])) ?></span>
                    </div>
                    <div class="sport-metrics" style="margin:0.85rem 0;">
                        <div><strong>#<?= $uidRow ?></strong><span>ID</span></div>
                        <div><strong><?= (int)$u['articles_count'] ?></strong><span>Articles</span></div>
                        <div><strong><?= (int)$u['comments_count'] ?></strong><span>Comments</span></div>
                    </div>
                    <div style="font-size:0.82rem;color:var(--hb-silver);margin-bottom:0.9rem;">
                        <i class="fa-solid fa-phone" style="color:var(--hb-coral);margin-right:0.35rem;"></i>
                        <?= htmlspecialchars($u['phone'] ?: 'No phone provided') ?>
                    </div>
                </div>
                <div style="display:flex;gap:0.45rem;padding-top:0.85rem;border-top:1px solid var(--border-subtle);flex-wrap:wrap;">
                    <button type="button" class="btn btn-navy btn-sm" data-action="read" data-id="<?= $uidRow ?>"><i class="fa-solid fa-eye"></i> Read</button>
                    <button type="button" class="btn btn-outline btn-sm" data-action="edit" data-id="<?= $uidRow ?>"><i class="fa-solid fa-pen"></i> Update</button>
                    <?php if (!$isSelf): ?>
                        <button type="button" class="btn btn-danger btn-sm" data-action="delete" data-id="<?= $uidRow ?>"><i class="fa-solid fa-trash"></i> Delete</button>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- MODAL 1: CREATE USER -->
<div class="modal-backdrop" id="createUserModal">
    <div class="modal-box">
        <div class="modal-header">
            <h3><i class="fa-solid fa-user-plus"></i> Create New User</h3>
            <button type="button" class="modal-close-btn" onclick="closeModal('createUserModal')"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <form method="POST" id="formModalCreate" novalidate>
            <input type="hidden" name="crud_action" value="create">
            <input type="hidden" name="avatar_color" id="create_avatar_color" value="#910329">

            <div class="form-group">
                <label>Full Name (min 3 letters)</label>
                <input type="text" name="full_name" id="c_full_name" class="form-control" placeholder="e.g. Sami Ben Youssef">
                <div class="field-error-msg" id="err_c_full_name"></div>
            </div>
            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="email" id="c_email" class="form-control" placeholder="athlete@fitpulse.tn">
                <div class="field-error-msg" id="err_c_email"></div>
            </div>
            <div class="form-grid">
                <div class="form-group">
                    <label>Password (min 6 chars)</label>
                    <input type="password" name="password" id="c_password" class="form-control" placeholder="••••••••">
                    <div class="field-error-msg" id="err_c_password"></div>
                </div>
                <div class="form-group">
                    <label>Role</label>
                    <select name="role" id="c_role" class="form-control">
                        <option value="user">User (Athlete)</option>
                        <option value="coach">Coach</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
            </div>
            <div class="form-grid">
                <div class="form-group">
                    <label>Phone (optional)</label>
                    <input type="text" name="phone" id="c_phone" class="form-control" placeholder="+216 55 000 000">
                    <div class="field-error-msg" id="err_c_phone"></div>
                </div>
                <div class="form-group">
                    <label>Avatar Accent Color</label>
                    <div class="color-swatches" data-target="create_avatar_color">
                        <?php foreach ($swatches as $i => $hex): ?>
                            <span class="color-swatch <?= $i === 0 ? 'selected' : '' ?>" style="background:<?= $hex ?>;" data-color="<?= $hex ?>"></span>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <div style="display:flex;gap:0.75rem;justify-content:flex-end;margin-top:1.1rem;">
                <button type="button" class="btn btn-navy" onclick="closeModal('createUserModal')">Cancel</button>
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> Create Account</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL 2: READ / VIEW USER DETAILS -->
<div class="modal-backdrop" id="readUserModal">
    <div class="modal-box">
        <div class="modal-header">
            <h3><i class="fa-solid fa-id-card"></i> User Profile Details</h3>
            <button type="button" class="modal-close-btn" onclick="closeModal('readUserModal')"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div style="display:flex;align-items:center;gap:1.1rem;margin-bottom:1.1rem;">
            <div class="user-avatar-circle user-avatar-lg" id="r_avatar">HB</div>
            <div>
                <h2 id="r_full_name" style="font-size:1.55rem;text-transform:uppercase;color:#fff;margin-bottom:0.25rem;"></h2>
                <span class="badge badge-orange" id="r_role"></span>
            </div>
        </div>
        <div class="user-detail-grid">
            <div class="user-detail-item"><small>User ID</small><strong id="r_id"></strong></div>
            <div class="user-detail-item"><small>Email Address</small><strong id="r_email"></strong></div>
            <div class="user-detail-item"><small>Phone Number</small><strong id="r_phone"></strong></div>
            <div class="user-detail-item"><small>Member Since</small><strong id="r_created"></strong></div>
            <div class="user-detail-item"><small>Published Articles</small><strong id="r_articles"></strong></div>
            <div class="user-detail-item"><small>Posted Comments</small><strong id="r_comments"></strong></div>
        </div>
        <div style="display:flex;gap:0.75rem;justify-content:flex-end;margin-top:1rem;">
            <button type="button" class="btn btn-navy" onclick="closeModal('readUserModal')">Close</button>
            <button type="button" class="btn btn-primary" id="r_edit_shortcut"><i class="fa-solid fa-pen"></i> Edit This User</button>
        </div>
    </div>
</div>

<!-- MODAL 3: UPDATE USER -->
<div class="modal-backdrop" id="editUserModal">
    <div class="modal-box">
        <div class="modal-header">
            <h3><i class="fa-solid fa-user-pen"></i> Update User <span id="e_title_id" style="color:var(--hb-coral);"></span></h3>
            <button type="button" class="modal-close-btn" onclick="closeModal('editUserModal')"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <form method="POST" id="formModalEdit" novalidate>
            <input type="hidden" name="crud_action" value="update">
            <input type="hidden" name="id" id="e_id">
            <input type="hidden" name="avatar_color" id="edit_avatar_color" value="#910329">

            <div class="form-group">
                <label>Full Name</label>
                <input type="text" name="full_name" id="e_full_name" class="form-control">
                <div class="field-error-msg" id="err_e_full_name"></div>
            </div>
            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="email" id="e_email" class="form-control">
                <div class="field-error-msg" id="err_e_email"></div>
            </div>
            <div class="form-grid">
                <div class="form-group">
                    <label>New Password (optional)</label>
                    <input type="password" name="password" id="e_password" class="form-control" placeholder="Leave blank to keep current">
                    <div class="field-error-msg" id="err_e_password"></div>
                </div>
                <div class="form-group">
                    <label>Role</label>
                    <select name="role" id="e_role" class="form-control">
                        <option value="user">User (Athlete)</option>
                        <option value="coach">Coach</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
            </div>
            <div class="form-grid">
                <div class="form-group">
                    <label>Phone</label>
                    <input type="text" name="phone" id="e_phone" class="form-control">
                    <div class="field-error-msg" id="err_e_phone"></div>
                </div>
                <div class="form-group">
                    <label>Avatar Accent Color</label>
                    <div class="color-swatches" data-target="edit_avatar_color" id="editSwatches">
                        <?php foreach ($swatches as $hex): ?>
                            <span class="color-swatch" style="background:<?= $hex ?>;" data-color="<?= $hex ?>"></span>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <div style="display:flex;gap:0.75rem;justify-content:flex-end;margin-top:1.1rem;">
                <button type="button" class="btn btn-navy" onclick="closeModal('editUserModal')">Cancel</button>
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-check"></i> Save Changes</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL 4: DELETE CONFIRMATION -->
<div class="modal-backdrop" id="deleteUserModal">
    <div class="modal-box" style="max-width:460px;">
        <div class="modal-header">
            <h3><i class="fa-solid fa-triangle-exclamation"></i> Delete User Account</h3>
            <button type="button" class="modal-close-btn" onclick="closeModal('deleteUserModal')"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <p style="color:#E5E5E5;margin-bottom:0.85rem;">
            Are you sure you want to permanently delete <strong id="d_name" style="color:var(--hb-coral);"></strong> (<span id="d_email" style="color:var(--hb-silver);"></span>)?
        </p>
        <div class="alert-box alert-error" style="margin-bottom:1.2rem;">
            <i class="fa-solid fa-circle-info"></i>
            <span>This action is irreversible and will also remove all articles and comments authored by this user.</span>
        </div>
        <form method="POST">
            <input type="hidden" name="crud_action" value="delete">
            <input type="hidden" name="id" id="d_id">
            <div style="display:flex;gap:0.75rem;justify-content:flex-end;">
                <button type="button" class="btn btn-navy" onclick="closeModal('deleteUserModal')">Cancel</button>
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-trash"></i> Confirm Delete</button>
            </div>
        </form>
    </div>
</div>

<script src="../assets/js/fitness.js?v=3"></script>
<script>
(function () {
    const USERS_BY_ID = <?= json_encode($usersMap, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE) ?>;

    window.openModal = id => document.getElementById(id)?.classList.add('open');
    window.closeModal = id => document.getElementById(id)?.classList.remove('open');

    document.querySelectorAll('.modal-backdrop').forEach(bd => {
        bd.addEventListener('click', e => { if (e.target === bd) bd.classList.remove('open'); });
    });

    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') {
            document.querySelectorAll('.modal-backdrop.open').forEach(m => m.classList.remove('open'));
        }
    });

    // Event delegation for all CRUD action buttons (Create, Read, Update, Delete)
    document.addEventListener('click', e => {
        const btn = e.target.closest('[data-action]');
        if (!btn) return;
        const action = btn.getAttribute('data-action');
        if (action === 'create') {
            openCreateModal();
            return;
        }
        const id = btn.getAttribute('data-id');
        const user = USERS_BY_ID[id];
        if (!user) return;
        if (action === 'read') openReadModal(user);
        else if (action === 'edit') openEditModal(user);
        else if (action === 'delete') openDeleteModal(user);
    });

    document.querySelectorAll('.color-swatches').forEach(group => {
        const targetId = group.dataset.target;
        group.querySelectorAll('.color-swatch').forEach(sw => {
            sw.addEventListener('click', () => {
                group.querySelectorAll('.color-swatch').forEach(s => s.classList.remove('selected'));
                sw.classList.add('selected');
                document.getElementById(targetId).value = sw.dataset.color;
            });
        });
    });

    window.openCreateModal = function () {
        document.getElementById('formModalCreate').reset();
        document.querySelectorAll('#formModalCreate .field-error-msg').forEach(el => el.textContent = '');
        openModal('createUserModal');
    };

    let lastReadUser = null;
    window.openReadModal = function (u) {
        lastReadUser = u;
        const av = document.getElementById('r_avatar');
        av.textContent = u.initials;
        av.style.background = u.avatar_color;
        document.getElementById('r_full_name').textContent = u.full_name;
        document.getElementById('r_role').textContent = u.role.toUpperCase();
        document.getElementById('r_id').textContent = '#' + u.id;
        document.getElementById('r_email').textContent = u.email;
        document.getElementById('r_phone').textContent = u.phone || 'Not provided';
        document.getElementById('r_created').textContent = u.created_at || '—';
        document.getElementById('r_articles').textContent = u.articles_count + ' article(s)';
        document.getElementById('r_comments').textContent = u.comments_count + ' comment(s)';
        openModal('readUserModal');
    };

    document.getElementById('r_edit_shortcut').addEventListener('click', () => {
        if (!lastReadUser) return;
        closeModal('readUserModal');
        openEditModal(lastReadUser);
    });

    window.openEditModal = function (u) {
        document.querySelectorAll('#formModalEdit .field-error-msg').forEach(el => el.textContent = '');
        document.getElementById('e_title_id').textContent = '#' + u.id;
        document.getElementById('e_id').value = u.id;
        document.getElementById('e_full_name').value = u.full_name;
        document.getElementById('e_email').value = u.email;
        document.getElementById('e_password').value = '';
        document.getElementById('e_role').value = u.role;
        document.getElementById('e_phone').value = u.phone || '';
        document.getElementById('edit_avatar_color').value = u.avatar_color || '#910329';
        document.querySelectorAll('#editSwatches .color-swatch').forEach(sw => {
            sw.classList.toggle('selected', sw.dataset.color.toLowerCase() === (u.avatar_color || '').toLowerCase());
        });
        openModal('editUserModal');
    };

    window.openDeleteModal = function (u) {
        document.getElementById('d_id').value = u.id;
        document.getElementById('d_name').textContent = u.full_name;
        document.getElementById('d_email').textContent = u.email;
        openModal('deleteUserModal');
    };

    let activeRole = 'all';
    const roleBtns = document.querySelectorAll('#roleFilterBar .subnav-pill');
    const searchInput = document.getElementById('userLiveSearch');
    const rows = document.querySelectorAll('.user-item');
    const cards = document.querySelectorAll('.user-card-item');
    const countSpan = document.getElementById('visibleCount');

    function applyFilters() {
        const q = searchInput.value.toLowerCase().trim();
        let visible = 0;
        rows.forEach((row, idx) => {
            const matchRole = activeRole === 'all' || row.dataset.role === activeRole;
            const matchText = q === '' || row.textContent.toLowerCase().includes(q);
            const show = matchRole && matchText;
            row.style.display = show ? '' : 'none';
            if (cards[idx]) cards[idx].style.display = show ? '' : 'none';
            if (show) visible++;
        });
        countSpan.textContent = visible;
    }

    roleBtns.forEach(btn => btn.addEventListener('click', () => {
        roleBtns.forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        activeRole = btn.dataset.role;
        applyFilters();
    }));
    searchInput.addEventListener('input', applyFilters);

    const btnTable = document.getElementById('btnTableView');
    const btnCard  = document.getElementById('btnCardView');
    const tblBox   = document.getElementById('usersTableContainer');
    const crdBox   = document.getElementById('usersCardsContainer');

    btnTable.addEventListener('click', () => {
        btnTable.classList.add('active'); btnCard.classList.remove('active');
        tblBox.style.display = ''; crdBox.style.display = 'none';
    });
    btnCard.addEventListener('click', () => {
        btnCard.classList.add('active'); btnTable.classList.remove('active');
        tblBox.style.display = 'none'; crdBox.style.display = 'grid';
    });

    const nameRegex  = /^[\p{L}\s\-'\.]+$/u;
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    const phoneRegex = /^\+?[0-9\s\-]{8,20}$/;

    function attachValidation(formId, prefix, requirePass) {
        document.getElementById(formId).addEventListener('submit', function (e) {
            let valid = true;
            const fn = document.getElementById(prefix + '_full_name');
            const em = document.getElementById(prefix + '_email');
            const pw = document.getElementById(prefix + '_password');
            const ph = document.getElementById(prefix + '_phone');
            this.querySelectorAll('.field-error-msg').forEach(el => el.textContent = '');

            if (fn.value.trim().length < 3 || !nameRegex.test(fn.value.trim())) {
                document.getElementById('err_' + prefix + '_full_name').textContent = 'Full Name must contain only letters and spaces (min 3 chars).';
                fn.classList.add('is-invalid'); valid = false;
            } else { fn.classList.remove('is-invalid'); }

            if (!emailRegex.test(em.value.trim())) {
                document.getElementById('err_' + prefix + '_email').textContent = 'Please enter a valid email address.';
                em.classList.add('is-invalid'); valid = false;
            } else { em.classList.remove('is-invalid'); }

            if ((requirePass && pw.value.length < 6) || (!requirePass && pw.value !== '' && pw.value.length < 6)) {
                document.getElementById('err_' + prefix + '_password').textContent = 'Password must be at least 6 characters.';
                pw.classList.add('is-invalid'); valid = false;
            } else { pw.classList.remove('is-invalid'); }

            if (ph.value.trim() !== '' && !phoneRegex.test(ph.value.trim())) {
                document.getElementById('err_' + prefix + '_phone').textContent = 'Phone must contain 8 to 20 digits.';
                ph.classList.add('is-invalid'); valid = false;
            } else { ph.classList.remove('is-invalid'); }

            if (!valid) e.preventDefault();
        });
    }

    attachValidation('formModalCreate', 'c', true);
    attachValidation('formModalEdit', 'e', false);
})();
</script>
</body>
</html>
