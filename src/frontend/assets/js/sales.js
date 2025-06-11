/**
 * Sales Dashboard JavaScript
 * Handles sales analytics, reporting, and data visualization
 */

// API_BASE_URL, isLocalDev, and isLiveServer are already defined in Submission.js
// which is loaded before this file

console.log('Sales Dashboard - Environment:', isLiveServer ? 'VS Code Live Server' : 'XAMPP');
console.log('Sales Dashboard - API Base URL:', API_BASE_URL);

document.addEventListener('DOMContentLoaded', function() {
    // Debug: Check if we have authentication data
    const token = localStorage.getItem('jwt_token');
    const userRole = localStorage.getItem('user_role');
    console.log('Sales Page - JWT Token exists:', !!token);
    console.log('Sales Page - User Role:', userRole);
      // Check authentication and role
    const auth = checkAuth();
    if (!auth) return;
    
    // Only admin can access sales reports
    if (!checkRole('Admin')) return;
    
    // Initialize the sales dashboard
    initializeSalesDashboard();
});

/**
 * Initialize the sales dashboard
 */
function initializeSalesDashboard() {
    console.log('Initializing sales dashboard...');
      // Set default date range (last 30 days)
    setDefaultDateRange();
      // Load initial data
    loadSalesSummary();
    loadTopProducts();
    loadProductSalesReport();
    loadCategories();
    
    // Setup event listeners
    setupEventListeners();
    
    console.log('Sales dashboard initialization complete');
}

/**
 * Load sales summary data
 */
async function loadSalesSummary() {
    try {
        const filters = getFilters();
        console.log('🔍 Sales API Filters:', filters); // Debug: Log filters being sent
        const response = await fetchWithAuth(`${API_BASE_URL}/backend/api/sales/sales_api.php?action=summary&${new URLSearchParams(filters)}`);
        
        if (!response) {
            console.error('No response received - check authentication');
            showAuthenticationError();
            return;
        }
        
        const data = await response.json();
        console.log('📊 Sales API Response:', data); // Debug: Log full API response
        
        if (data.success) {
            console.log('✅ Transaction count from API:', data.data.current.total_transactions); // Debug: Log transaction count
            updateSummaryCards(data.data);
        } else {
            console.error('Error loading sales summary:', data.message);
            showErrorInSummaryCards('API Error');
        }
    } catch (error) {
        console.error('Error fetching sales summary:', error);
        if (error.message.includes('Unexpected end of JSON input')) {
            console.error('Server returned empty response - likely authentication failure');
            showAuthenticationError();
        } else {
            showErrorInSummaryCards('Network Error');
        }
    }
}

/**
 * Update summary cards
 */
function updateSummaryCards(data) {
    const current = data.current;
    const changes = data.changes;
    
    document.getElementById('totalSales').textContent = `₱${parseFloat(current.total_sales || 0).toFixed(2)}`;
    document.getElementById('itemsSold').textContent = current.total_items_sold || 0;
    document.getElementById('totalTransactions').textContent = current.total_transactions || 0;
    document.getElementById('averageSale').textContent = `₱${parseFloat(current.average_sale || 0).toFixed(2)}`;
    
    // Update change indicators
    updateChangeIndicators(changes);
}

/**
 * Update change indicators
 */
function updateChangeIndicators(changes) {
    const changeElements = {
        'salesChange': changes.total_sales,
        'itemsChange': changes.total_items_sold,
        'transactionsChange': changes.total_transactions,
        'averageChange': changes.average_sale
    };
    
    Object.entries(changeElements).forEach(([elementId, changeData]) => {
        const element = document.getElementById(elementId);
        if (element && changeData) {
            const changePercent = changeData.change_percent || 0;
            const isPositive = changePercent >= 0;
            element.textContent = `${isPositive ? '+' : ''}${changePercent}%`;
            element.className = `card-change ${isPositive ? 'positive' : 'negative'}`;
        }
    });
}

/**
 * Load top selling products
 */
