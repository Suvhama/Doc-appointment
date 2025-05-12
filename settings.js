
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
                        // Update sidebar
                        document.getElementById('footerName').textContent = `Dr. ${data.name}`;
                        document.getElementById('footerSpeciality').textContent = data.speciality.charAt(0).toUpperCase() + data.speciality.slice(1);
                        // Update header
                        document.getElementById('proName').textContent = `Dr. ${data.name.split(' ')[0]}`;
                        document.getElementById('proSpeciality').textContent = data.speciality.charAt(0).toUpperCase() + data.speciality.slice(1);
                        // Update dropdown
                        document.getElementById('dropdownName').textContent = `Dr. ${data.name}`;
                        document.getElementById('dropdownDetails').textContent = `${data.speciality.charAt(0).toUpperCase() + data.speciality.slice(1)} • ${data.qualification.toUpperCase()}`;
                        // Update avatars
                        const avatars = document.querySelectorAll('#sidebarAvatar, #headerAvatar, #dropdownAvatar');
                        avatars.forEach(avatar => {
                            avatar.textContent = `${data.name.split(' ')[0][0]}${data.name.split(' ')[1][0]}`;
                        });
                        // Populate form fields
                        document.getElementById('firstName').value = data.name.split(' ')[0];
                        document.getElementById('lastName').value = data.name.split(' ')[1];
                        document.getElementById('email').value = email;
                        document.getElementById('phone').value = data.phone || '';
                        document.getElementById('specialty').value = data.speciality;
                        document.getElementById('license').value = data.license;
                        document.getElementById('bio').value = data.bio || '';
                        document.getElementById('ticketPrice').value = data.ticketPrice || '';
                        // Update image preview
                        if (data.images) {
                            document.getElementById('imagePreview').src = `/Uploads/DoctorImages/${data.images}`;
                        }
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

        // Initialize page
        document.addEventListener('DOMContentLoaded', () => {
            getDoctorsDetails();
            updateNotificationCount();
            setupImageUpload();
        });

        function setupImageUpload() {
            const imageInput = document.getElementById('doctorImage');
            const imagePreview = document.getElementById('imagePreview');

            imageInput.addEventListener('change', function() {
                if (this.files && this.files[0]) {
                    // Preview image
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        imagePreview.src = e.target.result;
                    };
                    reader.readAsDataURL(this.files[0]);

                    // Upload image
                    const formData = new FormData();
                    formData.append('doctorImage', this.files[0]);
                    formData.append('email', localStorage.getItem('email'));

                    fetch('http://localhost/Medisync/backend/upload_photo.php', {
                            method: 'POST',
                            body: formData
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                showAlert('Profile image updated successfully!', 'success');
                                imagePreview.src = `/Uploads/DoctorImages/${data.imagePath}`;
                            } else {
                                showAlert(data.message || 'Failed to upload image.', 'error');
                                getDoctorsDetails(); // Reset image
                            }
                        })
                        .catch(error => {
                            console.error('Error uploading image:', error);
                            showAlert('An error occurred while uploading the image.', 'error');
                            getDoctorsDetails(); // Reset image
                        });
                }
            });
        }

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

        function showAlert(message, type) {
            const alertBox = document.getElementById('alertBox');
            alertBox.textContent = message;
            alertBox.className = `alert alert-${type}`;
            alertBox.style.display = 'block';
            setTimeout(() => {
                alertBox.style.display = 'none';
            }, 5000);
        }

        function saveProfile() {
            const firstName = document.getElementById('firstName').value.trim();
            const lastName = document.getElementById('lastName').value.trim();
            const email = document.getElementById('email').value.trim();
            const phone = document.getElementById('phone').value.trim();
            const specialty = document.getElementById('specialty').value;
            const license = document.getElementById('license').value.trim();
            const bio = document.getElementById('bio').value.trim();
            const ticketPrice = document.getElementById('ticketPrice').value.trim();

            // Basic client-side validation
            if (!firstName || !lastName || !email || !phone || !ticketPrice) {
                showAlert('Please fill in all required fields.', 'error');
                return;
            }

            // Validate email format
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                showAlert('Invalid email format.', 'error');
                return;
            }

            // Validate phone format (basic example)
            const phoneRegex = /^\+?\d{10,15}$/;
            if (!phoneRegex.test(phone)) {
                showAlert('Invalid phone number format.', 'error');
                return;
            }

            // Validate ticketPrice
            if (isNaN(ticketPrice) || ticketPrice <= 0) {
                showAlert('Consultation fee must be a positive number.', 'error');
                return;
            }

            const profileData = {
                firstName,
                lastName,
                email,
                phone,
                specialty,
                license,
                bio,
                ticketPrice
            };

            fetch('http://localhost/Medisync/backend/doctors/updateDoctor.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(profileData)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        console.log('Profile successfully updated');
                        showAlert('Profile updated successfully!', 'success');
                        localStorage.setItem('email', email);
                        getDoctorsDetails();
                    } else {
                        showAlert(data.message || 'Failed to update profile.', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error updating profile:', error);
                    showAlert('An error occurred while updating the profile.', 'error');
                });
        }

        function cancelChanges() {
            console.log('Changes cancelled');
            getDoctorsDetails();
        }
   