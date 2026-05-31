-- ============================================================
-- Migration: Add columns and tables required by realignment plan
-- Run this file once against the bfpprofiler database
-- ============================================================

-- 1. Add email verification columns to user table
ALTER TABLE `user`
  ADD COLUMN IF NOT EXISTS `verification_token` VARCHAR(64) DEFAULT NULL AFTER `status`,
  ADD COLUMN IF NOT EXISTS `token_expiry` DATETIME DEFAULT NULL AFTER `verification_token`;

-- 2. Add endorsement_status to reports table (Chief review workflow)
ALTER TABLE `reports`
  ADD COLUMN IF NOT EXISTS `endorsement_status` VARCHAR(20) DEFAULT 'pending'
    COMMENT 'pending, endorsed, rejected' AFTER `compliance_status`,
  ADD COLUMN IF NOT EXISTS `endorsement_notes` TEXT DEFAULT NULL AFTER `endorsement_status`,
  ADD COLUMN IF NOT EXISTS `endorsed_by` INT(11) DEFAULT NULL AFTER `endorsement_notes`,
  ADD COLUMN IF NOT EXISTS `endorsed_at` DATETIME DEFAULT NULL AFTER `endorsed_by`;

-- FK for endorsed_by (drop first if exists, then re-add to avoid duplicate key error)
ALTER TABLE `reports` DROP FOREIGN KEY IF EXISTS `fk_reports_endorsed_by`;
ALTER TABLE `reports`
  ADD CONSTRAINT `fk_reports_endorsed_by`
    FOREIGN KEY (`endorsed_by`) REFERENCES `user` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

-- 3. Add expiry_date to certificates table
ALTER TABLE `certificates`
  ADD COLUMN IF NOT EXISTS `expiry_date` DATE DEFAULT NULL
    COMMENT 'One year from authorized_at' AFTER `certificate_number`;

