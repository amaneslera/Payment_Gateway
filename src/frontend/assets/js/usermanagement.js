document.addEventListener('DOMContentLoaded', function () {
    console.log('User Management Page Loaded');

    // Detect environment and set base URL accordingly
    const isLocalDev = window.location.hostname === '127.0.0.1' || window.location.hostname === 'localhost';
    const isLiveServer = isLocalDev && window.location.port === '5500';

    // Set API base URL according to environment
    const BASE_URL = isLocalDev 
        ? 'http://localhost/Payment_Gateway' // Using Local Server
        : ''; // Using XAMPP directly

    console.log('Environment:', isLocalDev ? 'Local Development' : 'Production');
    console.log('API Base URL:', BASE_URL);    // Authentication check using imported auth.js functions
    const token = localStorage.getItem('jwt_token');
    const userRole = localStorage.getItem('user_role');
    
    // Simple validation without redirect to prevent refresh loops
    if (!token || userRole !== 'admin') {
        console.error('Authentication failed or insufficient permissions');
        document.body.innerHTML = '<div style="text-align: center; margin-top: 100px;"><h1>Access Denied</h1><p>You do not have permission to access this page.</p><a href="login.html">Back to Login</a></div>';
        return;
    }
    
    // Create auth object for use by other functions
    const auth = { token: token, role: userRole };

    const userTableBody = document.getElementById('userTableBody');
    const userCount = document.getElementById('userCount');
    const searchInput = document.getElementById('searchInput');
    const addUserBtn = document.getElementById('addUserBtn');
    const addUserModal = document.getElementById('addUserModal');
    const closeModal = document.getElementById('closeModal');
    const addUserForm = document.getElementById('addUserForm');
    const editUserModal = document.getElementById('editUserModal');
    const closeEditModal = document.getElementById('closeEditModal');
    const editUserForm = document.getElementById('editUserForm');

    // Get auth token from the auth object or localStorage
    function getAuthToken() {
        return auth.token || localStorage.getItem('jwt_token');
    }

    // API base URL - corrected path
    const API_BASE_URL = `${BASE_URL}/src/backend/api/users/user_api.php`;

    // Properly implemented fetchUsers function
    async function fetchUsers() {
        try {
            // Show loading state
            userTableBody.innerHTML = '<tr><td colspan="6" class="loading-message">Loading users...</td></tr>';
            
            // Get token 
            const token = getAuthToken();
            
            console.log('Fetching users with token:', token ? 'Token available' : 'No token');
            
            const response = await fetch(API_BASE_URL, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${token}`
                }
            });
            
            // Check if response is a redirect (usually due to auth issues)
            if (response.redirected) {
                console.error('API redirected the request - likely an authentication issue');
                throw new Error('Authentication error');
            }
            
            if (!response.ok) {
                throw new Error(`API error: ${response.status} ${response.statusText}`);
            }
            
            const users = await response.json();
            console.log('Users fetched successfully:', users);
            
            // Update UI with users
            populateUserTable(users);
            if (userCount) {
                userCount.textContent = users.length;
            }
        } catch (error) {
            console.error('Error fetching users:', error);
            userTableBody.innerHTML = `<tr><td colspan="6" class="error-message">Error: ${error.message}</td></tr>`;
            
            // Don't redirect automatically - just show the error
            // This prevents the blinking issue by avoiding unnecessary page reloads
        }
    }

    // Populate the user table dynamically
    function populateUserTable(users) {
        userTableBody.innerHTML = ''; // Clear existing rows
        users.forEach(user => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td><input type="checkbox"></td>
                <td>${user.username}</td>
                <td><span class="badge ${getRoleBadgeClass(user.role)}">${user.role}</span></td>
                <td>${user.updated_at || 'N/A'}</td>
                <td>${user.created_at}</td>
                <td style="overflow: visible; position: relative;">
                    <div class="dropdown">
                        <button class="ellipsis-btn" title="Actions" style="margin:0 auto;">⋮</button>
                        <div class="dropdown-menu">
                            <button class="edit-btn" data-id="${user.user_id}">Edit</button>
                            <button class="delete-btn" data-id="${user.user_id}">Delete</button>
                        </div>
                    </div>
                </td>
            `;
            userTableBody.appendChild(row);
        });        // Add event listeners to ellipsis buttons
        document.querySelectorAll('.edit-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const userId = this.getAttribute('data-id');
                editUser(userId);
                // Hide the dropdown after clicking
                this.closest('.dropdown-menu').classList.remove('show');
            });
        });

        document.querySelectorAll('.delete-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const userId = this.getAttribute('data-id');
                deleteUser(userId);
                // Hide the dropdown after clicking
                this.closest('.dropdown-menu').classList.remove('show');
            });
        });
    }

    // Close dropdowns when clicking elsewhere on the page
    document.addEventListener('click', function(e) {
        if (!e.target.matches('.ellipsis-btn')) {
            const dropdowns = document.getElementsByClassName('dropdown-menu');
            for (let i = 0; i < dropdowns.length; i++) {
                if (dropdowns[i].classList.contains('show')) {
                    dropdowns[i].classList.remove('show');
                }
            }
        }
    });

    // Open the edit user modal with user data
    function openEditModal(userId) {
        const user = users.find(u => u.id == userId);
        if (user) {
            document.getElementById('editUserId').value = user.id;
            document.getElementById('displayUserId').value = user.id;
            document.getElementById('editUsername').value = user.username;
            document.getElementById('editPassword').value = '';
            document.getElementById('editRole').value = user.role;
            document.getElementById('editEmail').value = user.email;
            
            editUserModal.style.display = 'flex';
        }
    }

    // Delete a user
    function deleteUser(userId) {
        if (confirm('Are you sure you want to delete this user?')) {
            const index = users.findIndex(u => u.id == userId);
            if (index !== -1) {
                users.splice(index, 1);
                renderUsers();
                userCount.textContent = users.length;
            }
        }
    }

    // Updated editUser function to handle array response
    function editUser(userId) {
        console.log(`Edit user with ID: ${userId}`);
        
        fetch(API_BASE_URL, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${getAuthToken()}`
            }
        })
            .then(response => {
                console.log("Response status:", response.status);
                return response.json();
            })
            .then(users => {
                console.log("Received users data:", users);
                
                // Find the specific user in the array by ID
                const user = users.find(u => u.user_id == userId);
                
                if (user) {
                    console.log("Found user to edit:", user);
                    // Populate the form with user data
                    document.getElementById('editUserId').value = user.user_id;
                    document.getElementById('displayUserId').value = user.user_id;
                    document.getElementById('editUsername').value = user.username;
                    document.getElementById('editPassword').value = ''; // Clear password field
                    document.getElementById('editRole').value = user.role;
                    document.getElementById('editEmail').value = user.email;
                    
                    // Show the edit modal
                    const editModal = document.getElementById('editUserModal');
                    
                    // Reset any transform on modal content
                    const modalContent = editModal.querySelector('.modal-content');
                    if (modalContent) {
                        modalContent.style.transform = '';
                        modalContent.style.top = '';
                    }
                    
                    // Display with flex for centering
                    editModal.style.display = 'flex';
                    editModal.style.alignItems = 'center';
                    editModal.style.justifyContent = 'center';
                } else {
                    alert('User not found');
                }
            })
            .catch(error => {
                console.error('Error fetching user details:', error);
                alert('An error occurred while fetching user details.');
            });
    }

    // Function to handle deleting a user
    function deleteUser(userId) {
        console.log(`Delete user with ID: ${userId}`);
        const confirmDelete = confirm('Are you sure you want to delete this user?');
        
        if (confirmDelete) {
            // Send delete request to the backend
            fetch(API_BASE_URL, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${getAuthToken()}`
                },
                body: JSON.stringify({ user_id: userId })
            })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        alert('User deleted successfully!');
                        fetchUsers(); // Refresh the user table
                    } else {
                        alert(`Error: ${data.message}`);
                    }
                })
                .catch(error => {
                    console.error('Error deleting user:', error);
                    alert('An error occurred while deleting the user.');
                });
        }
    }

    // Open Add User Modal - IMPROVED CENTERING
    addUserBtn.addEventListener('click', function () {
        // Reset modal content positioning first
        const modalContent = addUserModal.querySelector('.modal-content');
        if (modalContent) {
            // Remove any transform that might affect positioning
            modalContent.style.transform = '';
            modalContent.style.top = '';
        }
        
        // Display modal with flex to enable centering
        addUserModal.style.display = 'flex';
        addUserModal.style.alignItems = 'center';
        addUserModal.style.justifyContent = 'center';
    });

    // Close Add User Modal
    closeModal.addEventListener('click', function () {
        addUserModal.style.display = 'none'; // Hide the modal
    });

    // Close modal on ESC key press
    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            addUserModal.style.display = 'none'; // Hide the modal
            editUserModal.style.display = 'none'; // Hide the edit modal
        }
    });

    // Handle Add User Form Submission
    addUserForm.addEventListener('submit', function (event) {
        event.preventDefault();

        const newUser = {
            username: document.getElementById('newUsername').value,
            password: document.getElementById('newPassword').value,
            role: document.getElementById('newRole').value,
            email: document.getElementById('newEmail').value
        };

        fetch(API_BASE_URL, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${getAuthToken()}`
            },
            body: JSON.stringify(newUser)
        })
            .then(response => response.json())
            .then(data => {
                console.log('User added:', data);
                if (data.status === 'success') {
                    alert('User added successfully!');
                    addUserModal.style.display = 'none'; // Hide the modal
                    fetchUsers(); // Refresh the user table
                } else {
                    alert(`Error: ${data.message}`);
                }
            })
            .catch(error => {
                console.error('Error adding user:', error);
                alert('An error occurred while adding the user.');
            });
    });

    // Event listener for Edit User Form submission
    editUserForm.addEventListener('submit', function(event) {
        event.preventDefault();
        
        const userId = document.getElementById('editUserId').value;
        const updatedUser = {
            user_id: userId,         // Include user_id for identification in backend
            username: document.getElementById('editUsername').value,
            role: document.getElementById('editRole').value,
            email: document.getElementById('editEmail').value
        };
        
        // Only include password if it's provided
        const password = document.getElementById('editPassword').value;
        if (password) {
            updatedUser.password = password;
        }
        
        fetch(API_BASE_URL, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${getAuthToken()}`
            },
            body: JSON.stringify(updatedUser)
        })
            .then(response => response.json())
            .then(data => { // Fixed missing parenthesis here
                if (data.status === 'success') {
                    alert('User updated successfully!');
                    editUserModal.style.display = 'none';
                    fetchUsers(); // Refresh the user table
                } else {
                    alert(`Error: ${data.message}`);
                }
            })
            .catch(error => {
                console.error('Error updating user:', error);
                alert('An error occurred while updating the user.');
            });
    });

    // Search functionality
    searchInput.addEventListener('input', function () {
        const searchTerm = searchInput.value.toLowerCase();
        const rows = userTableBody.querySelectorAll('tr');
        rows.forEach(row => {
            const username = row.children[1].textContent.toLowerCase();
            if (username.includes(searchTerm)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    });

    // Logout functionality
    logoutBtn.addEventListener('click', function () {
        localStorage.clear(); // Changed from sessionStorage to localStorage
        window.location.href = '../pages/login.html'; // Redirect to login page
    });

    // Fetch users on page load
    fetchUsers();

    // Call setupModalCloseHandlers at the end of DOMContentLoaded event
    setupModalCloseHandlers();
});

// Handle ellipsis button clicks to show/hide dropdowns
document.addEventListener('click', function(event) {
    // Check if click is on an ellipsis button
    if (event.target.classList.contains('ellipsis-btn')) {
        event.preventDefault();
        event.stopPropagation();
        
        // Get the dropdown menu
        const dropdownMenu = event.target.nextElementSibling;
        
        // Close all dropdown menus
        document.querySelectorAll('.dropdown-menu.show').forEach(menu => {
            if (menu !== dropdownMenu) {
                menu.classList.remove('show');
            }
        });
        
        // Calculate fixed position for the dropdown menu
        const rect = event.target.getBoundingClientRect();
        
        // Position the dropdown - MOVED MORE TO THE RIGHT
        let top = rect.bottom + 5; // Add small offset
        let left = rect.left + 15; // Moved 15px more to the right
        
        // Make sure the dropdown doesn't go off-screen
        if (window.innerHeight - rect.bottom < 150) {
            // Not enough space below, show above the button
            top = rect.top - 80;
        }
        
        // Adjust horizontal position if needed
        if (left + 120 > window.innerWidth) {
            left = window.innerWidth - 130;
        }
        
        // Apply the fixed position with improved styling
        dropdownMenu.style.position = 'fixed';
        dropdownMenu.style.top = top + 'px';
        dropdownMenu.style.left = left + 'px';
        dropdownMenu.style.right = 'auto';
        
        // Add solid colors and prevent transparency
        dropdownMenu.style.backgroundColor = '#ffffff';
        dropdownMenu.style.background = '#ffffff';
        dropdownMenu.style.opacity = '1';
        dropdownMenu.style.zIndex = '9999';
        
        // Force a solid background
        dropdownMenu.style.boxShadow = '0 4px 8px rgba(0,0,0,0.3)';
        dropdownMenu.style.border = '2px solid #999';
        
        // Toggle the clicked dropdown menu
        dropdownMenu.classList.toggle('show');
        
        // Add a solid background element if not already present
        if (!dropdownMenu.querySelector('.solid-bg')) {
            const solidBg = document.createElement('div');
            solidBg.className = 'solid-bg';
            solidBg.style.position = 'absolute';
            solidBg.style.top = '0';
            solidBg.style.left = '0';
            solidBg.style.width = '100%';
            solidBg.style.height = '100%';
            solidBg.style.backgroundColor = '#ffffff';
            solidBg.style.zIndex = '-1';
            solidBg.style.borderRadius = '3px';
            dropdownMenu.insertBefore(solidBg, dropdownMenu.firstChild);
        }
    } else if (!event.target.closest('.dropdown-menu')) {
        // If clicked outside any dropdown menu, close all dropdowns
        document.querySelectorAll('.dropdown-menu.show').forEach(menu => {
            menu.classList.remove('show');
        });
    }
});

// Fix for ESC key not working
document.addEventListener('keydown', function(event) {
    // Check if the pressed key is Escape
    if (event.key === 'Escape' || event.key === 'Esc') {
        const addUserModal = document.getElementById('addUserModal');
        const editUserModal = document.getElementById('editUserModal');
        
        // Close both modals if they exist
        if (addUserModal) addUserModal.style.display = 'none';
        if (editUserModal) editUserModal.style.display = 'none';
        
        console.log('ESC key pressed - closing modals');
    }
});

// Fix for ESC key not working with modals
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        // Get both modals
        const editUserModal = document.getElementById('editUserModal');
        const addUserModal = document.getElementById('addUserModal');
        
        // Close the modals if they're open
        if (editUserModal && window.getComputedStyle(editUserModal).display !== 'none') {
            console.log('Closing edit user modal with ESC key');
            editUserModal.style.display = 'none';
        }
        
        if (addUserModal && window.getComputedStyle(addUserModal).display !== 'none') {
            console.log('Closing add user modal with ESC key');
            addUserModal.style.display = 'none';
        }
    }
});

