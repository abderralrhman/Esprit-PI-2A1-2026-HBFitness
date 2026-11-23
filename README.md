# HB FITNESS — Project Integration (User Management & Marketplace)

> **Train smart. Reuse more.**
> Web Technologies 2A Integrated Project (ESPRIT School of Engineering, 2026–2027).
> Integrated Modules: **User Management & Authentication** + **Sports Equipment Marketplace**.

---

## Table of Contents

- [Overview](#overview)
- [Integrated Modules](#integrated-modules)
  - [1. User Management & Security Core](#1-user-management--security-core)
  - [2. Sports Marketplace & Orders](#2-sports-marketplace--orders)
- [Tech Stack & Architecture](#tech-stack--architecture)
- [Setup & Installation with XAMPP](#setup--installation-with-xampp)
- [Database Configuration](#database-configuration)
- [Routing & Navigation](#routing--navigation)
- [Contributors](#contributors)
- [Academic Context](#academic-context)

---

## Overview

**HB FITNESS** is a sustainable fitness platform built following strict ESPRIT MVC guidelines.
This repository brings together:
* The **User Management System** (Authentication, User Roles, Backoffice Administration, Secure Sessions).
* The **Atelier Market** (Sports equipment listings, category filters, interactive order tracking, public storefront).

---

## Integrated Modules

### 1. User Management & Security Core
* **Backoffice User Management**: Complete CRUD for accounts with roles (`admin`, `coach`, `user`) and account statuses (`active`, `suspended`).
* **Frontoffice Experience**: Member registration, email/password login, athlete dashboard, and profile management.
* **Security Workshop Standard**:
  * 100% PDO prepared statements.
  * `password_hash()` and `password_verify()`.
  * Cryptographic CSRF tokens on all POST requests.
  * Hardened session cookies (`HttpOnly`, `SameSite=Lax`, strict mode).
  * HTTP security headers (`X-Frame-Options: DENY`, `X-Content-Type-Options: nosniff`, CSP).

### 2. Sports Marketplace & Orders
* **Storefront (`?action=store` / `?page=store`)**: Public marketplace to browse fitness gear, filter by categories, search products, and submit reservations.
* **Market Admin (`?action=market_admin` / `?page=admin`)**: Full dashboard managing listings, tracking order states (`To process` → `Shipped` → `Completed`), category management, and CSV export.
* **REST API (`api.php`)**: JSON backend powering asynchronous CRUD operations and interactive widgets.

---

## Tech Stack & Architecture

* **Backend**: PHP 8+ (Strict typing, OOP, PDO, Singleton Database connection)
* **Frontend**: Bootstrap 5.3, Font Awesome 6, Vanilla JS, CSS3
* **Database**: MySQL / MariaDB (InnoDB, UTF8MB4)
* **Design Identity**: Black `#030303`, Crimson `#910329`, Charcoal `#1A1A1D`, Coral Red `#E8405E`

### MVC Project Directory Structure

```text
HBfitess/
├── config/
│   └── database.php            # Singleton Database connection (ESPRIT MVC)
├── Controllers/
│   ├── AuthController.php      # Authentication, CSRF, sessions, role guards
│   ├── UserController.php      # User CRUD & SQL JOIN operations
│   ├── RoleController.php      # Roles & status management
│   ├── PageController.php      # Marketplace storefront and admin controller
│   └── ApiController.php       # Marketplace REST API controller
├── Models/
│   ├── User.php                # User entity
│   ├── Role.php                # Role entity
│   ├── UserStatus.php          # Status entity
│   └── MarketplaceRepository.php # Marketplace PDO data access
├── Views/
│   ├── Frontoffice/            # User frontoffice templates (home, login, register, profile)
│   ├── Backoffice/             # User backoffice templates (dashboard, users, roles)
│   ├── front/                  # Marketplace storefront view (store.php)
│   ├── admin/                  # Marketplace admin dashboard (dashboard.php)
│   ├── includes/               # Reusable partials (navbar, sidebar, header, footer)
│   ├── layout.php              # Frontoffice master template
│   └── layout_admin.php        # Backoffice master template
├── api.php                     # Marketplace API entry point
├── db.php                      # Legacy database helper bridge
├── index.php                   # Unified Front Controller & Router
├── hbfitess.sql                # User management schema & seed data
└── marketplace_setup.sql       # Marketplace schema & seed data
```

---

## Setup & Installation with XAMPP

1. Place the project inside your XAMPP web root:
   ```text
   C:\xampp\htdocs\ali - Copy\
   ```
2. Start **Apache** and **MySQL** in the XAMPP Control Panel.
3. Open phpMyAdmin at `http://localhost/phpmyadmin/`.
4. Import the database schemas:
   * Import `hbfitess.sql` (Creates `hbfitess_db` with `users`, `roles`, `user_statuses`).
   * Import `marketplace_setup.sql` (Creates `hb_fitness` with listings, categories, and orders).
5. Open your browser:
   * **Homepage & Authentication**: `http://localhost/ali%20-%20Copy/index.php?action=home`
   * **Marketplace Storefront**: `http://localhost/ali%20-%20Copy/index.php?action=store` (or `?page=store`)
   * **Marketplace Admin**: `http://localhost/ali%20-%20Copy/index.php?action=market_admin` (or `?page=admin`)
   * **User Backoffice**: `http://localhost/ali%20-%20Copy/index.php?action=admin_dashboard`

---

## Module 4 — Events & Sponsors

The public Events page is `index.php?action=events`. Sign in as an active User Management account to register or cancel before an event starts. An administrator can open `index.php?action=admin_events` and `index.php?action=admin_sponsors` from the Backoffice sidebar.

### XAMPP development setup

1. Use PHP 8.1 or later with `pdo_mysql` and `mbstring`. Keep this snapshot in a separate XAMPP development copy.
2. Configure the Apache environment for that copy with `HBFITESS_DB_NAME=hbfitess_muadh_dev` and the matching `HBFITESS_DB_HOST`, `HBFITESS_DB_USER`, and `HBFITESS_DB_PASS`. Set credentials in local Apache configuration outside the project archive. Use a MySQL account with privileges only on `hbfitess_muadh_dev`. The existing connection fallback can attempt to run the team setup script if a connection fails. The scoped account prevents it from writing to the team schema.
3. Connect to `hbfitess_muadh_dev` with the same account in phpMyAdmin or the MySQL client and verify the schema. The development copy needs the plural `users`, `roles`, and `user_statuses` tables, as well as `sponsors`, `events`, and `event_registrations`. The team setup scripts explicitly select their own schemas. Use the existing development database for Module 4.
4. Confirm the definitions with these read-only queries. Verify that the Module 4 tables and `users` use InnoDB before testing concurrent operations, and confirm `uk_event_user` covers `(event_id, user_id)`. Compare existing columns and timestamp defaults with `MODULE4_DATABASE_VERIFICATION.txt`.
   ```sql
   SELECT DATABASE();
   SHOW CREATE TABLE hbfitess_muadh_dev.users;
   SHOW CREATE TABLE hbfitess_muadh_dev.roles;
   SHOW CREATE TABLE hbfitess_muadh_dev.user_statuses;
   SHOW CREATE TABLE hbfitess_muadh_dev.sponsors;
   SHOW CREATE TABLE hbfitess_muadh_dev.events;
   SHOW CREATE TABLE hbfitess_muadh_dev.event_registrations;
   ```
5. The included `module4_setup.sql` is a reference and optional fresh setup for the three Module 4 tables. Review it and obtain approval before executing schema changes. The existing development tables can be used directly once verified.

Event start times use Africa/Tunis. A capacity of zero closes registration. Sponsor logos are optional and use an existing asset path such as `Views/assets/images/hb-fitness-logo.svg`. Sponsors linked to events and events with registrations are protected from deletion. Registrations left behind by User Management account deletion still occupy a seat and appear as **Deleted account** in the admin participant list.

### Local verification

Run PHP lint from the project root in XAMPP PowerShell. Each file should report **No syntax errors detected**.

```powershell
Get-ChildItem -Recurse -Filter *.php | ForEach-Object { & 'C:\xampp\php\php.exe' -l $_.FullName }
```

| Area | Checks against `hbfitess_muadh_dev` |
|---|---|
| Sponsor CRUD | Create a sponsor, edit its details, and link an event. Confirm deletion is blocked while linked. Unlink it and confirm deletion succeeds. Try an invalid website and logo path. |
| Event CRUD | Create future and past events, edit capacity and sponsor, and verify both public and admin displays. Check that an event with a registration cannot be deleted and capacity cannot be reduced below attendance. |
| Registration | Use two separate active user sessions and a future event with one seat. Submit both registration forms at nearly the same time. Exactly one should succeed. Verify duplicate refusal, cancellation by the registered account, and cutoff when the event starts. |
| Access and validation | Verify guest browsing, sign-in requirements for registration and cancellation, and denial for suspended users and non-admins on restricted routes. Submit a form missing its CSRF token and verify a 403. Try a malformed date or negative capacity with JavaScript disabled. |
| Team-module regression | Sign in, open the profile and User Management pages as appropriate, and check existing navigation. Review the Marketplace links without submitting changes to any team database. |

---

## Contributors

* **Abdulrahman** (Team Leader)
* **Ali** (Marketplace Module)
* **Sofiane**
* **Mouadh**
* **Hanine**
* **Meriem**

---

## Academic Context

* **Institution**: Esprit School of Engineering – Tunisia
* **Course**: Web Technologies 2A (Projet Intégré 2026–2027)
