function validateLogin() {
    const username = document.getElementById('Username').value.trim();
    const password = document.getElementById('password').value.trim();
    if (username === '' || password === '') {
        alert('Please fill in both fields.');
        return false;
    }
    return true;
}

function continueAsGuest() {
    alert('Continuing as Guest...');
    window.location.href = 'home.html';
}

function validateRegister() {
    const name = document.getElementById('regName').value.trim();
    const username = document.getElementById('regUsername').value.trim();
    const email = document.getElementById('regEmail').value.trim();
    const password = document.getElementById('regPassword').value.trim();

    if (!name || !username || !email || !password) {
        alert('Please fill all fields.');
        return false;
    }

    alert('Account created successfully!');
    window.close();
    return false;  // Prevent actual form submission
}
