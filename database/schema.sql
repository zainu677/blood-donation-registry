-- Blood Donation Registry System - database schema
-- Import this fresh in phpMyAdmin: create a NEW database (see instructions below)
-- then Import this file into it, or run `mysql -u root -p lifelink_db < schema.sql`.
--
-- Renamed to lifelink_db (was blood_donation / blood_donation_registry) to avoid
-- clashing with any half-built copy left over from earlier testing.
--
-- ALREADY have lifelink_db set up? You don't need to re-import this whole file.
-- Just run these three statements in phpMyAdmin's SQL tab instead (in this order):
--
--   ALTER TABLE appointments
--     MODIFY status ENUM('scheduled','pending','confirmed','completed','cancelled') NOT NULL DEFAULT 'pending',
--     ADD COLUMN appointment_time TIME NULL AFTER appointment_date;
--   UPDATE appointments SET status = 'pending' WHERE status = 'scheduled';
--   ALTER TABLE appointments
--     MODIFY status ENUM('pending','confirmed','completed','cancelled') NOT NULL DEFAULT 'pending';

CREATE DATABASE IF NOT EXISTS lifelink_db;
USE lifelink_db;

-- Everyone who registers or is created by an admin: donors, recipients,
-- hospital staff, blood bank staff, and admin accounts.
CREATE TABLE IF NOT EXISTS users (
    id                  INT AUTO_INCREMENT PRIMARY KEY,
    fullname            VARCHAR(100) NOT NULL,
    username            VARCHAR(50)  NOT NULL UNIQUE,
    email               VARCHAR(100) NOT NULL UNIQUE,
    phone               VARCHAR(20)  NOT NULL,
    password_hash       VARCHAR(255) NOT NULL,
    role                ENUM('donor', 'recipient', 'hospital', 'bank', 'admin') NOT NULL DEFAULT 'donor',
    age                 INT NULL,
    blood_group         VARCHAR(5) NULL,
    location            VARCHAR(100) NOT NULL,
    status              ENUM('pending', 'approved') NOT NULL DEFAULT 'pending',
    availability        ENUM('available', 'unavailable') NOT NULL DEFAULT 'available',
    last_donation_date  DATE NULL,
    created_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- One row per completed donation, tied back to the donor.
CREATE TABLE IF NOT EXISTS donations (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    donor_id        INT NOT NULL,
    donation_date   DATE NOT NULL,
    hospital_name   VARCHAR(100),
    CONSTRAINT fk_donations_donor FOREIGN KEY (donor_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Urgent/personal blood requests - posted by hospitals OR by recipients themselves.
CREATE TABLE IF NOT EXISTS blood_requests (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    requester_id    INT NOT NULL,
    blood_group     VARCHAR(5) NOT NULL,
    quantity        INT NOT NULL,
    location        VARCHAR(100) NOT NULL,
    status          ENUM('open', 'fulfilled') NOT NULL DEFAULT 'open',
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_requests_requester FOREIGN KEY (requester_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Donation appointments booked by donors.
CREATE TABLE IF NOT EXISTS appointments (
    id                INT AUTO_INCREMENT PRIMARY KEY,
    donor_id          INT NOT NULL,
    appointment_date  DATE NOT NULL,
    appointment_time  TIME NULL,
    location          VARCHAR(100) NOT NULL,
    status            ENUM('pending', 'confirmed', 'completed', 'cancelled') NOT NULL DEFAULT 'pending',
    created_at        TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_appointments_donor FOREIGN KEY (donor_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Messages submitted through the homepage "Contact Us" form.
CREATE TABLE IF NOT EXISTS contact_messages (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(100) NOT NULL,
    email       VARCHAR(100) NOT NULL,
    subject     VARCHAR(150) NOT NULL,
    message     TEXT NOT NULL,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Feedback left by recipients (or anyone) about their experience.
CREATE TABLE IF NOT EXISTS feedback (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    user_id     INT NOT NULL,
    message     TEXT NOT NULL,
    rating      TINYINT NULL, -- 1-5 stars, optional
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_feedback_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Reset tokens for the "Forgot password" flow.
CREATE TABLE IF NOT EXISTS password_resets (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    email       VARCHAR(100) NOT NULL,
    token       VARCHAR(100) NOT NULL,
    expires_at  DATETIME NOT NULL,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Blood bank inventory - one row per blood type, managed by blood bank staff.
CREATE TABLE IF NOT EXISTS blood_stock (
    blood_group      VARCHAR(5) PRIMARY KEY,
    units_available  INT NOT NULL DEFAULT 0,
    last_updated     TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT IGNORE INTO blood_stock (blood_group, units_available) VALUES
('A+', 0), ('A-', 0), ('B+', 0), ('B-', 0),
('AB+', 0), ('AB-', 0), ('O+', 0), ('O-', 0);

-- Helpful indexes for fast search (non-functional requirement: 2-second search on 10,000 records)
CREATE INDEX idx_users_blood_location ON users (blood_group, location);
CREATE INDEX idx_users_status_availability ON users (status, availability);
