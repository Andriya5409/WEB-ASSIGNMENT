<?php
/**
 * TribalConnect - Assignment 3
 * POST /api/items_create.php
 * Creates a new lesson (protected — requires active session).
 *
 * Body: {
 *   "itemType": "lesson",
 *   "title": "...", "subject": "...", "description": "...",
 *   "grade": "...", "teacher": "...", "date": "YYYY-MM-DD"
 * }
 *
 * Returns:
 *   201 { "status": "created", "id": "lesson-XXX" }
 *   400 { "error": "..." }  — missing/invalid fields
 *   401 { "error": "..." }  — not authenticated
 *   500 { "error": "..." }  — DB failure
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Allow-Methods: POST, OPTIONS');

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

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed.']);
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

// --- Validate required fields ---
if ($itemType === '' || $title === '' || $subject === '' || $description === '' || $grade === '') {
    http_response_code(400);
    echo json_encode(['error' => 'Missing required fields: itemType, title, subject, description, grade.']);
    exit;
}

try {
    $pdo = getDB();

    if ($itemType === 'lesson') {
        $teacher = isset($body['teacher']) ? trim($body['teacher']) : '';
        $date = isset($body['date']) ? trim($body['date']) : date('Y-m-d');
        $videoUrl = isset($body['videoUrl']) ? trim($body['videoUrl']) : '';

        if ($teacher === '') {
            http_response_code(400);
            echo json_encode(['error' => 'Teacher name is required for lessons.']);
            exit;
        }

        // Resolve or create teacher
        $stmt = $pdo->prepare("SELECT id FROM teachers WHERE name = ?");
        $stmt->execute([$teacher]);
        $teacherRow = $stmt->fetch();

        if ($teacherRow) {
            $teacherId = $teacherRow['id'];
        } else {
            $ins = $pdo->prepare("INSERT INTO teachers (name, subject) VALUES (?, ?)");
            $ins->execute([$teacher, $subject]);
            $teacherId = $pdo->lastInsertId();
        }

        // Generate next lesson_id
        $maxRow = $pdo->query("SELECT MAX(id) AS maxId FROM lessons")->fetch();
        $nextNum = ($maxRow['maxId'] ?? 0) + 1;
        $lessonId = 'lesson-' . str_pad($nextNum, 3, '0', STR_PAD_LEFT);

        $insert = $pdo->prepare("
            INSERT INTO lessons (lesson_id, title, subject, description, grade, teacher_id, lesson_date, video_url)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $insert->execute([$lessonId, $title, $subject, $description, $grade, $teacherId, $date, $videoUrl]);

        http_response_code(201);
        echo json_encode(['status' => 'created', 'id' => $lessonId, 'type' => 'Lesson']);

    } elseif ($itemType === 'student') {
        $school = isset($body['school']) ? trim($body['school']) : '';
        $village = isset($body['village']) ? trim($body['village']) : '';
        $bio = isset($body['bio']) ? trim($body['bio']) : '';
        $skills = isset($body['skills']) ? trim($body['skills']) : '';

        if ($school === '' || $village === '') {
            http_response_code(400);
            echo json_encode(['error' => 'School and village are required for students.']);
            exit;
        }

        $maxRow = $pdo->query("SELECT MAX(id) AS maxId FROM students")->fetch();
        $nextNum = ($maxRow['maxId'] ?? 0) + 1;
        $studentId = 'student-' . str_pad($nextNum, 3, '0', STR_PAD_LEFT);

        $insert = $pdo->prepare("
            INSERT INTO students (student_id, name, grade, school, village, bio, skills)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $insert->execute([$studentId, $title, $grade, $school, $village, $bio, $skills]);

        http_response_code(201);
        echo json_encode(['status' => 'created', 'id' => $studentId, 'type' => 'Student']);

    } else {
        http_response_code(400);
        echo json_encode(['error' => 'Unsupported itemType. Use "lesson" or "student".']);
    }

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to create record.']);
}
