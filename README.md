HB FITNESS
Train smart. Eat well. Reuse more.
Academic Year: 2026–2027
Institution: Esprit School of Engineering – Tunisia
Program: Integrated Project (PI), Web Technologies — Second Year (2A)
Project status: In development — module integration and end-to-end validation ongoing.
README draft for team review. It describes the source files currently present in the uploaded project snapshot. It is not a claim that all modules have passed live testing.
Overview
HB FITNESS is a web platform focused on fitness, healthy living, and the reuse of sports equipment. Its broader vision combines workout programs, nutrition, community content, events, coaching sessions, and a marketplace for second-hand sports gear.
The current source snapshot contains code for User Management, Blog Management, and Sports Equipment Marketplace. Other planned modules may be developed on teammates' branches and are not claimed as integrated here.
Table of Contents
Features
Tech Stack
Architecture
Database
Getting Started
Using the Application
Authentication and Permissions
Security
Testing and Current Status
Collaboration and Contributions
Contributors
Academic Context
Acknowledgments
License
Features
1. Blog Management — Article & Comment
Module responsibility: Abdulrahman (team leader; confirm exact name spelling before publication).
The Blog module has two main entities, Article and Comment, with a one-to-many (1:N) relationship. One article may have several comments.
Implemented in the source code:
Article CRUD: create, list/view, update, and delete articles.
Comment CRUD: add, view, update, and delete comments.
Front Office: article listings, article details, comments, and My Comments.
Back Office: article management, comment moderation, and Blog statistics.
PDO queries and SQL JOINs connecting articles, comments, and author information.
Input validation, CSRF checks, and server-side role/ownership checks.
Blog roles currently reflected in the code:
Role	Blog permissions
Visitor	Read articles and visible comments.
User	Read articles; add, edit, and delete their own comments.
Coach	User comment permissions; create and manage their own articles.
Admin	Manage all articles and moderate comments.
These permissions must still be verified with real users in the final shared authentication system.
2. User Management & Authentication
Implemented in the source code:
User CRUD and administration.
Registration, email/password login, and logout.
Roles: admin, user, coach.
Account statuses: active, suspended.
User profile management, session handling, and role checks.
Integration check pending: confirm that every module uses the same up-to-date role and account status, particularly after an administrator changes or suspends a user.
3. Sports Equipment Marketplace
Present in the current source snapshot:
Public equipment storefront and category filtering.
Marketplace listings, categories, and orders.
Admin interface and JSON API endpoints.
Integration and security review pending: marketplace database identity, admin-route protection, and API authorization/CSRF controls must be verified and corrected as needed.
4. Other Planned Modules
The HB FITNESS team project also plans Nutrition & Recipes, Workout Programs, Events & Sponsors, and Coaching Sessions. Their implementation status must be confirmed from each contributor's actual branch or pull request before being marked complete.
Tech Stack
Frontend
HTML5 and CSS3
Bootstrap 5
JavaScript (Vanilla JS)
Responsive Front Office and Back Office layouts
Backend
PHP 8+ and Object-Oriented Programming (OOP)
MVC pattern (Model–View–Controller)
PHP Data Objects (PDO) with prepared statements
MySQL / MariaDB
XAMPP (local Apache and MySQL environment)
Branding
The HB FITNESS visual identity uses Black #030303, Crimson #910329, Silver #B4B4B4, Charcoal #1A1A1D, and Coral Red #E8405E, with Oswald and DM Sans typography where available.
Architecture
The application uses a PHP MVC structure:
Models: data entities and supporting data-access components.
Controllers: request handling, business logic, validation, and database operations.
Views: presentation pages, forms, reusable layouts, and partials.
index.php: shared front controller and application router.
Current high-level source structure:
HBfitess/
├── Controllers/
│   ├── Blog/
│   │   ├── ArticleController.php
│   │   ├── CommentController.php
│   │   └── BlogAuthAdapter.php
│   ├── AuthController.php
│   ├── UserController.php
│   ├── RoleController.php
│   ├── PageController.php
│   └── ApiController.php
├── Models/
│   ├── Blog/
│   │   ├── Article.php
│   │   └── Comment.php
│   ├── User.php
│   ├── Role.php
│   ├── UserStatus.php
│   └── MarketplaceRepository.php
├── Views/
│   ├── Frontoffice/blog/
│   ├── Backoffice/blog/
│   ├── Frontoffice/
│   ├── Backoffice/
│   ├── front/
│   ├── admin/
│   ├── includes/
│   ├── assets/
│   ├── layout.php
│   └── layout_admin.php
├── config/database.php
├── index.php
├── api.php
├── db.php
├── blog.sql
├── hbfitess.sql
├── marketplace_setup.sql
└── README.md
Some directories contain additional CSS, JavaScript, and image assets. Paths above describe the uploaded integration snapshot, not a proposed restructure.
Database
The repository currently includes separate SQL files:
File	Purpose	Current state
hbfitess.sql	Creates hbfitess_db and the users, roles, and account-status tables.	Present in source.
blog.sql	Creates articles and comments linked to the shared users table.	Present; import into the database holding the compatible users table.
marketplace_setup.sql	Creates a separate hb_fitness database for marketplace data.	Present; shared-database integration remains pending.
Blog relationships:
articles.author_id → users.id
comments.article_id → articles.id
comments.user_id → users.id
articles.recipe_id is nullable for possible future Nutrition integration.
Important: The team must reconcile the Marketplace database schema with the shared user identities before claiming one fully unified database. Never import SQL over existing data without a backup and schema review.
Getting Started
Prerequisites
XAMPP with PHP 8+ and MySQL/MariaDB
Browser
Git (recommended)
A local copy of the project
Local Installation (Current Integration Snapshot)
Place the project in C:\xampp\htdocs\HBfitess\ (or adjust the URL to match its actual folder name).
Start Apache and MySQL in XAMPP.
Open http://localhost/phpmyadmin/.
On a new local database only, import hbfitess.sql, then select hbfitess_db and import blog.sql. Confirm the SQL import succeeded.
Review config/database.php for the User/Blog PDO connection. Its default database name is hbfitess_db; optional environment variables begin with HBFITESS_DB_.
Marketplace requires separate setup for now: inspect marketplace_setup.sql and db.php before importing/connecting hb_fitness. This is not yet a unified database setup.
Open http://localhost/HBfitess/index.php?action=home.
Do not run these imports against an existing team database without backing it up. These steps describe source configuration; they have not been verified on this review environment's running XAMPP instance.
Initial Account Setup
The current source includes a localhost-only ?action=setup route for creating initial accounts when no administrator exists. This is development setup infrastructure and must be reviewed/disabled before a public deployment. Use secure passwords; do not commit passwords or real user information.
Using the Application
The following routes are defined in the current index.php:
Area	Local route
Home	?action=home
Login	?action=login
Register	?action=register
User Dashboard	?action=dashboard
Admin Dashboard	?action=admin_dashboard
Blog Home	?action=blog
Article Listing	?action=articles
Blog Admin Dashboard	?action=admin_blog_dashboard
Manage Blog Articles	?action=admin_articles
Manage Comments	?action=admin_comments
Marketplace Store	?action=store
Marketplace Admin	?action=market_admin
Append each query to http://localhost/HBfitess/index.php if you use the suggested folder name. Some routes require login and specific permissions.
Authentication and Permissions
The application has three registered roles: Admin, User, and Coach. Login uses email and password; roles must be loaded from trusted server-side account data, not selected by a visitor. The Blog applies ownership rules to user comments and coach articles.
Known integration task: Reconnect Blog authorization to the shared User Management controller so role changes and suspended statuses are checked against current database records, not only a potentially stale session snapshot.
Security
The current code includes security-related measures such as:
PDO prepared statements in the Blog/User paths.
Password hashing and verification in authentication flows.
Session configuration and inactivity timeout.
CSRF tokens and request validation in Blog operations.
Output escaping and ownership checks in Blog views/controllers.
Security is not yet signed off: cross-module authorization, Marketplace API protection, role/session freshness, and final browser-based security testing are pending.
Testing and Current Status
Item	Code inspection	Live XAMPP verification
Article and Comment Models	Present	Not applicable
Article CRUD	Present	Requires final integration test
Comment CRUD	Present	Requires final integration test
SQL JOIN queries	Present	Requires database execution test
Blog Front/Back Office	Present	Requires route/UI test
Blog validation and authorization	Present	Requires permission/security test
User Management	Present	Requires regression test
Marketplace	Present	Requires database/security integration work
In the source review, 53 PHP files passed PHP syntax checks. This does not prove database or browser functionality.
Next Priorities
Verify shared Blog/User authentication, especially changed roles and suspended users.
Complete the XAMPP Article/Comment CRUD, JOIN, and role-permission test checklist.
Resolve the separate Marketplace database/user model and protect its admin/API routes.
Validate cross-module navigation, document screenshots and results, and review integration through pull requests.
Collaboration and Contributions
The project uses GitHub branches, issues, pull requests, and the HB Fitness Board to track work. Each team member should own and document their assigned module; an issue should be marked Done only when its stated acceptance criteria are met.
Recommended workflow: create a dedicated feature branch, make focused commits, open a pull request, review integration conflicts, test the combined application, then merge after approval. Never force-push over teammates' work.
Contributors
Contributor	Assigned module (from project presentation)
Abdulrahman	Team leader — Blog (Article & Comment)
Meriem	Nutrition & Recipes
Sofiane	Workout Programs
Mouadh	Events & Sponsors
Ali	Used-equipment Marketplace
Hanine	Coaching Sessions
Note: Roles above identify planned ownership; they do not assert all modules are implemented or integrated. The shared User Management contributor(s) should be confirmed by the team before publication.
Academic Context
Developed at Esprit School of Engineering – Tunisia
Academic Year: 2026–2027
Project: Integrated Project (PI), Web Technologies, Second Year (2A)
Acknowledgments
We thank our ESPRIT instructors and project supervisors for their guidance, course workshops, and validation feedback.
License
To be decided by the team. No license is claimed until an appropriate LICENSE file is added to the repository.
