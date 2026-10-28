# OPEN-ENDED PROBLEM (OEP) PROJECT REPORT
## Web Technology & Database Management Laboratory

---

### **PROJECT TITLE:**
# EVENT MANAGEMENT SYSTEM (EventHub)
### A Full-Stack Web-Based Application using PHP, MySQL, HTML5, CSS3, and JavaScript

---

**Academic Year:** 2026 – 2027  
**Department:** Computer Engineering / Information Technology  
**Subject:** Web Technology / Advanced Web Programming  
**Course Code:** OEP-CS402  

**Group Members (Team of 5 Students):**
1. **Student 1 (Team Lead):** [Roll No. / Enrollment No.] — [Student Name]
2. **Student 2:** [Roll No. / Enrollment No.] — [Student Name]
3. **Student 3:** [Roll No. / Enrollment No.] — [Student Name]
4. **Student 4:** [Roll No. / Enrollment No.] — [Student Name]
5. **Student 5:** [Roll No. / Enrollment No.] — [Student Name]

**Project Guide / Faculty Mentor:**
[Name of Professor / Faculty Guide]  
[Department Designation]  
[Institute / College Name]

---

## CERTIFICATE OF APPROVAL

This is to certify that the Open-Ended Problem (OEP) project titled **"EVENT MANAGEMENT SYSTEM"** submitted by the group of 5 students listed above has been carried out under our supervision and guidance in partial fulfillment of the requirements for the Degree of Bachelor of Engineering / Technology in Computer Engineering.

**Date:** `___ / ___ / 2026`  
**Place:** `________________`  

_________________________ &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; _________________________  
**Internal Examiner / Guide** &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; **Head of Department (HOD)**

---

## 1. ABSTRACT & PROBLEM STATEMENT

Modern institutions, universities, and commercial entities host diverse events ranging from annual technical symposiums and cultural festivals to concerts, keynote conferences, and sports meets. Historically, managing these events involved disconnected spreadsheets, manual sign-in registers, and unlinked email chains, resulting in venue scheduling clashes, uncoordinated artist logistics, untracked corporate sponsorships, and chaotic on-ground staff allocation.

The **Open-Ended Problem (OEP)** addressed in this project is to develop a centralized, reliable, and secure web application using the core LAMP/WAMP technology stack (**PHP, MySQL, HTML5, CSS3, and JavaScript**). 

The application, named **EventHub**, provides complete CRUD operations (Create, Read, Update, Delete) across 9 core relational tables and 1 security table. The system enforces referential integrity through foreign key constraints, implements bulletproof session-based access control, dual-layer form validation (client-side JS and server-side PHP), and relational assignment modules for complex Many-to-Many relationships.

---

## 2. OBJECTIVES & SCOPE

### 2.1 Primary Objectives
1. **Complete CRUD Operations:** Empower administrators to add, view, update, delete, and search across events, organizers, venues, artists, sponsors, staff members, and equipment.
2. **Relational Data Integrity:** Prevent orphan records using strict MySQL foreign key constraints (`ON DELETE RESTRICT` for parent entities, and `ON DELETE CASCADE` for dedicated event allocations).
3. **Many-to-Many Relationship Support:** Provide intuitive management screens to map artists to events (`event_artist`) and sponsors to events (`event_sponsor`), with duplicate prevention.
4. **Session Management & Authentication:** Secure administration portals with PHP sessions, Bcrypt password hashing (`password_hash` / `password_verify`), and session ID regeneration.
5. **Dual-Layer Field Validation:** Enforce immediate JavaScript feedback on the client browser while verifying all data types, formats, and numerical thresholds on the server in PHP.
6. **Unified User Experience:** Maintain a consistent, modern design featuring a dark charcoal sidebar, responsive grids, and breadcrumb navigation.

### 2.2 Scope of the Application
- College and university festival coordination.
- Corporate seminar and technical hackathon scheduling.
- Concert and public event cataloging with public exploration views.

---

## 3. SYSTEM REQUIREMENTS

### 3.1 Software Requirements
- **Operating System:** Windows 10/11, Linux, or macOS
- **Local Server Stack:** XAMPP (Apache 2.4+, MySQL/MariaDB 10.4+, PHP 8.1+)
- **Database Management Tool:** phpMyAdmin
- **Web Browser:** Google Chrome, Mozilla Firefox, or Microsoft Edge
- **Code Editor:** Visual Studio Code / Notepad++

### 3.2 Hardware Requirements
- **Processor:** Intel Core i3 or AMD Ryzen 3 (or higher)
- **RAM:** Minimum 4 GB (8 GB recommended)
- **Hard Disk Space:** 500 MB free storage for web server and database records

---

