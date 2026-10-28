# 🎪 EventHub - Event Management System
> **College Open-Ended Problem (OEP) Web Application Project**  
> **Tech Stack:** PHP (PDO) &bull; MySQL &bull; HTML5 &bull; CSS3 &bull; JavaScript

---

## 🌟 Project Features Overview

- **Public Portal:**
  - `index.php`: High-impact landing page with hero banner, live platform stats, featured events, and about section.
  - `events.php`: Event browsing with keyword search, status filter, and city filter.
  - `event-details.php`: Comprehensive details showing organizers, venues, artist lineup, sponsors, and logistics.
- **Security & Authentication:**
  - `login.php`: Secure administrator login with Bcrypt password verification (`password_verify()`).
  - `logout.php`: Session termination and clean redirect.
  - Session protection across all `/admin/` routes via `includes/auth.php`.
  - CSRF token validation on all POST requests.
  - SQL Injection prevention using 100% PDO prepared statements.
  - XSS prevention via output sanitization.
- **Admin Dashboard & CRUD Modules:**
  - `admin/dashboard.php`: 8 dynamic metric cards calculated from live MySQL queries.
  - **Events Module:** Add, edit, delete, view, search, and status filtering.
  - **Organizers Module:** Full CRUD with referential integrity protection.
  - **Venues Module:** Full CRUD with positive capacity validation.
  - **Artists & Speakers Module:** Full CRUD with unique email enforcement.
  - **Sponsors Module:** Full CRUD with contribution calculations.
  - **Staff Allocation Module:** Full CRUD linked to specific events.
  - **Equipment Module:** Full CRUD with quantity checks and condition tracking.
- **Relational Many-to-Many Assignment Modules:**
  - `admin/assignments/event_artists.php`: Map artists to events with role titles and duplicate prevention.
  - `admin/assignments/event_sponsors.php`: Map sponsors to events with funding amounts, sponsorship tiers, inline editing, and duplicate prevention.
- **Validation:**
  - Client-side real-time validation via JavaScript (`assets/js/script.js`).
  - Strict server-side validation in PHP before any database operation.
- **Academic Documentation:**
  - Complete OEP Project Report included at `docs/OEP_PROJECT_REPORT.md`.

---

## 🚀 Quick Setup Instructions (XAMPP for Windows)

### 1. Download & Install XAMPP
If you don't already have XAMPP installed:
- Download the installer from: [https://www.apachefriends.org/](https://www.apachefriends.org/)
- Run the installer and finish standard installation (default location is usually `C:\xampp`).

### 2. Copy the Project Folder
- Copy this entire `event-management` folder into your XAMPP web directory:
  👉 **`C:\xampp\htdocs\event-management`**

### 3. Start Apache & MySQL
- Open **XAMPP Control Panel**.
- Click the **Start** button next to **Apache**.
- Click the **Start** button next to **MySQL**.
*(Both should have green highlights indicating they are running.)*

### 4. Import the Database in phpMyAdmin
1. Open your web browser (Chrome, Edge, etc.) and visit:  
   👉 **`http://localhost/phpmyadmin`**
2. In the top navigation menu, click the **Import** tab.
3. Click the **Choose File** (or Browse) button.
4. Select the file:  
   `C:\xampp\htdocs\event-management\database\event_management.sql`
5. Scroll down to the bottom and click **Import** (or **Go**).
*(You will see a success message that `event_management_db` and all sample tables were created!)*

### 5. Open the Website in Your Browser!
- Open your browser and go to:  
  👉 **`http://localhost/event-management`**
- To access the administrator dashboard, click **Admin Login** in the navbar or go directly to:  
  👉 **`http://localhost/event-management/login.php`**

### 🔑 Default Administrator Login Credentials:
- **Username:** `admin`
- **Password:** `admin123`

---

## 📁 Project Folder Structure
```text
event-management/
│
├── index.php                 <- Public Home Page
├── events.php                <- Public Event Browsing & Search
├── event-details.php         <- Public Event Detail Page
├── login.php                 <- Administrator Login
├── logout.php                <- Session Termination
├── README.md                 <- This Quick Setup Guide
│
├── admin/                    <- Protected Administrator Portal
│   ├── dashboard.php         <- Dynamic Metrics & Recent Events
│   ├── events/               <- Events CRUD (index, add, edit, delete, view)
│   ├── organizers/           <- Organizers CRUD (index, add, edit, delete)
│   ├── venues/               <- Venues CRUD (index, add, edit, delete)
│   ├── artists/              <- Artists CRUD (index, add, edit, delete)
│   ├── sponsors/             <- Sponsors CRUD (index, add, edit, delete)
│   ├── staff/                <- Staff Allocations CRUD (index, add, edit, delete)
│   ├── equipment/            <- Equipment Allocations CRUD (index, add, edit, delete)
│   └── assignments/          <- Many-to-Many Assignment Handlers
│       ├── event_artists.php <- Event-Artist Assignment & Duplicate Checking
│       └── event_sponsors.php<- Event-Sponsor Assignment, Tiers, & Funding
│
├── config/
│   └── database.php          <- PDO Database Connection & Base URL Detector
│
├── includes/
│   ├── auth.php              <- Session Gatekeeper (Protects Admin Routes)
│   ├── functions.php         <- Global Helpers, Flash Alerts, CSRF & XSS Cleaners
│   ├── header.php            <- Public Header & Navbar
│   ├── footer.php            <- Public Footer
│   ├── admin_header.php      <- Admin Header & Topbar
│   ├── admin_sidebar.php     <- Admin Sidebar Navigation
│   └── admin_footer.php      <- Admin Footer
│
├── assets/
│   ├── css/
│   │   └── style.css         <- Modern, Polished Responsive CSS
│   ├── js/
│   │   └── script.js         <- Client-Side Validation & Table Search
│   └── images/               <- Image Placeholders
│
├── database/
│   └── event_management.sql  <- Database Schema + Realistic Sample Seed Data
│
└── docs/
    └── OEP_PROJECT_REPORT.md <- Complete Formal Academic Project Report
```
