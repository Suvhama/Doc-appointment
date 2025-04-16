x
<?php
// Start session to access session variables
session_start();


header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS"); // Changed to GET to match JS
header("Access-Control-Allow-Headers: Content-Type, Authorization"); // Added Authorization header


// Database connection details
$servername = "localhost";
$username = "root";
$dbpassword = "";
$dbname = "medisync";

// Set header to return JSON response
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'User not authenticated'
    ]);
    exit;
}

// Get user ID from session
$userId = $_SESSION['id'];

// Check if it's a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
    exit;
}

// Get POST data
$postData = json_decode(file_get_contents('php://input'), true);

// Validate required fields
if (!isset($postData['first_name']) || !isset($postData['last_name']) || !isset($postData['email'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Required fields are missing'
    ]);
    exit;
}

// Extract data
$firstName = $postData['first_name'];
$lastName = $postData['last_name'];
$email = $postData['email'];
$phone = $postData['phone_number'];


try {
    // Create connection
    $conn = new mysqli($servername, $username, $dbpassword, $dbname);

    // Check connection
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    // Check if email already exists for another user
    $checkEmailStmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
    $checkEmailStmt->bind_param("si", $email, $userId);
    $checkEmailStmt->execute();
    $checkResult = $checkEmailStmt->get_result();

    if ($checkResult->num_rows > 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Email already in use by another account'
        ]);
        $checkEmailStmt->close();
        $conn->close();
        exit;
    }
    $checkEmailStmt->close();

    // Prepare and execute update query
    $stmt = $conn->prepare("UPDATE users SET first_name = ?, last_name = ?, email = ?, phone=? WHERE id = ?");
    $stmt->bind_param("sssssi", $firstName, $lastName, $email, $phone, $userId);

    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Profile updated successfully'
        ]);
    } else {
        throw new Exception("Error updating profile: " . $stmt->error);
    }

    // Close statement and connection
    $stmt->close();
    $conn->close();
} catch (Exception $e) {
    // Return error response
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>