<?php
// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header("Access-Control-Allow-Origin: http://127.0.0.1:5501");
    header("Access-Control-Allow-Methods: POST, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type");
    header("Access-Control-Allow-Credentials: true");
    header("Access-Control-Max-Age: 86400"); // Cache preflight response for 24 hours
    http_response_code(204); // No Content
    exit;
}

// Set CORS headers for the actual request
header("Access-Control-Allow-Origin: http://127.0.0.1:5501");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json; charset=UTF-8");

// Start output buffering to prevent unwanted output
ob_start();

// Ensure no PHP errors are displayed in the output
ini_set('display_errors', 0);

// Database configuration
$host = 'localhost';
$dbname = 'medisync';
$username = 'root';
$password = '';

// Response array to store success status and message
$response = [];

try {
    // Establish database connection using PDO
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Read JSON input from the request
    $input = json_decode(file_get_contents('php://input'), true);

    // Validate input data
    if (!isset($input['email']) || !isset($input['password'])) {
        $response = [
            'success' => false,
            'message' => 'Email and password are required.'
        ];
        http_response_code(400);
        echo json_encode($response);
        exit;
    }

    $email = htmlspecialchars(trim($input['email']));
    $password = trim($input['password']);

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response = [
            'success' => false,
            'message' => 'Invalid email format.'
        ];
        http_response_code(400);
        echo json_encode($response);
        exit;
    }

    // Check if the user exists in the database
    $stmt = $pdo->prepare("SELECT password FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        // Compare the provided plain-text password with the stored plain-text password
        if ($password === $user['password']) {
            $response = [
                'success' => true,
                'message' => 'Password verified successfully.'
            ];
            http_response_code(200);
        } else {
            $response = [
                'success' => false,
                'message' => 'Incorrect password.'
            ];
            http_response_code(401);
        }
    } else {
        $response = [
            'success' => false,
            'message' => 'User not found.'
        ];
        http_response_code(404);
    }
} catch (PDOException $e) {
    // Handle database errors
    $response = [
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ];
    http_response_code(500);
} catch (Exception $e) {
    // Handle other errors
    $response = [
        'success' => false,
        'message' => 'An error occurred: ' . $e->getMessage()
    ];
    http_response_code(500);
}

// Clear the output buffer to ensure only JSON is sent
ob_end_clean();

// Send the JSON response
echo json_encode($response);
