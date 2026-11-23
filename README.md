# 🏋️ HB FITNESS

### Train Smart. Eat Well. Reuse More.

**Academic Year:** 2026–2027  
**Institution:** ESPRIT School of Engineering – Tunisia  
**Project Type:** Integrated Project (PIDEV)  
**Technologies:** PHP, MySQL, HTML5, CSS3, JavaScript, Bootstrap 5  
**Architecture:** MVC (Model–View–Controller)  
**Status:** Under Development — Integration & Testing

---

## 📌 1. Project Overview

**HB Fitness** is a web platform designed to promote a healthy, active, and sustainable lifestyle.

The application aims to bring several fitness-related services together in one place, including workout programs, healthy nutrition, fitness articles, coaching sessions, events, and a marketplace for second-hand sports equipment.

Our main objectives are to:

- Encourage healthy lifestyles and physical activity.
- Provide accessible fitness information.
- Support personalized training and healthy nutrition.
- Build an interactive fitness community.
- Promote sustainability through the reuse of sports equipment.

**Our vision:** Make fitness smarter, more accessible, and more sustainable.

---

## 🎯 2. Project Objectives

The platform is designed to provide:

- Secure account management and authentication.
- Different access permissions for Admin, User, and Coach.
- Fitness articles and community discussions.
- Workout plans and exercises.
- Healthy recipes and nutritional information.
- Fitness events and coaching sessions.
- Buying and selling of second-hand sports equipment.
- Responsive Front Office and Back Office interfaces.

---

## 🧩 3. Project Modules

HB Fitness is organized into six main functional modules, supported by a shared User Management system.

### Module 1 — Blog Management

**Responsible: Abdulrahman — Team Leader**

The Blog Management module allows users to explore fitness articles, share opinions, and interact with the fitness community.

#### Main Entities

**Article**
- `id` — Primary Key
- `title`
- `content`
- `published_at`
- `author_id` — Foreign Key
- `recipe_id` — Optional future relationship

**Comment**
- `id` — Primary Key
- `content`
- `created_at`
- `article_id` — Foreign Key
- `user_id` — Foreign Key

**Relationship:**

One Article can contain multiple Comments.

`Article (1) → (N) Comment`

#### Front Office Features

- Browse fitness articles.
- Read complete articles.
- Display article authors.
- View comments.
- Add comments.
- Edit personal comments.
- Delete personal comments.
- View comment history.

#### Back Office Features

- Create articles.
- View and manage articles.
- Update articles.
- Delete articles.
- Moderate comments.
- View Blog statistics.

#### Technical Features

- PHP Object-Oriented Programming.
- MVC architecture.
- Complete Article CRUD.
- Complete Comment CRUD.
- PDO prepared statements.
- SQL JOIN queries.
- Input validation.
- CSRF protection.
- Role-based access control.
- Comment ownership verification.

**Current Status:** Core functionality implemented. Shared authentication and integration testing remain in progress.

---

### Module 2 — Nutrition & Healthy Recipes

**Responsible: Meriem**

This module focuses on healthy nutrition and meal preparation.

**Main Entities:**
- RecipeCategory
- Recipe
- Step

**Planned Features:**
- Browse healthy recipes.
- View nutritional information.
- Display calories and macronutrients.
- Filter recipes by category.
- Manage recipes and preparation steps.

**Status:** Team module — integration pending verification.

---

### Module 3 — Workout Programs

**Responsible: Sofiane**

This module helps users discover workout programs based on their fitness goals.

**Main Entities:**
- WorkoutPlan
- Exercise

**Planned Features:**
- Browse workout programs.
- View exercises.
- Filter plans by difficulty and goal.
- Access exercise instructions.
- Manage training programs.

**Status:** Team module — integration pending verification.

---

### Module 4 — Events & Sponsors

**Responsible: Mouadh**

This module manages fitness-related activities and sponsorships.

**Main Entities:**
- Sponsor
- Event
- EventRegistration