async function loadTopProducts() {
    try {
        const filters = getFilters();
        filters.limit = 10;
        const response = await fetchWithAuth(`${API_BASE_URL}/backend/api/sales/sales_api.php?action=top_products&${new URLSearchParams(filters)}`);
        
        if (!response) return;
        
        const data = await response.json();
        
        if (data.success) {
            displayTopProducts(data.data);
        } else {
            console.error('Error loading top products:', data.message);
        }
    } catch (error) {
        console.error('Error fetching top products:', error);
        document.getElementById('topProductsBody').innerHTML = 
            '<tr><td colspan="6" class="error">Error loading top products</td></tr>';
    }
}

/**
 * Display top products
 */
function displayTopProducts(products) {
    const tbody = document.getElementById('topProductsBody');
    
    if (!products || products.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6" class="no-data">No products found</td></tr>';
        return;
    }
    
    tbody.innerHTML = products.map((product, index) => `
        <tr>
            <td><span class="rank rank-${index + 1}">${product.rank || (index + 1)}</span></td>
            <td>${product.product_name}</td>
            <td>${product.category_name || 'N/A'}</td>
            <td>${product.units_sold}</td>
            <td>₱${parseFloat(product.total_revenue).toFixed(2)}</td>
            <td class="profit">₱${parseFloat(product.profit || 0).toFixed(2)}</td>
        </tr>
    `).join('');
}

/**
 * Load product sales report
 */
async function loadProductSalesReport(page = 1) {
    try {
        const filters = getFilters();
        filters.page = page;
        filters.limit = 20;
        
        const searchTerm = document.getElementById('productSearchInput').value;
        if (searchTerm) {
            filters.search = searchTerm;
        }
        
        const sortBy = document.getElementById('sortBy').value;
        if (sortBy) {
            filters.sort_by = sortBy;
        }
        
        const response = await fetchWithAuth(`${API_BASE_URL}/backend/api/sales/sales_api.php?action=product_sales&${new URLSearchParams(filters)}`);
        
        if (!response) return;
        
        const data = await response.json();
        
        if (data.success) {
            displayProductSalesReport(data.data.products);
            updatePagination(data.data.pagination);
        } else {
            console.error('Error loading products report:', data.message);
        }
    } catch (error) {
        console.error('Error fetching products report:', error);
        document.getElementById('salesReportBody').innerHTML = 
            '<tr><td colspan="9" class="error">Error loading sales report</td></tr>';
    }
}

/**
 * Display product sales report
 */
function displayProductSalesReport(products) {
    const tbody = document.getElementById('salesReportBody');
    
    if (!products || products.length === 0) {
        tbody.innerHTML = '<tr><td colspan="9" class="no-data">No sales data found</td></tr>';
        return;
    }
    
    tbody.innerHTML = products.map(product => {
        const profitMargin = product.profit_margin || '0.0';
        
        return `
            <tr>
                <td>${product.product_id}</td>
                <td>${product.product_name}</td>
                <td>${product.category_name || 'N/A'}</td>
                <td>${product.units_sold}</td>
                <td>₱${parseFloat(product.unit_price).toFixed(2)}</td>
                <td>₱${parseFloat(product.total_revenue).toFixed(2)}</td>
                <td>₱${parseFloat(product.total_cost || 0).toFixed(2)}</td>
                <td class="profit">₱${parseFloat(product.profit || 0).toFixed(2)}</td>
                <td>${profitMargin}%</td>
            </tr>
        `;
    }).join('');
}

/**
 * Load categories for filter
 */
async function loadCategories() {
    try {
        const response = await fetchWithAuth(`${API_BASE_URL}/backend/api/sales/sales_api.php?action=categories`);
        
        if (!response) return;
        
        const data = await response.json();
        
        if (data.success) {
            const select = document.getElementById('categoryFilter');
            select.innerHTML = '<option value="">All Categories</option>';
            
            data.data.forEach(category => {
                const option = document.createElement('option');
                option.value = category.category_id;
                option.textContent = category.category_name;
                select.appendChild(option);
            });
        }
    } catch (error) {
        console.error('Error loading categories:', error);
    }
}

/**
 * Get current filters
 */
function getFilters() {
    return {
        start_date: document.getElementById('startDate').value,
        end_date: document.getElementById('endDate').value,
        category_id: document.getElementById('categoryFilter').value
    };
}

