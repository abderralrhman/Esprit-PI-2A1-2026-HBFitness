# HB Fitness – Sustainable Fitness and Nutrition Platform

## Overview

HB Fitness is a comprehensive web application for managing fitness activities, nutrition, workouts, and community events. This project was developed as part of the Web Technologies Project (PI) – 2nd Year Engineering Program, class 2A1, at **Esprit School of Engineering** (Academic Year 2026–2027).

## Features

- **User Management:** user accounts, profiles, and role-based access control.
- **Nutrition:** healthy recipes and nutritional macros organized by category.
- **Workout:** structured training plans, exercises, and muscle group tracking.
- **Events:** community fitness events, capacities, registrations, and official sponsors.
- **Marketplace:** swap or sell used fitness equipment through listings and orders.
- **Sessions:** booking of specialized coaching and training sessions.
- **Membership:** membership plans and user subscriptions.
- **Challenges:** fitness challenges where users can participate and track their progress.

## Tech Stack

### Frontend

- HTML5, CSS3, JavaScript
- Front Office template:
- Back Office template: TailAdmin

### Backend

- PHP (MVC architecture, object-oriented)
- PDO with MySQL

## Architecture

This project follows the **MVC (Model-View-Controller)** architecture to separate the business logic from the user interface.

- **Controllers:** receive user requests and decide which view to display.
- **Models:** represent the data and handle database interactions.
- **Views:** contain the dynamic PHP user interface integrating the data sent by the controllers.

```
Esprit-PI-2A1-2026-HBFitness/
├── Controllers/
├── Models/
├── Views/
│   ├── assets/        # css, images, js
│   ├── includes/      # Front Office partials
│   ├── partials/      # Back Office partials
│   └── pages/         # Back Office content pages
├── config/
│   
└── README.md
```

## Contributors

- Labidi Abderrahman – User Module
- Mariem Ben Mrad – Nutrition Module
- Sofien Boudhaouia – Workout Module
- Mouaadh Ben Youssef – Events Module
- Ghabri Ali – Marketplace Module
- Hanin Aouina – Sessions Module

## Academic Context

Developed at **Esprit School of Engineering – Tunisia**

- **Module:** Web Technologies Project (PI)
- **Class:** 2A1
- **Academic Year:** 2026–2027

## Getting Started

1. Clone the repository:

```bash
git clone https://github.com/abderralrhman/Esprit-PI-2A1-2026-HBFitness.git
cd Esprit-PI-2A1-2026-HBFitness
```

2. Create the database: open phpMyAdmin and import `database/hb_fitness.sql`.
3. Set your database credentials in `config/database.php` (host, database name, user, password).
4. Start the server with XAMPP/WAMP, or from the terminal:

```bash
php -S localhost:8000
```

5. Open `http://localhost:8000` in your browser.
