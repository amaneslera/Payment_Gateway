document.addEventListener('DOMContentLoaded', function () {
    console.log('JavaScript is linked and running!');

    // Handle login form submission
    const loginForm = document.querySelector('#loginForm');
    const loginMessage = document.querySelector('#loginMessage');

    if (!loginForm) {
        console.error('Login form not found!');
        return;
    }

    loginForm.addEventListener('submit', function (event) {
        event.preventDefault();

        // Get form values
        const username = document.querySelector('#userNameBox').value;
        const password = document.querySelector('#passWordBox').value;

        console.log('Attempting login with:', username);

        // Show loading message
        if (loginMessage) {
            loginMessage.textContent = 'Logging in...';
            loginMessage.style.color = 'blue';
        }

        // Create request data
        const loginData = {
            username: username,
            password: password
        };

        console.log('Sending login request...');

        // Send login request to backend
        fetch('http://localhost/PaymentSystem/backend/login.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(loginData)
        })
        .then(response => {
            console.log('Response received:', response.status);
            return response.json(); // Parse JSON response
        })
        .then(data => {
            console.log('Login response:', data);

            if (data.status === 'success') {
                // Login successful
                loginMessage.textContent = '';

                // Store user info in session storage
                sessionStorage.setItem('user', JSON.stringify(data.user));

                // Show a custom modal pop-up
                showModal(`Welcome, ${data.user.username}!`, 3, 'usermanagement.html'); // Redirect to user management
            } else {
                // Login failed
                loginMessage.textContent = data.message || 'Login failed';
                loginMessage.style.color = 'red';
            }
        })
        .catch(error => {
            console.error('Error during login:', error);
            loginMessage.textContent = 'Error connecting to server';
            loginMessage.style.color = 'red';
        });
    });

    // Function to show a custom modal pop-up
    function showModal(message, delayInSeconds, redirectUrl) {
        // Create modal elements
        const modal = document.createElement('div');
        modal.style.position = 'fixed';
        modal.style.top = '0';
        modal.style.left = '0';
        modal.style.width = '100%';
        modal.style.height = '100%';
        modal.style.backgroundColor = 'rgba(0, 0, 0, 0.5)';
        modal.style.display = 'flex';
        modal.style.justifyContent = 'center';
        modal.style.alignItems = 'center';
        modal.style.zIndex = '1000';

        const modalContent = document.createElement('div');
        modalContent.style.backgroundColor = 'white';
        modalContent.style.padding = '20px';
        modalContent.style.borderRadius = '8px';
        modalContent.style.textAlign = 'center';
        modalContent.style.boxShadow = '0 4px 8px rgba(0, 0, 0, 0.2)';

        const modalMessage = document.createElement('h2');
        modalMessage.textContent = message;

        modalContent.appendChild(modalMessage);
        modal.appendChild(modalContent);
        document.body.appendChild(modal);

        // Automatically close the modal and redirect after the delay
        setTimeout(() => {
            modal.remove(); // Remove the modal
            window.location.href = redirectUrl; // Redirect to the specified URL
        }, delayInSeconds * 1000); // Convert seconds to milliseconds
    }
});