**Planned Features:**
- Browse fitness events.
- Register for events.
- View event information.
- Manage event registrations.
- Manage sponsors.

**Status:** Team module — integration pending verification.

---

### Module 5 — Sports Equipment Marketplace

**Responsible: Ali**

The Marketplace promotes sustainability by allowing users to buy and sell second-hand sports equipment.

**Main Entities:**
- EquipmentCategory
- Listing
- MarketOrder

**Features Present in the Current Source:**
- Equipment listings.
- Equipment categories.
- Product search and filtering.
- Marketplace orders.
- Marketplace administration interfaces.
- API endpoints.

**Current Status:** Implementation present. Shared database integration and security verification remain necessary.

---

### Module 6 — Coaching Sessions

**Responsible: Hanine**

This module supports fitness coaching activities.

**Main Entities:**
- TrainingSession
- Booking

**Planned Features:**
- Browse coaching sessions.
- Book training sessions.
- View personal bookings.
- Manage coaching sessions.
- Manage session participants.

**Status:** Team module — integration pending verification.

---

## 👥 4. User Management

User Management is a shared component used across the HB Fitness platform.

### Main Entities

- User
- Role
- UserStatus

### Features

- User registration.
- Secure login and logout.
- User CRUD operations.
- User profile management.
- Role management.
- Account status management.
- PHP session handling.
- Access control and authorization.

### User Roles

| Role | Permissions |
|---|---|
| Admin | Manage users, content, and authorized Back Office operations |
| User | Access Front Office features and manage personal content |
| Coach | Access permitted coaching functions and publish authorized fitness content |

### Authentication

The application uses:

- Email and password authentication.
- PHP sessions.
- Password hashing and verification.
- Role-based access restrictions.

**Current Status:** Implementation present, with integrated authorization testing still required.

---

## 💻 5. Technologies Used

### Frontend

- HTML5
- CSS3
- JavaScript
- Bootstrap 5
- Flexbox
- CSS Grid
- Responsive Web Design

### Backend

- PHP 8+
- PHP Object-Oriented Programming
- PHP Data Objects (PDO)
- MVC Architecture

### Database

- MySQL / MariaDB
- SQL CRUD operations
- SQL JOIN queries
- Primary Keys
- Foreign Keys
- Relational Database Design

### Development Tools

- XAMPP
- phpMyAdmin
- Visual Studio Code / Antigravity
- Git
- GitHub
- GitHub Projects

---

## 🏗️ 6. MVC Architecture

The project follows the **Model–View–Controller (MVC)** architectural pattern.

### Model

Represents the application entities and their attributes.

Examples:
- User
- Article
- Comment
- Role
- UserStatus

### View

Responsible for displaying the user interface.

Includes:
- HTML pages.
- Forms.
- Tables.
- Front Office templates.
- Back Office templates.
- Reusable PHP layouts.

### Controller

Responsible for:
- Processing requests.
- Business logic.
- CRUD operations.
- SQL queries using PDO.
- Input validation.
- Authentication and authorization.
- Connecting Models and Views.

### Main Project Structure

```text
HB-Fitness/
│
├── Controllers/
│   ├── Blog/
│   │   ├── ArticleController.php
│   │   ├── CommentController.php
│   │   └── BlogAuthAdapter.php
│   │
│   ├── AuthController.php
│   ├── UserController.php
│   ├── RoleController.php
│   ├── PageController.php
│   └── ApiController.php
│
├── Models/
│   ├── Blog/
│   │   ├── Article.php
│   │   └── Comment.php
│   │
│   ├── User.php
│   ├── Role.php
│   ├── UserStatus.php
│   └── MarketplaceRepository.php
│
├── Views/
│   ├── Frontoffice/
│   │   └── blog/
│   │
│   ├── Backoffice/
│   │   └── blog/
│   │
│   ├── front/
│   ├── admin/
│   ├── includes/
│   ├── assets/
│   ├── layout.php
│   └── layout_admin.php
│
├── config/
│   └── database.php
│
├── index.php
├── api.php
├── db.php
├── blog.sql
├── hbfitess.sql
├── marketplace_setup.sql
└── README.md
```

