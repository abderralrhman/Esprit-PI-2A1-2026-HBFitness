# Atelier Market

A sports marketplace built with PHP/PDO, MySQL, HTML, CSS, and JavaScript using an MVC structure.

## Setup with XAMPP

1. Start **Apache** and **MySQL** in the XAMPP Control Panel.
2. Open phpMyAdmin at `http://localhost/phpmyadmin`.
3. Import `marketplace_setup.sql`. It creates or updates the `hb_fitness` database and adds demo data.
4. Open the Back Office at `http://localhost/ali/index.php?page=admin` or the Front Office at `http://localhost/ali/index.php?page=store`.

The default connection uses `127.0.0.1:3306`, database `hb_fitness`, MySQL user `root`, and an empty password, as in a standard XAMPP installation. For other configurations, set the `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, and `DB_PASSWORD` environment variables in Apache.

## Features

- Dashboard powered by listings, orders, and categories stored in MySQL.
- Public storefront with search, category filters, item details, and reservations that create orders.
- Create, update the status of, and delete listings.
- Track orders through **To process → Shipped → Completed**.
- Create categories and export orders to CSV.
- Search and filter listings and orders.

## MVC structure

- `index.php` and `api.php`: application entry points.
- `app/Controllers/`: page routing and API request handling.
- `app/Models/MarketplaceRepository.php`: PDO-based MySQL access and prepared queries.
- `app/Views/admin/` and `app/Views/front/`: Back Office and Front Office templates.
- `style.css`, `front-office.css`, and `app.js`: styling and browser interactions.
- `marketplace_setup.sql`: schema and seed data compatible with the HB Fitness model.

The browser communicates with the PHP API; storefront pages are rendered on the server.

The included accounts and records are development examples only. This version does not include user authentication or access control; do not expose it to the internet without adding authentication and authorization.