## 4. SYSTEM ARCHITECTURE & ER DIAGRAM

### 4.1 3-Tier System Architecture
```
+-----------------------------------------------------------+
|                    PRESENTATION TIER                      |
|  - HTML5 Responsive Views (Public Site & Admin Dashboard) |
|  - CSS3 Modern UI (Dark Sidebar, Modals, Badges)          |
|  - JavaScript Validation & Real-time Live Filter          |
+-----------------------------+-----------------------------+
                              | HTTP / HTTPS Requests
                              v
+-----------------------------------------------------------+
|                    APPLICATION LOGIC TIER                 |
|  - PHP 8.x Engine with Object-Oriented PDO                |
|  - Session Gatekeeper (auth.php)                          |
|  - CSRF Token Generator & Validator                       |
|  - Server-Side Sanitization & Regex Validation            |
+-----------------------------+-----------------------------+
                              | SQL Queries (Prepared)
                              v
+-----------------------------------------------------------+
|                     DATA STORAGE TIER                     |
|  - MySQL Relational Database (event_management_db)        |
|  - 10 Tables, Foreign Keys, Unique & Check Constraints    |
+-----------------------------------------------------------+
```

### 4.2 Entity-Relationship (ER) Relationships
1. **Organizer to Event (1 : M):** One organizer manages many events.
2. **Venue to Event (1 : M):** One venue can host multiple events over different dates.
3. **Event to Artist (M : N):** An event can have multiple artists; an artist can perform at multiple events. Resolved via bridge table `event_artist`.
4. **Event to Sponsor (M : N):** An event can have multiple sponsors; a sponsor can fund multiple events. Resolved via bridge table `event_sponsor`.
5. **Event to Staff (1 : M):** An event has multiple allocated staff members.
6. **Event to Equipment (1 : M):** An event has multiple allocated equipment items.

---

## 5. DATABASE SCHEMA & DATA DICTIONARY

The system uses a dedicated relational database: `event_management_db`.

### Table 1: `admin_users` (Authentication)
| Column Name | Data Type | Constraint | Description |
|---|---|---|---|
| `admin_id` | INT | PRIMARY KEY, AUTO_INCREMENT | Unique identifier for admin |
| `username` | VARCHAR(50) | NOT NULL, UNIQUE | Administrator login name |
| `email` | VARCHAR(100) | NOT NULL, UNIQUE | Administrator email |
| `password_hash` | VARCHAR(255) | NOT NULL | Bcrypt hashed password |
| `created_at` | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | Account registration timestamp |

### Table 2: `organizer`
| Column Name | Data Type | Constraint | Description |
|---|---|---|---|
| `organizer_id` | INT | PRIMARY KEY, AUTO_INCREMENT | Unique organizer identifier |
| `organizer_name` | VARCHAR(100) | NOT NULL | Full name of coordinator |
| `email` | VARCHAR(100) | NOT NULL, UNIQUE | Official email address |
| `phone` | VARCHAR(15) | NOT NULL, UNIQUE | Contact phone number |
| `organization` | VARCHAR(100) | NULLABLE | Department or club name |

### Table 3: `venue`
| Column Name | Data Type | Constraint | Description |
|---|---|---|---|
| `venue_id` | INT | PRIMARY KEY, AUTO_INCREMENT | Unique venue identifier |
| `venue_name` | VARCHAR(100) | NOT NULL | Name of the auditorium or hall |
| `address` | VARCHAR(200) | NOT NULL | Physical campus location |
| `city` | VARCHAR(50) | NOT NULL | City location |
| `capacity` | INT | NOT NULL, CHECK (capacity > 0) | Seating capacity |

### Table 4: `artist`
| Column Name | Data Type | Constraint | Description |
|---|---|---|---|
| `artist_id` | INT | PRIMARY KEY, AUTO_INCREMENT | Unique artist identifier |
| `artist_name` | VARCHAR(100) | NOT NULL | Stage or speaker name |
| `genre` | VARCHAR(50) | NULLABLE | Musical genre or lecture topic |
| `contact_email` | VARCHAR(100) | NOT NULL, UNIQUE | Management email |
| `country` | VARCHAR(50) | NULLABLE | Country of origin |

### Table 5: `event`
| Column Name | Data Type | Constraint | Description |
|---|---|---|---|
| `event_id` | INT | PRIMARY KEY, AUTO_INCREMENT | Unique event identifier |
| `event_name` | VARCHAR(100) | NOT NULL | Title of the event |
| `event_description` | TEXT | NULLABLE | Detailed schedule & info |
| `event_date` | DATE | NOT NULL | Scheduled event date |
| `event_time` | TIME | NOT NULL | Scheduled start time |
| `organizer_id` | INT | FOREIGN KEY (organizer) | Host organizer reference |
| `venue_id` | INT | FOREIGN KEY (venue) | Host venue reference |
| `status` | VARCHAR(20) | CHECK (Upcoming, Ongoing, Completed, Cancelled) | Operational state |

