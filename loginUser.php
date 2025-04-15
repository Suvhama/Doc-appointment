<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Set Content-Type header for JSON response
header('Content-Type: application/json');

// CORS Headers (configure for your environment)
header("Access-Control-Allow-Origin: *"); //ONLY for testing - REPLACE with specific origin in production
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Handle OPTIONS request
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Database credentials
$host = 'localhost';
$dbName = 'medisync';
$username = 'root';
$password = '';

try {
    // Create PDO connection
    $pdo = new PDO("mysql:host=$host;dbname=$dbName", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // Log the database connection error for debugging
    error_log("Database connection error: " . $e->getMessage());

    // Send a generic error message to the client (avoid exposing sensitive information)
    echo json_encode(['success' => false, 'message' => 'An error occurred. Please try again.']);  //Generic message
    exit;
}

// Process POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get JSON data from the request body
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    // Check if JSON was decoded successfully
    if ($data === null) {
        $jsonError = json_last_error_msg();
        error_log("JSON decode error: " . $jsonError); // Log the JSON error
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid data. Please check your input.']);//Generic message
        exit;
    }

    // Get email and password from JSON data, with fallback to empty string
    $email = isset($data['email']) ? trim($data['email']) : '';
    $password = isset($data['password']) ? $data['password'] : '';

    // Validate input
    if (empty($email) || empty($password)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Email and password are required.']);//User message
        exit;
    }

    try {
        // Prepare and execute SQL query
        $stmt = $pdo->prepare("SELECT id, first_name, last_name, password FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Check if user exists
        if ($user) {
            // Verify password
            if ($password == $user['password']) { 

                // Login successful - Prepare user data
                $response = [
                    'success' => true,
                    'message' => 'Login successful!',
                    'firstName' => $user['first_name'],
                    'lastName' => $user['last_name'],
                    'userId' => $user['id']
                ];

                // Output JSON data
                echo json_encode($response);

            } else {
                // Log the password verification failure
                error_log("Password verification failed for user: " . $email); //internal logging
                http_response_code(401);
                echo json_encode(['success' => false, 'message' => 'Invalid password.']);//User message
            }
        } else {
            // Log that the user wasn't found
            error_log("User not found with email: " . $email); // Internal log
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Invalid email or password.']);//User Message
        }
    } catch (PDOException $e) {
        // Log the database exception
        error_log("Database error during login: " . $e->getMessage());

        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'An error occurred. Please try again.']); //generic message
    }
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'An error occurred. Please try again.']); //Generic message
}
?>