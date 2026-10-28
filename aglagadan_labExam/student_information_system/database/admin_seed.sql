-- Optional initial administrator seed for phpMyAdmin imports.
-- Import this AFTER schema.sql. Login: admin / admin123.
-- Change the default password before storing real student records.
INSERT INTO users (username, password_hash, role)
VALUES ('admin', '$2y$12$m2tAqRtztJxZoCwrxi1Al.P8kZwk6EW58SbpTh5uKBb6p8S7rm6im', 'admin')
ON DUPLICATE KEY UPDATE username = VALUES(username);
