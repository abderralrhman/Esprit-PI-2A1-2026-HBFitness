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
- [Demo Data for Testing](#demo-data-for-testing)
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
* **Workout Programs** (plans filtered by level and goal, detailed exercise lists, and admin plan management).

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

### 3. Workout Programs
* **Plan library (`?action=workouts`)**: Public workout plans filtered by experience level and fitness goal, with exercise lists and optional video links.
* **Workout administration (`?action=admin_workouts`)**: Administrator-only CRUD for workout plans and their exercises.
* **PHP-managed schema**: The application creates the `workout_plans` and `exercises` tables in the user database connection; no workout SQL import is required.

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
│   ├── WorkoutController.php   # Workout plan and exercise CRUD
│   ├── PageController.php      # Marketplace storefront and admin controller
│   └── ApiController.php       # Marketplace REST API controller
├── Models/
│   ├── User.php                # User entity
│   ├── Role.php                # Role entity
│   ├── UserStatus.php          # Status entity
│   ├── WorkoutPlan.php         # Workout plan entity
│   ├── Exercise.php            # Workout exercise entity
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
4. Import `hbfitess.sql` to create the main `hbfitess_db` user-management database.
   The Marketplace creates its `hb_fitness` database and required tables in PHP the
   first time it is opened. The MySQL account configured for the Marketplace must
   have permission to create databases when `hb_fitness` does not exist.
5. Open your browser:
   * **Homepage & Authentication**: `http://localhost/ali%20-%20Copy/index.php?action=home`
   * **Marketplace Storefront**: `http://localhost/ali%20-%20Copy/index.php?action=store` (or `?page=store`)
   * **Marketplace Admin**: `http://localhost/ali%20-%20Copy/index.php?action=market_admin` (or `?page=admin`)
   * **User Backoffice**: `http://localhost/ali%20-%20Copy/index.php?action=admin_dashboard`

## Demo Data for Testing

After importing the user schema and starting MySQL, populate all modules with local
test records from a terminal:

```powershell
C:\xampp\php\php.exe seed_demo_data.php
```

The CLI-only script is safe to rerun: it reuses its reserved QA accounts and
avoids duplicating demo plans, articles, comments, sessions, bookings, listings,
and orders. On the first run, it creates QA admin, coach, and member accounts
with random passwords and prints those passwords once. Keep them locally; they
are not stored in the repository. The script also runs the marketplace schema
and sample-data setup.

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
