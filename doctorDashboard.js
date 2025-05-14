function getDoctorsDetails() {
    const email = localStorage.getItem('email');
    fetch(`http://localhost/Medisync/backend/doctorDashboard.php?email=${email}`)
        .then((response) => {
            if (!response.ok) {
                throw new Error(`HTTP error! Status: ${response.status}`);
            }
            return response.json();
        })
        .then((data) => {
            if (data) {
                // Log the entire data object to the console
                console.log("Doctor's Data:", data);

                // Log specific fields for clarity
                console.log("ID:", data.id);
                console.log("Name:", data.name);
                console.log("Email:", data.email);
                console.log("Speciality:", data.speciality);
                console.log("Qualification:", data.qualification);
                console.log("License:", data.license);
                console.log("Phone Number:", data.phone_number);
                console.log("Experience:", data.experience);
                console.log("Ticket Price:", data.ticketPrice);
                console.log("Image:", data.image);
                console.log("Rating:", data.rating);
                console.log("Hospital:", data.hospital);

                // Update DOM elements with doctor's data
                document.getElementById('footerName').textContent = `Dr. ${data.name}`;
                document.getElementById('footerSpeciality').textContent = data.speciality.charAt(0).toUpperCase() + data.speciality.slice(1);
                document.getElementById('proName').textContent = `Dr. ${data.name.split(' ')[0]}`;
                document.getElementById('proSpeciality').textContent = data.speciality.charAt(0).toUpperCase() + data.speciality.slice(1);
                document.getElementById('speciality').textContent = data.speciality.charAt(0).toUpperCase() + data.speciality.slice(1);
                document.getElementById('Qualifications').textContent = data.qualification.charAt(0).toUpperCase() + data.qualification.slice(1);
                document.getElementById('License').textContent = `License #${data.license}`;
                document.getElementById('hospital').textContent = `Hospital: ${data.hospital || 'Not specified'}`;

                // Update avatars
                const avatars = document.querySelectorAll('.doctor-avatar, .profile-avatar, .dropdown-avatar');
                avatars.forEach(avatar => {
                    avatar.textContent = `${data.name.split(' ')[0][0]}${data.name.split(' ')[1][0]}`;
                });

                // Update welcome message
                document.querySelector('.welcome-text h1').textContent = `Welcome back, Dr. ${data.name.split(' ')[0]}!`;

                // Update dropdown header
                document.querySelector('.dropdown-header h3').textContent = `Dr. ${data.name}`;
                document.querySelector('.dropdown-header p').textContent = `${data.speciality.charAt(0).toUpperCase() + data.speciality.slice(1)} • ${data.qualification.toUpperCase()}`;
            } else {
                console.log(data.message || "No email found.");
            }
        })
        .catch((error) => {
            console.error("Error fetching email:", error);
        });
}
getDoctorsDetails();

window.addEventListener('load', () => {
    setTimeout(() => {
        document.getElementById('loadingOverlay').style.opacity = '0';
        setTimeout(() => {
            document.getElementById('loadingOverlay').style.display = 'none';
        }, 100);
    }, 100);
});

function toggleSidebar() {
    document.querySelector('.sidebar').classList.toggle('hidden');
    document.querySelector('.main-content').classList.toggle('full');
    document.querySelector('.sidebar').classList.toggle('active');
}

function toggleNotifications() {
    document.getElementById('notificationDropdown').classList.toggle('active');
}

function toggleDropdown() {
    document.getElementById('profileDropdown').classList.toggle('active');
}

function markAsRead(button) {
    const notification = button.closest('.notification-item');
    notification.classList.remove('unread');
    updateNotificationCount();
}

function clearNotifications() {
    const notifications = document.querySelectorAll('.notification-item');
    notifications.forEach(notification => notification.classList.remove('unread'));
    updateNotificationCount();
}

function updateNotificationCount() {
    const unreadCount = document.querySelectorAll('.notification-item.unread').length;
    document.getElementById('notificationCount').textContent = unreadCount;
    document.getElementById('notificationTotal').textContent = unreadCount;
}

function logout() {
    localStorage.clear();
    sessionStorage.clear();
    window.location.href = '/index.html';
}

function viewPatientDetails(id) {
    console.log(`Viewing patient details for ID: ${id}`);
}

function viewMessage(id) {
    console.log(`Viewing message with ID: ${id}`);
}

function viewAppointment(id) {
    console.log(`Viewing appointment with ID: ${id}`);
}

document.getElementById('current-date').textContent = new Date().toLocaleDateString();
document.addEventListener('DOMContentLoaded', updateNotificationCount);