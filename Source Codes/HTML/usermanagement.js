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

    // Fetch user data from the backend
    function fetchUsers() {
        fetch('http://localhost/PaymentSystem/backend/user_api.php', {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json'
            }
        })
            .then(response => response.json())
            .then(users => {
                console.log('Fetched users:', users);
                populateUserTable(users);
                userCount.textContent = users.length;
            })
            .catch(error => {
                console.error('Error fetching users:', error);
            });
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
            `;
            userTableBody.appendChild(row);
        });
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
                'Content-Type': 'application/json'
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