### Table 6: `event_artist` (Many-to-Many Bridge)
| Column Name | Data Type | Constraint | Description |
|---|---|---|---|
| `event_id` | INT | Composite PK, FK (event) | Target event reference |
| `artist_id` | INT | Composite PK, FK (artist) | Assigned artist reference |
| `role` | VARCHAR(50) | DEFAULT 'Performer' | Role (e.g. Keynote, DJ, Headliner) |

### Table 7: `sponsor`
| Column Name | Data Type | Constraint | Description |
|---|---|---|---|
| `sponsor_id` | INT | PRIMARY KEY, AUTO_INCREMENT | Unique sponsor identifier |
| `sponsor_name` | VARCHAR(100) | NOT NULL | Corporate partner company name |
| `contact_person` | VARCHAR(100) | NULLABLE | Representative name |
| `email` | VARCHAR(100) | NOT NULL, UNIQUE | Official sponsorship email |
| `phone` | VARCHAR(15) | NULLABLE | Representative phone number |

### Table 8: `event_sponsor` (Many-to-Many Bridge)
| Column Name | Data Type | Constraint | Description |
|---|---|---|---|
| `event_id` | INT | Composite PK, FK (event) | Target event reference |
| `sponsor_id` | INT | Composite PK, FK (sponsor) | Contributing sponsor reference |
| `sponsorship_amount` | DECIMAL(12,2) | NOT NULL, CHECK (> 0) | Financial commitment in ₹ |
| `sponsorship_type` | VARCHAR(50) | DEFAULT 'Gold Sponsor' | Tier (Title, Platinum, Gold, etc.) |

### Table 9: `staff`
| Column Name | Data Type | Constraint | Description |
|---|---|---|---|
| `staff_id` | INT | PRIMARY KEY, AUTO_INCREMENT | Unique staff identifier |
| `staff_name` | VARCHAR(100) | NOT NULL | Name of crew member |
| `role` | VARCHAR(50) | NOT NULL | Duty (e.g. Stage Manager, Audio) |
| `phone` | VARCHAR(15) | NULLABLE | Contact telephone |
| `event_id` | INT | FOREIGN KEY (event) | Assigned event reference |

### Table 10: `equipment`
| Column Name | Data Type | Constraint | Description |
|---|---|---|---|
| `equipment_id` | INT | PRIMARY KEY, AUTO_INCREMENT | Unique equipment identifier |
| `equipment_name` | VARCHAR(100) | NOT NULL | Equipment title (e.g. 4K Projector) |
| `quantity` | INT | NOT NULL, CHECK (> 0) | Number of allocated items |
| `condition_status` | VARCHAR(30) | DEFAULT 'Good' | Condition (Excellent, Good, Fair) |
| `event_id` | INT | FOREIGN KEY (event) | Assigned event reference |

---

## 6. SECURITY & VALIDATION ARCHITECTURE

1. **Authentication & Session Management:**
   - Administrators authenticate through `login.php`.
   - Credentials are verified via PHP `password_verify()`.
   - Upon successful authentication, `session_regenerate_id(true)` invalidates existing session IDs to prevent Session Fixation.
   - All management pages enforce `includes/auth.php`. Unauthenticated attempts are redirected to `login.php`.

2. **SQL Injection Prevention:**
   - 100% of SQL statements involving parameters utilize PHP Data Objects (PDO) prepared statements with parameter binding.

3. **Cross-Site Scripting (XSS) Prevention:**
   - All dynamic output rendered in HTML is sanitized through `htmlspecialchars($var, ENT_QUOTES, 'UTF-8')` via the custom `sanitize()` helper.

4. **Cross-Site Request Forgery (CSRF) Protection:**
   - Destructive operations (Create, Update, Delete) are strictly bound to `POST` requests.
   - Form tokens are validated using cryptographically secure `bin2hex(random_bytes(32))` and `hash_equals()`.

5. **Client-Side Form Validation (JavaScript):**
   - Intercepts form submissions using `.needs-validation`.
   - Evaluates required fields, regular expression email formats, 10–15 digit phone constraints, and positive number minimums (`min="1"`).

---

## 7. STEP-BY-STEP LOCAL INSTALLATION GUIDE (FOR XAMPP)

Follow these step-by-step instructions to run the application on any computer:

