/**
 * Transaction page JavaScript
 * Handles fetching and displaying transaction data
 */

// API base URL
const API_BASE_URL = (window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1') 
    ? 'http://localhost/Payment_Gateway/src' 
    : 'https://yourproductionurl.com';

document.addEventListener('DOMContentLoaded', function() {
    // Check authentication
    const auth = checkAuth();
    if (!auth) return;
      // Check if user has Admin role - case sensitive in our system
    checkRole('Admin');
    
    // Initialize date inputs with datepickers if available
    setupDatePickers();
    
    // Load initial transaction data
    loadTransactions();
    
    // Setup search functionality
    setupSearch();
});

/**
 * Setup date pickers and format inputs
 */
function setupDatePickers() {
    const startDateInput = document.getElementById('startDate');
    const endDateInput = document.getElementById('endDate');
    
    // Set current date as default for end date
    const today = new Date();
    endDateInput.value = formatDate(today);
    
    // Set date 30 days ago as default for start date
    const thirtyDaysAgo = new Date();
    thirtyDaysAgo.setDate(today.getDate() - 30);
    startDateInput.value = formatDate(thirtyDaysAgo);
    
    // Set min/max attributes to prevent invalid date selections
    startDateInput.setAttribute('max', formatDate(today));
    endDateInput.setAttribute('max', formatDate(today));
    
    // Ensure end date is not before start date
    startDateInput.addEventListener('change', function() {
        if (endDateInput.value && this.value > endDateInput.value) {
            endDateInput.value = this.value;
        }
    });
    
    // Ensure start date is not after end date
    endDateInput.addEventListener('change', function() {
        if (startDateInput.value && this.value < startDateInput.value) {
            startDateInput.value = this.value;
        }
    });
}

/**
 * Setup search functionality
 */
function setupSearch() {
    const searchInput = document.getElementById('searchTransaction');
    const searchBtn = document.querySelector('.search-btn');
    const applyFilterBtn = document.getElementById('applyFilter');
    
    // Search on enter key
    searchInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            loadTransactions();
        }
    });
    
    // Search on button click
    searchBtn.addEventListener('click', function() {
        loadTransactions();
    });
    
    // Apply date filter
    if (applyFilterBtn) {
        applyFilterBtn.addEventListener('click', function() {
            loadTransactions();
        });
    }
}

/**
 * Load transactions from the API
 */
function loadTransactions(page = 1) {
    // Show loading indicator
    const tableBody = document.querySelector('.transaction-table tbody');
    tableBody.innerHTML = '<tr><td colspan="6" style="text-align: center;">Loading transactions...</td></tr>';
    
    // Get filter values
    const searchTerm = document.getElementById('searchTransaction').value;
    const startDate = document.getElementById('startDate').value;
    const endDate = document.getElementById('endDate').value;
    
    // Validate date inputs
    let validStartDate = startDate;
    let validEndDate = endDate;
    
    try {
        if (startDate && !isValidDate(startDate)) {
            console.warn('Invalid start date format, using default');
            const thirtyDaysAgo = new Date();
            thirtyDaysAgo.setDate(new Date().getDate() - 30);
            validStartDate = formatDate(thirtyDaysAgo);
            document.getElementById('startDate').value = validStartDate;
        }
        
        if (endDate && !isValidDate(endDate)) {
            console.warn('Invalid end date format, using default');
            validEndDate = formatDate(new Date());
            document.getElementById('endDate').value = validEndDate;
        }
    } catch (e) {
        console.error('Date validation error:', e);
    }
    
    // Build URL
    let url = `${API_BASE_URL}/backend/api/transactions/transaction_api.php?page=${page}&limit=10`;
    
    if (searchTerm) {
        url += `&search=${encodeURIComponent(searchTerm)}`;
    }
    
    if (validStartDate) {
        url += `&start_date=${encodeURIComponent(validStartDate)}`;
    }
    
    if (validEndDate) {
        url += `&end_date=${encodeURIComponent(validEndDate)}`;
    }
    
    console.log(`Fetching transactions from: ${url}`);
    
    // Fetch transactions
    fetch(url, {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
            'Authorization': `Bearer ${localStorage.getItem('jwt_token')}`
        }
    })    .then(response => {
        if (!response.ok) {
            return response.text().then(text => {
                // Try to parse the error response as JSON
                try {
                    const errorData = JSON.parse(text);
                    throw new Error(`Server Error: ${errorData.message || 'Unknown Error'}`);
                } catch (e) {
                    // If parsing fails, return the original error
                    throw new Error(`Error: ${response.status} - ${text}`);
                }
            });
        }
        return response.json();
    })
    .then(data => {
        displayTransactions(data);
    })
    .catch(error => {
        console.error('Error fetching transactions:', error);
        tableBody.innerHTML = `<tr><td colspan="6" style="text-align: center; color: red;">
            Error loading transactions: ${error.message}</td></tr>`;
        
        // Show detailed error if it's a development environment
        if (window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1') {
            console.debug('API Error Details:', error);
        }
    });
}

