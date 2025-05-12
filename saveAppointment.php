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

    // Validate required input fields
    $required_fields = [
        'doctor_name',
        'specialty',
        'experience',
        'appointment_date',
        'consultation_time',
        'token_no',
        'consultation_fee',
        'patient_name',
        'patient_age',
        'patient_mobile',
        'patient_email',
        'payment_email'
    ];

    foreach ($required_fields as $field) {
        if (!isset($input[$field]) || empty($input[$field])) {
            $response = [
                'success' => false,
                'message' => "Missing or empty required field: $field"
            ];
            http_response_code(400);
            echo json_encode($response);
            exit;
        }
    }

    // Sanitize and prepare data
    $doctor_name = htmlspecialchars(trim($input['doctor_name']));
    $specialty = htmlspecialchars(trim($input['specialty']));
    $experience = htmlspecialchars(trim($input['experience']));
    $appointment_date = htmlspecialchars(trim($input['appointment_date']));
    $consultation_time = htmlspecialchars(trim($input['consultation_time']));
    $token_no = htmlspecialchars(trim($input['token_no']));
    $consultation_fee = floatval($input['consultation_fee']);
    $patient_name = htmlspecialchars(trim($input['patient_name']));
    $patient_age = intval($input['patient_age']);
    $patient_mobile = htmlspecialchars(trim($input['patient_mobile']));
    $patient_email = htmlspecialchars(trim($input['patient_email']));
    $patient_weight = isset($input['patient_weight']) ? htmlspecialchars(trim($input['patient_weight'])) : null;
    $patient_height = isset($input['patient_height']) ? htmlspecialchars(trim($input['patient_height'])) : null;
    $patient_reason = isset($input['patient_reason']) ? htmlspecialchars(trim($input['patient_reason'])) : null;
    $payment_email = htmlspecialchars(trim($input['payment_email']));

    // Validate email formats
    if (!filter_var($patient_email, FILTER_VALIDATE_EMAIL) || !filter_var($payment_email, FILTER_VALIDATE_EMAIL)) {
        $response = [
            'success' => false,
            'message' => 'Invalid email format.'
        ];
        http_response_code(400);
        echo json_encode($response);
        exit;
    }

    // Prepare and execute the insert query
    $stmt = $pdo->prepare("
        INSERT INTO appointments (
            doctor_name, specialty, experience, appointment_date, consultation_time,
            token_no, consultation_fee, patient_name, patient_age, patient_mobile,
            patient_email, patient_weight, patient_height, patient_reason, payment_email
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([
        $doctor_name,
        $specialty,
        $experience,
        $appointment_date,
        $consultation_time,
        $token_no,
        $consultation_fee,
        $patient_name,
        $patient_age,
        $patient_mobile,
        $patient_email,
        $patient_weight,
        $patient_height,
        $patient_reason,
        $payment_email
    ]);

    $response = [
        'success' => true,
        'message' => 'Appointment saved successfully.'
    ];
    http_response_code(200);
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
