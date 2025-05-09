document.addEventListener("DOMContentLoaded", function () {
    const loginButton = document.querySelector(".login-button");
    const dropdown = document.querySelector(".dropdown-content");

    loginButton.addEventListener("click", function (event) {
        event.stopPropagation(); // Prevent closing when clicking the button
        dropdown.classList.toggle("show");
    });

    // Close dropdown when clicking outside
    document.addEventListener("click", function (event) {
        if (!loginButton.contains(event.target) && !dropdown.contains(event.target)) {
            dropdown.classList.remove("show");
        }
    });
});
document.addEventListener("DOMContentLoaded", function () {
    var swiper = new Swiper(".mySwiper", {
        slidesPerView: 2, 
        spaceBetween: 1, 
        loop: true, 
        autoplay: {
            delay: 3000, 
            disableOnInteraction: false, 
        },
        breakpoints: {
            1024: {
                slidesPerView: 7, // Show 7 cards on large screens
            },
            768: {
                slidesPerView: 3, // Show 3 cards on tablets
            },
            480: {
                slidesPerView: 1, // Show 1 card on mobile
            },
        }
    });
});


function toggleMenu() {
    document.querySelector("nav").classList.toggle("active");
}

