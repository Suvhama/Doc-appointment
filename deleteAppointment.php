<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: http://127.0.0.1:5501');
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit();
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "medisync";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Connection failed: " . $conn->connect_error]);
    exit();
}

// Get input data
$input = json_decode(file_get_contents('php://input'), true);
$appointment_id = isset($input['appointment_id']) ? $conn->real_escape_string($input['appointment_id']) : '';

if (empty($appointment_id)) {
    echo json_encode(["success" => false, "message" => "Appointment ID is required"]);
    exit();
}

// Delete appointment
$sql = "DELETE FROM appointments WHERE id = '$appointment_id'";
if ($conn->query($sql) === TRUE && $conn->affected_rows > 0) {
    echo json_encode(["success" => true]);
} else {
    echo json_encode(["success" => false, "message" => "Failed to remove appointment or appointment not found"]);
}

$conn->close();