/**
 * Search products
 */
function searchProducts() {
    loadProductSalesReport(1);
}

/**
 * Sort products
 */
function sortProducts() {
    loadProductSalesReport(1);
}

/**
 * Update pagination
 */
function updatePagination(pagination) {
    const container = document.getElementById('reportPagination');
    
    if (!pagination || pagination.pages <= 1) {
        container.innerHTML = '';
        return;
    }
    
    let paginationHTML = '<div class="pagination-controls">';
    
    // Previous button
    if (pagination.page > 1) {
        paginationHTML += `<button class="pagination-btn" onclick="loadProductSalesReport(${pagination.page - 1})">Previous</button>`;
    }
    
    // Page numbers
    for (let i = 1; i <= pagination.pages; i++) {
        if (i === pagination.page) {
            paginationHTML += `<span class="pagination-current">${i}</span>`;
        } else {
            paginationHTML += `<button class="pagination-btn" onclick="loadProductSalesReport(${i})">${i}</button>`;
        }
    }
    
    // Next button
    if (pagination.page < pagination.pages) {
        paginationHTML += `<button class="pagination-btn" onclick="loadProductSalesReport(${pagination.page + 1})">Next</button>`;
    }
    
    paginationHTML += '</div>';
    container.innerHTML = paginationHTML;
}

/**
 * Initialize charts (disabled - charts removed)
 */
function initializeCharts() {
    // Charts have been removed from the interface
    console.log('Chart initialization skipped - charts removed from interface');
}

/**
 * Initialize sales trend chart
 */
function initializeSalesTrendChart() {
    const ctx = document.getElementById('salesTrendChart');
    if (!ctx) return;
    
    window.salesTrendChart = new Chart(ctx.getContext('2d'), {
        type: 'line',
        data: {
            labels: [],
            datasets: [{
                label: 'Daily Sales',
                data: [],
                borderColor: '#484c8b',
                backgroundColor: 'rgba(72, 76, 139, 0.1)',
                tension: 0.4
            }]
        },        options: {
            responsive: true,
            maintainAspectRatio: false,
            aspectRatio: 2, // Width:Height ratio
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                x: {
                    display: true,
                    ticks: {
                        maxRotation: 45,
                        minRotation: 0
                    }
                },
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '₱' + value.toLocaleString();
                        }
                    }
                }
            }
        }
    });
    
    loadSalesTrendData();
}

/**
 * Initialize category chart
 */
function initializeCategoryChart() {
    const ctx = document.getElementById('categoryChart');
    if (!ctx) return;
    
    window.categoryChart = new Chart(ctx.getContext('2d'), {
        type: 'doughnut',
        data: {
            labels: [],
            datasets: [{
                data: [],
                backgroundColor: [
                    '#484c8b',
                    '#4caf50',
                    '#ff9800',
                    '#f44336',
                    '#2196f3',
                    '#9c27b0'
                ]
            }]
        },        options: {
            responsive: true,
            maintainAspectRatio: false,
            aspectRatio: 1, // Square aspect ratio for doughnut chart
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 20,
                        usePointStyle: true
                    }
                }
            }
        }
    });
    
    loadCategorySalesData();
}

/**
 * Load sales trend data
 */
async function loadSalesTrendData() {
    try {
        const filters = getFilters();
        filters.days = 7; // Last 7 days
        const response = await fetchWithAuth(`${API_BASE_URL}/backend/api/sales/sales_api.php?action=sales_trends&${new URLSearchParams(filters)}`);
        
        if (!response) return;
        
        const data = await response.json();
        
        if (data.success && window.salesTrendChart) {
            const labels = data.data.map(item => {
                const date = new Date(item.sale_date);
                return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
            });
            const values = data.data.map(item => parseFloat(item.daily_sales));
            
            window.salesTrendChart.data.labels = labels;
            window.salesTrendChart.data.datasets[0].data = values;
            window.salesTrendChart.update();
        }
    } catch (error) {
        console.error('Error loading sales trend data:', error);
    }
}

/**
 * Load category sales data
 */
