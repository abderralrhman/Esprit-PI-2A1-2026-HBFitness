# HB FITNESS — User Management System (Strict ESPRIT MVC)

> **Train smart. Reuse more.**
> Complete PHP 8 / MySQL / PDO / OOP User Management application built following the official **Esprit School of Engineering** Web Technologies 2A workshops (2026–2027).

## Table of Contents

- [Overview](#overview)
- [Features](#features)
- [Tech Stack](#tech-stack)
- [MVC Architecture](#mvc-architecture)
- [Database Configuration](#database-configuration)
- [SQL Import Instructions](#sql-import-instructions)
- [XAMPP Installation & Startup](#xampp-installation--startup)
- [Authentication & User Roles](#authentication--user-roles)
- [Security Measures](#security-measures)
- [Demo Setup (Safe Local Initialization)](#demo-setup-safe-local-initialization)
- [Contributors](#contributors)
- [Academic Context & Acknowledgments](#academic-context--acknowledgments)

---

## Overview

**HB FITNESS** is a sustainable fitness and healthy-nutrition platform. This repository implements the **Shared User Management Core** (`User`, `Role`, `UserStatus`) with a strict separation of MVC layers, a Bootstrap 5 **Frontoffice** for Athletes and Coaches, and a TailAdmin-inspired **Backoffice** for Administrators.

---

## Features

- **Complete User CRUD (Backoffice)**
  - **Create**: Add users with full name, email, hashed password, phone, role (`admin`, `user`, `coach`), and status (`active`, `suspended`).
  - **Read**: Pageless searchable directory with live filters by Role and Account Status using SQL `INNER JOIN` queries, plus dashboard statistics.
  - **Update**: Modify user details, change roles, suspend/reactivate accounts, or reset passwords.
  - **Delete**: Dedicated CSRF-protected POST confirmation page with self-deletion protection.
- **Role & Account Status Management**
  - Visual role/status summary cards powered by SQL `LEFT JOIN` counts and inline role/status assignment.
- **Frontoffice Member Experience**
  - Public homepage, Email + Password login, public registration (strictly creating `user` accounts), personal dashboard (User & Coach views), profile viewer, and self-service profile editor.
- **Template Integration**
  - Reusable partials (`head.php`, `navbar.php`, `sidebar.php`, `header.php`, `footer.php`, `scripts.php`) assembled through `Views/layout.php` and `Views/layout_admin.php`.

---

## Tech Stack

- **Backend**: PHP 8 (strict types, OOP, PDO)
- **Database**: MySQL / MariaDB (InnoDB, UTF8MB4, Foreign Keys, SQL JOINs)
- **Frontend**: HTML5, CSS3 (Flexbox & Grid), Bootstrap 5.3, Font Awesome 6, Vanilla JavaScript
- **Graphic Identity**: Black `#030303`, Crimson `#910329`, Silver `#B4B4B4`, Charcoal `#1A1A1D`, Coral Red `#E8405E`, **Oswald** headings & **DM Sans** body text

---

## MVC Architecture

```text
HBfitess/
├── Controllers/
│   ├── AuthController.php      # Auth, sessions, CSRF, role guards, local setup
│   ├── UserController.php      # User CRUD, SQL JOIN queries, validation
│   └── RoleController.php      # Roles & account statuses with JOIN counts
├── Models/
│   ├── User.php                # Pure OOP entity (no SQL / no business logic)
│   ├── Role.php                # Pure OOP entity
│   └── UserStatus.php          # Pure OOP entity
├── Views/
│   ├── layout.php              # Frontoffice layout
│   ├── layout_admin.php        # Backoffice layout
│   ├── Frontoffice/            # home, login, register, dashboard, profile, editProfile
│   ├── Backoffice/             # dashboard, listUsers, createUser, updateUser, deleteUser, manageRoles
│   ├── includes/               # head, navbar, sidebar, header, footer, scripts
│   └── assets/                 # css/, js/, images/
├── config/
│   └── database.php            # Centralized Singleton PDO connection
├── hbfitess.sql                # Importable phpMyAdmin SQL schema
├── index.php                   # Main front controller / router
├── README.md
└── .gitignore
```

---

## Database Configuration

Database connection settings are centralized in `config/database.php` using the Singleton `Database::getConnexion()` pattern with:

- `PDO::ERRMODE_EXCEPTION`
- `PDO::FETCH_ASSOC`
- `PDO::ATTR_EMULATE_PREPARES => false`
- Database name: `hbfitess_db`

Default local XAMPP credentials (`localhost`, `root`, empty password) can also be overridden via environment variables (`HBFITESS_DB_HOST`, `HBFITESS_DB_NAME`, `HBFITESS_DB_USER`, `HBFITESS_DB_PASS`).

---

## SQL Import Instructions

1. Start **Apache** and **MySQL** in the XAMPP Control Panel.
2. Open **phpMyAdmin** at `http://localhost/phpmyadmin`.
3. Click the **Import** tab.
4. Select `hbfitess.sql` from the root of `C:\xampp\htdocs\HBfitess\`.
5. Click **Import** (or **Go**). This creates `hbfitess_db` with tables `roles`, `user_statuses`, and `users`, along with the required reference roles (`admin`, `user`, `coach`) and statuses (`active`, `suspended`).

---

## XAMPP Installation & Startup

1. Place this project folder at `C:\xampp\htdocs\HBfitess`.
2. Open **XAMPP Control Panel** and start **Apache** and **MySQL**.
3. Import `hbfitess.sql` in phpMyAdmin.
4. Visit `http://localhost/HBfitess/` in your browser.

---

## Authentication & User Roles

- **Login (`index.php?action=login`)**: Requests **only Email and Password** (+ hidden CSRF token), verified via `password_verify()`.
- **Registration (`index.php?action=register`)**: Public visitors can only register as normal `user` accounts with `active` status.
- **Role-Based Redirection**:
  - `admin` → Backoffice Dashboard (`index.php?action=admin_dashboard`)
  - `user` → Frontoffice Dashboard (`index.php?action=dashboard`)
  - `coach` → Authorized Coach Frontoffice Dashboard (`index.php?action=dashboard`)

---

## Security Measures

- **SQL Injection Protection**: 100% PDO prepared statements with `PDO::ATTR_EMULATE_PREPARES => false`.
- **Password Security**: `password_hash(..., PASSWORD_DEFAULT)` and `password_verify()`. Password hashes are never displayed in Views.
- **XSS Protection**: All dynamic output escaped with `htmlspecialchars(..., ENT_QUOTES, 'UTF-8')`.
- **CSRF Protection**: Per-session cryptographic tokens (`random_bytes(32)`) verified with `hash_equals()` on all state-changing POST forms.
- **Session Hardening**: `session.use_strict_mode = 1`, `HttpOnly`, `SameSite=Lax`, `Secure` flag over HTTPS, `session_regenerate_id(true)` after login, 30-minute inactivity timeout, and full cookie/session destruction on logout.
- **Authorization**: Server-side `requireAuth()` and `requireRole('admin')` (HTTP 403) on every protected route, plus automatic blocking of `suspended` accounts.
- **Brute-Force Mitigation**: Temporary 60-second lockout after 5 consecutive failed login attempts.
- **HTTP Security Headers**: `X-Frame-Options: DENY`, `X-Content-Type-Options: nosniff`, `Referrer-Policy`, and `Content-Security-Policy`.

---

## Demo Setup (Safe Local Initialization)

No plaintext demo passwords are stored in this repository.

1. After importing `hbfitess.sql`, open `http://localhost/HBfitess/index.php?action=setup` (accessible **only from localhost** when no administrator account exists yet).
2. Enter your chosen administrator email and a strong password (min 8 characters with letters and digits).
3. Keep **"Also create sample Coach and User accounts"** checked to generate:
   - Admin (`admin@hbfitess.local`)
   - Coach (`coach@hbfitess.local`)
   - User (`user@hbfitess.local`)
4. All passwords are immediately hashed with `password_hash()` in MySQL.

---

## Contributors

- **Abdulrahman** (Team Leader)
- **Ali**
- **Sofiane**
- **Mouadh**
- **Hanine**
- **Meriem**

---

## Academic Context & Acknowledgments

- **Institution**: Esprit School of Engineering – Tunisia
- **Module**: Web Technologies Project 2A (PIDEV)
- **Academic Year**: 2026–2027
- **Acknowledgments**: Built following the official ESPRIT UP WEB workshops on Template Integration, PHP OOP, PDO CRUD & JOINs, and Sessions & PHP Security.
