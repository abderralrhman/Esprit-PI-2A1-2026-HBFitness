# HB FITNESS — Blog Management Module (`Blog Model`)

> **Train smart. Reuse more.**  
> **Developed at Esprit School of Engineering – Tunisia** · **Academic Year: 2026–2027**

---

## 1. Project Overview

**HB FITNESS — Blog Model** is a standalone, strict-MVC PHP 8 & MySQL web application implementing the **Blog Management** module of the HB FITNESS platform, designed according to the Esprit School of Engineering (2026–2027) Web Programming workshops and the HB Fitness Validation Deck (Page 11).

It manages two core relational entities (**Article** `1 — N` **Comment**) with a clean Frontoffice for visitors, athletes (`user`), and certified coaches (`coach`), plus a TailAdmin-style Backoffice reserved for administrators (`admin`).

---

## 2. Assigned Scope & Entities

| Entity | Table | Primary Key | Key Columns & Relations |
| :--- | :--- | :--- | :--- |
| **Article** | `articles` | `id` | `title`, `content`, `published_at`, `author_id` (`FK -> users.id`), `recipe_id` (nullable FK for future Nutrition module integration) |
| **Comment** | `comments` | `id` | `content`, `created_at`, `article_id` (`FK -> articles.id ON DELETE CASCADE`), `user_id` (`FK -> users.id ON DELETE CASCADE`) |
| **Users** *(minimal standalone table)* | `users` | `id` | `full_name`, `email`, `password_hash`, `role` (`ENUM('admin','user','coach')`), `created_at` — strictly minimal for local login & FK integrity |

> **Scope Boundary:** No User Management module (no registration, profile editing, user CRUD, or role administration) is included. Those belong to the team's separate User Management module.

---

## 3. Roles & Permissions Matrix

| Capability | Visitor (Guest) | User (`user`) | Coach (`coach`) | Admin (`admin`) |
| :--- | :---: | :---: | :---: | :---: |
| Browse & search articles (Frontoffice) | Yes | Yes | Yes | Yes |
| Read article details & comments | Yes | Yes | Yes | Yes |
| Add comments on articles | No (redirect to login) | Yes | Yes | Yes |
| Edit / delete **own** comments | No | Yes | Yes | Yes |
| View `My Comments` | No | Yes | Yes | Yes |
| Publish new articles | No | No (`403`) | Yes (Frontoffice) | Yes (Backoffice) |
| Edit / delete **own** articles | No | No (`403`) | Yes | Yes |
| Edit / delete **other authors'** articles | No | No (`403`) | No (`403`) | Yes |
| Access Admin Backoffice (`admin_*` routes) | No | No (`403`) | No (`403`) | Yes |
| Moderate / edit / delete **all** comments | No | No (`403`) | No (`403`) | Yes |

---

## 4. Technical Stack

- **Backend:** PHP 8.x (`strict_types=1`), OOP, strict MVC
- **Database:** MySQL / MariaDB (InnoDB, `utf8mb4_unicode_ci`) via **PDO** prepared statements
- **Frontend:** HTML5, CSS3, Bootstrap 5.3, Font Awesome 6.5 Free, Vanilla JavaScript
- **Visual Identity:** HB FITNESS palette (`#030303` Black, `#910329` Crimson, `#E8405E` Coral Red, `#1A1A1D` Charcoal, `#B4B4B4` Silver), **Oswald** display typography + **DM Sans** body typography.

---

## 5. Strict MVC Architecture

```text
Blog Model/
├── Controllers/
│   ├── ArticleController.php   # Article CRUD, SQL JOINs, Coach/Admin ownership checks
│   ├── AuthController.php      # Minimal standalone session login, CSRF, role guards
│   └── CommentController.php   # Comment CRUD, 3-table JOINs, ownership & moderation
├── Models/
│   ├── Article.php             # Pure OOP Entity (private properties, getters/setters, fromArray)
│   └── Comment.php             # Pure OOP Entity (private properties, getters/setters, fromArray)
├── Views/
│   ├── layout.php              # Frontoffice layout (navbar, animated canvas, flash, footer)
│   ├── layout_admin.php        # TailAdmin-style Backoffice layout (Admin only)
│   ├── auth/
│   │   └── login.php           # Minimal Email + Password login form
│   ├── Frontoffice/
│   │   ├── home.php
│   │   ├── listArticles.php
│   │   ├── articleDetails.php
│   │   ├── createComment.php
│   │   ├── updateComment.php
│   │   ├── deleteComment.php
│   │   └── myComments.php
│   ├── Backoffice/
│   │   ├── dashboard.php
│   │   ├── listArticles.php
│   │   ├── createArticle.php   # Renders layout_admin.php for Admin, layout.php for Coach
│   │   ├── updateArticle.php   # Renders layout_admin.php for Admin, layout.php for Coach
│   │   ├── deleteArticle.php   # Renders layout_admin.php for Admin, layout.php for Coach
│   │   └── manageComments.php
│   ├── includes/
│   │   ├── head.php, navbar.php, header.php, sidebar.php, footer.php, scripts.php
│   └── assets/
│       ├── css/ (style.css, frontoffice.css, backoffice.css, blog.css)
│       ├── js/  (main.js, validation.js, blog.js)
│       └── images/ (hb-monogram.svg, hb-fitness-logo.svg)
├── config/
│   └── database.php            # Singleton PDO connection (only DB connection file)
├── blog.sql                    # Database schema (root)
├── index.php                   # Single front-controller router
├── README.md
└── .gitignore
```

