<?php
/**
 * TribalConnect - Assignment 2
 * API endpoint: reads data.json and returns it as a JSON response.
 * No HTML is generated. All output is JSON.
 *
 * Usage:
 *   GET api.php           -> returns full dataset (HTTP 200)
 *   Error reading file    -> HTTP 500 with error message
 */

// Set JSON content type header before any output
header('Content-Type: application/json; charset=utf-8');
// Allow cross-origin requests if needed during local testing
header('Access-Control-Allow-Origin: *');

$dataFile = __DIR__ . '/data.json';

// Check file exists
if (!file_exists($dataFile)) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Data file not found on the server.'
    ]);
    exit;
}

// Read file contents
$raw = file_get_contents($dataFile);
if ($raw === false) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Failed to read data file.'
    ]);
    exit;
}

// Validate that the file contains valid JSON
$data = json_decode($raw, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Data file contains invalid JSON: ' . json_last_error_msg()
    ]);
    exit;
}

// Return the data with HTTP 200
http_response_code(200);
echo json_encode($data);
