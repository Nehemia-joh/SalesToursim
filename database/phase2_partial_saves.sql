-- ============================================================
--  Phase 2 migration: partial saves / resume after sign-in
--  Run this ONCE against an existing database.
--
--  Fresh installs do NOT need this file: schema.sql already
--  contains the partial_saves table.
-- ============================================================

SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS `partial_saves` (
  `id`         INT AUTO_INCREMENT PRIMARY KEY,
  `user_id`    INT NOT NULL,
  `draft_key`  VARCHAR(190) NOT NULL,
  `route`      VARCHAR(255) NOT NULL,
  `label`      VARCHAR(190) NOT NULL DEFAULT '',
  `payload`    MEDIUMTEXT NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY `uq_user_draft` (`user_id`, `draft_key`),
  KEY `idx_user_updated` (`user_id`, `updated_at`),
  CONSTRAINT `fk_partial_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB;
