<?php

include "userDB.php";

error_log('Request received: ' . $_SERVER['REQUEST_URI']);

// Set headers for CORS and JSON response
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");


if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit;
}

session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST' || $_SERVER['REQUEST_METHOD'] === 'GET') {
    // Retrieve email and password from the request
    $email = $_REQUEST['email'] ?? '';

    // Validate input
    if (empty($email)) {
        echo json_encode(['success' => false, 'message' => 'Email and password are required.']);
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Invalid email format.']);
        exit;
    }

    // If validation passes, return a success response
    // echo json_encode([
    //     'success' => true,
    //     'message' => 'Request successful.',
    //     'email' => $email
    // ]);
} else {
    // Invalid request method
    echo json_encode(['success' => false, 'message' => 'Invalid request method. Only POST or GET is allowed.']);
}

// echo json_encode([
//     'success' => true,
//     'message' => 'Request successful.',
//     'email' => $email
// ]);

// SELECT THE DATA OF THE DOCTOR FROM THE DATABASE
$sql_select_doctor = "SELECT * FROM users WHERE email= ?";
$stmt = $connection->prepare($sql_select_doctor);

if ($stmt) {
    // Bind the variable to the placeholder and execute the query
    $stmt->bind_param("s", $email); // "s" indicates the variable is a string
    $stmt->execute();

    // Get the result set
    $result = $stmt->get_result();

    // Fetch and output the results
    while ($row = $result->fetch_assoc()) {
        $id = $row['id'];
        $firstName = $row['first_name'];
        $last_name = $row['last_name'];
        $email = $row['email'];
        $phone = $row['phone_number'];
    }

    // Create an associative array
    $doctorData = array(
        'id' => $id,
        // 'name' => $firstName . " " . $last_name,
        'first_name' => $firstName,
        'last_name' => $last_name,
        'email' => $email,
        'phone' => $phone
    );

    $jsonData = json_encode($doctorData);

    echo $jsonData;

    // Close the statement
    $stmt->close();
} else {
    echo "\nError preparing statement: " . $connection->error;
}

// Close the connection
$connection->close();
