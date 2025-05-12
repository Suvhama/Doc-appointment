<?php
// Add CORS headers
header('Access-Control-Allow-Origin: http://127.0.0.1:5501');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$conn = new mysqli('localhost', 'root', '', 'Medisync');

if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['id'], $data['patientId'], $data['name'], $data['age'], $data['conditions'], $data['time'], $data['reason'], $data['status'], $data['email'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid input']);
    exit;
}

$appointment_id = $data['id'];
$patient_id = $data['patientId'];
$doctor_email = $data['email'];
$name = $data['name'];
$age = $data['age'];
$condition = $data['conditions'];
$time = $data['time'];
$reason = $data['reason'];
$status = $data['status'];

// Insert into previous_appointment
$stmt = $conn->prepare("INSERT INTO previous_appointment (appointment_id, patient_id, doctor_email, name, age, conditions, time, reason, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param('iisssisss', $appointment_id, $patient_id, $doctor_email, $name, $age, $condition, $time, $reason, $status);

if ($stmt->execute()) {
    // Delete from appointments table
    $deleteStmt = $conn->prepare("DELETE FROM appointments WHERE id = ? AND doctor_email = ?");
    $deleteStmt->bind_param('is', $appointment_id, $doctor_email);

    if ($deleteStmt->execute()) {
        http_response_code(200);
        echo json_encode(['success' => true]);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to delete appointment']);
    }
    $deleteStmt->close();
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to save to previous appointments']);
}

$stmt->close();
$conn->close();
