-- ---------------------------------------------------------------------
--  Create the first administrator when you import schema.sql only.
--  Do NOT run this if you imported seed.sql (it already has an admin).
--
--  Change the email and password below, then run this in phpMyAdmin
--  (SQL tab) or with the mysql client.
--
--  The hash format matches the application:
--      password_hash = sha256(salt . password)
-- ---------------------------------------------------------------------

SET @salt = SUBSTRING(MD5(RAND()), 1, 32);
SET @admin_email = 'admin@yourdomain.com';
SET @admin_password = 'ChangeMe123!';

INSERT INTO users (name, email, salt, password_hash, role, active)
VALUES (
  'Administrator',
  @admin_email,
  @salt,
  SHA2(CONCAT(@salt, @admin_password), 256),
  'admin',
  1
);