This structure represents the reviewed project snapshot and may evolve during integration.

---

## 🗄️ 7. Database Design

The platform uses relational databases with MySQL.

### Blog Relationships

**Article and Comment**

`articles.id → comments.article_id`

**User and Article**

`users.id → articles.author_id`

**User and Comment**

`users.id → comments.user_id`

### SQL JOIN

SQL JOIN queries are used to:

- Retrieve articles with their authors.
- Display comments with their authors.
- Retrieve comments related to a specific article.
- Display article comment counts.
- Connect related entities across tables.

### Current SQL Files

| File | Description |
|---|---|
| `hbfitess.sql` | Shared User Management schema |
| `blog.sql` | Blog Article and Comment schema |
| `marketplace_setup.sql` | Marketplace schema |

**Important:** Database integration is still in progress. The final project must use a consistent shared user identity across modules.

---

## ⚙️ 8. Installation and Setup

### Prerequisites

Before running the application, install:

- XAMPP
- PHP 8+
- MySQL / MariaDB
- A modern web browser

### Step 1 — Download the Project

Clone the GitHub repository:

```bash
git clone https://github.com/abderralrhman/Esprit-PI-2A1-2026-HBFitness.git
```

### Step 2 — Place the Project in XAMPP

Move or clone the project into the XAMPP `htdocs` folder.

Example:

```text
C:\xampp\htdocs\fitness
```

### Step 3 — Start XAMPP

Open XAMPP Control Panel and start:

- Apache
- MySQL

### Step 4 — Configure the Database

Open phpMyAdmin:

`http://localhost/phpmyadmin/`

For a fresh local installation:

1. Create or select the database `hbfitess_db`.
2. Import `hbfitess.sql`.
3. Import `blog.sql` into the compatible database.
4. Check database configuration in `config/database.php`.

Do not import SQL over an existing database without a backup and schema review.

The Marketplace currently has a separate setup file and may require additional configuration until database integration is completed.

### Step 5 — Run the Application

Open:

`http://localhost/fitness/`

If your local project folder has a different name, adjust the URL accordingly.

---

## 🔗 9. Application Navigation

The shared PHP router uses `index.php`.

Examples of application routes in the reviewed source:

| Function | Route |
|---|---|
| Home | `?action=home` |
| Login | `?action=login` |
| Register | `?action=register` |
| User Dashboard | `?action=dashboard` |
| Admin Dashboard | `?action=admin_dashboard` |
| Blog | `?action=blog` |
| Articles | `?action=articles` |
| Blog Admin | `?action=admin_blog_dashboard` |
| Manage Articles | `?action=admin_articles` |
| Manage Comments | `?action=admin_comments` |
| Marketplace | `?action=store` |

Some features require authentication or specific user permissions.

---

## 🔐 10. Security

HB Fitness applies web security practices taught during the PHP Security workshop.

The current implementation includes:

- PDO prepared statements.
- Password hashing and verification.
- PHP sessions.
- Role-based authorization.
- CSRF protections in Blog operations.
- Input validation.
- XSS output escaping.
- Ownership checks for articles and comments.

### Security Work in Progress

- Verify permission changes during active sessions.
- Test all three roles after module integration.
- Review Marketplace API authorization.
- Verify account suspension behavior.
- Complete cross-module security testing.

---

## ✅ 11. Development Progress

### Implemented in the Reviewed Source

- [x] Article Model and Comment Model.
- [x] Article CRUD using PHP PDO.
- [x] Comment CRUD using PHP PDO.
- [x] SQL JOIN queries.
- [x] Blog Front Office pages.
- [x] Blog Back Office pages.
- [x] Blog input validation.
- [x] Blog ownership checks.
- [x] User Management controllers and interfaces.
- [x] Marketplace source components.

### In Progress

