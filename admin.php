<?php
session_start();
require_once '../backend/connect.php';

// Handle POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Login
    if (isset($_POST['action']) && $_POST['action'] === 'login') {
        $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
        $password = $_POST['password'];

        $stmt = $pdo->prepare("SELECT id, name, password FROM user WHERE email = ? AND role = 'admin'");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['admin_id'] = $user['id'];
            $_SESSION['admin_name'] = $user['name'];
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid email or password']);
        }
        exit;
    }

    // Dashboard actions
    if (isset($_POST['add_appointment'])) {
        addAppointment($pdo, $_POST['patient_name'], $_POST['doctor'], $_POST['date'], $_POST['time'], $_POST['status']);
    }
    if (isset($_POST['add_doctor'])) {
        addDoctor($pdo, $_POST['doctor_name'], $_POST['specialty'], $_POST['doctor_status']);
    }
    if (isset($_POST['add_patient'])) {
        addPatient($pdo, $_POST['patient_name'], $_POST['patient_age'], $_POST['last_visit'], $_POST['patient_status']);
    }
    if (isset($_POST['add_record'])) {
        addRecord($pdo, $_POST['record_patient_name'], $_POST['record_type'], $_POST['record_date'], $_POST['record_status']);
    }
    if (isset($_POST['update_profile'])) {
        $full_name = filter_input(INPUT_POST, 'full_name', FILTER_SANITIZE_STRING);
        $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
        $stmt = $pdo->prepare("UPDATE user SET name = ?, email = ? WHERE id = ?");
        $stmt->execute([$full_name, $email, $_SESSION['admin_id']]);
        $_SESSION['admin_name'] = $full_name;
    }
    if (isset($_POST['update_system_settings'])) {
        $_SESSION['theme'] = $_POST['theme'];
        header("Location: admin.php?section=settings");
        exit();
    }
}

// Handle logout
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    session_destroy();
    header("Location: admin.php");
    exit;
}

// Check authentication
$authenticated = isset($_SESSION['admin_id']);

// Fetch dashboard data if authenticated
if ($authenticated) {
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    $filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
    $appointments = getAppointments($pdo, $filter, $search);
    $doctors = getDoctors($pdo, $filter, $search);
    $patients = getPatients($pdo, $filter, $search);
    $records = getRecords($pdo, $filter, $search);
    $theme = isset($_SESSION['theme']) ? $_SESSION['theme'] : 'light';
}

// Determine page content
$page = isset($_GET['page']) ? $_GET['page'] : ($authenticated ? 'dashboard' : 'login');
?>
<!DOCTYPE html>
<html lang="en" data-theme="<?php echo htmlspecialchars($theme ?? 'light'); ?>">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>MediSync Admin Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" integrity="sha512-Avb2QiuDEEvB4bZJYdft2mNjVShBftLdPG8FJ0V7irTLQ8Uo0qcPxh4Plq7G5tGm0rU+1SPhVotteLpBERwTkw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="/MediSync/css-styles/admin.css">
    <link rel="stylesheet" href="/MediSync/css-styles/global.css">
    <style>
        .auth-container {
            max-width: 400px;
            margin: 100px auto;
            padding: 20px;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }
        .auth-container h2 {
            text-align: center;
            color: #053c6f;
            margin-bottom: 20px;
        }
        .auth-form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        .auth-form input {
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 1rem;
        }
        .auth-form button {
            background-color: #0096D1;
            color: white;
            padding: 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background 0.3s;
        }
        .auth-form button:hover {
            background-color: #007bb5;
        }
    </style>
