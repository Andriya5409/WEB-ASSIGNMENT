<?php
/**
 * TribalConnect - Assignment 3
 * PUT /api/items_update.php?id=lesson-001
 * Updates an existing lesson (protected — requires active session).
 *
 * Body: {
 *   "itemType": "lesson",
 *   "title": "...", "subject": "...", "description": "...",
 *   "grade": "...", "date": "YYYY-MM-DD"
 * }
 *
 * Returns:
 *   200 { "status": "updated", "id": "lesson-001" }
 *   400 { "error": "..." }
 *   401 { "error": "..." }
 *   404 { "error": "..." }
 *   500 { "error": "..." }
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Allow-Methods: PUT, OPTIONS');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

session_start();

// --- Auth check ---
if (empty($_SESSION['loggedIn'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized. Please log in first.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed.']);
    exit;
}

$id = isset($_GET['id']) ? trim($_GET['id']) : '';
if ($id === '') {
    http_response_code(400);
    echo json_encode(['error' => 'Missing id parameter in URL.']);
    exit;
}

require_once __DIR__ . '/../db.php';

$raw = file_get_contents('php://input');
$body = json_decode($raw, true);

$itemType = isset($body['itemType']) ? strtolower(trim($body['itemType'])) : '';
$title = isset($body['title']) ? trim($body['title']) : '';
$subject = isset($body['subject']) ? trim($body['subject']) : '';
$description = isset($body['description']) ? trim($body['description']) : '';
$grade = isset($body['grade']) ? trim($body['grade']) : '';

if ($itemType === '' || $title === '' || $subject === '' || $description === '' || $grade === '') {
    http_response_code(400);
    echo json_encode(['error' => 'Missing required fields: itemType, title, subject, description, grade.']);
    exit;
}

try {
    $pdo = getDB();

    if ($itemType === 'lesson') {
        // Check record exists
        $check = $pdo->prepare("SELECT id FROM lessons WHERE lesson_id = ?");
        $check->execute([$id]);
        if (!$check->fetch()) {
            http_response_code(404);
            echo json_encode(['error' => "Lesson with id '$id' not found."]);
            exit;
        }

        $date = isset($body['date']) ? trim($body['date']) : date('Y-m-d');
        $videoUrl = isset($body['videoUrl']) ? trim($body['videoUrl']) : '';

        $update = $pdo->prepare("
            UPDATE lessons
            SET title = ?, subject = ?, description = ?, grade = ?,
                lesson_date = ?, video_url = ?
            WHERE lesson_id = ?
        ");
        $update->execute([$title, $subject, $description, $grade, $date, $videoUrl, $id]);

        http_response_code(200);
        echo json_encode(['status' => 'updated', 'id' => $id, 'type' => 'Lesson']);

    } elseif ($itemType === 'student') {
        $check = $pdo->prepare("SELECT id FROM students WHERE student_id = ?");
        $check->execute([$id]);
        if (!$check->fetch()) {
            http_response_code(404);
            echo json_encode(['error' => "Student with id '$id' not found."]);
            exit;
        }

        $school = isset($body['school']) ? trim($body['school']) : '';
        $village = isset($body['village']) ? trim($body['village']) : '';
        $bio = isset($body['bio']) ? trim($body['bio']) : '';
        $skills = isset($body['skills']) ? trim($body['skills']) : '';

        $update = $pdo->prepare("
            UPDATE students
            SET name = ?, grade = ?, school = ?, village = ?, bio = ?, skills = ?
            WHERE student_id = ?
        ");
        $update->execute([$title, $grade, $school, $village, $bio, $skills, $id]);

        http_response_code(200);
        echo json_encode(['status' => 'updated', 'id' => $id, 'type' => 'Student']);

    } else {
        http_response_code(400);
        echo json_encode(['error' => 'Unsupported itemType. Use "lesson" or "student".']);
    }

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to update record.']);
}
