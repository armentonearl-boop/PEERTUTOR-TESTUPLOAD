-- PeerTutor database
-- How to use: open phpMyAdmin > Import > choose this file > Go
-- (or paste it in the SQL tab)

CREATE DATABASE IF NOT EXISTS peertutor;
USE peertutor;

-- everybody who can log in
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id VARCHAR(20) NOT NULL UNIQUE,
    full_name VARCHAR(100) NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin','tutor','tutee') NOT NULL,
    approved TINYINT(1) NOT NULL DEFAULT 1,   -- tutors start at 0 until the teacher approves
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE subjects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(80) NOT NULL UNIQUE
);

-- which subjects a tutor can teach and how good they are at it
CREATE TABLE tutor_subjects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tutor_id INT NOT NULL,
    subject_id INT NOT NULL,
    skill_level ENUM('Beginner','Intermediate','Advanced') NOT NULL DEFAULT 'Intermediate',
    UNIQUE (tutor_id, subject_id),
    FOREIGN KEY (tutor_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE
);

-- free time of tutors. day_of_week: 1 = Monday ... 7 = Sunday
CREATE TABLE availability (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tutor_id INT NOT NULL,
    day_of_week TINYINT NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    FOREIGN KEY (tutor_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tutee_id INT NOT NULL,
    tutor_id INT NOT NULL,
    subject_id INT NOT NULL,
    session_date DATE NOT NULL,
    session_time TIME NOT NULL,
    status ENUM('pending','accepted','declined','completed','cancelled','no_show') NOT NULL DEFAULT 'pending',
    notes TEXT,                                -- tutor writes this after the session
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (tutee_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (tutor_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE
);

CREATE TABLE ratings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    session_id INT NOT NULL UNIQUE,
    score TINYINT NOT NULL,                    -- 1 to 5
    comment VARCHAR(255),
    FOREIGN KEY (session_id) REFERENCES sessions(id) ON DELETE CASCADE
);

-- what the users did (login, booking, approving...). Used for the activity log report
CREATE TABLE activity_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    action VARCHAR(50) NOT NULL,
    details VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- some starting subjects
INSERT INTO subjects (name) VALUES
('Mathematics'), ('English'), ('Science'), ('Programming'), ('Filipino');

-- default teacher account -> student ID: admin   password: admin123
-- (please change the password after the first login!)
INSERT INTO users (student_id, full_name, password, role, approved) VALUES
('admin', 'Teacher / Coordinator', '$2y$10$1vCipXhyChiDxMerJ2LtLe8DaGVkhJto1fnwPn3.NVNrt8EaJQOq.', 'admin', 1);
