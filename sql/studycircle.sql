DROP DATABASE IF EXISTS studycircle_db;
CREATE DATABASE studycircle_db;
USE studycircle_db;

CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  full_name VARCHAR(100) NOT NULL,
  email VARCHAR(100) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  role ENUM('admin','student') DEFAULT 'student',
  status ENUM('active','blocked') DEFAULT 'active',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE subjects (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL
);

CREATE TABLE materials (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  subject_id INT NOT NULL,
  title VARCHAR(150) NOT NULL,
  description TEXT,
  file_name VARCHAR(255) NOT NULL,
  status ENUM('pending','approved','rejected') DEFAULT 'pending',
  downloads INT DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE
);

CREATE TABLE reviews (
  id INT AUTO_INCREMENT PRIMARY KEY,
  material_id INT NOT NULL,
  user_id INT NOT NULL,
  rating INT NOT NULL,
  comment TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (material_id) REFERENCES materials(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE questions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  subject_id INT NOT NULL,
  title VARCHAR(200) NOT NULL,
  body TEXT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE
);

CREATE TABLE answers (
  id INT AUTO_INCREMENT PRIMARY KEY,
  question_id INT NOT NULL,
  user_id INT NOT NULL,
  body TEXT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (question_id) REFERENCES questions(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE study_groups (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  description TEXT,
  created_by INT NOT NULL,
  FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE group_members (
  id INT AUTO_INCREMENT PRIMARY KEY,
  group_id INT NOT NULL,
  user_id INT NOT NULL,
  FOREIGN KEY (group_id) REFERENCES study_groups(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE study_sessions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  group_id INT NOT NULL,
  topic VARCHAR(150) NOT NULL,
  session_date DATE NOT NULL,
  session_time TIME NOT NULL,
  location VARCHAR(150),
  meeting_link VARCHAR(500) DEFAULT NULL,
  FOREIGN KEY (group_id) REFERENCES study_groups(id) ON DELETE CASCADE
);

INSERT INTO subjects (name) VALUES ('Web Development'), ('Databases'), ('Programming'), ('Mathematics');
CREATE TABLE IF NOT EXISTS student_profiles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL UNIQUE,
    full_name VARCHAR(100) NOT NULL,
    student_id VARCHAR(30) NOT NULL,
    degree VARCHAR(100) NOT NULL,
    academic_year VARCHAR(30) NOT NULL,
    bio VARCHAR(500),
    photo VARCHAR(255),
    phone VARCHAR(20),
    interests VARCHAR(255),
    skills VARCHAR(255),
    profile_link VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_student_profiles_user
        FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE CASCADE
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS academic_events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    event_type ENUM(
        'Exam',
        'Assignment',
        'Academic Event',
        'Important Date'
    ) NOT NULL,
    event_date DATE NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
INSERT INTO academic_events
(title, event_type, event_date, description)
SELECT 'Sample Exam', 'Exam', '2026-11-10', 'Demo exam event'
WHERE NOT EXISTS (
    SELECT 1 FROM academic_events
    WHERE title = 'Sample Exam'
);

INSERT INTO academic_events
(title, event_type, event_date, description)
SELECT 'Sample Assignment', 'Assignment', '2026-11-15', 'Demo assignment deadline'
WHERE NOT EXISTS (
    SELECT 1 FROM academic_events
    WHERE title = 'Sample Assignment'
);

INSERT INTO academic_events
(title, event_type, event_date, description)
SELECT 'Sample Academic Event', 'Academic Event', '2026-11-20', 'Demo academic event'
WHERE NOT EXISTS (
    SELECT 1 FROM academic_events
    WHERE title = 'Sample Academic Event'
);

INSERT INTO academic_events
(title, event_type, event_date, description)
SELECT 'Semester Registration Deadline', 'Important Date', '2026-11-25', 'Last date for semester registration'
WHERE NOT EXISTS (
    SELECT 1 FROM academic_events
    WHERE title = 'Semester Registration Deadline'
);