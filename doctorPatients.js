        // Fetch doctor's details from backend
        function getDoctorsDetails() {
            const email = localStorage.getItem('email');
            if (!email) {
                console.error("No email found in localStorage");
                window.location.href = '/index.html';
                return;
            }
            fetch(`http://localhost/Medisync/backend/doctorDashboard.php?email=${email}`)
                .then((response) => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! Status: ${response.status}`);
                    }
                    return response.json();
                })
                .then((data) => {
                    if (data) {
                        console.log("Doctor's Data:", data);
                        document.getElementById('footerName').textContent = `Dr. ${data.name}`;
                        document.getElementById('footerSpeciality').textContent = data.speciality.charAt(0).toUpperCase() + data.speciality.slice(1);
                        document.getElementById('proName').textContent = `Dr. ${data.name.split(' ')[0]}`;
                        document.getElementById('proSpeciality').textContent = data.speciality.charAt(0).toUpperCase() + data.speciality.slice(1);
                        document.getElementById('dropdownName').textContent = `Dr. ${data.name}`;
                        document.getElementById('dropdownDetails').textContent = `${data.speciality.charAt(0).toUpperCase() + data.speciality.slice(1)} • ${data.qualification.toUpperCase()}`;

                        const avatars = document.querySelectorAll('#sidebarAvatar, #headerAvatar, #dropdownAvatar');
                        avatars.forEach(avatar => {
                            avatar.textContent = `${data.name.split(' ')[0][0]}${data.name.split(' ')[1][0]}`;
                        });
                    } else {
                        console.log(data.message || "No email found.");
                        window.location.href = '/index.html';
                    }
                })
                .catch((error) => {
                    console.error("Error fetching doctor's details:", error);
                    window.location.href = '/index.html';
                });
        }

        // Fetch patients from backend
        function fetchPatients() {
            const email = localStorage.getItem('email');
            if (!email) {
                console.error("No email found in localStorage");
                populatePatientsTable([]);
                alert("Please log in to view patients.");
                return;
            }
            fetch(`http://localhost/Medisync/backend/fetchPatients.php?email=${email}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! Status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        console.log(data);
                        populatePatientsTable(data.patients);
                    } else {
                        console.error("Error fetching patients:", data.message);
                        populatePatientsTable([]);
                        alert("No patients found or an error occurred.");
                    }
                })
                .catch(error => {
                    console.error("Error fetching patients:", error);
                    populatePatientsTable([]);
                    alert("Failed to fetch patients. Please try again later.");
                });
        }

        // Loading state with error handling
        window.addEventListener('load', () => {
            try {
                setTimeout(() => {
                    const loadingOverlay = document.getElementById('loadingOverlay');
                    if (loadingOverlay) {
                        loadingOverlay.style.opacity = '0';
                        setTimeout(() => {
                            loadingOverlay.style.display = 'none';
                        }, 100);
                    }
                }, 100);
                getDoctorsDetails();
                initializePatientsPage();
            } catch (error) {
                console.error('Error during page load:', error);
                const loadingOverlay = document.getElementById('loadingOverlay');
                if (loadingOverlay) {
                    loadingOverlay.style.opacity = '0';
                    setTimeout(() => {
                        loadingOverlay.style.display = 'none';
                    }, 100);
                }
            }
        });

        // Initialize page by fetching patients
        async function initializePatientsPage() {
            try {
                // Fetch notifications (using dummy data for now)
                const notifications = [{
                    id: 1,
                    title: "New Appointment",
                    message: "John Doe has requested an appointment for tomorrow at 10:00 AM",
                    time: "5 minutes ago",
                    icon: "calendar-check",
                    color: "blue",
                    unread: true
                }, {
                    id: 2,
                    title: "Urgent Patient Message",
                    message: "Robert Chen has sent you an urgent message regarding his medication",
                    time: "2 hours ago",
                    icon: "exclamation-circle",
                    color: "red",
                    unread: true
                }];
                populateNotifications(notifications);
                updateNotificationCount();
                // Fetch patients from backend
                fetchPatients();
            } catch (error) {
                console.error('Error initializing patients page:', error);
                populatePatientsTable([]);
            }
        }

        // Sidebar toggle
        function toggleSidebar() {
            try {
                document.querySelector('.sidebar').classList.toggle('hidden');
                document.querySelector('.main-content').classList.toggle('full');
                document.querySelector('.sidebar').classList.toggle('active');
            } catch (error) {
                console.error('Error toggling sidebar:', error);
            }
        }

        // Notification toggle
        function toggleNotifications() {
            try {
                document.getElementById('notificationDropdown').classList.toggle('active');
            } catch (error) {
                console.error('Error toggling notifications:', error);
            }
        }

        // Profile dropdown toggle
        function toggleDropdown() {
            try {
                document.getElementById('profileDropdown').classList.toggle('active');
            } catch (error) {
                console.error('Error toggling profile dropdown:', error);
            }
        }

        // Modal functions
        function openModal(patient) {
            try {
                const modalBody = document.getElementById('modalBody');
                modalBody.innerHTML = `
                    <p><strong>Name:</strong> ${patient.name}</p>
                    <p><strong>Appointment ID:</strong> ${patient.appointmentId}</p>
                    <p><strong>Date & Time:</strong> ${patient.dateTime}</p>
                    <p><strong>Reason:</strong> ${patient.reason}</p>
                    <p><strong>Age:</strong> ${patient.age || 'N/A'}</p>
                    <p><strong>Gender:</strong> ${patient.gender || 'N/A'}</p>
                    <p><strong>Last Visit:</strong> ${patient.lastVisit || 'N/A'}</p>
                `;
                document.getElementById('patientModal').classList.add('active');
            } catch (error) {
                console.error('Error opening modal:', error);
            }
        }

        function closeModal() {
            try {
                document.getElementById('patientModal').classList.remove('active');
            } catch (error) {
                console.error('Error closing modal:', error);
            }
        }

        // Notification functionality
        function markAsRead(button) {
            try {
                const notification = button.closest('.notification-item');
                notification.classList.remove('unread');
                updateNotificationCount();
            } catch (error) {
                console.error('Error marking notification as read:', error);
            }
        }

        function clearNotifications() {
            try {
                const notifications = document.querySelectorAll('.notification-item');
                notifications.forEach(notification => notification.classList.remove('unread'));
                updateNotificationCount();
            } catch (error) {
                console.error('Error clearing notifications:', error);
            }
        }

        function updateNotificationCount() {
            try {
                const unreadCount = document.querySelectorAll('.notification-item.unread').length;
                document.getElementById('notificationCount').textContent = unreadCount;
                document.getElementById('notificationTotal').textContent = unreadCount;
            } catch (error) {
                console.error('Error updating notification count:', error);
            }
        }

        // Populate notifications
        function populateNotifications(notifications) {
            try {
                const notificationList = document.getElementById('notificationList');
                notificationList.innerHTML = '';
                notifications.forEach(notification => {
                    const item = document.createElement('div');
                    item.className = `notification-item ${notification.unread ? 'unread' : ''}`;
                    item.innerHTML = `
                        <div class="notification-icon ${notification.color}">
                            <i class="fas fa-${notification.icon}"></i>
                        </div>
                        <div class="notification-content">
                            <h4>${notification.title}</h4>
                            <p>${notification.message}</p>
                            <span class="notification-time">${notification.time}</span>
                        </div>
                        <div class="notification-actions">
                            <button class="notification-action-btn" onclick="markAsRead(this)">Mark as read</button>
                        </div>
                    `;
                    notificationList.appendChild(item);
                });
            } catch (error) {
                console.error('Error populating notifications:', error);
            }
        }

        // Populate patients table
        function populatePatientsTable(patients) {
            try {
                const patientsTableBody = document.getElementById('patientsTableBody');
                patientsTableBody.innerHTML = '';
                if (patients.length === 0) {
                    const row = document.createElement('tr');
                    row.innerHTML = `<td colspan="5" style="text-align: center;">No patients found.</td>`;
                    patientsTableBody.appendChild(row);
                    return;
                }
                patients.forEach(patient => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${patient.name}</td>
                        <td>${patient.appointmentId}</td>
                        <td>${patient.dateTime}</td>
                        <td>${patient.reason}</td>
                        <td>
                            <button class="action-btn btn-primary" onclick='openModal(${JSON.stringify(patient)})'>
                                <i class="fas fa-eye"></i> View
                            </button>
                        </td>
                    `;
                    patientsTableBody.appendChild(row);
                });
            } catch (error) {
                console.error('Error populating patients table:', error);
            }
        }

        // Search filter
        function filterPatients() {
            try {
                const searchInput = document.getElementById('searchInput').value.toLowerCase();
                const rows = document.querySelectorAll('#patientsTableBody tr');

                rows.forEach(row => {
                    const patientName = row.cells[0].textContent.toLowerCase();
                    row.style.display = patientName.includes(searchInput) ? '' : 'none';
                });
            } catch (error) {
                console.error('Error filtering patients:', error);
            }
        }

        // Action functions (to be implemented by backend)
        function viewFullProfile() {
            try {
                const modalBody = document.getElementById('modalBody');
                const patientName = modalBody.querySelector('p:first-child').textContent.split(': ')[1];
                console.log(`Viewing full profile for patient: ${patientName}`);
                // Redirect or fetch additional profile data as needed
            } catch (error) {
                console.error('Error viewing full profile:', error);
            }
        }

        function logout() {
            try {
                localStorage.clear();
                sessionStorage.clear();
                window.location.href = '/index.html';
            } catch (error) {
                console.error('Error during logout:', error);
            }
        }