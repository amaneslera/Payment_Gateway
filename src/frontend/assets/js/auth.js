// Role-Based Access Control (RBAC) for frontend pages

function checkAuth() {
    // Get JWT token and user role from localStorage
    const token = localStorage.getItem('jwt_token');
    const userRole = localStorage.getItem('user_role');
    
    // If no token exists, redirect to login
    if (!token) {
        window.location.href = 'login.html';
        return false;
    }
    
    // Check if token is valid JWT format
    if (!isValidJwt(token)) {
        localStorage.clear();
        window.location.href = 'login.html';
        return false;
    }
    
    return {
        token: token,
        role: userRole || '' // Provide empty string as fallback if userRole is null
    };
}

// Check if the user has required role
function checkRole(requiredRole) {
    const auth = checkAuth();
    
    if (!auth) return false;
    
    // Check if role exists
    if (!auth.role) {
        console.error('User role not found');
        localStorage.clear();
        window.location.href = 'login.html';
        return false;
    }
    
    // If user doesn't have the required role, redirect to appropriate dashboard
    if (auth.role.toLowerCase() !== requiredRole.toLowerCase()) {
        if (auth.role.toLowerCase() === 'admin') {
            window.location.href = 'overview.html';
            return false;
        } else if (auth.role.toLowerCase() === 'cashier') {
            window.location.href = 'cashier.html';
            return false;
        } else {
            localStorage.clear();
            window.location.href = 'login.html';
            return false;
        }
    }
    
    return true;
}

// Helper function to check if token is valid JWT
function isValidJwt(token) {
    if (!token) return false;
    const parts = token.split('.');
    return parts.length === 3;
}

// Logout function
function logout() {
    // Clear all localStorage items including user_role
    localStorage.clear();
    window.location.href = 'login.html';
}