---

## 6. Installation & Local Setup (XAMPP)

1. Place this folder at `C:\xampp\htdocs\Blog-Model` (or `C:\xampp\htdocs\Blog Model`).
2. Open **XAMPP Control Panel** and start **Apache** and **MySQL**.
3. Open **phpMyAdmin** at `http://localhost/phpmyadmin`.
4. Import `blog.sql` (this creates `hbfitess_blog_db` and the `users`, `articles`, and `comments` tables).
   - *If you already imported an earlier version of `blog.sql` before the `status` column was added, run:*
     ```sql
     USE hbfitess_blog_db;
     ALTER TABLE users ADD COLUMN IF NOT EXISTS status ENUM('active', 'suspended') NOT NULL DEFAULT 'active' AFTER role;
     ```
   - *Note:* `config/database.php` also auto-bootstraps `blog.sql` if the database does not exist yet.
5. Create the three local test accounts with hashed passwords (see Section 7).
6. Open the application in your browser:
   - **`http://localhost/Blog-Model/`**

---

## 7. Creating the Three Real Local Accounts (Admin, Coach, User)

For security best practices, **no plaintext passwords or pre-seeded demo credentials are stored in the repository**. Generate bcrypt hashes locally using PHP CLI and insert your three real accounts via phpMyAdmin.

### Step 1 — Generate Password Hashes via PHP CLI (PowerShell / CMD)

```powershell
C:\xampp\php\php.exe -r "echo password_hash('YourStrongAdminPass!', PASSWORD_DEFAULT) . PHP_EOL;"
C:\xampp\php\php.exe -r "echo password_hash('YourStrongCoachPass!', PASSWORD_DEFAULT) . PHP_EOL;"
C:\xampp\php\php.exe -r "echo password_hash('YourStrongUserPass!', PASSWORD_DEFAULT) . PHP_EOL;"
```

### Step 2 — Insert the Three Accounts in phpMyAdmin (`hbfitess_blog_db` → SQL tab)

Replace `<ADMIN_HASH>`, `<COACH_HASH>`, and `<USER_HASH>` with the three `$2y$10$...` hashes from Step 1:

```sql
USE hbfitess_blog_db;

INSERT INTO users (full_name, email, password_hash, role, status) VALUES
('Admin HB Fitness', 'admin@hbfitness.tn', '<ADMIN_HASH>', 'admin', 'active'),
('Coach Amine',      'coach@hbfitness.tn', '<COACH_HASH>', 'coach', 'active'),
('Karim Trabelsi',   'user@hbfitness.tn',  '<USER_HASH>',  'user',  'active');
```

### Step 3 — Professor Validation Test Sequence

Open `http://localhost/Blog-Model/index.php?action=login` and test:

1. **Log in as User** (`user@hbfitness.tn`) — role is fetched automatically from MySQL (no role selector).
2. Open an article (or publish one first as Coach/Admin) and **add a comment** using the inline comment form.
3. **Edit** your comment and verify the updated text persists in MySQL.
4. **Delete** your comment (CSRF-protected POST confirmation).
5. **Log out**.
6. **Log in as Coach** (`coach@hbfitness.tn`).
7. **Add a comment** on an article.
8. Click **Publish Article** in the Frontoffice navbar and publish a new fitness article (verify Edit/Delete buttons appear only on your own articles, and Admin Backoffice returns HTTP `403`).
9. **Log out**.
10. **Log in as Admin** (`admin@hbfitness.tn`) — redirected automatically to the **Blog Backoffice**.
11. Access **Dashboard**, **All Articles**, and **Manage Comments**.
12. **Delete another user's comment** from Backoffice moderation.
13. Verify that logging back in as **User** cannot access `index.php?action=admin_dashboard`, `admin_comments`, or `delete_comment&id=<other_id>` (server returns HTTP `403 Access Denied`).
14. *(Optional)* Set `UPDATE users SET status = 'suspended' WHERE email = 'user@hbfitness.tn';` in phpMyAdmin and verify login and protected actions are immediately blocked.