/**
 * Display transactions in the table
 */
function displayTransactions(data) {
    const tableBody = document.querySelector('.transaction-table tbody');
    
    // Clear existing rows
    tableBody.innerHTML = '';
    
    if (!data.success) {
        tableBody.innerHTML = `<tr><td colspan="6" style="text-align: center; color: red;">
            Error: ${data.message}</td></tr>`;
        return;
    }
    
    // Check if data exists and has transactions array
    if (!data.data || !Array.isArray(data.data)) {
        tableBody.innerHTML = `<tr><td colspan="6" style="text-align: center; color: red;">
            Invalid data format received from server</td></tr>`;
        return;
    }
    
    if (data.data.length === 0) {
        tableBody.innerHTML = '<tr><td colspan="6" style="text-align: center;">No transactions found</td></tr>';
        return;
    }
    
    // Add rows for each transaction
    data.data.forEach(transaction => {
        const row = document.createElement('tr');
          // Add data-id attribute for row click handling
        row.setAttribute('data-id', transaction.transaction_id);
        
        // Add status class for color coding
        const statusClass = getStatusClass(transaction.status);
        
        row.innerHTML = `
            <td>${transaction.transaction_id}<br>${transaction.order_id}</td>
            <td>${transaction.date}<br>${transaction.time}</td>
            <td>${transaction.transaction_type}</td>
            <td>₱${formatCurrency(transaction.transaction_amount)}</td>
            <td>${transaction.payment_details}</td>
            <td>
                ${transaction.invoice_no}
                <span class="status-indicator ${statusClass}">${transaction.status}</span>
            </td>
        `;
        
        // Add click event for viewing transaction details
        row.addEventListener('click', function() {
            viewTransactionDetails(transaction.transaction_id);
        });
        
        tableBody.appendChild(row);
    });
    
    // Add pagination if needed
    addPagination(data.pagination);
}

/**
 * Add pagination controls
 */
function addPagination(pagination) {
    // Check if we already have a pagination element
    let paginationDiv = document.querySelector('.pagination');
    
    // If not, create one
    if (!paginationDiv) {
        paginationDiv = document.createElement('div');
        paginationDiv.className = 'pagination';
        document.querySelector('.transaction-section').appendChild(paginationDiv);
    }
    
    if (pagination.total_pages <= 1) {
        paginationDiv.style.display = 'none';
        return;
    }
    
    paginationDiv.style.display = 'flex';
    
    // Generate pagination HTML
    let paginationHtml = '';
    
    // Previous button
    paginationHtml += `<button class="page-btn ${pagination.page === 1 ? 'disabled' : ''}" 
                      ${pagination.page === 1 ? 'disabled' : `onclick="loadTransactions(${pagination.page - 1})"`}>
                      &laquo; Previous</button>`;
    
    // Page numbers
    const maxPages = 5;
    const startPage = Math.max(1, pagination.page - Math.floor(maxPages / 2));
    const endPage = Math.min(pagination.total_pages, startPage + maxPages - 1);
    
    for (let i = startPage; i <= endPage; i++) {
        paginationHtml += `<button class="page-btn ${i === pagination.page ? 'active' : ''}" 
                        onclick="loadTransactions(${i})">${i}</button>`;
    }
    
    // Next button
    paginationHtml += `<button class="page-btn ${pagination.page === pagination.total_pages ? 'disabled' : ''}" 
                      ${pagination.page === pagination.total_pages ? 'disabled' : `onclick="loadTransactions(${pagination.page + 1})"`}>
                      Next &raquo;</button>`;
    
    paginationDiv.innerHTML = paginationHtml;
}

/**
 * Format a date to YYYY-MM-DD
 */
function formatDate(date) {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
}

/**
 * Format currency with thousand separators
 */
