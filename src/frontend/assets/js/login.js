document.addEventListener('DOMContentLoaded', function () {
    console.log('JavaScript is linked and running!');

    // Handle login form submission
    const loginForm = document.querySelector('#loginForm');
    const loginMessage = document.querySelector('#loginMessage');

    if (!loginForm) {
        console.error('Login form not found!');
        return;
    }

    loginForm.addEventListener('submit', async function (event) {
        event.preventDefault(); // Prevent the default form submission

        // Get form data
        const username = document.querySelector('#userNameBox').value;
        const password = document.querySelector('#passWordBox').value;

        console.log('Attempting login with:', username);

        // Send data to the PHP backend using fetch
        try {
            const response = await fetch('../../../src/backend/services/login.php', {
                method: 'POST', // Ensure this is POST
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ username, password }), // Send the username and password as JSON
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const result = await response.json(); // Parse the JSON response
            console.log('Login response:', result);

            // Display the response message
            if (result.status === 'success') {
                loginMessage.style.color = 'green';
                loginMessage.textContent = `Welcome, ${result.user.username}!`;
                
                // Make sure we're storing the token correctly
                console.log('Received token:', result.token);
                sessionStorage.setItem('token', result.token);
                console.log('Stored token:', sessionStorage.getItem('token'));
                
                // Redirect to user management page instead of dashboard
                window.location.href = 'usermanagement.html';
            } else {
                loginMessage.style.color = 'red';
                loginMessage.textContent = `Error: ${result.message}`;
            }
        } catch (error) {
            console.error('Error:', error);
            loginMessage.textContent = 'An error occurred. Please try again.';
            loginMessage.style.color = 'red';
        }
    });

    function isValidJwt(token) {
        if (!token) return false;
        const parts = token.split('.');
        return parts.length === 3;
    }

    // Then in your fetch calls
    const token = getAuthToken();
    if (token && isValidJwt(token)) {
        // Proceed with fetch
    } else {
        // Redirect to login
        sessionStorage.clear();
        window.location.href = 'login.html';
    }
});