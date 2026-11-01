-- TribalConnect - Assignment 3
-- MySQL Schema: normalized tables with seed data
-- Run this script once to set up the database.

CREATE DATABASE IF NOT EXISTS tribalconnect
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE tribalconnect;

-- ------------------------------------------------------------
-- Table: teachers
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS teachers (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name        VARCHAR(120) NOT NULL,
  subject     VARCHAR(100) NOT NULL,
  created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- Table: students
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS students (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  student_id  VARCHAR(20)  NOT NULL UNIQUE,   -- e.g. student-001
  name        VARCHAR(120) NOT NULL,
  grade       VARCHAR(30)  NOT NULL,
  school      VARCHAR(150) NOT NULL,
  village     VARCHAR(100) NOT NULL,
  bio         TEXT,
  skills      TEXT,                            -- comma-separated list
  created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- Table: lessons
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS lessons (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  lesson_id   VARCHAR(20)  NOT NULL UNIQUE,   -- e.g. lesson-001
  title       VARCHAR(200) NOT NULL,
  subject     VARCHAR(100) NOT NULL,
  description TEXT         NOT NULL,
  grade       VARCHAR(30)  NOT NULL,
  teacher_id  INT UNSIGNED NOT NULL,
  lesson_date DATE         NOT NULL,
  video_url   VARCHAR(300) DEFAULT '',
  created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (teacher_id) REFERENCES teachers(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- Table: quizzes
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS quizzes (
  id             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  quiz_id        VARCHAR(20)  NOT NULL UNIQUE,
  title          VARCHAR(200) NOT NULL,
  subject        VARCHAR(100) NOT NULL,
  description    TEXT         NOT NULL,
  grade          VARCHAR(30)  NOT NULL,
  question_count TINYINT UNSIGNED NOT NULL DEFAULT 5,
  duration_mins  TINYINT UNSIGNED NOT NULL DEFAULT 10,
  created_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- Table: scholarships
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS scholarships (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  item_id     VARCHAR(20)  NOT NULL UNIQUE,
  title       VARCHAR(200) NOT NULL,
  category    VARCHAR(80)  NOT NULL,           -- 'Scholarship' or 'Skill Course'
  description TEXT         NOT NULL,
  deadline    VARCHAR(60)  NOT NULL,
  eligibility VARCHAR(200) NOT NULL,
  created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- Seed Data
-- ============================================================

INSERT INTO teachers (name, subject) VALUES
  ('Smt. Suma K.',  'Mathematics'),
  ('Sri. Manoj P.', 'Environmental Science'),
  ('Smt. Latha R.', 'Malayalam'),
  ('Sri. Arun V.',  'Science');

INSERT INTO students (student_id, name, grade, school, village, bio, skills) VALUES
  ('student-001', 'Rajan M.',   'Class 5', 'GTHS Edavaka',  'Meppadi',   'A curious learner from Meppadi who enjoys mathematics and drawing.',                   'Mathematics,Drawing,Malayalam Reading'),
  ('student-002', 'Lakshmi A.','Class 4', 'GLPS Poothadi', 'Poothadi',  'Enjoys learning about plants and nature. Active in quiz practice sessions.',           'Environmental Science,Storytelling'),
  ('student-003', 'Vishnu K.', 'Class 6', 'GTHS Edavaka',  'Thirunelly','Interested in science and has completed the water conservation lesson module.',         'Science,Observation,Farming Knowledge'),
  ('student-004', 'Divya P.',  'Class 3', 'GLPS Kalpetta', 'Kalpetta',  'Enthusiastic about Malayalam reading and creative writing.',                            'Malayalam,Creative Writing,Drawing');

INSERT INTO lessons (lesson_id, title, subject, description, grade, teacher_id, lesson_date, video_url) VALUES
  ('lesson-001','Introduction to Natural Numbers',           'Mathematics',           'Learn what natural numbers are, how to count and arrange them in order. Includes simple examples from daily life in Wayanad.',                                                  'Class 5', 1, '2026-10-01', ''),
  ('lesson-002','Our Environment - Forests and Plants',     'Environmental Science', 'Study the importance of forests, types of plants found in Wayanad and the relationship between humans and nature.',                                                              'Class 4', 2, '2026-10-03', ''),
  ('lesson-003','Malayalam Alphabets - Vowels',             'Malayalam',             'Practice writing and reading Malayalam vowels (swarangal). Worksheets available for download.',                                                                                  'Class 3', 3, '2026-10-05', ''),
  ('lesson-004','Simple Addition and Subtraction',          'Mathematics',           'Practice adding and subtracting two-digit numbers using the column method. Exercises included with answers.',                                                                    'Class 4', 1, '2026-10-07', ''),
  ('lesson-005','Our Body - Organs and Their Functions',    'Science',               'Learn about the major organs of the human body - heart, lungs, stomach - and what they do to keep us healthy.',                                                                  'Class 5', 4, '2026-10-08', ''),
  ('lesson-006','Water Conservation and Rain',              'Environmental Science', 'Understand the water cycle, importance of rain, and how tribal communities in Wayanad conserve water traditionally.',                                                            'Class 6', 2, '2026-10-10', '');

INSERT INTO quizzes (quiz_id, title, subject, description, grade, question_count, duration_mins) VALUES
  ('quiz-001','Math Quiz - Natural Numbers',    'Mathematics',           'Test your understanding of natural numbers, ordering and basic counting.',                           'Class 5', 10, 15),
  ('quiz-002','EVS Quiz - Forests and Plants',  'Environmental Science', 'Answer questions about forests, plants and environmental conservation based on Lesson 002.',         'Class 4',  8, 12),
  ('quiz-003','Malayalam Vowels Practice',      'Malayalam',             'Identify and match Malayalam vowels in this short quiz.',                                             'Class 3',  6, 10),
  ('quiz-004','Science Quiz - Human Body',      'Science',               'Questions on major body organs, their names and basic functions.',                                   'Class 5', 10, 15);

INSERT INTO scholarships (item_id, title, category, description, deadline, eligibility) VALUES
  ('scholarship-001','ST Pre-Matric Scholarship',          'Scholarship',  'Central government scholarship for Scheduled Tribe students studying in Class 1 to 10. Covers tuition, hostel and book allowances.', '2026-11-30',      'Class 1-10 ST Students'),
  ('scholarship-002','Kerala Tribal Welfare Scholarship',  'Scholarship',  'State government scholarship for tribal students in Kerala. Apply through the ITDP office or tribal welfare portal.',               '2026-12-15',      'Tribal Students, Kerala'),
  ('scholarship-003','Basic Computer Skills for Beginners','Skill Course', 'Free 10-hour online course covering basic computer operations, the internet and typing. Available in Malayalam.',                    'Open Enrollment', 'All students, Class 5 and above'),
  ('scholarship-004','Spoken English for Rural Learners',  'Skill Course', 'A beginner-friendly spoken English course designed for students in rural and tribal areas. Audio-based learning included.',          'Open Enrollment', 'Class 6 and above');