function formatCurrency(amount) {
    return Number(amount).toLocaleString(undefined, {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
}

/**
 * Get CSS class based on transaction status
 */
function getStatusClass(status) {
    status = status ? status.toLowerCase() : '';
    
    switch(status) {
        case 'completed':
        case 'success':
        case 'approved':
            return 'status-success';
            
        case 'pending':
        case 'processing':
            return 'status-pending';
            
        case 'failed':
        case 'declined':
        case 'error':
            return 'status-failed';
            
        default:
            return 'status-default';
    }
}

/**
 * Check if a string is a valid JWT
 * This function is now redundant since it's in auth.js, but keeping for compatibility
 */
function isValidJwt(token) {
    if (!token) return false;
    
    // Simple format check: should be 3 parts separated by dots
    const parts = token.split('.');
    return parts.length === 3;
}

/**
 * Check if a string is a valid date in YYYY-MM-DD format
 */
function isValidDate(dateString) {
    // Check if the format is correct (YYYY-MM-DD)
    const regex = /^\d{4}-\d{2}-\d{2}$/;
    
    if (!regex.test(dateString)) {
        return false;
    }
    
    // Check if it's a valid date
    const parts = dateString.split('-');
    const year = parseInt(parts[0], 10);
    const month = parseInt(parts[1], 10) - 1; // JavaScript months are 0-based
    const day = parseInt(parts[2], 10);
    
    const date = new Date(year, month, day);
    
    return (
        date.getFullYear() === year &&
        date.getMonth() === month &&
        date.getDate() === day
    );
}

/**
 * View transaction details in a modal
 */
function viewTransactionDetails(transactionId) {
    console.log('Viewing transaction details for ID:', transactionId);
    
    // Check if modal container already exists
    let modalContainer = document.getElementById('transactionModal');
    
    // If not, create it
    if (!modalContainer) {
        modalContainer = document.createElement('div');
        modalContainer.id = 'transactionModal';
        modalContainer.className = 'modal';
        document.body.appendChild(modalContainer);
    }
    
    // Show loading state
    modalContainer.innerHTML = `
        <div class="modal-content">
            <span class="close-btn">&times;</span>
            <h2>Transaction Details</h2>
            <p>Loading transaction #${transactionId}...</p>
        </div>
    `;
    
    // Show the modal
    modalContainer.style.display = 'flex';
    
    // Add close functionality
    const closeBtn = modalContainer.querySelector('.close-btn');
    closeBtn.addEventListener('click', function() {
        modalContainer.style.display = 'none';
    });
    
    // Fetch transaction details
    fetch(`${API_BASE_URL}/backend/api/transactions/transaction_api.php?transaction_id=${transactionId}`, {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
            'Authorization': `Bearer ${localStorage.getItem('jwt_token')}`
        }
    })
    .then(response => {
        if (!response.ok) {
            return response.text().then(text => {
                throw new Error(`Error: ${response.status} - ${text}`);
            });
        }
        return response.json();
    })
    .then(data => {
        if (!data.success) {
            throw new Error(data.message || 'Failed to fetch transaction details');
        }
        
        // For now, display the transaction info we already have
        // This can be expanded to include more details like order items
        const transaction = data.data;        modalContainer.innerHTML = `
            <div class="modal-content">
                <span class="close-btn">&times;</span>
                <h2>Transaction #${transaction.transaction_id}</h2>
                <div class="transaction-detail">
                    <p><strong>Date:</strong> ${transaction.date} at ${transaction.time}</p>
                    <p><strong>Order ID:</strong> ${transaction.order_id}</p>
                    <p><strong>Amount:</strong> ₱${formatCurrency(transaction.transaction_amount)}</p>
                    <p><strong>Payment Method:</strong> ${transaction.payment_method}</p>
                    <p><strong>Status:</strong> ${transaction.status}</p>
                    <p><strong>Cashier:</strong> ${transaction.cashier}</p>
                    <p><strong>Invoice No:</strong> ${transaction.invoice_no}</p>
                </div>
                <div class="action-buttons">
                    <button class="print-btn">Print Receipt</button>
                </div>
            </div>
        `;
        
        // Re-add close functionality
        const newCloseBtn = modalContainer.querySelector('.close-btn');
        newCloseBtn.addEventListener('click', function() {
            modalContainer.style.display = 'none';
        });
        
        // Add print functionality
        const printBtn = modalContainer.querySelector('.print-btn');
        printBtn.addEventListener('click', function() {
            // For now, just show an alert
            alert('Print functionality will be implemented in the future.');
        });
    })
    .catch(error => {
        console.error('Error fetching transaction details:', error);
        modalContainer.innerHTML = `
            <div class="modal-content">
                <span class="close-btn">&times;</span>
                <h2>Error</h2>
                <p>Failed to load transaction details: ${error.message}</p>
            </div>
        `;
        
        // Re-add close functionality
        const newCloseBtn = modalContainer.querySelector('.close-btn');
        newCloseBtn.addEventListener('click', function() {
            modalContainer.style.display = 'none';
        });
    });
}
