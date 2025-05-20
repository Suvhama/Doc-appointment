<?php
$host = 'localhost';
$dbname = 'medisync1';
$username = 'root'; // Change to your MySQL username
$password = '';     // Change to your MySQL password

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

function getAppointments($pdo, $status = 'all', $search = '') {
    $query = "SELECT * FROM appointments WHERE (status = :status OR :status = 'all')";
    if ($search) {
        $query .= " AND (patient_name LIKE :search OR doctor_name LIKE :search)";
    }
    $query .= " ORDER BY date, time";
    $stmt = $pdo->prepare($query);
    $stmt->execute([
        'status' => $status,
        'search' => "%$search%"
    ]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function addAppointment($pdo, $patient_name, $doctor_name, $date, $time, $status) {
    $stmt = $pdo->prepare("INSERT INTO appointments (patient_name, doctor_name, date, time, status) VALUES (:patient_name, :doctor_name, :date, :time, :status)");
    $stmt->execute(['patient_name' => $patient_name, 'doctor_name' => $doctor_name, 'date' => $date, 'time' => $time, 'status' => $status]);
}

function getDoctors($pdo, $status = 'all', $search = '') {
    $query = "SELECT * FROM doctors WHERE (status = :status OR :status = 'all')";
    if ($search) {
        $query .= " AND (name LIKE :search OR specialty LIKE :search)";
    }
    $stmt = $pdo->prepare($query);
    $stmt->execute([
        'status' => $status,
        'search' => "%$search%"
    ]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function addDoctor($pdo, $name, $specialty, $status) {
    $stmt = $pdo->prepare("INSERT INTO doctors (name, specialty, status) VALUES (:name, :specialty, :status)");
    $stmt->execute(['name' => $name, 'specialty' => $specialty, 'status' => $status]);
}

function getPatients($pdo, $status = 'all', $search = '') {
    $query = "SELECT * FROM patients WHERE (status = :status OR :status = 'all')";
    if ($search) {
        $query .= " AND name LIKE :search";
    }
    $query .= " ORDER BY last_visit DESC";
    $stmt = $pdo->prepare($query);
    $stmt->execute([
        'status' => $status,
        'search' => "%$search%"
    ]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function addPatient($pdo, $name, $age, $last_visit, $status) {
    $stmt = $pdo->prepare("INSERT INTO patients (name, age, last_visit, status) VALUES (:name, :age, :last_visit, :status)");
    $stmt->execute(['name' => $name, 'age' => $age, 'last_visit' => $last_visit, 'status' => $status]);
}

function getRecords($pdo, $status = 'all', $search = '') {
    $query = "SELECT * FROM medical_records WHERE (status = :status OR :status = 'all')";
    if ($search) {
        $query .= " AND (patient_name LIKE :search OR record_type LIKE :search)";
    }
    $query .= " ORDER BY date DESC";
    $stmt = $pdo->prepare($query);
    $stmt->execute([
        'status' => $status,
        'search' => "%$search%"
    ]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function addRecord($pdo, $patient_name, $record_type, $date, $status) {
    $stmt = $pdo->prepare("INSERT INTO medical_records (patient_name, record_type, date, status) VALUES (:patient_name, :record_type, :date, :status)");
    $stmt->execute(['patient_name' => $patient_name, 'record_type' => $record_type, 'date' => $date, 'status' => $status]);
}
?>