async function loadCategorySalesData() {
    try {
        const filters = getFilters();
        const response = await fetchWithAuth(`${API_BASE_URL}/backend/api/sales/sales_api.php?action=category_sales&${new URLSearchParams(filters)}`);
        
        if (!response) return;
        
        const data = await response.json();
        
        if (data.success && window.categoryChart) {
            const labels = data.data.map(item => item.category_name);
            const values = data.data.map(item => parseFloat(item.total_sales));
            
            window.categoryChart.data.labels = labels;
            window.categoryChart.data.datasets[0].data = values;
            window.categoryChart.update();
        }
    } catch (error) {
        console.error('Error loading category sales data:', error);
    }
}

/**
 * Update charts (disabled - charts removed)
 */
function updateCharts() {
    // Charts have been removed from the interface
    console.log('Chart update skipped - charts removed from interface');
}

/**
 * Utility function for debouncing
 */
function debounce(func, delay) {
    let timeoutId;
    return function (...args) {
        clearTimeout(timeoutId);
        timeoutId = setTimeout(() => func.apply(this, args), delay);
    };
}

/**
 * Set default date range
 */
function setDefaultDateRange() {
    const endDate = new Date();
    const startDate = new Date();
    startDate.setDate(endDate.getDate() - 365); // Changed from 30 to 365 days to show all transactions
    
    document.getElementById('startDate').value = formatDateForInput(startDate);
    document.getElementById('endDate').value = formatDateForInput(endDate);
}

/**
 * Format date for input field
 */
function formatDateForInput(date) {
    return date.toISOString().split('T')[0];
}

/**
 * Setup event listeners
 */
function setupEventListeners() {
    // Category filter - triggers chart updates when changed
    const categoryFilter = document.getElementById('categoryFilter');
    if (categoryFilter) {
        categoryFilter.addEventListener('change', function() {
            loadSalesSummary();
            loadProductSalesReport(1); // Reload product sales with new filter
            updateCharts(); // This will update both trend and category charts
        });
    }
    
    // Search and sort
    const productSearchInput = document.getElementById('productSearchInput');
    const sortBy = document.getElementById('sortBy');
    
    if (productSearchInput) {
        productSearchInput.addEventListener('input', debounce(searchProducts, 300));
    }
    
    if (sortBy) {
        sortBy.addEventListener('change', sortProducts);
    }    // Date change events - also trigger chart updates
    const startDateInput = document.getElementById('startDate');
    const endDateInput = document.getElementById('endDate');
    
    if (startDateInput) {
        startDateInput.addEventListener('change', function() {
            if (validateDateRange()) {
                loadSalesSummary();
                loadProductSalesReport(1); // Reload product sales with new date range
                updateCharts();
            }
        });
    }
    
    if (endDateInput) {
        endDateInput.addEventListener('change', function() {
            if (validateDateRange()) {
                loadSalesSummary();
                loadProductSalesReport(1); // Reload product sales with new date range
                updateCharts();
            }
        });
    }
}

/**
 * Apply filters and refresh data
 */
function applyFilters() {
    if (!validateDateRange()) {
        return;
    }
    
    refreshAllData();
}

/**
 * Validate date range
 */
function validateDateRange() {
    const startDate = new Date(document.getElementById('startDate').value);
    const endDate = new Date(document.getElementById('endDate').value);
    
    if (startDate > endDate) {
        alert('Start date cannot be after end date');
        return false;
    }
    
    const today = new Date();
    if (endDate > today) {
        alert('End date cannot be in the future');
        return false;
    }
    
    return true;
}

/**
 * Refresh all data
 */
function refreshAllData() {
    loadSalesSummary();
    loadTopProducts();
    loadProductSalesReport(1); // Start from page 1 when refreshing
    updateCharts();
}

/**
 * Show authentication error
 */
function showAuthenticationError() {
    alert('Authentication failed. Please log in again.');
    localStorage.clear();
    window.location.href = 'login.html';
}

/**
 * Show error in summary cards
 */
function showErrorInSummaryCards(errorType) {
    document.getElementById('totalSales').textContent = errorType;
    document.getElementById('itemsSold').textContent = errorType;
    document.getElementById('totalTransactions').textContent = errorType;
    document.getElementById('averageSale').textContent = errorType;
}
 
