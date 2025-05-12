
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

        let doctorsData = [];
        const doctorsPerPage = 3;
        let currentPage = 1;
        let filteredDoctors = [];

        // Hospital data for address lookup
        const hospitalData = [
            { name: "B&B Hospital", address: "Gwarko, Lalitpur" },
            { name: "Bir Hospital", address: "Kanti Path, Kathmandu" },
            { name: "T.U. Teaching Hospital", address: "Maharajgunj, Bagmati, Kathmandu" },
            { name: "Civil Service Hospital", address: "Min Bhawan, Kathmandu" },
            { name: "Koshi Hospital", address: "Biratnagar, Morang" },
            { name: "Manmohan Cardiothoracic Vascular and Transplant Center", address: "Maharajgunj, Kathmandu" },
            { name: "Rapti Academy of Health Sciences", address: "Ghorahi" },
            { name: "Dhulikhel Hospital", address: "Kathmandu University Hospital" }
        ];

        // Function to display hospital info
        function displayHospitalInfo() {
            const urlParams = new URLSearchParams(window.location.search);
            const hospitalName = urlParams.get('hospitalName');
            if (hospitalName) {
                const hospital = hospitalData.find(h => h.name === decodeURIComponent(hospitalName));
                if (hospital) {
                    document.getElementById('hospitalName').textContent = hospital.name;
                    document.getElementById('hospitalAddress').textContent = hospital.address;
                    document.getElementById('hospitalInfo').style.display = 'block';
                }
            }
        }

        async function fetchDoctorData() {
            document.getElementById('loading').style.display = 'block';
            const apiUrl = "http://localhost/MediSync/backend/doctorDataForUser.php";
            try {
                const response = await fetch(apiUrl);
                if (!response.ok) {
                    throw new Error(`HTTP error! Status: ${response.status}`);
                }
                const fetchedData = await response.json();
                if (!fetchedData || fetchedData.length === 0) {
                    throw new Error(fetchedData.message || "No doctor data found.");
                }
                doctorsData = fetchedData.map(doctor => ({
                    id: doctor.id || doctor.doctor_id,
                    name: doctor.name || doctor.full_name,
                    specialty: doctor.specialty || doctor.specialization,
                    rating: Number(doctor.rating) || 0,
                    available: Boolean(doctor.available) || false,
                    featured: Boolean(doctor.featured) || false,
                    ticketPrice: Number(doctor.ticketPrice) || 0,
                    reviews: doctor.reviews || [],
                    image: doctor.image || '/images/default-doctor.png'
                }));
                filteredDoctors = [...doctorsData];
                renderDoctors(filteredDoctors, currentPage);
            } catch (error) {
                console.error("Error fetching doctor data:", error);
                document.getElementById('doctorList').innerHTML = '<p>Error loading doctors. Please try again later.</p>';
            } finally {
                document.getElementById('loading').style.display = 'none';
            }
        }

        function renderDoctors(doctors, page) {
            const doctorList = document.getElementById('doctorList');
            doctorList.innerHTML = '';

            const startIndex = (page - 1) * doctorsPerPage;
            const endIndex = Math.min(startIndex + doctorsPerPage, doctors.length);
            const currentDoctors = doctors.slice(startIndex, endIndex);

            currentDoctors.forEach(doctor => {
                const card = document.createElement('div');
                card.classList.add('doctor-card');
                card.setAttribute('data-specialty', doctor.specialty);
                card.setAttribute('data-rating', Math.floor(doctor.rating));
                card.setAttribute('data-available', doctor.available ? 'available' : 'not-available');

                const stars = Math.floor(doctor.rating);
                const ratingStars = '<i class="fas fa-star"></i>'.repeat(stars) +
                    (doctor.rating % 1 >= 0.5 ? '<i class="fas fa-star-half-alt"></i>' : '') +
                    '<i class="far fa-star"></i>'.repeat(5 - Math.ceil(doctor.rating));

                card.innerHTML = `
                    <img src="${doctor.image}" alt="Doctor Image" />
                    <div class="doctor-details">
                        <div class="doctor-info">
                            ${doctor.featured ? '<div class="featured-badge">Featured</div>' : ''}
                            <h3>${doctor.name}</h3>
                            <p class="speciality_doc">${doctor.specialty.charAt(0).toUpperCase() + doctor.specialty.slice(1)}</p>
                            <div class="rating">
                                ${ratingStars}
                                <span>(${doctor.rating}/5)</span>
                            </div>
                            <div class="availability-status ${doctor.available ? 'available' : 'not-available'}">
                                <i class="fas ${doctor.available ? 'fa-check-circle' : 'fa-times-circle'}"></i>
                                <span>${doctor.available ? 'Available Now' : 'Currently Unavailable'}</span>
                            </div>
                        </div>
                        <button class="consult-now-button" onclick="viewDoctorDetails(${doctor.id})">View Details</button>
                    </div>
                `;

                doctorList.appendChild(card);
            });

            setTimeout(() => {
                doctorList.querySelectorAll('.doctor-card').forEach(card => {
                    card.classList.add('fade-in');
                });
            }, 10);

            updatePagination(doctors.length);
        }

        function viewDoctorDetails(doctorId) {
            window.location.href = `/html/doctors/doctorsDetail.html?id=${doctorId}`;
        }

        function updatePagination(totalDoctors) {
            const totalPages = Math.max(1, Math.ceil(totalDoctors / doctorsPerPage));
            document.querySelector('.page-info').textContent = totalDoctors > 0 ? `Page ${currentPage} of ${totalPages}` : "No doctors found";
            const prevButton = document.querySelector('.page-button:first-child');
            const nextButton = document.querySelector('.page-button:last-child');
            prevButton.disabled = currentPage === 1 || totalDoctors === 0;
            nextButton.disabled = currentPage === totalPages || totalDoctors === 0;
        }

        function changePage(direction) {
            const doctorList = document.getElementById('doctorList');
            doctorList.classList.add('fade-out');

            setTimeout(() => {
                currentPage += direction;
                renderDoctors(filteredDoctors, currentPage);
                doctorList.classList.remove('fade-out');
            }, 300);
        }

        function filterDoctors() {
            const specialtyFilter = document.querySelectorAll('.filter-select')[0].value;
            const availabilityFilter = document.querySelectorAll('.filter-select')[1].value;
            const ratingFilter = document.querySelectorAll('.filter-select')[2].value;

            filteredDoctors = doctorsData.filter(doctor => {
                const specialtyMatch = !specialtyFilter || doctor.specialty.toLowerCase() === specialtyFilter.toLowerCase();
                const ratingMatch = !ratingFilter || Math.floor(doctor.rating) >= parseInt(ratingFilter);
                const availabilityMatch = !availabilityFilter ||
                    (availabilityFilter === 'available' && doctor.available) ||
                    (availabilityFilter === 'not-available' && !doctor.available);
                return specialtyMatch && ratingMatch && availabilityMatch;
            });

            currentPage = 1;
            renderDoctors(filteredDoctors, currentPage);
        }

        let searchTimeout;

        function searchDoctors() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                const searchTerm = document.getElementById('searchInput').value.toLowerCase();
                filteredDoctors = doctorsData.filter(doctor =>
                    doctor.name.toLowerCase().includes(searchTerm) ||
                    doctor.specialty.toLowerCase().includes(searchTerm)
                );
                currentPage = 1;
                renderDoctors(filteredDoctors, currentPage);
            }, 300);
        }

        function resetFilters() {
            const filters = document.querySelectorAll('.filter-select');
            filters.forEach(filter => filter.value = '');
            document.getElementById('searchInput').value = '';
            filteredDoctors = [...doctorsData];
            currentPage = 1;
            renderDoctors(filteredDoctors, currentPage);
        }

        document.getElementById('searchInput').addEventListener('input', searchDoctors);

        // Initialize page
        fetchDoctorData();
        displayHospitalInfo();
