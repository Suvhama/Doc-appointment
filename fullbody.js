const toggleMenu = () => {
    const nav = document.querySelector("nav");
    const hamburger = document.querySelector(".hamburger");
    
    nav.classList.toggle("active");
    
    // Change hamburger icon to X when menu is open
    if (nav.classList.contains("active")) {
        hamburger.innerHTML = "✕";
    } else {
        hamburger.innerHTML = "☰";
    }
}

// Close menu when clicking outside
document.addEventListener('click', function(event) {
    const nav = document.querySelector("nav");
    const hamburger = document.querySelector(".hamburger");
    
    if (nav.classList.contains("active") && 
        !event.target.closest('nav') && 
        !event.target.closest('.hamburger')) {
        nav.classList.remove("active");
        hamburger.innerHTML = "☰";
    }
});

// Login dropdown functionality
document.querySelector('.login-button').addEventListener('click', function() {
    this.parentElement.classList.toggle('open');
    document.querySelector('.dropdown-content').classList.toggle('show');
});

// Close dropdown when clicking elsewhere
window.addEventListener('click', function(e) {
    if (!e.target.matches('.login-button') && !e.target.matches('.arrow')) {
        const dropdowns = document.querySelectorAll('.dropdown-content');
        dropdowns.forEach(dropdown => {
            if (dropdown.classList.contains('show')) {
                dropdown.classList.remove('show');
                document.querySelector('.login-dropdown').classList.remove('open');
            }
        });
    }
});