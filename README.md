# HB FITNESS — Blog Management Module (Integration-Ready)

> **Train smart. Reuse more.**  
> **Developed at Esprit School of Engineering – Tunisia** · **Academic Year: 2026–2027**  
> **Integration Target:** `Esprit-PI-2A1-2026-HBFitness`

---

## 1. Module Overview

This repository contains the **integration-ready Blog Management module** for the HB FITNESS platform. All temporary standalone authentication, login pages, quick-login shortcuts, and local test-account provisioning have been removed so the module integrates cleanly with the team's shared **User Management** module without conflicts.

### Assigned Entities

| Entity | Table | Primary Key | Relationships |
| :--- | :--- | :--- | :--- |
| **Article** | `articles` | `id` | `author_id` → `users.id` (`ON DELETE RESTRICT`), `recipe_id` (nullable for future Nutrition module) |
| **Comment** | `comments` | `id` | `article_id` → `articles.id` (`ON DELETE CASCADE`), `user_id` → `users.id` (`ON DELETE CASCADE`) |

---

## 2. Integration-Ready MVC Structure

```text
Blog-Model/
├── Controllers/
│   └── Blog/
│       ├── ArticleController.php   # Article CRUD + SQL JOINs + Coach/Admin ownership checks
│       ├── BlogAuthAdapter.php     # Shared-session authentication adapter (no auth bypasses)
│       └── CommentController.php   # Comment CRUD + 3-table SQL JOINs + ownership & moderation
├── Models/
│   └── Blog/
│       ├── Article.php             # Pure OOP Entity
│       └── Comment.php             # Pure OOP Entity
├── Views/
│   ├── Frontoffice/
│   │   └── blog/
│   │       ├── home.php
│   │       ├── listArticles.php
│   │       ├── articleDetails.php
│   │       ├── createComment.php
│   │       ├── updateComment.php
│   │       ├── deleteComment.php
│   │       └── myComments.php
│   ├── Backoffice/
│   │   └── blog/
│   │       ├── dashboard.php
│   │       ├── listArticles.php
│   │       ├── createArticle.php   # Renders Backoffice layout for Admin, Frontoffice for Coach
│   │       ├── updateArticle.php   # Renders Backoffice layout for Admin, Frontoffice for Coach
│   │       ├── deleteArticle.php   # Renders Backoffice layout for Admin, Frontoffice for Coach
│   │       └── manageComments.php
│   ├── layout.php                  # Local preview layout (reuse shared layout on integration)
│   ├── layout_admin.php            # Local preview layout (reuse shared layout on integration)
│   ├── includes/                   # Local preview partials
│   └── assets/
│       ├── css/ (style.css, frontoffice.css, backoffice.css, blog.css)
│       ├── js/  (main.js, validation.js, blog.js)
│       └── images/ (hb-monogram.svg, hb-fitness-logo.svg)
├── config/
│   └── database.php                # Local PDO connection (reuse shared DB connection on integration)
├── blog.sql                        # Non-destructive Blog-only SQL (articles & comments ONLY)
├── index.php                       # Local Blog router (merge routes into shared index.php)
├── README.md
└── .gitignore
```

---

## 3. Shared Authentication & 3-Role Permissions

Authentication is consumed through `Controllers/Blog/BlogAuthAdapter.php`, which reads the authenticated session (`$_SESSION['user_id']`, `$_SESSION['role']`, `$_SESSION['full_name']`) established by the team's shared `AuthController`.

- **No authentication bypasses:** Protected Blog actions return HTTP `401 Authentication Required` when no valid shared session exists, and HTTP `403 Access Denied` when the role or ownership check fails.
- **Schema compatibility:** `ArticleController` and `CommentController` automatically support both the shared normalized User Management schema (`users.role_id -> roles.id`) and a flat `users.role` column.

| Action | Visitor | User (`user`) | Coach (`coach`) | Admin (`admin`) |
| :--- | :---: | :---: | :---: | :---: |
| Read & search articles | Yes | Yes | Yes | Yes |
| Add comments | No (`401`) | Yes | Yes | Yes |
| Edit / delete **own** comments | No (`401`) | Yes | Yes | Yes |
| Delete / moderate **others'** comments | No (`401`) | No (`403`) | No (`403`) | Yes |
| Publish new articles | No (`401`) | No (`403`) | Yes | Yes |
| Edit / delete **own** articles | No (`401`) | No (`403`) | Yes | Yes |
| Manage **all** articles & Backoffice | No (`401`) | No (`403`) | No (`403`) | Yes |

---

## 4. GitHub Integration Guide (`Esprit-PI-2A1-2026-HBFitness`)

### Step 1 — Files to Copy Directly into the Shared Repository

Copy ONLY these Blog-specific folders/files into `Esprit-PI-2A1-2026-HBFitness`:

- `Controllers/Blog/ArticleController.php`
- `Controllers/Blog/CommentController.php`
- `Controllers/Blog/BlogAuthAdapter.php`
- `Models/Blog/Article.php`
- `Models/Blog/Comment.php`
- `Views/Frontoffice/blog/*`
- `Views/Backoffice/blog/*`
- `Views/assets/css/blog.css`
- `Views/assets/js/blog.js`

### Step 2 — Files NOT to Overwrite (Exclude / Merge Carefully)

Do **NOT** copy over the shared repository's existing files:

- **Do NOT overwrite** `Controllers/AuthController.php`, `Controllers/UserController.php`, or `Models/User.php`
- **Do NOT overwrite** `config/database.php` (reuse the shared database connection)
- **Do NOT overwrite** root `index.php` (merge only the Blog `switch ($action)` cases from this project's `index.php`)
- **Do NOT overwrite** shared `Views/layout.php`, `Views/layout_admin.php`, or `Views/includes/*` (add Blog navigation links to the shared navbar and admin sidebar)

### Step 3 — Non-Destructive Database Migration

1. Select the shared HB FITNESS database in phpMyAdmin (verify `users.id` is `INT UNSIGNED PRIMARY KEY`).
2. Import `blog.sql`. It uses `CREATE TABLE IF NOT EXISTS` for `articles` and `comments` only — it never drops tables or touches `users`.

### Step 4 — Verify Shared Session Keys

`BlogAuthAdapter` reads:
- `$_SESSION['user_id']` (or `$_SESSION['user']['id']`)
- `$_SESSION['role']` (or `$_SESSION['user_role']` / `$_SESSION['user']['role']`) — expected values: `'admin'`, `'user'`, `'coach'`
- `$_SESSION['full_name']` (or `$_SESSION['user_name']` / `$_SESSION['user']['full_name']`)

If your team's shared `AuthController` uses different session keys, update the 3 getter methods in `Controllers/Blog/BlogAuthAdapter.php` — no changes are needed in `ArticleController` or `CommentController`.

---

## 5. Security & Academic Standards

- **PDO Prepared Statements** (`ATTR_EMULATE_PREPARES => false`)
- **CSRF Protection** (`bin2hex(random_bytes(32))` + `hash_equals()`) on all POST mutations
- **XSS Escaping** (`htmlspecialchars(..., ENT_QUOTES, 'UTF-8')`) on all rendered output
- **Server-side Ownership & Role Enforcement** in `ArticleController::canManageArticle()`, `CommentController::canManageComment()`, and `BlogAuthAdapter` guards
- **HB Fitness Brand Identity:** `#030303` Black, `#910329` Crimson, `#E8405E` Coral Red, `#1A1A1D` Charcoal, `#B4B4B4` Silver, **Oswald** + **DM Sans**

---

**Developed at Esprit School of Engineering – Tunisia · Academic Year 2026–2027**
