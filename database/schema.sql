-- ============================================================
--  Michael Tourism Sales System - Web Version
--  Database schema (MySQL 5.7+ / MariaDB 10.3+)
--  Import order:
--    1) create the database named in your .envWebVersion (DB_NAME)
--         mysql -u USER -p -e "CREATE DATABASE your_db_name CHARACTER SET utf8mb4"
--    2) mysql -u USER -p your_db_name < schema.sql
--    3) mysql -u USER -p your_db_name < seed.sql   (demo data, optional)
-- ============================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ---------- users & auth ----------
CREATE TABLE IF NOT EXISTS `users` (
  `id`            INT AUTO_INCREMENT PRIMARY KEY,
  `name`          VARCHAR(100) NOT NULL,
  `email`         VARCHAR(150) NOT NULL UNIQUE,
  `salt`          VARCHAR(64)  NOT NULL,
  `password_hash` VARCHAR(64)  NOT NULL,
  `role`          ENUM('admin','manager','sales') NOT NULL DEFAULT 'sales',
  `active`        TINYINT(1) NOT NULL DEFAULT 1,
  `created_at`    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------- dropdown lists (mirrors 'Settings & Lists' sheet) ----------
CREATE TABLE IF NOT EXISTS `list_items` (
  `id`        INT AUTO_INCREMENT PRIMARY KEY,
  `list_name` VARCHAR(50)  NOT NULL,
  `value`     VARCHAR(100) NOT NULL,
  `sort`      INT NOT NULL DEFAULT 0,
  UNIQUE KEY `uq_list_value` (`list_name`, `value`)
) ENGINE=InnoDB;

-- ---------- currency conversion (base currency from .envWebVersion) ----------
CREATE TABLE IF NOT EXISTS `fx_rates` (
  `currency`     CHAR(3) PRIMARY KEY,
  `rate_to_base` DECIMAL(14,8) NOT NULL,
  `updated_at`   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------- key/value settings ----------
CREATE TABLE IF NOT EXISTS `settings` (
  `k` VARCHAR(50) PRIMARY KEY,
  `v` VARCHAR(255) NOT NULL
) ENGINE=InnoDB;

-- ---------- clients (CRM master) ----------
CREATE TABLE IF NOT EXISTS `clients` (
  `id`             INT AUTO_INCREMENT PRIMARY KEY,
  `name`           VARCHAR(150) NOT NULL,
  `client_type`    VARCHAR(30)  NULL,
  `country`        VARCHAR(80)  NULL,
  `contact_person` VARCHAR(100) NULL,
  `email`          VARCHAR(150) NULL,
  `phone`          VARCHAR(50)  NULL,
  `first_inquiry`  DATE         NULL,
  `owner_id`       INT          NULL,
  `notes`          TEXT         NULL,
  `created_at`     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY `idx_name` (`name`)
) ENGINE=InnoDB;

-- ---------- inquiries ----------
CREATE TABLE IF NOT EXISTS `inquiries` (
  `id`             INT AUTO_INCREMENT PRIMARY KEY,
  `received_date`  DATE NULL,
  `client_id`      INT NULL,
  `contact_person` VARCHAR(100) NULL,
  `email`          VARCHAR(150) NULL,
  `phone`          VARCHAR(50)  NULL,
  `country`        VARCHAR(80)  NULL,
  `client_type`    VARCHAR(30)  NULL,
  `lead_source`    VARCHAR(50)  NULL,
  `tour`           VARCHAR(150) NULL,
  `travel_date`    DATE NULL,
  `pax`            INT NULL,
  `budget`         DECIMAL(12,2) NULL,
  `currency`       CHAR(3) DEFAULT 'USD',
  `assigned_to`    INT NULL,
  `lead_status`    VARCHAR(20) DEFAULT 'Open',
  `next_follow_up` DATE NULL,
  `notes`          TEXT NULL,
  KEY `idx_client` (`client_id`), KEY `idx_assigned` (`assigned_to`),
  KEY `idx_status` (`lead_status`), KEY `idx_received` (`received_date`)
) ENGINE=InnoDB;

-- ---------- opportunities (sales pipeline) ----------
CREATE TABLE IF NOT EXISTS `opportunities` (
  `id`             INT AUTO_INCREMENT PRIMARY KEY,
  `inquiry_id`     INT NULL,
  `sales_stage`    VARCHAR(30) NULL,
  `opened_date`    DATE NULL,
  `expected_close` DATE NULL,
  `estimated_value` DECIMAL(12,2) NULL,
  `currency`       CHAR(3) DEFAULT 'USD',
  `probability`    INT NULL,
  `assigned_to`    INT NULL,
  `last_contact`   DATE NULL,
  `next_follow_up` DATE NULL,
  `lead_status`    VARCHAR(20) NULL,
  `lost_reason`    TEXT NULL,
  KEY `idx_inquiry` (`inquiry_id`), KEY `idx_stage` (`sales_stage`),
  KEY `idx_assigned` (`assigned_to`)
) ENGINE=InnoDB;

-- ---------- quotations ----------
CREATE TABLE IF NOT EXISTS `quotations` (
  `id`              INT AUTO_INCREMENT PRIMARY KEY,
  `inquiry_id`      INT NULL,
  `booking_id`      INT NULL,
  `quote_date`      DATE NULL,
  `valid_until`     DATE NULL,
  `tour`            VARCHAR(150) NULL,
  `pax`             INT NULL,
  `amount`          DECIMAL(12,2) NULL,
  `currency`        CHAR(3) DEFAULT 'USD',
  `status`          VARCHAR(20) NULL,
  `prepared_by`     INT NULL,
  `version`         INT DEFAULT 1,
  `notes`           TEXT NULL,
  `public_token`    VARCHAR(64) NULL,
  `public_enabled`  TINYINT(1) NOT NULL DEFAULT 1,
  `cover_note`      TEXT NULL,
  `inclusions`      TEXT NULL,
  `exclusions`      TEXT NULL,
  `terms`           TEXT NULL,
  `client_response` VARCHAR(20) NULL,
  `client_comment`  TEXT NULL,
  `responded_at`    DATETIME NULL,
  KEY `idx_inquiry` (`inquiry_id`), KEY `idx_status` (`status`),
  UNIQUE KEY `uq_public_token` (`public_token`)
) ENGINE=InnoDB;

-- ---------- quote itinerary days ----------
CREATE TABLE IF NOT EXISTS `quote_days` (
  `id`            INT AUTO_INCREMENT PRIMARY KEY,
  `quotation_id`  INT NOT NULL,
  `day_no`        INT NULL,
  `title`         VARCHAR(180) NULL,
  `description`   TEXT NULL,
  `accommodation` VARCHAR(180) NULL,
  `meals`         VARCHAR(120) NULL,
  `notes`         TEXT NULL,
  `sort`          INT DEFAULT 0,
  KEY `idx_quote` (`quotation_id`)
) ENGINE=InnoDB;

-- ---------- quote priced items ----------
CREATE TABLE IF NOT EXISTS `quote_items` (
  `id`           INT AUTO_INCREMENT PRIMARY KEY,
  `quotation_id` INT NOT NULL,
  `category`     VARCHAR(50) NULL,
  `description`  VARCHAR(255) NULL,
  `qty`          DECIMAL(10,2) DEFAULT 1,
  `unit`         VARCHAR(40) NULL,
  `unit_price`   DECIMAL(12,2) DEFAULT 0,
  `total`        DECIMAL(12,2) DEFAULT 0,
  `optional`     TINYINT(1) NOT NULL DEFAULT 0,
  `sort`         INT DEFAULT 0,
  KEY `idx_quote` (`quotation_id`)
) ENGINE=InnoDB;

-- ---------- public proposal view tracking ----------
CREATE TABLE IF NOT EXISTS `quote_views` (
  `id`           INT AUTO_INCREMENT PRIMARY KEY,
  `quotation_id` INT NOT NULL,
  `viewed_at`    DATETIME NOT NULL,
  `ip`           VARCHAR(45) NULL,
  `user_agent`   VARCHAR(255) NULL,
  KEY `idx_quote` (`quotation_id`),
  KEY `idx_viewed` (`viewed_at`)
) ENGINE=InnoDB;

-- ---------- follow-ups ----------
CREATE TABLE IF NOT EXISTS `follow_ups` (
  `id`             INT AUTO_INCREMENT PRIMARY KEY,
  `inquiry_id`     INT NULL,
  `action`         VARCHAR(255) NULL,
  `due_date`       DATE NULL,
  `priority`       VARCHAR(10) NULL,
  `assigned_to`    INT NULL,
  `status`         VARCHAR(20) DEFAULT 'Pending',
  `completed_date` DATE NULL,
  `notes`          TEXT NULL,
  KEY `idx_inquiry` (`inquiry_id`), KEY `idx_due` (`due_date`),
  KEY `idx_status` (`status`), KEY `idx_assigned` (`assigned_to`)
) ENGINE=InnoDB;

-- ---------- bookings ----------
CREATE TABLE IF NOT EXISTS `bookings` (
  `id`               INT AUTO_INCREMENT PRIMARY KEY,
  `inquiry_id`       INT NULL,
  `client_id`        INT NULL,
  `tour`             VARCHAR(150) NULL,
  `arrival`          DATE NULL,
  `departure`        DATE NULL,
  `pax`              INT NULL,
  `status`           VARCHAR(20) DEFAULT 'Tentative',
  `booking_date`     DATE NULL,
  `salesperson`      INT NULL,
  `booking_value`    DECIMAL(12,2) NULL,
  `currency`         CHAR(3) DEFAULT 'USD',
  `actual_revenue`   DECIMAL(12,2) NULL,
  `direct_cost`      DECIMAL(12,2) NULL,
  `payment_due_date` DATE NULL,
  `notes`            TEXT NULL,
  KEY `idx_client` (`client_id`), KEY `idx_inquiry` (`inquiry_id`),
  KEY `idx_status` (`status`), KEY `idx_salesperson` (`salesperson`),
  KEY `idx_bdate` (`booking_date`)
) ENGINE=InnoDB;

-- ---------- travelers ----------
CREATE TABLE IF NOT EXISTS `travelers` (
  `id`                INT AUTO_INCREMENT PRIMARY KEY,
  `booking_id`        INT NULL,
  `inquiry_id`        INT NULL,
  `full_name`         VARCHAR(150) NOT NULL,
  `dob`               DATE NULL,
  `nationality`       VARCHAR(80) NULL,
  `passport_no`       VARCHAR(60) NULL,
  `passport_expiry`   DATE NULL,
  `dietary`           VARCHAR(255) NULL,
  `allergies`         VARCHAR(255) NULL,
  `medical_notes`     TEXT NULL,
  `emergency_contact` VARCHAR(180) NULL,
  `room_preference`   VARCHAR(120) NULL,
  `notes`             TEXT NULL,
  `created_at`        TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  KEY `idx_booking` (`booking_id`),
  KEY `idx_inquiry` (`inquiry_id`)
) ENGINE=InnoDB;

-- ---------- reusable proposal content ----------
CREATE TABLE IF NOT EXISTS `content_library` (
  `id`         INT AUTO_INCREMENT PRIMARY KEY,
  `title`      VARCHAR(160) NOT NULL,
  `category`   VARCHAR(60) NULL,
  `body`       TEXT NOT NULL,
  `tags`       VARCHAR(255) NULL,
  `active`     TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  KEY `idx_category` (`category`),
  KEY `idx_active` (`active`)
) ENGINE=InnoDB;

-- ---------- reusable email / WhatsApp / SMS messages ----------
CREATE TABLE IF NOT EXISTS `message_templates` (
  `id`         INT AUTO_INCREMENT PRIMARY KEY,
  `name`       VARCHAR(160) NOT NULL,
  `channel`    VARCHAR(30) NULL,
  `subject`    VARCHAR(200) NULL,
  `body`       TEXT NOT NULL,
  `category`   VARCHAR(60) NULL,
  `active`     TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  KEY `idx_channel` (`channel`),
  KEY `idx_active` (`active`)
) ENGINE=InnoDB;

-- ---------- reusable follow-up task templates ----------
CREATE TABLE IF NOT EXISTS `task_templates` (
  `id`            INT AUTO_INCREMENT PRIMARY KEY,
  `name`          VARCHAR(160) NOT NULL,
  `trigger_stage` VARCHAR(40) NULL,
  `action`        VARCHAR(255) NOT NULL,
  `due_days`      INT DEFAULT 3,
  `priority`      VARCHAR(20) NULL,
  `active`        TINYINT(1) NOT NULL DEFAULT 1,
  `created_at`    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  KEY `idx_stage` (`trigger_stage`),
  KEY `idx_active` (`active`)
) ENGINE=InnoDB;

-- ---------- payment schedule (planned milestones) ----------
CREATE TABLE IF NOT EXISTS `payment_schedule` (
  `id`           INT AUTO_INCREMENT PRIMARY KEY,
  `booking_id`   INT NULL,
  `payment_type` VARCHAR(20) NULL,
  `description`  VARCHAR(255) NULL,
  `due_date`     DATE NULL,
  `amount_due`   DECIMAL(12,2) NULL,
  `date_paid`    DATE NULL,
  `amount_paid`  DECIMAL(12,2) NULL,
  `notes`        TEXT NULL,
  KEY `idx_booking` (`booking_id`), KEY `idx_due` (`due_date`)
) ENGINE=InnoDB;

-- ---------- payments (money received) ----------
CREATE TABLE IF NOT EXISTS `payments` (
  `id`           INT AUTO_INCREMENT PRIMARY KEY,
  `booking_id`   INT NULL,
  `invoice_ref`  VARCHAR(60) NULL,
  `payment_date` DATE NULL,
  `payment_type` VARCHAR(20) NULL,
  `amount`       DECIMAL(12,2) NULL,
  `currency`     CHAR(3) DEFAULT 'USD',
  `method`       VARCHAR(30) NULL,
  `received_by`  INT NULL,
  `notes`        TEXT NULL,
  KEY `idx_booking` (`booking_id`), KEY `idx_date` (`payment_date`)
) ENGINE=InnoDB;

-- ---------- sales targets ----------
CREATE TABLE IF NOT EXISTS `sales_targets` (
  `id`             INT AUTO_INCREMENT PRIMARY KEY,
  `month`          INT NOT NULL,
  `year`           INT NOT NULL,
  `salesperson`    INT NOT NULL,
  `target_revenue` DECIMAL(12,2) NULL,
  `notes`          TEXT NULL,
  UNIQUE KEY `uq_target` (`month`, `year`, `salesperson`)
) ENGINE=InnoDB;

-- ---------- sales activities ----------
CREATE TABLE IF NOT EXISTS `activities` (
  `id`              INT AUTO_INCREMENT PRIMARY KEY,
  `activity_date`   DATE NULL,
  `inquiry_id`      INT NULL,
  `activity_type`   VARCHAR(30) NULL,
  `summary`         VARCHAR(255) NULL,
  `next_action`     VARCHAR(255) NULL,
  `next_action_date` DATE NULL,
  `salesperson`     INT NULL,
  `duration_min`    INT NULL,
  KEY `idx_inquiry` (`inquiry_id`), KEY `idx_date` (`activity_date`)
) ENGINE=InnoDB;

SET FOREIGN_KEY_CHECKS = 1;
