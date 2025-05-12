<?php
// save_appointment.php

// Set headers for API response
header('Content-Type: application/json');

// Get the raw POST data
$jsonData = file_get_contents('php://input');
$appointmentData = json_decode($jsonData, true);

// Validate the incoming data
if (
    !isset($appointmentData['appointment_id']) ||
    !isset($appointmentData['hospital_name']) ||
    !isset($appointmentData['department']) ||
    !isset($appointmentData['time_slot'])
) {

    // Return error if required data is missing
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Missing required appointment data']);
    exit;
}

// Database connection parameters
$dbHost = 'localhost';
$dbUsername = 'root';
$dbPassword = '';
$dbName = 'medisync';

// Create database connection
$conn = new mysqli($dbHost, $dbUsername, $dbPassword, $dbName);

// Check connection
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Database connection failed']);
    exit;
}

// Prepare SQL statement to prevent SQL injection
$stmt = $conn->prepare("INSERT INTO book_appointment (appointment_id, hospital_name, department, time_slot, booking_date) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param(
    "sssss",
    $appointmentData['appointment_id'],
    $appointmentData['hospital_name'],
    $appointmentData['department'],
    $appointmentData['time_slot'],
    $appointmentData['booking_date']
);

// Execute the query
if ($stmt->execute()) {
    // Return success response
    echo json_encode([
        'status' => 'success',
        'message' => 'Appointment saved successfully',
        'appointment_id' => $appointmentData['appointment_id']
    ]);
} else {
    // Return error response
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to save appointment: ' . $stmt->error
    ]);
}

// Close connection
$stmt->close();
$conn->close();
