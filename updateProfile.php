<?php
// Enable CORS
header('Access-Control-Allow-Origin: http://127.0.0.1:5501'); // Allow requests from your frontend origin
header('Access-Control-Allow-Methods: POST, OPTIONS'); // Allow POST and OPTIONS methods
header('Access-Control-Allow-Headers: Content-Type'); // Allow Content-Type header
header('Access-Control-Allow-Credentials: true'); // Allow credentials

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Disable displaying errors to prevent corrupting JSON (use in production or temporarily)
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);

// Set the content type to JSON
header('Content-Type: application/json');

// Database connection configuration
$host = 'localhost';
$dbname = 'medisync'; // Replace with your database name
$username = 'root';   // Replace with your database username
$password = '';       // Replace with your database password

try {
    // Create a PDO connection
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . $e->getMessage()]);
    exit;
}

// Read the raw POST data
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// Validate input
if (!isset($data['id']) || !isset($data['first_name']) || !isset($data['last_name']) || !isset($data['email'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

// Sanitize and validate inputs
$userId = filter_var($data['id'], FILTER_VALIDATE_INT);
$firstName = htmlspecialchars($data['first_name'], ENT_QUOTES, 'UTF-8');
$lastName = htmlspecialchars($data['last_name'], ENT_QUOTES, 'UTF-8');
$email = filter_var($data['email'], FILTER_SANITIZE_EMAIL);
$phoneNumber = isset($data['phone_number']) ? htmlspecialchars($data['phone_number'], ENT_QUOTES, 'UTF-8') : null;

if (!$userId || !$firstName || !$lastName || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid input data']);
    exit;
}

// Check if email is already in use by another user
try {
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
    $stmt->execute([$email, $userId]);
    if ($stmt->fetch()) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Email is already in use']);
        exit;
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error checking email: ' . $e->getMessage()]);
    exit;
}

// Update user data in the database
try {
    $sql = "UPDATE users SET first_name = ?, last_name = ?, email = ?, phone_number = ? WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$firstName, $lastName, $email, $phoneNumber, $userId]);

    // Check if any rows were affected
    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true, 'message' => 'Profile updated successfully']);
    } else {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'No changes made or user not found']);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error updating profile: ' . $e->getMessage()]);
    exit;
}

// Close the database connection
$pdo = null;
exit;
?>