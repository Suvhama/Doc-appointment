<?php
// signUpUser.php

header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *"); 
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit;  
}

// Database configuration
$host = 'localhost';
$dbName = 'medisync';   
$username = 'root';       
$password = '';           
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbName", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . $e->getMessage()]);
    exit;
}

// Handle POST request (form submission)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName = $_POST['firstName'];
    $lastName = $_POST['lastName'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Input validation (SERVER-SIDE is essential)
    if (empty($firstName) || empty($lastName) || empty($email) || empty($password)) {
        echo json_encode(['success' => false, 'message' => 'All fields are required.']);
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Invalid email format.']);
        exit;
    }

    // No password hashing - storing plaintext password
    //  THIS IS INSECURE. ONLY FOR TESTING/DEVELOPMENT**
    $plainTextPassword = $password;

    try {
        // Prepare and Execute SQL query
        $stmt = $pdo->prepare("INSERT INTO users (first_name, last_name, email, password) VALUES (?, ?, ?, ?)");
        $stmt->execute([$firstName, $lastName, $email, $plainTextPassword]);

        echo json_encode(['success' => true, 'message' => 'Registration successful!']);

    } catch (PDOException $e) {
        if ($e->getCode() == '23000') {
            echo json_encode(['success' => false, 'message' => 'Email address already registered.']);
        } else {
            error_log("Registration error: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'An error occurred during registration. Please try again.']);
        }
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.  Only POST is allowed.']);
}
?>