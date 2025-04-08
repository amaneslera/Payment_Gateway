document.addEventListener('DOMContentLoaded', function() {
    console.log('JavaScript is linked and running!');
    
    // Handle login form submission
    const loginForm = document.querySelector('#loginForm');
    const loginMessage = document.querySelector('#loginMessage');
    
    if (!loginForm) {
        console.error('Login form not found!');
        return;
    }
    
    loginForm.addEventListener('submit', function(event) {
        event.preventDefault();
        
        // Get form values - UPDATED SELECTORS TO MATCH HTML
        const username = document.querySelector('#userNameBox').value;
        const password = document.querySelector('#passWordBox').value;
        
        console.log('Attempting login with:', username);
        
        // Show loading message
        if (loginMessage) {
            loginMessage.textContent = 'Logging in...';
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
            return response.text(); // Get raw text first
        })
        .then(text => {
            console.log('Raw response:', text);
            
            // More robust cleanup - handle any non-JSON prefix
            let jsonText = text;
            const jsonStartPos = text.indexOf('{');
            if (jsonStartPos > 0) {
                jsonText = text.substring(jsonStartPos);
            }
            
            try {
                // Parse the cleaned JSON
                const data = JSON.parse(jsonText);
                console.log('Login response:', data);
                
                if (data.status === 'success') {
                    // Login successful
                    loginMessage.textContent = '';
                    
                    // Store user info in session storage
                    sessionStorage.setItem('user', JSON.stringify(data.user));
                    
                    // Show welcome modal
                    showWelcomeModal(data.user.username);
                    
                    // Redirect to dashboard after animation completes
                    setTimeout(() => {
                        window.location.href = 'dashboard.html';
                    }, 3000); // 3 seconds
                } else {
                    // Login failed
                    loginMessage.textContent = data.message || 'Login failed';
                    loginMessage.style.color = 'red';
                }
            } catch (e) {
                console.error('JSON parse error:', e);
                loginMessage.textContent = 'Error parsing server response';
                loginMessage.style.color = 'red';
            }
        })
        .catch(error => {
            console.error('Error during login:', error);
            loginMessage.textContent = 'Error connecting to server';
            loginMessage.style.color = 'red';
        });
    });
    
    // Function to show welcome modal
    function showWelcomeModal(username) {
        const modal = document.getElementById('welcomeModal');
        const welcomeUser = document.getElementById('welcomeUser');
        
        // Set welcome message
        welcomeUser.textContent = username;
        
        // Show modal with animation
        setTimeout(() => {
            modal.classList.add('show');
        }, 100);
    }
});