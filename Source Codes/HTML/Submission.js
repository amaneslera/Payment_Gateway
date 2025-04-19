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

                // Store JWT tokens in localStorage for persistence across browser sessions
                localStorage.setItem('token', data.token);
                localStorage.setItem('refresh_token', data.refresh_token);
                localStorage.setItem('token_expiry', Date.now() + (data.expires_in * 1000));
                
                // Store user info in localStorage
                localStorage.setItem('user', JSON.stringify(data.user));

                // Show a custom modal pop-up
                showModal(`Welcome, ${data.user.username}!`, 3, 
                    data.user.role === 'Admin' ? 'usermanagement.html' : 'cashier_dashboard.html');
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

// Add these utility functions for JWT authentication (can be used in other JS files)

// Get the authentication token from localStorage
function getAuthToken() {
    return localStorage.getItem('token');
}

// Check if the token is expired
function isTokenExpired() {
    const expiry = localStorage.getItem('token_expiry');
    return !expiry || Date.now() > parseInt(expiry);
}

// Refresh the authentication token
async function refreshAuthToken() {
    const refreshToken = localStorage.getItem('refresh_token');
    
    if (!refreshToken) {
        logout();
        return false;
    }
    
    try {
        const response = await fetch('http://localhost/PaymentSystem/backend/refresh_token.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ refresh_token: refreshToken })
        });
        
        const result = await response.json();
        
        if (result.status === 'success') {
            localStorage.setItem('token', result.token);
            localStorage.setItem('refresh_token', result.refresh_token);
            localStorage.setItem('token_expiry', Date.now() + (result.expires_in * 1000));
            return true;
        } else {
            logout();
            return false;
        }
    } catch (error) {
        console.error('Error refreshing token:', error);
        logout();
        return false;
    }
}

// Logout function - clears all stored auth data
function logout() {
    localStorage.removeItem('token');
    localStorage.removeItem('refresh_token');
    localStorage.removeItem('token_expiry');
    localStorage.removeItem('user');
    window.location.href = 'login.html';
}

// Make an authenticated API request with automatic token refresh
async function fetchWithAuth(url, options = {}) {
    // Check if token needs refresh
    if (isTokenExpired()) {
        const refreshed = await refreshAuthToken();
        if (!refreshed) {
            window.location.href = 'login.html';
            return null;
        }
    }
    
    // Add authorization header
    const headers = options.headers || {};
    headers['Authorization'] = `Bearer ${getAuthToken()}`;
    
    // Make the request
    const response = await fetch(url, {
        ...options,
        headers
    });
    
    // Handle 401 Unauthorized (token rejected)
    if (response.status === 401) {
        // Try to refresh the token
        const refreshed = await refreshAuthToken();
        if (!refreshed) {
            window.location.href = 'login.html';
            return null;
        }
        
        // Retry the request with new token
        headers['Authorization'] = `Bearer ${getAuthToken()}`;
        return fetch(url, {
            ...options,
            headers
        });
    }
    
    return response;
}