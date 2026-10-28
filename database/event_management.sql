-- ============================================================
-- EVENT MANAGEMENT SYSTEM DATABASE SCHEMA & SAMPLE DATA
-- Database Name: event_management_db
-- Technology: MySQL / MariaDB (Compatible with XAMPP phpMyAdmin)
-- ============================================================

CREATE DATABASE IF NOT EXISTS `event_management_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `event_management_db`;

-- Disable foreign key checks for clean drop and recreation
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `event_sponsor`;
DROP TABLE IF EXISTS `event_artist`;
DROP TABLE IF EXISTS `equipment`;
DROP TABLE IF EXISTS `staff`;
DROP TABLE IF EXISTS `event`;
DROP TABLE IF EXISTS `sponsor`;
DROP TABLE IF EXISTS `artist`;
DROP TABLE IF EXISTS `venue`;
DROP TABLE IF EXISTS `organizer`;
DROP TABLE IF EXISTS `admin_users`;

SET FOREIGN_KEY_CHECKS = 1;

-- ------------------------------------------------------------
-- Authentication Table: admin_users
-- ------------------------------------------------------------
CREATE TABLE `admin_users` (
    `admin_id` INT AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(50) NOT NULL UNIQUE,
    `email` VARCHAR(100) NOT NULL UNIQUE,
    `password_hash` VARCHAR(255) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Table 1: organizer
-- ------------------------------------------------------------
CREATE TABLE `organizer` (
    `organizer_id` INT AUTO_INCREMENT PRIMARY KEY,
    `organizer_name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(100) NOT NULL UNIQUE,
    `phone` VARCHAR(15) NOT NULL UNIQUE,
    `organization` VARCHAR(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Table 2: venue
-- ------------------------------------------------------------
CREATE TABLE `venue` (
    `venue_id` INT AUTO_INCREMENT PRIMARY KEY,
    `venue_name` VARCHAR(100) NOT NULL,
    `address` VARCHAR(200) NOT NULL,
    `city` VARCHAR(50) NOT NULL,
    `capacity` INT NOT NULL,
    CONSTRAINT `chk_venue_capacity` CHECK (`capacity` > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Table 3: artist
-- ------------------------------------------------------------
CREATE TABLE `artist` (
    `artist_id` INT AUTO_INCREMENT PRIMARY KEY,
    `artist_name` VARCHAR(100) NOT NULL,
    `genre` VARCHAR(50) DEFAULT NULL,
    `contact_email` VARCHAR(100) NOT NULL UNIQUE,
    `country` VARCHAR(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Table 4: event
-- ------------------------------------------------------------
CREATE TABLE `event` (
    `event_id` INT AUTO_INCREMENT PRIMARY KEY,
    `event_name` VARCHAR(100) NOT NULL,
    `event_description` TEXT DEFAULT NULL,
    `event_date` DATE NOT NULL,
    `event_time` TIME NOT NULL,
    `organizer_id` INT NOT NULL,
    `venue_id` INT NOT NULL,
    `status` VARCHAR(20) NOT NULL DEFAULT 'Upcoming',
    CONSTRAINT `chk_event_status` CHECK (`status` IN ('Upcoming', 'Ongoing', 'Completed', 'Cancelled')),
    CONSTRAINT `fk_event_organizer` FOREIGN KEY (`organizer_id`) REFERENCES `organizer` (`organizer_id`) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT `fk_event_venue` FOREIGN KEY (`venue_id`) REFERENCES `venue` (`venue_id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Table 5: event_artist (Many-to-Many Relationship)
-- ------------------------------------------------------------
CREATE TABLE `event_artist` (
    `event_id` INT NOT NULL,
    `artist_id` INT NOT NULL,
    `role` VARCHAR(50) DEFAULT 'Performer',
    PRIMARY KEY (`event_id`, `artist_id`),
    CONSTRAINT `fk_ea_event` FOREIGN KEY (`event_id`) REFERENCES `event` (`event_id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_ea_artist` FOREIGN KEY (`artist_id`) REFERENCES `artist` (`artist_id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Table 6: sponsor
-- ------------------------------------------------------------
CREATE TABLE `sponsor` (
    `sponsor_id` INT AUTO_INCREMENT PRIMARY KEY,
    `sponsor_name` VARCHAR(100) NOT NULL,
    `contact_person` VARCHAR(100) DEFAULT NULL,
    `email` VARCHAR(100) NOT NULL UNIQUE,
    `phone` VARCHAR(15) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Table 7: event_sponsor (Many-to-Many Relationship)
-- ------------------------------------------------------------
CREATE TABLE `event_sponsor` (
    `event_id` INT NOT NULL,
    `sponsor_id` INT NOT NULL,
    `sponsorship_amount` DECIMAL(12, 2) NOT NULL,
    `sponsorship_type` VARCHAR(50) DEFAULT 'Gold Sponsor',
    PRIMARY KEY (`event_id`, `sponsor_id`),
    CONSTRAINT `chk_sponsorship_amount` CHECK (`sponsorship_amount` > 0),
    CONSTRAINT `fk_es_event` FOREIGN KEY (`event_id`) REFERENCES `event` (`event_id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_es_sponsor` FOREIGN KEY (`sponsor_id`) REFERENCES `sponsor` (`sponsor_id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Table 8: staff (1:M with event)
-- ------------------------------------------------------------
CREATE TABLE `staff` (
    `staff_id` INT AUTO_INCREMENT PRIMARY KEY,
    `staff_name` VARCHAR(100) NOT NULL,
    `role` VARCHAR(50) NOT NULL,
    `phone` VARCHAR(15) DEFAULT NULL,
    `event_id` INT NOT NULL,
    CONSTRAINT `fk_staff_event` FOREIGN KEY (`event_id`) REFERENCES `event` (`event_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Table 9: equipment (1:M with event)
-- ------------------------------------------------------------
CREATE TABLE `equipment` (
    `equipment_id` INT AUTO_INCREMENT PRIMARY KEY,
    `equipment_name` VARCHAR(100) NOT NULL,
    `quantity` INT NOT NULL,
    `condition_status` VARCHAR(30) DEFAULT 'Good',
    `event_id` INT NOT NULL,
    CONSTRAINT `chk_equipment_qty` CHECK (`quantity` > 0),
    CONSTRAINT `fk_equipment_event` FOREIGN KEY (`event_id`) REFERENCES `event` (`event_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- SAMPLE DATA INSERTIONS
-- ============================================================

-- 1. Admin User (Credentials: admin / admin123)
-- Password hash generated with PHP password_hash('admin123', PASSWORD_BCRYPT)
INSERT INTO `admin_users` (`username`, `email`, `password_hash`) VALUES
('admin', 'admin@eventhub.com', '$2y$10$wT8K7xXfG5w3k3j6T9Vl6O1Y0p6UqE4R3m7L8v2M1K9Q8P7N6B5m.');

-- 2. Organizers
INSERT INTO `organizer` (`organizer_id`, `organizer_name`, `email`, `phone`, `organization`) VALUES
(1, 'Dr. Rajesh Sharma', 'rajesh.sharma@techfest.org', '9876543210', 'Department of Computer Engineering'),
(2, 'Prof. Ananya Sen', 'ananya.sen@unicul.edu', '9876543211', 'University Cultural Society'),
(3, 'Vikram Rathore', 'vikram.rathore@sportscouncil.org', '9876543212', 'Campus Athletic Council'),
(4, 'Meera Nair', 'meera.nair@campusmusic.org', '9876543213', 'Campus Music & Arts Guild'),
(5, 'Karan Malhotra', 'karan.malhotra@globalecell.com', '9876543214', 'Entrepreneurship Cell');

-- 3. Venues
INSERT INTO `venue` (`venue_id`, `venue_name`, `address`, `city`, `capacity`) VALUES
(1, 'Grand Auditorium Hall A', 'East Wing, Academic Block 1', 'Ahmedabad', 1200),
(2, 'Open Air Amphitheatre', 'Central Campus Gardens', 'Ahmedabad', 3500),
(3, 'Convention Center Hall', 'Science & Innovation Park', 'Gandhinagar', 850),
(4, 'University Sports Stadium', 'South Campus Sports Arena', 'Ahmedabad', 5000),
(5, 'Tech Seminar Hall 3', 'Computer Center, 2nd Floor', 'Ahmedabad', 300);

-- 4. Artists / Speakers
INSERT INTO `artist` (`artist_id`, `artist_name`, `genre`, `contact_email`, `country`) VALUES
(1, 'The Acoustic Waves', 'Indie Rock / Alternative', 'booking@acousticwaves.com', 'India'),
(2, 'DJ Electra', 'Electronic / EDM', 'mgmt@djelectra.com', 'USA'),
(3, 'Pandit Ravi Trio', 'Classical Instrumental Fusion', 'ravi.trio@fusionmusic.org', 'India'),
(4, 'Standup Samarth', 'Stand-up Comedy', 'samarth@comedyhub.in', 'India'),
(5, 'Dr. Alistair Finch', 'Keynote Speaker (AI & Robotics)', 'afinch@oxfordtech.edu', 'United Kingdom'),
(6, 'BeatBox Collective', 'Acapella & Vocal Percussion', 'contact@beatboxcollective.com', 'Germany');

-- 5. Events
INSERT INTO `event` (`event_id`, `event_name`, `event_description`, `event_date`, `event_time`, `organizer_id`, `venue_id`, `status`) VALUES
(1, 'Annual Tech Conference 2026', 'National level technology conference featuring expert keynotes on Artificial Intelligence, hands-on workshops, project exhibitions, and a 24-hour hackathon.', '2026-11-15', '09:30:00', 1, 3, 'Upcoming'),
(2, 'College Cultural Fest - Resonance', 'The biggest annual cultural celebration showcasing inter-college dance, drama, battle of the bands, fine arts exhibitions, and celebrity music performances.', '2026-11-20', '16:00:00', 2, 2, 'Upcoming'),
(3, 'Campus Music Night Live', 'An electrifying evening of rock, indie acoustic melodies, and an energetic electronic dance music set under the open sky.', '2026-10-28', '18:30:00', 4, 1, 'Ongoing'),
(4, 'Startup & Innovation Expo', 'Platform for young student entrepreneurs to pitch startup ideas to angel investors, industry mentors, and venture capitalists.', '2026-12-05', '10:00:00', 5, 5, 'Upcoming'),
(5, 'Annual University Sports Meet', 'Inter-department athletics championship, cricket and football finals, followed by the grand championship trophy presentation ceremony.', '2026-10-15', '08:00:00', 3, 4, 'Completed');

-- 6. Event - Artist Assignments (Many-to-Many)
INSERT INTO `event_artist` (`event_id`, `artist_id`, `role`) VALUES
(1, 5, 'Keynote Speaker - AI Innovations'),
(2, 1, 'Headline Band Performance'),
(2, 4, 'Guest Comedy Emcee'),
(3, 1, 'Opening Indie Rock Act'),
(3, 2, 'Closing DJ EDM Set'),
(3, 3, 'Classical Fusion Opening'),
(4, 5, 'Panel Discussion Moderator'),
(5, 6, 'Opening Ceremony Performance');

-- 7. Sponsors
INSERT INTO `sponsor` (`sponsor_id`, `sponsor_name`, `contact_person`, `email`, `phone`) VALUES
(1, 'Nexa Technologies Ltd.', 'Suresh Patel', 'sponsorship@nexatech.com', '9825012345'),
(2, 'RedPeak Energy Drinks', 'Alia Kapoor', 'events@redpeak.com', '9825012346'),
(3, 'Apex Global Bank', 'Manish Verma', 'community@apexbank.in', '9825012347'),
(4, 'CloudScale Systems', 'Neha Gupta', 'partners@cloudscale.io', '9825012348'),
(5, 'FastTrack Sports Apparel', 'Rohan Joshi', 'marketing@fasttrack.com', '9825012349');

-- 8. Event - Sponsor Assignments (Many-to-Many)
INSERT INTO `event_sponsor` (`event_id`, `sponsor_id`, `sponsorship_amount`, `sponsorship_type`) VALUES
(1, 1, 150000.00, 'Title Sponsor'),
(1, 4, 75000.00, 'Cloud Infrastructure Partner'),
(2, 2, 100000.00, 'Youth Energy Partner'),
(2, 3, 120000.00, 'Associate Sponsor'),
(3, 2, 60000.00, 'Beverage Partner'),
(3, 1, 80000.00, 'Sound & Stage Sponsor'),
(4, 4, 90000.00, 'Title Sponsor'),
(5, 5, 110000.00, 'Official Kit Sponsor');

-- 9. Staff Members (1:M with event)
INSERT INTO `staff` (`staff_id`, `staff_name`, `role`, `phone`, `event_id`) VALUES
(1, 'Aarav Patel', 'Technical Stage Manager', '9712001122', 1),
(2, 'Pooja Trivedi', 'Guest Relations Coordinator', '9712001123', 1),
(3, 'Devendra Singh', 'Audio & Sound Supervisor', '9712001124', 2),
(4, 'Tanvi Shah', 'Crowd Management Lead', '9712001125', 2),
(5, 'Kavita Rao', 'Hospitality & VIP Escort', '9712001126', 2),
(6, 'Ritesh Joshi', 'Backstage Coordinator', '9712001127', 3),
(7, 'Nikhil Dave', 'Lighting Operator', '9712001128', 3),
(8, 'Simran Kaur', 'Registration Desk Head', '9712001129', 4),
(9, 'Amit Solanki', 'Pitch Arena Supervisor', '9712001130', 4),
(10, 'Hardik Pandya', 'Chief Field Marshal', '9712001131', 5);

-- 10. Equipment Allocations (1:M with event)
INSERT INTO `equipment` (`equipment_id`, `equipment_name`, `quantity`, `condition_status`, `event_id`) VALUES
(1, '4K UHD Digital Projector & Motorized Screen', 2, 'Excellent', 1),
(2, 'Wireless Lapel & Handheld Microphones', 12, 'Good', 1),
(3, 'Dual Line-Array Concert Speaker System', 4, 'Excellent', 2),
(4, 'Intelligent Moving Head Stage Lights', 24, 'Good', 2),
(5, 'Heavy-Duty Stage Smoke & Fog Machines', 2, 'Fair', 2),
(6, 'Digital 32-Channel Audio Mixing Console', 1, 'Excellent', 3),
(7, 'Strobe & Laser Effect Lights', 8, 'Good', 3),
(8, 'Interactive Pitch Podiums with HDMI Splitters', 4, 'Excellent', 4),
(9, 'Outdoor Wireless PA Horn Speakers', 10, 'Good', 5),
(10, 'Digital Electronic Scoreboard Unit', 2, 'Excellent', 5);
