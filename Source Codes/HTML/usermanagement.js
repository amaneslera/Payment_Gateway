document.addEventListener('DOMContentLoaded', function () {
    console.log('User Management Page Loaded');

    const userTableBody = document.getElementById('userTableBody');
    const userCount = document.getElementById('userCount');
    const searchInput = document.getElementById('searchInput');
    const logoutBtn = document.getElementById('logoutBtn');
    const addUserBtn = document.getElementById('addUserBtn');
    const addUserModal = document.getElementById('addUserModal');
    const closeModal = document.getElementById('closeModal');
    const addUserForm = document.getElementById('addUserForm');
    const editUserModal = document.getElementById('editUserModal');
    const closeEditModal = document.getElementById('closeEditModal');
    const editUserForm = document.getElementById('editUserForm');

    // Helper function to get authentication token
    function getAuthToken() {
        return localStorage.getItem('token'); // Changed from sessionStorage to localStorage
    }

    // Replace your current fetchUsers function with this one
    async function fetchUsers() {
        try {
            const token = getAuthToken();
            console.log('Using token:', token ? token.substring(0, 20) + '...' : 'No token');
            
            const response = await fetch('http://localhost/PaymentSystem/backend/user_api.php', {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${token}`
                }
            });
            
            // Check if response is JSON
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                // Not JSON - get the text to see the HTML error
                const text = await response.text();
                console.error('Server returned non-JSON response:', text);
                alert('Server returned an HTML error. See console for details.');
                throw new Error('Server returned non-JSON response');
            }
            
            const users = await response.json();
            console.log('Fetched users:', users);
            populateUserTable(users);
            userCount.textContent = users.length;
        } catch (error) {
            console.error('Error fetching users:', error);
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
                <td><span class="badge ${user.role.toLowerCase()}">${user.role}</span></td>
                <td>${user.updated_at || 'N/A'}</td>
                <td>${user.created_at}</td>
                <td>
                    <div class="dropdown">
                        <button class="ellipsis-btn">⋮</button>
                        <div class="dropdown-menu">
                            <button class="edit-btn" data-id="${user.user_id}">Edit</button>
                            <button class="delete-btn" data-id="${user.user_id}">Delete</button>
                        </div>
                    </div>
                </td>
            `;
            userTableBody.appendChild(row);
        });

        // Add event listeners for Edit and Delete buttons
        document.querySelectorAll('.edit-btn').forEach(button => {
            button.addEventListener('click', function(event) {
                event.stopPropagation(); // Prevent event bubbling
                const userId = this.getAttribute('data-id');
                editUser(userId);
            });
        });

        document.querySelectorAll('.delete-btn').forEach(button => {
            button.addEventListener('click', function(event) {
                event.stopPropagation(); // Prevent event bubbling
                const userId = this.getAttribute('data-id');
                deleteUser(userId);
            });
        });
    }

    // Updated editUser function to handle array response
    function editUser(userId) {
        console.log(`Edit user with ID: ${userId}`);
        
        fetch(`http://localhost/PaymentSystem/backend/user_api.php`, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${getAuthToken()}` // Added auth header
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
                    document.getElementById('editPassword').value = ''; 
                    document.getElementById('editRole').value = user.role;
                    document.getElementById('editEmail').value = user.email;
                    
                    // Show the edit modal
                    document.getElementById('editUserModal').style.display = 'flex';
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
            fetch('http://localhost/PaymentSystem/backend/user_api.php', {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${getAuthToken()}` // Added auth header
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

    // Open Add User Modal
    addUserBtn.addEventListener('click', function () {
        addUserModal.style.display = 'flex'; // Show the modal and center it
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

        fetch('http://localhost/PaymentSystem/backend/user_api.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${getAuthToken()}` // Added auth header
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
        
        fetch('http://localhost/PaymentSystem/backend/user_api.php', {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${getAuthToken()}` // Added auth header
            },
            body: JSON.stringify(updatedUser)
        })
            .then(response => response.json())
            .then(data => {
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
        sessionStorage.clear(); // Clear session storage
        window.location.href = 'login.html'; // Redirect to login page
    });

    // Fetch users on page load
    fetchUsers();
});

// Handle ellipsis button clicks to show/hide dropdowns
document.addEventListener('click', function(event) {
    // Check if click is on an ellipsis button
    if (event.target.classList.contains('ellipsis-btn')) {
        event.preventDefault();
        
        // Close all other open dropdowns first
        document.querySelectorAll('.dropdown.active').forEach(dropdown => {
            if (dropdown !== event.target.parentNode) {
                dropdown.classList.remove('active');
            }
        });
        
        // Toggle the active class on the parent dropdown
        event.target.parentNode.classList.toggle('active');
    } else if (!event.target.closest('.dropdown-menu')) {
        // If clicked outside any dropdown menu, close all dropdowns
        document.querySelectorAll('.dropdown.active').forEach(dropdown => {
            dropdown.classList.remove('active');
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