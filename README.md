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
- Membership Module – Unassigned (spare)
- Challenges Module – Unassigned (spare)

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

2. Start Apache and MySQL in XAMPP. The application connects using the default XAMPP credentials (`root` with an empty password) and creates the `fitness_db` database, its tables, and demo records on first use. No SQL file needs to be imported.
3. If your MySQL credentials differ, update the connection settings in `config.php`.
4. Open the project through XAMPP at `http://localhost/Esprit-PI-2A1-2026-HBFitness/`, or start the PHP development server:

```bash
php -S localhost:8000
```

5. If using the PHP development server, open `http://localhost:8000` in your browser.
