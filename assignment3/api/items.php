<?php
/**
 * TribalConnect - Assignment 3
 * GET /api/items.php          -> all records (200)
 * GET /api/items.php?q=word   -> search results (200 or 404)
 * No HTML generated. JSON only.
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../db.php';

$pdo = getDB();
$query = isset($_GET['q']) ? trim($_GET['q']) : '';

try {

    if ($query !== '') {
        // ---- Searched fetch ----
        $like = '%' . $query . '%';

        $lessons = $pdo->prepare("
            SELECT l.lesson_id AS id, 'Lesson' AS type,
                   l.title, l.subject, l.description, l.grade,
                   t.name AS teacher,
                   DATE_FORMAT(l.lesson_date,'%Y-%m-%d') AS date,
                   l.video_url AS videoUrl
            FROM lessons l
            JOIN teachers t ON t.id = l.teacher_id
            WHERE l.title LIKE ? OR l.subject LIKE ? OR l.description LIKE ? OR l.grade LIKE ?
        ");
        $lessons->execute([$like, $like, $like, $like]);

        $quizzes = $pdo->prepare("
            SELECT quiz_id AS id, 'Quiz' AS type,
                   title, subject, description, grade,
                   question_count AS questionCount,
                   duration_mins  AS duration
            FROM quizzes
            WHERE title LIKE ? OR subject LIKE ? OR description LIKE ? OR grade LIKE ?
        ");
        $quizzes->execute([$like, $like, $like, $like]);

        $students = $pdo->prepare("
            SELECT student_id AS id, 'Student' AS type,
                   name, grade, school, village, bio, skills
            FROM students
            WHERE name LIKE ? OR grade LIKE ? OR school LIKE ? OR village LIKE ? OR bio LIKE ?
        ");
        $students->execute([$like, $like, $like, $like, $like]);

        $scholarships = $pdo->prepare("
            SELECT item_id AS id, category AS type,
                   title, category, description, deadline, eligibility
            FROM scholarships
            WHERE title LIKE ? OR category LIKE ? OR description LIKE ? OR eligibility LIKE ?
        ");
        $scholarships->execute([$like, $like, $like, $like]);

    } else {
        // ---- Full fetch ----
        $lessons = $pdo->query("
            SELECT l.lesson_id AS id, 'Lesson' AS type,
                   l.title, l.subject, l.description, l.grade,
                   t.name AS teacher,
                   DATE_FORMAT(l.lesson_date,'%Y-%m-%d') AS date,
                   l.video_url AS videoUrl
            FROM lessons l
            JOIN teachers t ON t.id = l.teacher_id
        ");
        $quizzes = $pdo->query("
            SELECT quiz_id AS id, 'Quiz' AS type,
                   title, subject, description, grade,
                   question_count AS questionCount,
                   duration_mins  AS duration
            FROM quizzes
        ");
        $students = $pdo->query("
            SELECT student_id AS id, 'Student' AS type,
                   name, grade, school, village, bio, skills
            FROM students
        ");
        $scholarships = $pdo->query("
            SELECT item_id AS id, category AS type,
                   title, category, description, deadline, eligibility
            FROM scholarships
        ");
    }

    $graph = array_merge(
        $lessons->fetchAll(),
        $quizzes->fetchAll(),
        $students->fetchAll(),
        $scholarships->fetchAll()
    );

    // Post-process: skills string -> array, questionCount/duration -> int
    $graph = array_map(function ($item) {
        if (isset($item['skills']) && is_string($item['skills'])) {
            $item['skills'] = array_map('trim', explode(',', $item['skills']));
        }
        if (isset($item['questionCount']))
            $item['questionCount'] = (int) $item['questionCount'];
        if (isset($item['duration']))
            $item['duration'] = (int) $item['duration'];
        return $item;
    }, $graph);

    if (empty($graph) && $query !== '') {
        http_response_code(404);
        echo json_encode(['graph' => [], 'message' => 'No results found for "' . htmlspecialchars($query) . '"']);
        exit;
    }

    http_response_code(200);
    echo json_encode(['graph' => $graph]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database query failed.']);
}