### Step 1: Install XAMPP
1. Download **XAMPP for Windows** from [apachefriends.org](https://www.apachefriends.org).
2. Install with Apache and MySQL components checked.

### Step 2: Place the Project in `htdocs`
1. Navigate to your XAMPP installation directory (normally `C:\xampp\htdocs\`).
2. Copy the entire `event-management` project folder into `C:\xampp\htdocs\`.
3. Your path should look like: `C:\xampp\htdocs\event-management\index.php`.

### Step 3: Start Apache and MySQL
1. Open the **XAMPP Control Panel**.
2. Click the **Start** button next to **Apache** (it will turn green).
3. Click the **Start** button next to **MySQL** (it will turn green).

### Step 4: Import Database in phpMyAdmin
1. Open your browser and go to: `http://localhost/phpmyadmin`.
2. Click on the **Import** tab in the top navigation bar.
3. Click **Choose File** (or Browse) and select the file:
   `C:\xampp\htdocs\event-management\database\event_management.sql`.
4. Scroll to the bottom of the page and click **Import** (or **Go**).
5. You will see green success alerts indicating that `event_management_db` and all 10 tables with sample data have been created.

### Step 5: Launch the Application
- Open your browser and enter:  
  👉 **`http://localhost/event-management`**
- Click on **Admin Login** in the top navigation bar.
- Enter default administrator credentials:
  - **Username:** `admin`
  - **Password:** `admin123`

---

## 8. TEST CASES & VERIFICATION MATRIX

| Test ID | Module Tested | Test Input / Action | Expected Result | Status |
|---|---|---|---|---|
| TC-01 | Authentication | Valid `admin` / `admin123` | Session initialized, redirected to `dashboard.php` | PASS |
| TC-02 | Authentication | Wrong password | Error alert: "Invalid username/email or password" | PASS |
| TC-03 | Authorization | Access `/admin/dashboard.php` directly | Redirected to `login.php` with access denied alert | PASS |
| TC-04 | Form Validation | Submit venue form with negative capacity (`-5`) | JS highlights field red with "Value must be at least 1" | PASS |
| TC-05 | Form Validation | Submit invalid email format (`user@`) | Field rejected on client and server | PASS |
| TC-06 | Referential Integrity | Delete an organizer who has active events | Rejection alert: "Cannot delete organizer: currently assigned to X events" | PASS |
| TC-07 | Many-to-Many | Assign artist already in the same event | Duplicate warning alert: "Artist already assigned to selected event" | PASS |
| TC-08 | Search & Filter | Filter events by status "Upcoming" | Only events with status 'Upcoming' are returned | PASS |
| TC-09 | Cascading Delete | Delete an event | Related entries in `event_artist`, `event_sponsor`, `staff`, `equipment` cascade cleanly | PASS |
| TC-10 | CSRF Protection | Submit delete request with tampered CSRF token | Blocked: "Invalid security token" | PASS |

---

## 9. SCREENSHOTS & FIGURES PLACEHOLDER GUIDE

*(When pasting into Microsoft Word or LibreOffice for your college hardcopy submission, insert screenshots of the following pages at these locations:)*

1. **Figure 1:** *Public Landing Page (`index.php`)* — Showing Hero banner, dynamic statistics counters, and featured event cards.
2. **Figure 2:** *Public Events Listing (`events.php`)* — Showing search bar, status filters, and multi-column card layout.
3. **Figure 3:** *Public Event Details (`event-details.php`)* — Showing organizer profile, venue capacity, performer lineup, and sponsor badges.
4. **Figure 4:** *Administrator Login Screen (`login.php`)* — Displaying credential input fields and academic test credentials hint.
5. **Figure 5:** *Administrator Dashboard (`admin/dashboard.php`)* — Showing 8 live MySQL aggregate metric cards, recent events table, and quick actions.
6. **Figure 6:** *Event Management Directory (`admin/events/index.php`)* — Showing table with status badges and allocation counters.
7. **Figure 7:** *Create Event Form (`admin/events/add.php`)* — Showing dropdown foreign key selectors for Organizers and Venues.
8. **Figure 8:** *Detailed Event Overview & Allocation (`admin/events/view.php`)* — Showing artists, sponsors, staff, and equipment tables.
9. **Figure 9:** *Artist Many-to-Many Assignment Screen (`admin/assignments/event_artists.php`)*.
10. **Figure 10:** *phpMyAdmin Database Schema (`event_management_db`)* — Showing all 10 tables and relation view.

---

## 10. CONCLUSION

The **EventHub Event Management System** fulfills all the academic objectives outlined in the Open-Ended Problem (OEP) guidelines. Developed using standard **PHP, MySQL, HTML5, CSS3, and JavaScript**, the system demonstrates real-world database design, strict referential integrity, secure session authentication, and clean dual-layer validation. The result is a robust, complete, and practical full-stack web application suitable for academic demonstration and real-world deployment.