---

## 8. Security Features Implemented

- **PDO Prepared Statements** (`ATTR_EMULATE_PREPARES => false`) preventing SQL injection.
- **Password Hashing** via `password_hash()` / `password_verify()` (bcrypt).
- **CSRF Protection** (`bin2hex(random_bytes(32))` + `hash_equals()`) on every state-changing POST form (login, article CRUD, comment CRUD).
- **XSS Prevention** via `htmlspecialchars(..., ENT_QUOTES, 'UTF-8')` on all rendered output.
- **Session Hardening:** `session.use_strict_mode = 1`, `cookie_httponly = 1`, `cookie_samesite = Lax`, `session_regenerate_id(true)` on login, 30-minute inactivity timeout, and brute-force login lockout (5 failed attempts → 60s lock).
- **Strict Ownership & Role Authorization:** Server-side checks in `ArticleController::canManageArticle()`, `CommentController::canManageComment()`, and `AuthController` guards return HTTP `403` on unauthorized access.
- **HTTP Security Headers:** `X-Frame-Options: DENY`, `X-Content-Type-Options: nosniff`, `Referrer-Policy`, and `Content-Security-Policy`.

---

## 9. Routes Reference

| Action (`index.php?action=...`) | Access | Description |
| :--- | :--- | :--- |
| `home` | Public | Dynamic Frontoffice landing page & latest articles |
| `articles` | Public | Browse & search all articles (`&mine=1` for Coach) |
| `article_details&id=X` | Public | Full article reader + comments thread |
| `login` / `logout` | Public / Auth | Minimal email/password login & secure logout |
| `create_comment` | User, Coach, Admin | Add comment on an article |
| `update_comment&id=X` | Owner or Admin | Edit own comment (or Admin moderation) |
| `delete_comment&id=X` | Owner or Admin | CSRF-protected comment deletion |
| `my_comments` | User, Coach, Admin | List all comments authored by the current user |
| `create_article` | Coach, Admin | Publish new article (Frontoffice for Coach, Backoffice for Admin) |
| `update_article&id=X` | Coach (own) or Admin | Edit article |
| `delete_article&id=X` | Coach (own) or Admin | Delete article (cascades comments; never deletes users) |
| `admin_dashboard` | Admin only | Backoffice KPIs & recent activity |
| `admin_articles` | Admin only | Full article management table |
| `admin_comments` | Admin only | 3-table JOIN comment moderation & filtering |

---

## 10. Future GitHub Integration Guide (`Esprit-PI-2A1-2026-HBFitness`)

This project is structured to merge cleanly into the shared team repository (`Esprit-PI-2A1-2026-HBFitness`) without conflicts:

### Files to Copy Directly into the Shared Repository
- `Models/Article.php`, `Models/Comment.php`
- `Controllers/ArticleController.php`, `Controllers/CommentController.php`
- `Views/Frontoffice/*`, `Views/Backoffice/*`
- `Views/assets/css/blog.css`, `Views/assets/js/blog.js`
- SQL `articles` and `comments` table definitions from `blog.sql`

### Files to Adapt or Exclude During Integration
- **IMPORTANT — Disable Local Quick-Login:** Set `HB_LOCAL_QUICK_LOGIN_ENABLED` to `false` in `config/database.php` (and remove the `quick_login` route/buttons) before pushing to GitHub or deploying outside localhost.
- **Exclude** standalone `users` table definition from `blog.sql` — point `articles.author_id` and `comments.user_id` to the shared team `users` table (ensure the shared `users` table exposes `id`, `full_name`, `role`).
- **Replace / Adapt** `Controllers/AuthController.php` and `Views/auth/login.php` with the team's shared User Management `AuthController`. Because `ArticleController` and `CommentController` depend only on the clean session/role methods (`getCurrentUser`, `requireAuth`, `requireAdmin`, `requireArticlePublisher`, `verifyCsrfToken`), Blog business logic requires zero rewrites.
- **Merge** `index.php` blog routes into the shared front controller and reuse the shared `config/database.php`.

---

## 11. Credits

- **Institution:** Esprit School of Engineering – Tunisia
- **Academic Year:** 2026–2027
- **Module:** Blog Management (`Article` & `Comment`) — HB FITNESS Integrated Web Project
