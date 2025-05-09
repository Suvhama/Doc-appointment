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

// Payment Modal Functions
function openPaymentModal() {
    const modal = document.getElementById('paymentModal');
    if (modal) {
        modal.style.display = 'flex';
        console.log("Payment modal opened");
    } else {
        console.error("Payment modal not found");
    }
}

function closePaymentModal() {
    const modal = document.getElementById('paymentModal');
    if (modal) {
        modal.style.display = 'none';
        // Reset form
        const form = document.getElementById('paymentForm');
        if (form) form.reset();
        togglePaymentDetails();
        console.log("Payment modal closed");
    } else {
        console.error("Payment modal not found");
    }
}

function togglePaymentDetails() {
    const paymentMethod = document.querySelector('input[name="paymentMethod"]:checked');
    const cardDetails = document.getElementById('cardDetails');
    const upiDetails = document.getElementById('upiDetails');
    const netbankingDetails = document.getElementById('netbankingDetails');

    // Hide all and remove required attributes
    cardDetails.style.display = 'none';
    cardDetails.querySelectorAll('input').forEach(input => input.required = false);

    upiDetails.style.display = 'none';
    upiDetails.querySelectorAll('input').forEach(input => input.required = false);

    netbankingDetails.style.display = 'none';
    netbankingDetails.querySelectorAll('select, input').forEach(input => input.required = false);

    if (paymentMethod) {
        console.log("Selected payment method:", paymentMethod.value);

        if (paymentMethod.value === 'card') {
            cardDetails.style.display = 'block';
            cardDetails.querySelectorAll('input').forEach(input => input.required = true);
        } else if (paymentMethod.value === 'upi') {
            upiDetails.style.display = 'block';
            upiDetails.querySelectorAll('input').forEach(input => input.required = true);
        } else if (paymentMethod.value === 'netbanking') {
            netbankingDetails.style.display = 'block';
            netbankingDetails.querySelectorAll('select, input').forEach(input => input.required = true);
        }
    }
}


function handlePayment(event) {
    event.preventDefault();
    console.log("Payment form submitted");

    const form = document.getElementById('paymentForm');
    if (!form) {
        console.error("Payment form not found");
        return;
    }

    // Check if a payment method is selected
    const paymentMethod = document.querySelector('input[name="paymentMethod"]:checked');
    if (!paymentMethod) {
        console.error("No payment method selected");
        alert("Please select a payment method.");
        return;
    }

    // Check form validity
    if (!form.checkValidity()) {
        console.error("Form validation failed. Please fill all required fields.");
        form.reportValidity(); // Show validation errors to the user
        return;
    }

    // Simulate payment processing
    closePaymentModal();
    const successMessage = document.getElementById('successMessage');
    if (successMessage) {
        successMessage.style.display = 'flex';
        console.log("Success message displayed");
    } else {
        console.error("Success message element not found");
    }
}

function closeSuccessMessage() {
    const successMessage = document.getElementById('successMessage');
    if (successMessage) {
        successMessage.style.display = 'none';
        console.log("Success message closed");
    } else {
        console.error("Success message element not found");
    }
    // Optionally redirect to another page
    // window.location.href = '/index.html';
}

document.querySelectorAll('input[name="paymentMethod"]').forEach(input => {
    input.addEventListener('change', togglePaymentDetails);
});


// Initial call to ensure details are hidden on load
togglePaymentDetails();