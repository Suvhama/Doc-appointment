
        // Alert function

        function showAlert(message, type) {
            const alertBox = document.getElementById('alertBox');
            alertBox.textContent = message;
            alertBox.className = `alert alert-${type}`;
            alertBox.style.display = 'block';
            setTimeout(() => {
                alertBox.style.display = 'none';
            }, 3000);
        }

        // Load existing user data from the server
        document.addEventListener('DOMContentLoaded', function() {
            const email = localStorage.getItem('email');

            // Fetch user data from the backend
            fetch(`http://localhost/Medisync/backend/userDashboard.php?email=${email}`)
                .then((response) => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! Status: ${response.status}`);
                    }
                    return response.json();
                })
                .then((data) => {
                    if (data) {
                        console.log("User data for profile edit:", data);

                        // Populate form fields with user data
                        document.getElementById('firstName').value = data.first_name || '';
                        document.getElementById('lastName').value = data.last_name || '';
                        document.getElementById('email').value = data.email || '';
                        document.getElementById('phone').value = data.phone_number || ''; // Adjust if your field name is different

                        // Store a reference to the user ID if needed for the update
                        if (data.id) {
                            localStorage.setItem('userId', data.id);
                        }
                    } else {
                        console.log("No user data found for profile edit");
                        showAlert('Failed to load user data', 'danger');
                    }
                })
                .catch((error) => {
                    console.error("Error fetching user data for profile edit:", error);
                    showAlert('Error loading profile data', 'danger');
                });
        });

        // Handle form submission
        document.getElementById('profileForm').addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = {
                id: localStorage.getItem('userId'), // Include user ID for the update
                first_name: document.getElementById('firstName').value,
                last_name: document.getElementById('lastName').value,
                email: document.getElementById('email').value,
                phone_number: document.getElementById('phone').value || null
            };

            fetch('http://localhost/Medisync/backend/users/updateProfile.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(formData),
                    credentials: 'include'
                })
                .then(response => {
                    console.log('Response Status:', response.status);
                    console.log('Response Headers:', response.headers.get('Content-Type'));
                    if (!response.ok) {
                        throw new Error(`HTTP error! Status: ${response.status}`);
                    }
                    return response.text();
                })
                .then(text => {
                    console.log('Raw Response:', text);
                    try {
                        const data = JSON.parse(text);
                        if (data.success) {
                            if (formData.email !== localStorage.getItem('email')) {
                                localStorage.setItem('email', formData.email);
                            }
                            showAlert('Profile updated successfully!', 'success');
                            setTimeout(() => {
                                window.location.href = 'userDashboard.html';
                            }, 2000);
                        } else {
                            showAlert(data.message || 'Failed to update profile', 'danger');
                        }
                    } catch (error) {
                        console.error('JSON Parse Error:', error);
                        showAlert('Server returned invalid response', 'danger');
                    }
                })
                .catch(error => {
                    console.error('Fetch Error:', error);
                    showAlert('Error updating profile: ' + error.message, 'danger');
                });
        });
   