-- ============================================================
--  Phase 1 migration: quote builder, public proposals, travelers
--  Run this ONCE against an existing database that was created
--  from the original schema.sql.
--
--  Fresh installs do NOT need this file: schema.sql already
--  contains these columns and tables.
-- ============================================================

SET NAMES utf8mb4;

-- ---------- add quotation columns safely ----------
DROP PROCEDURE IF EXISTS mt_add_column;
DELIMITER //
CREATE PROCEDURE mt_add_column(IN tbl VARCHAR(64), IN col VARCHAR(64), IN ddl TEXT)
BEGIN
  IF NOT EXISTS (
    SELECT 1 FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = tbl AND COLUMN_NAME = col
  ) THEN
    SET @s = CONCAT('ALTER TABLE `', tbl, '` ADD COLUMN ', ddl);
    PREPARE stmt FROM @s;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;
  END IF;
END//
DELIMITER ;

CALL mt_add_column('quotations', 'public_token',    '`public_token` VARCHAR(64) NULL');
CALL mt_add_column('quotations', 'public_enabled',  '`public_enabled` TINYINT(1) NOT NULL DEFAULT 1');
CALL mt_add_column('quotations', 'cover_note',      '`cover_note` TEXT NULL');
CALL mt_add_column('quotations', 'inclusions',      '`inclusions` TEXT NULL');
CALL mt_add_column('quotations', 'exclusions',      '`exclusions` TEXT NULL');
CALL mt_add_column('quotations', 'terms',           '`terms` TEXT NULL');
CALL mt_add_column('quotations', 'client_response', '`client_response` VARCHAR(20) NULL');
CALL mt_add_column('quotations', 'client_comment',  '`client_comment` TEXT NULL');
CALL mt_add_column('quotations', 'responded_at',    '`responded_at` DATETIME NULL');

DROP PROCEDURE IF EXISTS mt_add_column;

-- ---------- unique public token index ----------
DROP PROCEDURE IF EXISTS mt_add_index;
DELIMITER //
CREATE PROCEDURE mt_add_index(IN tbl VARCHAR(64), IN idx VARCHAR(64), IN ddl TEXT)
BEGIN
  IF NOT EXISTS (
    SELECT 1 FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = tbl AND INDEX_NAME = idx
  ) THEN
    SET @s = CONCAT('ALTER TABLE `', tbl, '` ADD ', ddl);
    PREPARE stmt FROM @s;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;
  END IF;
END//
DELIMITER ;

CALL mt_add_index('quotations', 'uq_public_token', 'UNIQUE KEY `uq_public_token` (`public_token`)');

DROP PROCEDURE IF EXISTS mt_add_index;

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
