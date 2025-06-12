
document.addEventListener("DOMContentLoaded", function () {
    sessionStorage.removeItem("modalOpen"); // Ensure no old modal states persist
});

// Function to open modals
function openModal(modalId) {
    document.getElementById(modalId).style.display = "flex";
}

// Function to close modals
function closeModal(modalId) {
    document.getElementById(modalId).style.display = "none";
}

// Ensure modals do not open on page load
document.addEventListener("DOMContentLoaded", function () {
    // Select all modals
    const modals = document.querySelectorAll(".modal");
    modals.forEach((modal) => {
        modal.style.display = "none"; // Hide all modals on page load
    });

    // Attach event listeners to footer links
    document.getElementById("privacyLink").addEventListener("click", function (event) {
        event.preventDefault();
        openModal("privacyModal");
    });

    document.getElementById("termsLink").addEventListener("click", function (event) {
        event.preventDefault();
        openModal("termsModal");
    });

    document.getElementById("contactLink").addEventListener("click", function (event) {
        event.preventDefault();
        openModal("contactModal");
    });
});