document.addEventListener('DOMContentLoaded', function() {
    // Fix for close (X) buttons not working in modals
    
    // Close button for Add User modal
    const closeAddModalBtn = document.getElementById('closeModal');
    if (closeAddModalBtn) {
        closeAddModalBtn.addEventListener('click', function() {
            document.getElementById('addUserModal').style.display = 'none';
            console.log('Add User modal closed via X button');
        });
    }
    
    // Close button for Edit User modal
    const closeEditModalBtn = document.getElementById('closeEditModal');
    if (closeEditModalBtn) {
        closeEditModalBtn.addEventListener('click', function() {
            document.getElementById('editUserModal').style.display = 'none';
            console.log('Edit User modal closed via X button');
        });
    }
});

// Replace the broken setupModalCloseHandlers implementation with this fixed version
function setupModalCloseHandlers() {
    // Close Add User Modal
    const closeAddModalBtn = document.getElementById('closeModal');
    if (closeAddModalBtn) {
        closeAddModalBtn.addEventListener('click', function() {
            document.getElementById('addUserModal').style.display = 'none';
        });
    }
    
    // Close Edit User Modal
    const closeEditModalBtn = document.getElementById('closeEditModal');
    if (closeEditModalBtn) {
        closeEditModalBtn.addEventListener('click', function() {
            document.getElementById('editUserModal').style.display = 'none';
        });
    }
    
    // ESC key closes modals
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            const modals = document.querySelectorAll('.modal');
            modals.forEach(modal => {
                modal.style.display = 'none';
            });
        }
    });
    
    // Click outside modal closes it
    window.addEventListener('click', function(event) {
        const modals = document.querySelectorAll('.modal');
        modals.forEach(modal => {
            if (event.target === modal) {
                modal.style.display = 'none';
            }
        });
    });
}

// Make sure we only define getRoleBadgeClass once
function getRoleBadgeClass(role) {
    if (!role) return '';
    return role.toLowerCase(); // Convert to lowercase for CSS classes
}