</head>
<body>
    <?php if (!$authenticated && $page === 'login'): ?>
        <div class="auth-container">
            <h2>Admin Login</h2>
            <form id="login-form" class="auth-form">
                <input type="hidden" name="action" value="login">
                <input type="email" name="email" placeholder="Email" required>
                <input type="password" name="password" placeholder="Password" required>
                <button type="submit">Log In</button>
            </form>
        </div>
    <?php elseif ($authenticated): ?>
        <span class="menu-toggle" onclick="toggleSidebar()"><i class="fas fa-bars"></i></span>
        <aside class="sidebar">
            <div>
                <div class="logo">
                    <h2>MediSync</h2>
                </div>
                <nav class="nav-links">
                    <a href="?section=dashboard" class="<?php echo ($_GET['section'] ?? 'dashboard') === 'dashboard' ? 'active' : ''; ?>"><i class="fas fa-tachometer-alt icon"></i><span>Dashboard</span></a>
                    <a href="?section=appointments" class="<?php echo $_GET['section'] === 'appointments' ? 'active' : ''; ?>"><i class="fas fa-calendar-check icon"></i><span>Appointments</span></a>
                    <a href="?section=doctors" class="<?php echo $_GET['section'] === 'doctors' ? 'active' : ''; ?>"><i class="fas fa-user-md icon"></i><span>Doctors</span></a>
                    <a href="?section=patients" class="<?php echo $_GET['section'] === 'patients' ? 'active' : ''; ?>"><i class="fas fa-users icon"></i><span>Patients</span></a>
                    <a href="?section=records" class="<?php echo $_GET['section'] === 'records' ? 'active' : ''; ?>"><i class="fas fa-file-medical icon"></i><span>Medical Records</span></a>
                    <a href="?section=profile" class="<?php echo $_GET['section'] === 'profile' ? 'active' : ''; ?>"><i class="fas fa-user icon"></i><span>Profile</span></a>
                    <a href="?section=settings" class="<?php echo $_GET['section'] === 'settings' ? 'active' : ''; ?>"><i class="fas fa-cog icon"></i><span>Settings</span></a>
                </nav>
            </div>
            <div class="profile-section">
                <div class="profile">
                    <div class="avatar"><?php echo htmlspecialchars(substr($_SESSION['admin_name'], 0, 2)); ?></div>
                    <div class="info">
                        <p class="name"><?php echo htmlspecialchars($_SESSION['admin_name']); ?></p>
                        <p class="role">Admin</p>
                    </div>
                </div>
                <a href="?action=logout" class="logout"><i class="fas fa-sign-out-alt icon"></i>Logout</a>
            </div>
        </aside>
        <main class="main">
            <?php
            $section = isset($_GET['section']) ? $_GET['section'] : 'dashboard';
            $sections = ['dashboard', 'appointments', 'doctors', 'patients', 'records', 'profile', 'settings'];
            $activeSection = in_array($section, $sections) ? $section : 'dashboard';
            ?>
            <!-- Dashboard Section -->
            <div id="dashboard-section" class="content-section <?php echo $activeSection === 'dashboard' ? 'active' : ''; ?>">
                <header class="search-bar">
                    <form method="get" class="search-wrapper">
                        <input type="hidden" name="section" value="<?php echo htmlspecialchars($activeSection); ?>">
                        <?php if ($filter !== 'all'): ?>
                            <input type="hidden" name="filter" value="<?php echo htmlspecialchars($filter); ?>">
                        <?php endif; ?>
                        <i class="fas fa-search"></i>
                        <input type="text" name="search" class="search-input" placeholder="Search patients, appointments, records..." value="<?php echo htmlspecialchars($search); ?>">
                    </form>
                    <div class="right-section">
                        <div class="notification-bell">
                            <button class="icon-button" onclick="toggleNotifications()">
                                <i class="fas fa-bell"></i>
                                <span class="notification-badge" id="notificationCount">2</span>
                            </button>
                            <div class="notification-dropdown" id="notificationDropdown">
                                <div class="notification-header">
                                    <h3>Notifications (<span id="notificationTotal">2</span>)</h3>
                                    <a href="#" onclick="clearNotifications()">Clear All</a>
                                </div>
                                <div class="notification-list">
                                    <div class="notification-item unread">
                                        <div class="notification-icon">
                                            <i class="fas fa-user-md"></i>
                                        </div>
                                        <div class="notification-content">
                                            <h4>New Doctor Registration</h4>
                                            <p>Dr. Alice Brown has submitted a registration request</p>
                                            <span class="notification-time">1 hour ago</span>
                                        </div>
                                        <div class="notification-actions">
                                            <button class="notification-action-btn" onclick="markAsRead(this)">Mark as read</button>
                                        </div>
                                    </div>
                                    <div class="notification-item unread">
                                        <div class="notification-icon">
                                            <i class="fas fa-calendar-check"></i>
                                        </div>
                                        <div class="notification-content">
                                            <h4>Appointment Conflict</h4>
                                            <p>Overlapping appointments detected for Dr. Prabal at 10:00 AM</p>
                                            <span class="notification-time">3 hours ago</span>
                                        </div>
                                        <div class="notification-actions">
                                            <button class="notification-action-btn" onclick="markAsRead(this)">Mark as read</button>
                                        </div>
                                    </div>
                                    <div class="notification-item">
                                        <div class="notification-icon">
                                            <i class="fas fa-cog"></i>
                                        </div>
                                        <div class="notification-content">
                                            <h4>System Update</h4>
                                            <p>New system update available for MediSync</p>
                                            <span class="notification-time">Yesterday</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="notification-footer">
                                    <a href="#">View all notifications</a>
                                </div>
                            </div>
                        </div>
                        <div class="dropdown" onclick="toggleDropdown()">
                            <div class="avatar"><?php echo htmlspecialchars(substr($_SESSION['admin_name'], 0, 2)); ?></div>
                            <span class="name-role"><?php echo htmlspecialchars($_SESSION['admin_name']); ?></span>
                            <div id="dropdown-menu" class="dropdown-menu">
                                <a href="?section=profile" id="profile-link"><i class="fas fa-user"></i> Profile</a>
                                <a href="?section=settings" id="settings-link"><i class="fas fa-cog"></i> Settings</a>
                                <a href="?action=logout" id="logout-link"><i class="fas fa-sign-out-alt"></i> Logout</a>
                            </div>
                        </div>
                    </div>
                </header>
                <div class="dashboard-content">
                    <div class="widget">
                        <h3>Overview</h3>
                        <div class="overview-stats">
                            <div class="stat-card">
                                <h4>Total Patients</h4>
                                <p><?php echo $pdo->query("SELECT COUNT(*) FROM patients" . ($search ? " WHERE name LIKE '%$search%'" : ""))->fetchColumn(); ?></p>
                            </div>
                            <div class="stat-card">
                                <h4>Appointments Today</h4>
                                <p><?php
                                    $query = "SELECT COUNT(*) FROM appointments WHERE DATE(date) = CURDATE()";
                                    if ($search) {
                                        $query .= " AND (patient_name LIKE '%$search%' OR doctor_name LIKE '%$search%')";
                                    }
                                    echo $pdo->query($query)->fetchColumn();
                                ?></p>
                            </div>
                            <div class="stat-card">
                                <h4>Active Doctors</h4>
                                <p><?php
                                    $query = "SELECT COUNT(*) FROM doctors WHERE status = 'available'";
                                    if ($search) {
                                        $query .= " AND (name LIKE '%$search%' OR specialty LIKE '%$search%')";
                                    }
                                    echo $pdo->query($query)->fetchColumn();
                                ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="widget">
                        <h3>Recent Appointments</h3>
                        <div class="appointments-list">
                            <?php
                            $query = "SELECT * FROM appointments";
                            if ($search) {
                                $query .= " WHERE patient_name LIKE '%$search%' OR doctor_name LIKE '%$search%'";
                            }
                            $query .= " ORDER BY date DESC, time DESC LIMIT 3";
                            $recent = $pdo->query($query)->fetchAll(PDO::FETCH_ASSOC);
                            foreach ($recent as $app) {
                                $date = new DateTime($app['date']);
                                echo "<div class='appointment-item'>
                                    <div class='appointment-details'>
                                        <p class='name'>{$app['patient_name']}</p>
                                        <p class='time'>" . $date->format('M d, Y') . ", {$app['time']} - {$app['doctor_name']}</p>
                                    </div>
                                    <span class='appointment-status {$app['status']}'>" . ucfirst($app['status']) . "</span>
                                </div>";
                            }
                            ?>
                        </div>
                    </div>
                    <div class="widget">
                        <h3>Quick Actions</h3>
                        <div class="quick-actions">
                            <a href="?section=patients" class="action-btn"><i class="fas fa-plus"></i> Add New Patient</a>
                            <a href="?section=appointments" class="action-btn"><i class="fas fa-calendar-plus"></i> Schedule Appointment</a>
                            <a href="?section=doctors" class="action-btn"><i class="fas fa-user-md"></i> Register Doctor</a>
                        </div>
                    </div>
                    <div class="widget">
                        <h3>Pending Tasks</h3>
                        <div class="tasks-list">
                            <div class="task-item">
                                <div class="task-details">
                                    <p class="task-name">Review New Patient Registration</p>
                                    <p class="time">Submitted 2 hours ago</p>
                                </div>
                                <span class="task-status pending">Pending</span>
                            </div>
                            <div class="task-item">
                                <div class="task-details">
                                    <p class="task-name">Approve Appointment Request</p>
                                    <p class="time">Submitted 4 hours ago</p>
                                </div>
                                <span class="task-status in-progress">In Progress</span>
                            </div>
                            <div class="task-item">
                                <div class="task-details">
                                    <p class="task-name">Update System Settings</p>
                                    <p class="time">Due by 5 PM today</p>
                                </div>
                                <span class="task-status pending">Pending</span>
                            </div>
                        </div>
                    </div>
                    <div class="widget">
                        <h3>Doctor Availability</h3>
                        <div class="doctors-list">
                            <?php
                            $query = "SELECT * FROM doctors WHERE status = 'available'";
                            if ($search) {
                                $query .= " AND (name LIKE '%$search%' OR specialty LIKE '%$search%')";
                            }
                            $query .= " LIMIT 3";
                            $available = $pdo->query($query)->fetchAll(PDO::FETCH_ASSOC);
                            foreach ($available as $doc) {
                                echo "<div class='doctor-item'>
                                    <div class='doctor-details'>
                                        <p class='name'>{$doc['name']}</p>
                                        <p class='specialty'>{$doc['specialty']}</p>
                                    </div>
                                    <span class='doctor-status {$doc['status']}'>" . ucfirst($doc['status']) . "</span>
                                </div>";
                            }
                            ?>
                        </div>
                    </div>
                    <div class="widget">
                        <h3>Recent Activity Log</h3>
                        <div class="activity-list">
                            <div class="activity-item">
                                <div class="activity-details">
                                    <p class="activity-description">Admin logged in</p>
                                    <p class="activity-time">10 minutes ago</p>
                                </div>
                            </div>
                            <div class="activity-item">
                                <div class="activity-details">
                                    <p class="activity-description">Patient record updated for John Doe</p>
                                    <p class="activity-time">1 hour ago</p>
                                </div>
                            </div>
                            <div class="activity-item">
                                <div class="activity-details">
                                    <p class="activity-description">Appointment scheduled for Jane Roe</p>
                                    <p class="activity-time">2 hours ago</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Appointments Section -->
            <div id="appointments-section" class="content-section <?php echo $activeSection === 'appointments' ? 'active' : ''; ?>">
                <header class="section-header">
                    <h1>Appointments</h1>
                    <div class="filter-wrapper">
                        <form method="get" style="display: inline;">
                            <input type="hidden" name="section" value="appointments">
                            <input type="hidden" name="search" value="<?php echo htmlspecialchars($search); ?>">
                            <select name="filter" class="filter-select" onchange="this.form.submit()">
                                <option value="all" <?php echo $filter === 'all' ? 'selected' : ''; ?>>All</option>
                                <option value="confirmed" <?php echo $filter === 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                                <option value="pending" <?php echo $filter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="cancelled" <?php echo $filter === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                            </select>
                        </form>
                        <div class="right-section">
                            <div class="notification-bell">
                                <button class="icon-button" onclick="toggleNotifications()">
                                    <i class="fas fa-bell"></i>
                                    <span class="notification-badge" id="notificationCount">2</span>
                                </button>
                                <div class="notification-dropdown" id="notificationDropdown">
                                    <div class="notification-header">
                                        <h3>Notifications (<span id="notificationTotal">2</span>)</h3>
                                        <a href="#" onclick="clearNotifications()">Clear All</a>
                                    </div>
                                    <div class="notification-list">
                                        <div class="notification-item unread">
                                            <div class="notification-icon">
                                                <i class="fas fa-user-md"></i>
                                            </div>
                                            <div class="notification-content">
                                                <h4>New Doctor Registration</h4>
                                                <p>Dr. Alice Brown has submitted a registration request</p>
                                                <span class="notification-time">1 hour ago</span>
                                            </div>
                                            <div class="notification-actions">
                                                <button class="notification-action-btn" onclick="markAsRead(this)">Mark as read</button>
                                            </div>
                                        </div>
                                        <div class="notification-item unread">
                                            <div class="notification-icon">
                                                <i class="fas fa-calendar-check"></i>
                                            </div>
                                            <div class="notification-content">
                                                <h4>Appointment Conflict</h4>
                                                <p>Overlapping appointments detected for Dr. Prabal at 10:00 AM</p>
                                                <span class="notification-time">3 hours ago</span>
                                            </div>
                                            <div class="notification-actions">
                                                <button class="notification-action-btn" onclick="markAsRead(this)">Mark as read</button>
                                            </div>
                                        </div>
                                        <div class="notification-item">
                                            <div class="notification-icon">
                                                <i class="fas fa-cog"></i>
                                            </div>
                                            <div class="notification-content">
                                                <h4>System Update</h4>
                                                <p>New system update available for MediSync</p>
                                                <span class="notification-time">Yesterday</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="notification-footer">
                                        <a href="#">View all notifications</a>
                                    </div>
                                </div>
                            </div>
                            <div class="dropdown" onclick="toggleDropdown()">
                                <div class="avatar"><?php echo htmlspecialchars(substr($_SESSION['admin_name'], 0, 2)); ?></div>
                                <span class="name-role"><?php echo htmlspecialchars($_SESSION['admin_name']); ?></span>
                                <div id="dropdown-menu" class="dropdown-menu">
                                    <a href="?section=profile" id="profile-link"><i class="fas fa-user"></i> Profile</a>
                                    <a href="?section=settings" id="settings-link"><i class="fas fa-cog"></i> Settings</a>
                                    <a href="?action=logout" id="logout-link"><i class="fas fa-sign-out-alt"></i> Logout</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </header>
                <div class="appointments-content">
                    <div class="widget">
                        <h3>All Appointments</h3>
                        <div class="appointments-list">
                            <?php foreach ($appointments as $app): ?>
                                <?php $date = new DateTime($app['date']); ?>
                                <div class="appointment-item" data-status="<?php echo $app['status']; ?>">
                                    <div class="appointment-details">
                                        <p class="name"><?php echo htmlspecialchars($app['patient_name']) . " - " . htmlspecialchars($app['doctor_name']); ?></p>
                                        <p class="time"><?php echo $date->format('M d, Y') . ", " . $app['time']; ?></p>
                                    </div>
                                    <span class="appointment-status <?php echo $app['status']; ?>"><?php echo ucfirst($app['status']); ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div class="widget">
                        <h3>Add New Appointment</h3>
                        <form method="post" class="form-group">
                            <input type="hidden" name="add_appointment" value="1">
                            <div class="form-group">
                                <label for="patient_name">Patient Name</label>
                                <input type="text" name="patient_name" id="patient_name" placeholder="Enter patient name" required>
                            </div>
                            <div class="form-group">
                                <label for="doctor">Doctor</label>
                                <select name="doctor" id="doctor" required>
                                    <option value="" disabled selected>Select a doctor</option>
                                    <?php foreach ($pdo->query("SELECT name FROM doctors")->fetchAll(PDO::FETCH_ASSOC) as $doc): ?>
                                        <option value="<?php echo htmlspecialchars($doc['name']); ?>"><?php echo htmlspecialchars($doc['name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="date">Date</label>
                                <input type="date" name="date" id="date" required>
                            </div>
                            <div class="form-group">
                                <label for="time">Time</label>
                                <input type="time" name="time" id="time" required>
                            </div>
                            <div class="form-group">
                                <label for="status">Status</label>
                                <select name="status" id="status" required>
                                    <option value="pending">Pending</option>
                                    <option value="confirmed">Confirmed</option>
                                    <option value="cancelled">Cancelled</option>
                                </select>
                            </div>
                            <button type="submit" class="submit-btn">Add Appointment</button>
                        </form>
                    </div>
                </div>
            </div>
            <!-- Doctors Section -->
            <div id="doctors-section" class="content-section <?php echo $activeSection === 'doctors' ? 'active' : ''; ?>">
                <header class="section-header">
                    <h1>Doctors</h1>
                    <div class="filter-wrapper">
                        <form method="get" style="display: inline;">
                            <input type="hidden" name="section" value="doctors">
                            <input type="hidden" name="search" value="<?php echo htmlspecialchars($search); ?>">
                            <select name="filter" class="filter-select" onchange="this.form.submit()">
                                <option value="all" <?php echo $filter === 'all' ? 'selected' : ''; ?>>All</option>
                                <option value="available" <?php echo $filter === 'available' ? 'selected' : ''; ?>>Available</option>
                                <option value="busy" <?php echo $filter === 'busy' ? 'selected' : ''; ?>>Busy</option>
                                <option value="on-leave" <?php echo $filter === 'on-leave' ? 'selected' : ''; ?>>On Leave</option>
                            </select>
                        </form>
                        <div class="right-section">
                            <div class="notification-bell">
                                <button class="icon-button" onclick="toggleNotifications()">
                                    <i class="fas fa-bell"></i>
                                    <span class="notification-badge" id="notificationCount">2</span>
                                </button>
                                <div class="notification-dropdown" id="notificationDropdown">
                                    <div class="notification-header">
                                        <h3>Notifications (<span id="notificationTotal">2</span>)</h3>
                                        <a href="#" onclick="clearNotifications()">Clear All</a>
                                    </div>
                                    <div class="notification-list">
                                        <div class="notification-item unread">
                                            <div class="notification-icon">
                                                <i class="fas fa-user-md"></i>
                                            </div>
                                            <div class="notification-content">
                                                <h4>New Doctor Registration</h4>
                                                <p>Dr. Alice Brown has submitted a registration request</p>
                                                <span class="notification-time">1 hour ago</span>
                                            </div>
                                            <div class="notification-actions">
                                                <button class="notification-action-btn" onclick="markAsRead(this)">Mark as read</button>
                                            </div>
                                        </div>
                                        <div class="notification-item unread">
                                            <div class="notification-icon">
                                                <i class="fas fa-calendar-check"></i>
                                            </div>
                                            <div class="notification-content">
                                                <h4>Appointment Conflict</h4>
                                                <p>Overlapping appointments detected for Dr. Prabal at 10:00 AM</p>
                                                <span class="notification-time">3 hours ago</span>
                                            </div>
                                            <div class="notification-actions">
                                                <button class="notification-action-btn" onclick="markAsRead(this)">Mark as read</button>
                                            </div>
                                        </div>
                                        <div class="notification-item">
                                            <div class="notification-icon">
                                                <i class="fas fa-cog"></i>
                                            </div>
                                            <div class="notification-content">
                                                <h4>System Update</h4>
                                                <p>New system update available for MediSync</p>
                                                <span class="notification-time">Yesterday</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="notification-footer">
                                        <a href="#">View all notifications</a>
                                    </div>
                                </div>
                            </div>
                            <div class="dropdown" onclick="toggleDropdown()">
                                <div class="avatar"><?php echo htmlspecialchars(substr($_SESSION['admin_name'], 0, 2)); ?></div>
                                <span class="name-role"><?php echo htmlspecialchars($_SESSION['admin_name']); ?></span>
                                <div id="dropdown-menu" class="dropdown-menu">
                                    <a href="?section=profile" id="profile-link"><i class="fas fa-user"></i> Profile</a>
                                    <a href="?section=settings" id="settings-link"><i class="fas fa-cog"></i> Settings</a>
                                    <a href="?action=logout" id="logout-link"><i class="fas fa-sign-out-alt"></i> Logout</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </header>
                <div class="doctors-content">
                    <div class="widget">
                        <h3>All Doctors</h3>
                        <div class="doctors-list">
                            <?php foreach ($doctors as $doc): ?>
                                <div class="doctor-item" data-status="<?php echo $doc['status']; ?>">
                                    <div class="doctor-details">
                                        <p class="name"><?php echo htmlspecialchars($doc['name']); ?></p>
                                        <p class="specialty"><?php echo htmlspecialchars($doc['specialty']); ?></p>
                                    </div>
                                    <span class="doctor-status <?php echo $doc['status']; ?>"><?php echo ucfirst(str_replace('-', ' ', $doc['status'])); ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div class="widget">
                        <h3>Add New Doctor</h3>
                        <form method="post" class="form-group">
                            <input type="hidden" name="add_doctor" value="1">
                            <div class="form-group">
                                <label for="doctor_name">Doctor Name</label>
                                <input type="text" name="doctor_name" id="doctor_name" placeholder="Enter doctor name" required>
                            </div>
                            <div class="form-group">
                                <label for="specialty">Specialty</label>
                                <input type="text" name="specialty" id="specialty" placeholder="Enter specialty" required>
                            </div>
                            <div class="form-group">
                                <label for="doctor_status">Status</label>
                                <select name="doctor_status" id="doctor_status" required>
                                    <option value="available">Available</option>
                                    <option value="busy">Busy</option>
                                    <option value="on-leave">On Leave</option>
                                </select>
                            </div>
                            <button type="submit" class="submit-btn">Add Doctor</button>
                        </form>
                    </div>
                </div>
            </div>
            <!-- Patients Section -->
            <div id="patients-section" class="content-section <?php echo $activeSection === 'patients' ? 'active' : ''; ?>">
                <header class="section-header">
                    <h1>Patients</h1>
                    <div class="filter-wrapper">
                        <form method="get" style="display: inline;">
                            <input type="hidden" name="section" value="patients">
                            <input type="hidden" name="search" value="<?php echo htmlspecialchars($search); ?>">
                            <select name="filter" class="filter-select" onchange="this.form.submit()">
                                <option value="all" <?php echo $filter === 'all' ? 'selected' : ''; ?>>All</option>
                                <option value="healthy" <?php echo $filter === 'healthy' ? 'selected' : ''; ?>>Healthy</option>
                                <option value="follow-up" <?php echo $filter === 'follow-up' ? 'selected' : ''; ?>>Follow-Up</option>
                            </select>
                        </form>
                        <div class="right-section">
                            <div class="notification-bell">
                                <button class="icon-button" onclick="toggleNotifications()">
                                    <i class="fas fa-bell"></i>
                                    <span class="notification-badge" id="notificationCount">2</span>
                                </button>
                                <div class="notification-dropdown" id="notificationDropdown">
                                    <div class="notification-header">
                                        <h3>Notifications (<span id="notificationTotal">2</span>)</h3>
                                        <a href="#" onclick="clearNotifications()">Clear All</a>
                                    </div>
                                    <div class="notification-list">
                                        <div class="notification-item unread">
                                            <div class="notification-icon">
                                                <i class="fas fa-user-md"></i>
                                            </div>
                                            <div class="notification-content">
                                                <h4>New Doctor Registration</h4>
                                                <p>Dr. Alice Brown has submitted a registration request</p>
                                                <span class="notification-time">1 hour ago</span>
                                            </div>
                                            <div class="notification-actions">
                                                <button class="notification-action-btn" onclick="markAsRead(this)">Mark as read</button>
                                            </div>
                                        </div>
                                        <div class="notification-item unread">
                                            <div class="notification-icon">
                                                <i class="fas fa-calendar-check"></i>
                                            </div>
                                            <div class="notification-content">
                                                <h4>Appointment Conflict</h4>
                                                <p>Overlapping appointments detected for Dr. Prabal at 10:00 AM</p>
                                                <span class="notification-time">3 hours ago</span>
                                            </div>
                                            <div class="notification-actions">
                                                <button class="notification-action-btn" onclick="markAsRead(this)">Mark as read</button>
                                            </div>
                                        </div>
                                        <div class="notification-item">
                                            <div class="notification-icon">
                                                <i class="fas fa-cog"></i>
                                            </div>
                                            <div class="notification-content">
                                                <h4>System Update</h4>
                                                <p>New system update available for MediSync</p>
                                                <span class="notification-time">Yesterday</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="notification-footer">
                                        <a href="#">View all notifications</a>
                                    </div>
                                </div>
                            </div>
                            <div class="dropdown" onclick="toggleDropdown()">
                                <div class="avatar"><?php echo htmlspecialchars(substr($_SESSION['admin_name'], 0, 2)); ?></div>
                                <span class="name-role"><?php echo htmlspecialchars($_SESSION['admin_name']); ?></span>
                                <div id="dropdown-menu" class="dropdown-menu">
                                    <a href="?section=profile" id="profile-link"><i class="fas fa-user"></i> Profile</a>
                                    <a href="?section=settings" id="settings-link"><i class="fas fa-cog"></i> Settings</a>
                                    <a href="?action=logout" id="logout-link"><i class="fas fa-sign-out-alt"></i> Logout</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </header>
                <div class="patients-content">
                    <div class="widget">
                        <h3>All Patients</h3>
                        <div class="patients-list">
                            <?php foreach ($patients as $pat): ?>
                                <?php $date = new DateTime($pat['last_visit']); ?>
                                <div class="patient-item" data-status="<?php echo $pat['status']; ?>">
                                    <div class="patient-details">
                                        <p class="name"><?php echo htmlspecialchars($pat['name']); ?></p>
                                        <p class="age">Age: <?php echo $pat['age']; ?>, Last Visit: <?php echo $date->format('M d, Y'); ?></p>
                                    </div>
                                    <span class="patient-status <?php echo $pat['status']; ?>"><?php echo ucfirst(str_replace('-', ' ', $pat['status'])); ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div class="widget">
                        <h3>Add New Patient</h3>
                        <form method="post" class="form-group">
                            <input type="hidden" name="add_patient" value="1">
                            <div class="form-group">
                                <label for="patient_name">Patient Name</label>
                                <input type="text" name="patient_name" id="patient_name" placeholder="Enter patient name" required>
                            </div>
                            <div class="form-group">
                                <label for="patient_age">Age</label>
                                <input type="number" name="patient_age" id="patient_age" placeholder="Enter age" required>
                            </div>
                            <div class="form-group">
                                <label for="last_visit">Last Visit</label>
                                <input type="date" name="last_visit" id="last_visit" required>
                            </div>
                            <div class="form-group">
                                <label for="patient_status">Status</label>
                                <select name="patient_status" id="patient_status" required>
                                    <option value="healthy">Healthy</option>
                                    <option value="follow-up">Follow-Up</option>
                                </select>
                            </div>
                            <button type="submit" class="submit-btn">Add Patient</button>
                        </form>
                    </div>
                </div>
            </div>
            <!-- Medical Records Section -->
            <div id="records-section" class="content-section <?php echo $activeSection === 'records' ? 'active' : ''; ?>">
                <header class="section-header">
                    <h1>Medical Records</h1>
                    <div class="filter-wrapper">
                        <form method="get" style="display: inline;">
                            <input type="hidden" name="section" value="records">
                            <input type="hidden" name="search" value="<?php echo htmlspecialchars($search); ?>">
                            <select name="filter" class="filter-select" onchange="this.form.submit()">
                                <option value="all" <?php echo $filter === 'all' ? 'selected' : ''; ?>>All</option>
                                <option value="active" <?php echo $filter === 'active' ? 'selected' : ''; ?>>Active</option>
                                <option value="archived" <?php echo $filter === 'archived' ? 'selected' : ''; ?>>Archived</option>
                            </select>
                        </form>
                        <div class="right-section">
                            <div class="notification-bell">
                                <button class="icon-button" onclick="toggleNotifications()">
                                    <i class="fas fa-bell"></i>
                                    <span class="notification-badge" id="notificationCount">2</span>
                                </button>
                                <div class="notification-dropdown" id="notificationDropdown">
                                    <div class="notification-header">
                                        <h3>Notifications (<span id="notificationTotal">2</span>)</h3>
                                        <a href="#" onclick="clearNotifications()">Clear All</a>
                                    </div>
                                    <div class="notification-list">
                                        <div class="notification-item unread">
                                            <div class="notification-icon">
                                                <i class="fas fa-user-md"></i>
                                            </div>
                                            <div class="notification-content">
                                                <h4>New Doctor Registration</h4>
                                                <p>Dr. Alice Brown has submitted a registration request</p>
                                                <span class="notification-time">1 hour ago</span>
                                            </div>
                                            <div class="notification-actions">
                                                <button class="notification-action-btn" onclick="markAsRead(this)">Mark as read</button>
                                            </div>
                                        </div>
                                        <div class="notification-item unread">
                                            <div class="notification-icon">
                                                <i class="fas fa-calendar-check"></i>
                                            </div>
                                            <div class="notification-content">
                                                <h4>Appointment Conflict</h4>
                                                <p>Overlapping appointments detected for Dr. Prabal at 10:00 AM</p>
                                                <span class="notification-time">3 hours ago</span>
                                            </div>
                                            <div class="notification-actions">
                                                <button class="notification-action-btn" onclick="markAsRead(this)">Mark as read</button>
                                            </div>
                                        </div>
                                        <div class="notification-item">
                                            <div class="notification-icon">
                                                <i class="fas fa-cog"></i>
                                            </div>
                                            <div class="notification-content">
                                                <h4>System Update</h4>
                                                <p>New system update available for MediSync</p>
                                                <span class="notification-time">Yesterday</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="notification-footer">
                                        <a href="#">View all notifications</a>
                                    </div>
                                </div>
                            </div>
                            <div class="dropdown" onclick="toggleDropdown()">
                                <div class="avatar"><?php echo htmlspecialchars(substr($_SESSION['admin_name'], 0, 2)); ?></div>
                                <span class="name-role"><?php echo htmlspecialchars($_SESSION['admin_name']); ?></span>
                                <div id="dropdown-menu" class="dropdown-menu">
                                    <a href="?section=profile" id="profile-link"><i class="fas fa-user"></i> Profile</a>
                                    <a href="?section=settings" id="settings-link"><i class="fas fa-cog"></i> Settings</a>
                                    <a href="?action=logout" id="logout-link"><i class="fas fa-sign-out-alt"></i> Logout</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </header>
                <div class="records-content">
                    <div class="widget">
                        <h3>All Medical Records</h3>
                        <div class="records-list">
                            <?php foreach ($records as $rec): ?>
                                <?php $date = new DateTime($rec['date']); ?>
                                <div class="record-item" data-status="<?php echo $rec['status']; ?>">
                                    <div class="record-details">
                                        <p class="name"><?php echo htmlspecialchars($rec['patient_name']) . " - " . htmlspecialchars($rec['record_type']); ?></p>
                                        <p class="date"><?php echo $date->format('M d, Y'); ?></p>
                                    </div>
                                    <span class="record-status <?php echo $rec['status']; ?>"><?php echo ucfirst($rec['status']); ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div class="widget">
                        <h3>Add New Record</h3>
                        <form method="post" class="form-group">
                            <input type="hidden" name="add_record" value="1">
                            <div class="form-group">
                                <label for="record_patient_name">Patient Name</label>
                                <input type="text" name="record_patient_name" id="record_patient_name" placeholder="Enter patient name" required>
                            </div>
                            <div class="form-group">
                                <label for="record_type">Record Type</label>
                                <input type="text" name="record_type" id="record_type" placeholder="Enter record type (e.g., Lab Results)" required>
                            </div>
                            <div class="form-group">
                                <label for="record_date">Date</label>
                                <input type="date" name="record_date" id="record_date" required>
                            </div>
                            <div class="form-group">
                                <label for="record_status">Status</label>
                                <select name="record_status" id="record_status" required>
                                    <option value="active">Active</option>
                                    <option value="archived">Archived</option>
                                </select>
                            </div>
                            <button type="submit" class="submit-btn">Add Record</button>
                        </form>
                    </div>
                </div>
            </div>
            <!-- Profile Section -->
            <div id="profile-section" class="content-section <?php echo $activeSection === 'profile' ? 'active' : ''; ?>">
                <header class="section-header">
                    <h1>Profile</h1>
                    <div class="right-section">
                        <div class="notification-bell">
                            <button class="icon-button" onclick="toggleNotifications()">
                                <i class="fas fa-bell"></i>
                                <span class="notification-badge" id="notificationCount">2</span>
                            </button>
                            <div class="notification-dropdown" id="notificationDropdown">
                                <div class="notification-header">
                                    <h3>Notifications (<span id="notificationTotal">2</span>)</h3>
                                    <a href="#" onclick="clearNotifications()">Clear All</a>
                                </div>
                                <div class="notification-list">
                                    <div class="notification-item unread">
                                        <div class="notification-icon">
                                            <i class="fas fa-user-md"></i>
                                        </div>
                                        <div class="notification-content">
                                            <h4>New Doctor Registration</h4>
                                            <p>Dr. Alice Brown has submitted a registration request</p>
                                            <span class="notification-time">1 hour ago</span>
                                        </div>
                                        <div class="notification-actions">
                                            <button class="notification-action-btn" onclick="markAsRead(this)">Mark as read</button>
                                        </div>
                                    </div>
                                    <div class="notification-item unread">
                                        <div class="notification-icon">
                                            <i class="fas fa-calendar-check"></i>
                                        </div>
                                        <div class="notification-content">
                                            <h4>Appointment Conflict</h4>
                                            <p>Overlapping appointments detected for Dr. Prabal at 10:00 AM</p>
                                            <span class="notification-time">3 hours ago</span>
                                        </div>
                                        <div class="notification-actions">
                                            <button class="notification-action-btn" onclick="markAsRead(this)">Mark as read</button>
                                        </div>
                                    </div>
                                    <div class="notification-item">
                                        <div class="notification-icon">
                                            <i class="fas fa-cog"></i>
                                        </div>
                                        <div class="notification-content">
                                            <h4>System Update</h4>
                                            <p>New system update available for MediSync</p>
                                            <span class="notification-time">Yesterday</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="notification-footer">
                                    <a href="#">View all notifications</a>
                                </div>
                            </div>
                        </div>
                        <div class="dropdown" onclick="toggleDropdown()">
                            <div class="avatar"><?php echo htmlspecialchars(substr($_SESSION['admin_name'], 0, 2)); ?></div>
                            <span class="name-role"><?php echo htmlspecialchars($_SESSION['admin_name']); ?></span>
                            <div id="dropdown-menu" class="dropdown-menu">
                                <a href="?section=profile" id="profile-link"><i class="fas fa-user"></i> Profile</a>
                                <a href="?section=settings" id="settings-link"><i class="fas fa-cog"></i> Settings</a>
                                <a href="?action=logout" id="logout-link"><i class="fas fa-sign-out-alt"></i> Logout</a>
                            </div>
                        </div>
                    </div>
                </header>
                <div class="profile-content">
                    <div class="widget">
                        <h3>Profile Details</h3>
                        <div class="profile-details">
                            <div class="detail-item">
                                <label>Full Name:</label>
                                <p><?php echo htmlspecialchars($_SESSION['admin_name']); ?></p>
                            </div>
                            <div class="detail-item">
                                <label>Email:</label>
                                <p><?php
                                    $stmt = $pdo->prepare("SELECT email FROM user WHERE id = ?");
                                    $stmt->execute([$_SESSION['admin_id']]);
                                    echo htmlspecialchars($stmt->fetchColumn());
                                ?></p>
                            </div>
                            <div class="detail-item">
                                <label>Role:</label>
                                <p>Admin</p>
                            </div>
                            <div class="detail-item">
                                <label>Joined:</label>
                                <p>January 15, 2023</p>
                            </div>
                        </div>
                        <button class="edit-btn" onclick="toggleEditProfile()">Edit Profile</button>
                    </div>
                    <div class="widget" id="edit-profile-form" style="display: none;">
                        <h3>Edit Profile</h3>
                        <form method="post" class="form-group">
                            <input type="hidden" name="update_profile" value="1">
                            <div class="form-group">
                                <label for="full_name">Full Name</label>
                                <input type="text" name="full_name" id="full_name" value="<?php echo htmlspecialchars($_SESSION['admin_name']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" name="email" id="email" value="<?php
                                    $stmt = $pdo->prepare("SELECT email FROM user WHERE id = ?");
                                    $stmt->execute([$_SESSION['admin_id']]);
                                    echo htmlspecialchars($stmt->fetchColumn());
                                ?>" required>
                            </div>
                            <button type="submit" class="submit-btn">Save Changes</button>
                            <button type="button" class="cancel-btn" onclick="toggleEditProfile()">Cancel</button>
                        </form>
                    </div>
                    <div class="widget">
                        <h3>Recent Activity</h3>
                        <div class="activity-list">
                            <div class="activity-item">
                                <div class="activity-details">
                                    <p class="activity-description">Updated patient record for John Doe</p>
                                    <p class="activity-time">1 hour ago</p>
                                </div>
                            </div>
                            <div class="activity-item">
                                <div class="activity-details">
                                    <p class="activity-description">Scheduled an appointment for Jane Roe</p>
                                    <p class="activity-time">2 hours ago</p>
                                </div>
                            </div>
                            <div class="activity-item">
                                <div class="activity-details">
                                    <p class="activity-description">Logged in to MediSync</p>
                                    <p class="activity-time">3 hours ago</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Settings Section -->
            <div id="settings-section" class="content-section <?php echo $activeSection === 'settings' ? 'active' : ''; ?>">
                <header class="section-header">
                    <h1>Settings</h1>
                    <div class="right-section">
                        <div class="notification-bell">
                            <button class="icon-button" onclick="toggleNotifications()">
                                <i class="fas fa-bell"></i>
                                <span class="notification-badge" id="notificationCount">2</span>
                            </button>
                            <div class="notification-dropdown" id="notificationDropdown">
                                <div class="notification-header">
                                    <h3>Notifications (<span id="notificationTotal">2</span>)</h3>
                                    <a href="#" onclick="clearNotifications()">Clear All</a>
                                </div>
                                <div class="notification-list">
                                    <div class="notification-item unread">
                                        <div class="notification-icon">
                                            <i class="fas fa-user-md"></i>
                                        </div>
                                        <div class="notification-content">
                                            <h4>New Doctor Registration</h4>
                                            <p>Dr. Alice Brown has submitted a registration request</p>
                                            <span class="notification-time">1 hour ago</span>
                                        </div>
                                        <div class="notification-actions">
                                            <button class="notification-action-btn" onclick="markAsRead(this)">Mark as read</button>
                                        </div>
                                    </div>
                                    <div class="notification-item unread">
                                        <div class="notification-icon">
                                            <i class="fas fa-calendar-check"></i>
                                        </div>
                                        <div class="notification-content">
                                            <h4>Appointment Conflict</h4>
                                            <p>Overlapping appointments detected for Dr. Prabal at 10:00 AM</p>
                                            <span class="notification-time">3 hours ago</span>
                                        </div>
                                        <div class="notification-actions">
                                            <button class="notification-action-btn" onclick="markAsRead(this)">Mark as read</button>
                                        </div>
                                    </div>
                                    <div class="notification-item">
                                        <div class="notification-icon">
                                            <i class="fas fa-cog"></i>
                                        </div>
                                        <div class="notification-content">
                                            <h4>System Update</h4>
                                            <p>New system update available for MediSync</p>
                                            <span class="notification-time">Yesterday</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="notification-footer">
                                    <a href="#">View all notifications</a>
                                </div>
                            </div>
                        </div>
                        <div class="dropdown" onclick="toggleDropdown()">
                            <div class="avatar"><?php echo htmlspecialchars(substr($_SESSION['admin_name'], 0, 2)); ?></div>
                            <span class="name-role"><?php echo htmlspecialchars($_SESSION['admin_name']); ?></span>
                            <div id="dropdown-menu" class="dropdown-menu">
                                <a href="?section=profile" id="profile-link"><i class="fas fa-user"></i> Profile</a>
                                <a href="?section=settings" id="settings-link"><i class="fas fa-cog"></i> Settings</a>
                                <a href="?action=logout" id="logout-link"><i class="fas fa-sign-out-alt"></i> Logout</a>
                            </div>
                        </div>
                    </div>
                </header>
                <div class="settings-content">
                    <div class="widget">
                        <h3>Account Settings</h3>
                        <form method="post" class="form-group">
                            <input type="hidden" name="change_password" value="1">
                            <div class="form-group">
                                <label for="current_password">Current Password</label>
                                <input type="password" name="current_password" id="current_password" placeholder="Enter current password" required>
                            </div>
                            <div class="form-group">
                                <label for="new_password">New Password</label>
                                <input type="password" name="new_password" id="new_password" placeholder="Enter new password" required>
                            </div>
                            <div class="form-group">
                                <label for="confirm_password">Confirm New Password</label>
                                <input type="password" name="confirm_password" id="confirm_password" placeholder="Confirm new password" required>
                            </div>
                            <button type="submit" class="submit-btn">Change Password</button>
                        </form>
                    </div>
                    <div class="widget">
                        <h3>Notification Preferences</h3>
                        <form method="post" class="form-group">
                            <input type="hidden" name="update_notifications" value="1">
                            <div class="form-group checkbox-group">
                                <input type="checkbox" name="email_notifications" id="email_notifications" checked>
                                <label for="email_notifications">Receive email notifications</label>
                            </div>
                            <div class="form-group checkbox-group">
                                <input type="checkbox" name="sms_notifications" id="sms_notifications">
                                <label for="sms_notifications">Receive SMS notifications</label>
                            </div>
                            <button type="submit" class="submit-btn">Save Preferences</button>
                        </form>
                    </div>
                    <div class="widget">
                        <h3>System Settings</h3>
                        <form method="post" class="form-group">
                            <input type="hidden" name="update_system_settings" value="1">
                            <div class="form-group">
                                <label for="theme">Theme</label>
                                <select name="theme" id="theme" onchange="previewTheme(this.value)">
                                    <option value="light" <?php echo $theme === 'light' ? 'selected' : ''; ?>>Light</option>
                                    <option value="dark" <?php echo $theme === 'dark' ? 'selected' : ''; ?>>Dark</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="language">Language</label>
                                <select name="language" id="language">
                                    <option value="en">English</option>
                                    <option value="es">Spanish</option>
                                    <option value="fr">French</option>
                                </select>
                            </div>
                            <button type="submit" class="submit-btn">Save Settings</button>
                        </form>
                    </div>
                </div>
            </div>
        </main>
    <?php else: ?>
        <div class="auth-container">
            <h2>Access Denied</h2>
            <p>Please <a href="?page=login">log in</a> to access the dashboard.</p>
        </div>
    <?php endif; ?>
    <script>
        // Login Form Submission
        <?php if (!$authenticated && $page === 'login'): ?>
            document.getElementById("login-form").addEventListener("submit", function (event) {
                event.preventDefault();
                const formData = new FormData(this);
                fetch("admin.php", {
                    method: "POST",
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        window.location.href = "admin.php?section=dashboard";
                    } else {
                        alert(data.message || "Login failed. Please try again.");
                    }
                })
                .catch(error => {
                    console.error("Error:", error);
                    alert("An error occurred. Please try again later.");
                });
            });
        <?php endif; ?>
    </script>
    <?php if ($authenticated): ?>
        <script src="/MediSync/javascript/admin/admin.js"></script>
    <?php endif; ?>
</body>
</html>