-- 4. Create documents table for establishment document uploads
CREATE TABLE IF NOT EXISTS `documents` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `establishment_id` INT(11) NOT NULL,
  `owner_id` INT(11) NOT NULL,
  `document_type` VARCHAR(100) NOT NULL COMMENT 'e.g. Business Permit, BIR Certificate, Floor Plan',
  `filename` VARCHAR(255) NOT NULL COMMENT 'Stored filename on server',
  `original_name` VARCHAR(255) NOT NULL COMMENT 'Original uploaded filename',
  `file_size` INT(11) DEFAULT NULL COMMENT 'File size in bytes',
  `status` VARCHAR(20) NOT NULL DEFAULT 'pending' COMMENT 'pending, approved, rejected',
  `review_notes` TEXT DEFAULT NULL,
  `reviewed_by` INT(11) DEFAULT NULL,
  `reviewed_at` DATETIME DEFAULT NULL,
  `createdAt` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_documents_establishment` (`establishment_id`),
  KEY `fk_documents_owner` (`owner_id`),
  KEY `fk_documents_reviewed_by` (`reviewed_by`),
  CONSTRAINT `fk_documents_establishment` FOREIGN KEY (`establishment_id`) REFERENCES `establishment` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_documents_owner` FOREIGN KEY (`owner_id`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_documents_reviewed_by` FOREIGN KEY (`reviewed_by`) REFERENCES `user` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 5. Add payment_confirmed_by to inspection (track who confirmed payment)
ALTER TABLE `inspection`
  ADD COLUMN IF NOT EXISTS `payment_confirmed_by` INT(11) DEFAULT NULL
    COMMENT 'CRO user id who confirmed payment' AFTER `payment`,
  ADD COLUMN IF NOT EXISTS `payment_confirmed_at` DATETIME DEFAULT NULL AFTER `payment_confirmed_by`;

ALTER TABLE `inspection` DROP FOREIGN KEY IF EXISTS `fk_inspection_payment_confirmed_by`;
ALTER TABLE `inspection`
  ADD CONSTRAINT `fk_inspection_payment_confirmed_by`
    FOREIGN KEY (`payment_confirmed_by`) REFERENCES `user` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

-- ============================================================
-- 6. Role Consolidation: Remove FireMarshal and Accessor roles
--    - FireMarshal functionality is now handled by Admin
--    - Accessor functionality is now handled by CRO
-- Run this section to migrate existing users to the new roles
-- ============================================================

-- Migrate all existing FireMarshal users → admin
UPDATE `user`
  SET `role` = 'admin'
  WHERE LOWER(`role`) IN ('firemarshal', 'fire marshal', 'fire marsh');

-- Migrate all existing Accessor users → CRO
UPDATE `user`
  SET `role` = 'CRO'
  WHERE LOWER(`role`) = 'accessor';

-- Update the role column comment to reflect the new allowed roles
ALTER TABLE `user`
  MODIFY COLUMN `role` VARCHAR(50) NOT NULL
    COMMENT 'admin, inspector, owner, Chief, CRO';

-- ============================================================
-- 7. Default accounts per role
--    Run the SELECT first to see existing credentials.
--    INSERT statements only add an account if NO user of that
--    role exists yet — they will never overwrite existing data.
--    Passwords stored as plain text here; login.php auto-upgrades
--    them to bcrypt hash on the very first successful login.
--    ⚠  CHANGE ALL PASSWORDS after first login!
-- ============================================================

-- View all current user accounts and their credentials
SELECT
  id,
  role,
  username,
  email,
  IF(password LIKE '$2y$%', '[bcrypt – use reset to recover]', password) AS password_or_note,
  status
FROM `user`
ORDER BY FIELD(role,'admin','Chief','CRO','inspector','owner'), username;

-- Seed: admin (if no admin exists)
INSERT INTO `user` (fullname, username, email, password, address, phone_number, role, status)
SELECT 'System Administrator','admin','admin@bfp.gov.ph','Admin@123','BFP Headquarters','09000000000','admin','active'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `user` WHERE LOWER(role) = 'admin' LIMIT 1);

-- Seed: Chief (if no Chief exists)
INSERT INTO `user` (fullname, username, email, password, address, phone_number, role, status)
SELECT 'BFP Chief','chief','chief@bfp.gov.ph','Chief@123','BFP Office','09100000000','Chief','active'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `user` WHERE LOWER(role) = 'chief' LIMIT 1);

-- Seed: CRO (if no CRO exists)
INSERT INTO `user` (fullname, username, email, password, address, phone_number, role, status)
SELECT 'CRO Officer','cro01','cro@bfp.gov.ph','Cro@123','BFP Office','09200000000','CRO','active'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `user` WHERE LOWER(role) = 'cro' LIMIT 1);

-- Seed: inspector (if no inspector exists)
INSERT INTO `user` (fullname, username, email, password, address, phone_number, role, status)
SELECT 'BFP Inspector','inspector01','inspector@bfp.gov.ph','Inspector@123','BFP Office','09300000000','inspector','active'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `user` WHERE LOWER(role) = 'inspector' LIMIT 1);

-- Seed: owner (if no owner exists)
INSERT INTO `user` (fullname, username, email, password, address, phone_number, role, status)
SELECT 'Sample Owner','owner01','owner@bfp.gov.ph','Owner@123','Sample Address','09400000000','owner','active'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `user` WHERE LOWER(role) = 'owner' LIMIT 1);

-- ============================================================
-- 8. Add missing columns to establishment table
--    These fields exist in the Add/Edit Establishment form
--    but were never persisted to the database.
-- ============================================================
ALTER TABLE `establishment`
  ADD COLUMN IF NOT EXISTS `ownership_type`  VARCHAR(100)  DEFAULT NULL AFTER `type`,
  ADD COLUMN IF NOT EXISTS `tin_number`      VARCHAR(50)   DEFAULT NULL AFTER `ownership_type`,
  ADD COLUMN IF NOT EXISTS `contact_number`  VARCHAR(50)   DEFAULT NULL AFTER `tin_number`,
  ADD COLUMN IF NOT EXISTS `contact_email`   VARCHAR(255)  DEFAULT NULL AFTER `contact_number`;