- [ ] Integrate Blog authentication with shared User Management.
- [ ] Verify Admin, User, and Coach permissions.
- [ ] Unify database relationships across modules.
- [ ] Test shared Front Office and Back Office navigation.
- [ ] Review Marketplace security.
- [ ] Update integration documentation.

### Pending

- [ ] Complete end-to-end integration testing.
- [ ] Test user deletion with related content.
- [ ] Verify foreign-key constraints after database unification.
- [ ] Integrate and verify the remaining team modules.
- [ ] Perform final project validation.

Checked items indicate that source-code implementations were identified, not that every browser or database test has passed.

---

## 🧪 12. Testing

Testing includes the following areas:

| Test | Status |
|---|---|
| PHP Syntax | Passed in reviewed snapshot |
| Article CRUD | Implemented — integrated testing pending |
| Comment CRUD | Implemented — integrated testing pending |
| SQL JOIN | Implemented — runtime testing pending |
| User Authentication | Integration testing pending |
| Admin Authorization | Further testing required |
| Coach Authorization | Further testing required |
| User Authorization | Further testing required |
| Marketplace Security | Review required |
| Full Project Integration | In Progress |

The reviewed snapshot passed syntax checks for 53 PHP files.

Additional runtime tests are required before the final project validation.

---

## 🌍 13. Sustainable Development Goals

HB Fitness supports the following United Nations Sustainable Development Goals:

**SDG 3 — Good Health and Well-Being**

Encouraging fitness, healthy nutrition, and physical activity.

**SDG 10 — Reduced Inequalities**

Supporting accessible fitness information and services.

**SDG 12 — Responsible Consumption and Production**

Promoting the resale and reuse of sports equipment.

**SDG 13 — Climate Action**

Encouraging more sustainable consumption habits.

---

## 🤝 14. Team Members

| Team Member | Responsibility |
|---|---|
| Abdulrahman | Team Leader — Blog Management |
| Meriem | Nutrition & Recipes |
| Sofiane | Workout Programs |
| Mouadh | Events & Sponsors |
| Ali | Sports Equipment Marketplace |
| Hanine | Coaching Sessions |

User Management is a shared application component. Individual contributions are tracked through GitHub Issues, branches, and commits.

---

## 🔄 15. GitHub Collaboration

The team uses Git and GitHub to manage development.

### Workflow

1. Create a GitHub Issue for the assigned task.
2. Assign the issue to the responsible team member.
3. Create a dedicated Git branch.
4. Implement and test the feature.
5. Commit the changes.
6. Push the feature branch.
7. Open a Pull Request.
8. Review and integrate the changes.
9. Update the GitHub Project Board.

### Project Board Status

- **Todo** — Not started.
- **In Progress** — Currently being developed or tested.
- **Done** — Completed and verified according to the task's scope.

### Repository

[HB Fitness — GitHub Repository](https://github.com/abderralrhman/Esprit-PI-2A1-2026-HBFitness)

---

## 🚀 16. Future Improvements

Future development priorities include:

- Complete integration of all six modules.
- Unified authentication and database management.
- Improved security and authorization testing.
- Better integration between fitness content and nutrition.
- Enhanced user experience.
- Mobile-friendly improvements.
- Additional personalization features.

---

## 🎓 17. Academic Context

This project was developed as part of the integrated project curriculum at:

**ESPRIT School of Engineering — Tunisia**

**Academic Year: 2026–2027**

The project applies technical concepts learned throughout the academic year, including:

- HTML5
- CSS3
- JavaScript
- PHP Object-Oriented Programming
- MVC architecture
- PDO and CRUD
- SQL JOIN
- Database relationships
- Session Management
- Web Security
- Git and GitHub collaboration

---

## 🙏 18. Acknowledgments

We would like to thank our teachers and project supervisors at ESPRIT School of Engineering for their support, technical workshops, and valuable feedback.

---

### HB FITNESS

**Train Smart. Eat Well. Reuse More.**

*Building a healthier and more sustainable fitness community.*
