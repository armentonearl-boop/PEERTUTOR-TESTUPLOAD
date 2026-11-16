-- Run this ONCE if you already imported the old database.sql
-- phpMyAdmin > click "peertutor" on the left > Import > choose this file > Import
-- (If you import the new database.sql from scratch you do NOT need this file.)

USE peertutor;

CREATE TABLE IF NOT EXISTS activity_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    action VARCHAR(50) NOT NULL,
    details VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
