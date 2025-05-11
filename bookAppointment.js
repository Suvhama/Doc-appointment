  function getUserDetails() {
            const email = localStorage.getItem('email');
            console.log("Fetching details for email:", email);

            if (!email) {
                // If no email in localStorage, redirect to login
                window.location.href = '/html/users/userLogin.html';
                return;
            }

            fetch(`http://localhost/Medisync/backend/userDashboard.php?email=${email}`)
                .then((response) => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! Status: ${response.status}`);
                    }
                    return response.json();
                })
                .then((data) => {
                    if (data) {
                        console.log("User data received:", data);
                        document.getElementById("userName").textContent = data.first_name || "User";
                    } else {
                        console.log("No user data found or error in response");
                        document.getElementById("userName").textContent = "User";
                    }
                })
                .catch((error) => {
                    console.error("Error fetching user data:", error);
                    document.getElementById("userName").textContent = "User";
                    // Redirect to login on error
                    setTimeout(() => {
                        window.location.href = '/html/users/userLogin.html';
                    }, 2000);
                });
        }

        document.addEventListener('DOMContentLoaded', function() {
            getUserDetails();
        });

        function bookAppointment(hospitalName, department) {
            window.location.href = `/html/doctors/ourDoctor.html?hospitalName=${encodeURIComponent(hospitalName)}&department=${encodeURIComponent(department)}`;
        }

        document.getElementById('avatarDropdown').addEventListener('click', function() {
            document.getElementById('userDropdown').classList.toggle('active');
        });

        document.addEventListener('click', function(event) {
            const userProfile = document.querySelector('.user-profile');
            const dropdown = document.getElementById('userDropdown');
            if (!userProfile.contains(event.target) && dropdown.classList.contains('active')) {
                dropdown.classList.remove('active');
            }
        });

        function showAlert(message, type) {
            const alertBox = document.getElementById('alertBox');
            alertBox.textContent = message;
            alertBox.className = `alert alert-${type}`;
            alertBox.style.display = 'block';
            setTimeout(() => {
                alertBox.style.display = 'none';
            }, 3000);
        }

        document.getElementById('logoutBtn').addEventListener('click', function(event) {
            event.preventDefault();
            fetch('/backend/logout.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    credentials: 'include'
                })
                .then(response => response.json())
                .then(data => {
                    console.log(data);
                    if (data.success) {
                        localStorage.removeItem('email');
                        showAlert('Logout successful!', 'success');
                        setTimeout(() => {
                            window.location.href = '/index.html';
                        }, 1000);
                    } else {
                        showAlert(data.message || 'Logout failed', 'danger');
                    }
                })
                .catch(error => {
                    console.error('Logout error:', error);
                    localStorage.removeItem('email');
                    showAlert('Logging out...', 'success');
                    setTimeout(() => {
                        window.location.href = '/index.html';
                    }, 1000);
                });
        });

        document.querySelectorAll('.favorite').forEach(button => {
            button.addEventListener('click', () => {
                const icon = button.querySelector('i');
                icon.classList.toggle('far');
                icon.classList.toggle('fas');
                icon.style.color = icon.classList.contains('fas') ? '#ff4d4d' : '#ccc';
            });
        });

        function filterHospitals() {
            const input = document.getElementById('searchInput').value.toLowerCase();
            const hospitalItems = document.querySelectorAll('.hospital-item');

            hospitalItems.forEach(item => {
                const hospitalName = item.querySelector('h3').textContent.toLowerCase();
                const hospitalLocation = item.querySelector('p').textContent.toLowerCase();

                if (hospitalName.includes(input) || hospitalLocation.includes(input)) {
                    item.style.display = 'block';
                } else {
                    item.style.display = 'none';
                }
            });
        }
        // Check for search query parameter on page load
        document.addEventListener('DOMContentLoaded', function() {
            getUserDetails(); // Existing function call
            const urlParams = new URLSearchParams(window.location.search);
            const searchTerm = urlParams.get('search');
            if (searchTerm) {
                document.getElementById('searchInput').value = searchTerm;
                filterHospitals(); // Trigger the filter with the search term
            }
        });