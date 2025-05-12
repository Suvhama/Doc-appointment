<?php
include "doctorDB.php";

// Log the request
error_log('Request received: ' . $_SERVER['REQUEST_URI']);

// Set headers for CORS and JSON response
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Start session (if needed)
session_start();

// Check if the request method is either POST or GET
if ($_SERVER['REQUEST_METHOD'] === 'POST' || $_SERVER['REQUEST_METHOD'] === 'GET') {
    // Retrieve email from the request
    $email = $_REQUEST['email'] ?? '';

    // Validate input
    if (empty($email)) {
        echo json_encode(['success' => false, 'message' => 'Email is required.']);
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Invalid email format.']);
        exit;
    }
} else {
    // Invalid request method
    echo json_encode(['success' => false, 'message' => 'Invalid request method. Only POST or GET is allowed.']);
    exit;
}

// SELECT THE DATA OF THE DOCTOR FROM THE DATABASE
$sql_select_doctor = "SELECT * FROM doctors WHERE email = ?";
$stmt = $connection->prepare($sql_select_doctor);

if ($stmt) {
    // Bind the variable to the placeholder and execute the query
    $stmt->bind_param("s", $email); // "s" indicates the variable is a string
    $stmt->execute();

    // Get the result set
    $result = $stmt->get_result();

    // Fetch and output the results
    if ($row = $result->fetch_assoc()) {
        $id = $row['doctor_id'];
        $firstName = $row['first_name'];
        $last_name = $row['last_name'];
        $email = $row['email'];
        $speciality = $row['speciality'];
        $qualification = $row['qualification'];
        $license = $row['license_number'];
        $phone_number = $row['phone_number'];
        $experience = $row['experience'];
        $ticketPrice = $row['ticketPrice'];
        $image = $row['image'];
        $rating = $row['rating'];
        $hospital = $row['hospital_affiliation'];

        // Create an associative array
        $doctorData = array(
            'id' => $id,
            'name' => $firstName . " " . $last_name,
            'email' => $email,
            'speciality' => $speciality,
            'qualification' => $qualification,
            'license' => $license,
            'phone_number' => $phone_number,
            'experience' => $experience,
            'ticketPrice' => $ticketPrice,
            'image' => $image,
            'rating' => $rating,
            'hospital' => $hospital
        );

        echo json_encode($doctorData);
    } else {
        echo json_encode(['success' => false, 'message' => 'No doctor found with this email.']);
    }

    // Close the statement
    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Error preparing statement: ' . $connection->error]);
}

// Close the connection
$connection->close();
