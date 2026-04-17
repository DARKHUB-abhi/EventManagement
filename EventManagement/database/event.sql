CREATE DATABASE IF NOT EXISTS event_management DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE event_management;

CREATE TABLE IF NOT EXISTS users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('student', 'admin') NOT NULL DEFAULT 'student'
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS events (
    event_id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(150) NOT NULL,
    date DATE NOT NULL,
    capacity INT NOT NULL DEFAULT 0,
    available_seats INT NOT NULL DEFAULT 0
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS registrations (
    registration_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    event_id INT NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (event_id) REFERENCES events(event_id) ON DELETE CASCADE,
    UNIQUE KEY unique_registration (user_id, event_id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS feedback (
    feedback_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    event_id INT NOT NULL,
    rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    comment TEXT,
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (event_id) REFERENCES events(event_id) ON DELETE CASCADE,
    UNIQUE KEY unique_feedback (user_id, event_id)
) ENGINE=InnoDB;

INSERT IGNORE INTO users (name, email, password, role)
VALUES ('Admin User', 'admin@example.com', 'admin123', 'admin');

INSERT IGNORE INTO events (title, date, capacity, available_seats)
VALUES
    ('Campus Welcome Party', '2026-05-10', 80, 80),
    ('Guest Lecture: Future of Tech', '2026-05-15', 50, 50),
    ('Career Fair', '2026-06-01', 100, 100);
