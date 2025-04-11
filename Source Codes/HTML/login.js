document.addEventListener('DOMContentLoaded', function() {
    console.log('JavaScript is linked and running!');
    
    // Handle login form submission
    const loginForm = document.querySelector('#loginForm');
    loginForm.addEventListener('submit', function(event) {
        event.preventDefault();
        const username = document.querySelector('#username').value;
        const password = document.querySelector('#password').value;
        
        // Simple validation (for demonstration purposes)
        if (username === 'user' && password === 'pass') {
            document.querySelector('#loginMessage').textContent = `Welcome, ${username}!`;
        } else {
            document.querySelector('#loginMessage').textContent = 'Invalid username or password.';
        }
    });
});