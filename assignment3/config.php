<?php
/**
 * TribalConnect - Assignment 3
 * Database configuration constants.
 * Update these values to match your MySQL server settings.
 */

define('DB_HOST', 'localhost');
define('DB_NAME', 'tribalconnect');
define('DB_USER', 'root');       // Change to your MySQL username
define('DB_PASS', '');           // Change to your MySQL password
define('DB_CHARSET', 'utf8mb4');

// Hardcoded teacher credentials (per spec: no hashing, no registration)
define('TEACHER_USERNAME', 'teacher');
define('TEACHER_PASSWORD', 'pass123');
