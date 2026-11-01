<?php
/**
 * TribalConnect - Assignment 3
 * POST /api/login.php
 * Body: { "username": "teacher", "password": "pass123" }
 *
 * Returns:
 *   200 { "status": "ok", "message": "Login successful" }
 *   400 { "error": "Missing credentials" }
 *   401 { "error": "Invalid username or password" }
 *
 * No HTML generated. JSON only.
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Allow-Methods: POST, OPTIONS');

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed. Use POST.']);
    exit;
}

require_once __DIR__ . '/../config.php';

session_start();

// Parse JSON body
$raw = file_get_contents('php://input');
$body = json_decode($raw, true);

$username = isset($body['username']) ? trim($body['username']) : '';
$password = isset($body['password']) ? trim($body['password']) : '';

if ($username === '' || $password === '') {
    http_response_code(400);
    echo json_encode(['error' => 'Missing credentials. Provide username and password.']);
    exit;
}

// Check credentials (per spec: no hashing, basic session check only)
if ($username === TEACHER_USERNAME && $password === TEACHER_PASSWORD) {
    $_SESSION['user'] = $username;
    $_SESSION['loggedIn'] = true;

    http_response_code(200);
    echo json_encode(['status' => 'ok', 'message' => 'Login successful.']);
} else {
    http_response_code(401);
    echo json_encode(['error' => 'Invalid username or password.']);
}
