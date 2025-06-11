/**
 * Overview Dashboard JavaScript
 * Handles dashboard data fetching and display for the admin overview interface
 */

// API base URL configuration (following existing pattern)
// Use existing API_BASE_URL if available, otherwise define our own
const DASHBOARD_API_BASE_URL = window.API_BASE_URL || 
    ((window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1') 
        ? 'http://localhost/Payment_Gateway/src' 
        : 'https://yourproductionurl.com');

// Dashboard data cache
let dashboardData = null;
let refreshInterval = null;

document.addEventListener('DOMContentLoaded', function() {
    // Check authentication
    const auth = checkAuth();
    if (!auth) return;

    // Check if user has Admin role
    checkRole('Admin');

    // Initialize dashboard
    initializeDashboard();
    
    // Set up auto-refresh
    setupAutoRefresh();
    
    // Set up event listeners
    setupEventListeners();
});

/**
 * Initialize the dashboard
 */
function initializeDashboard() {
    console.log('Initializing overview dashboard...');
    
    // Show loading state
    showLoadingState();
    
    // Load dashboard data
    loadDashboardData();
}

/**
 * Load comprehensive dashboard data
 */
async function loadDashboardData() {
    try {
        const response = await fetchWithAuth(`${DASHBOARD_API_BASE_URL}/backend/api/dashboard/dashboard_api.php?action=overview`);
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const result = await response.json();
        
        if (result.status === 'success') {
            dashboardData = result.data;
            displayDashboardData(dashboardData);
            hideLoadingState();
        } else {
            throw new Error(result.message || 'Failed to fetch dashboard data');
        }
        
    } catch (error) {
        console.error('Error loading dashboard data:', error);
        showErrorState('Failed to load dashboard data. Please try again.');
    }
}

/**
 * Display dashboard data in the UI
 */
function displayDashboardData(data) {
    console.log('Displaying dashboard data:', data);
    
    // Update key metrics
    updateKeyMetrics(data.metrics);
    
    // Update recent transactions
    updateRecentTransactions(data.recent_transactions);
    
    // Update cash registry
    updateCashRegistry(data.cash_registry);
    
    // Update inventory status
    updateInventoryStatus(data.inventory_status);
    
    // Update system status
    updateSystemStatus(data.system_status);
    
    // Update payment breakdown
    updatePaymentBreakdown(data.payment_breakdown);
    
    // Update last updated time
    updateLastUpdatedTime(data.last_updated);
}

/**
 * Update key performance metrics cards
 */
function updateKeyMetrics(metrics) {
    // Today's Sales
    updateMetricCard('today-sales', {
        value: `₱${formatCurrency(metrics.today_sales.value)}`,
        change: metrics.today_sales.change,
        trend: metrics.today_sales.trend,
        subtitle: 'Today\'s Revenue'
    });
    
    // Today's Transactions
    updateMetricCard('today-transactions', {
        value: metrics.today_transactions.value.toString(),
        change: metrics.today_transactions.change,
        trend: metrics.today_transactions.trend,
        subtitle: 'Transactions Today'
    });
    
    // Monthly Sales
    updateMetricCard('monthly-sales', {
        value: `₱${formatCurrency(metrics.monthly_sales.value)}`,
        subtitle: `${metrics.monthly_sales.transactions} transactions this month`
    });
    
    // Active Users
    updateMetricCard('total-users', {
        value: metrics.total_users.value.toString(),
        subtitle: 'Active System Users'
    });
    
    // Inventory Alerts
    const alertCount = metrics.inventory_alerts.low_stock + metrics.inventory_alerts.out_of_stock;
    updateMetricCard('inventory-alerts', {
        value: alertCount.toString(),
        subtitle: `${metrics.inventory_alerts.low_stock} low stock, ${metrics.inventory_alerts.out_of_stock} out of stock`,
        alert: alertCount > 0
    });
}

/**
 * Update a metric card
 */
function updateMetricCard(cardId, data) {
    const card = document.getElementById(cardId);
    if (!card) return;
    
    const valueElement = card.querySelector('.metric-value');
    const subtitleElement = card.querySelector('.metric-subtitle');
    const changeElement = card.querySelector('.metric-change');
    
    if (valueElement) valueElement.textContent = data.value;
    if (subtitleElement) subtitleElement.textContent = data.subtitle;
    
    if (changeElement && data.change !== undefined) {
        const changeText = data.change > 0 ? `+${data.change}%` : `${data.change}%`;
        changeElement.textContent = changeText;
        changeElement.className = `metric-change ${data.trend}`;
    }
    
    // Add alert styling if needed
    if (data.alert) {
        card.classList.add('alert');
    } else {
        card.classList.remove('alert');
    }
}

/**
 * Update recent transactions table
 */
function updateRecentTransactions(transactions) {
    const tableBody = document.querySelector('#recent-transactions-table tbody');
    if (!tableBody) return;
    
    tableBody.innerHTML = '';
    
    if (transactions.length === 0) {
        const row = document.createElement('tr');
        row.innerHTML = '<td colspan="3" class="no-data">No recent transactions</td>';
        tableBody.appendChild(row);
        return;
    }
    
    transactions.forEach(transaction => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>
                <div class="transaction-info">
                    <strong>${transaction.invoice_no}</strong>
                    <small>${transaction.date} ${transaction.time}</small>
                </div>
            </td>
            <td>
                <div class="amount">₱${formatCurrency(transaction.amount)}</div>
                <small class="cashier">by ${transaction.cashier}</small>
            </td>
            <td>
                <span class="payment-method ${transaction.payment_method.toLowerCase()}">${transaction.payment_method}</span>
            </td>
        `;
        
        // Add click event to view transaction details
        row.addEventListener('click', () => {
            viewTransactionDetails(transaction.order_id);
        });
        
        tableBody.appendChild(row);
    });
}

/**
 * Update cash registry section
 */
function updateCashRegistry(cashData) {
    const registryTable = document.querySelector('#cash-registry-table tbody');
    if (!registryTable) return;
    
    registryTable.innerHTML = `
        <tr>
            <td>${formatDate(cashData.date)}</td>
            <td>
                <div class="cash-summary">
                    <div><strong>₱${formatCurrency(cashData.cash_on_hand)}</strong></div>
                    <small>${cashData.cash_transactions} transactions</small>
                </div>
            </td>
        </tr>
        <tr class="cash-details">
            <td colspan="2">
                <div class="cash-breakdown">
                    <span>Received: ₱${formatCurrency(cashData.total_received)}</span>
                    <span>Change: ₱${formatCurrency(cashData.total_change)}</span>
                    <span>Net: ₱${formatCurrency(cashData.net_sales)}</span>
                </div>
            </td>
        </tr>
    `;
}

/**
 * Update inventory status section
 */
function updateInventoryStatus(inventoryData) {
    const inventoryTable = document.querySelector('#inventory-status-table tbody');
    if (!inventoryTable) return;
    
    inventoryTable.innerHTML = '';
    
    if (inventoryData.critical_items.length === 0) {
        const row = document.createElement('tr');
        row.innerHTML = '<td colspan="2" class="no-data">All items are well stocked</td>';
        inventoryTable.appendChild(row);
        return;
    }
    
    inventoryData.critical_items.forEach(item => {
        const row = document.createElement('tr');
        const statusClass = item.status.toLowerCase().replace(' ', '-');
        
        let statusText = item.status;
        if (item.days_to_expiry !== null && item.days_to_expiry <= 7) {
            statusText += ` (Expires in ${item.days_to_expiry} days)`;
        }
        
        row.innerHTML = `
            <td>
                <div class="product-info">
                    <strong>${item.name}</strong>
                    <small>Stock: ${item.stock_quantity}/${item.min_stock_level}</small>
                </div>
            </td>
            <td>
                <span class="stock-status ${statusClass}">${statusText}</span>
            </td>
        `;
        
        // Add click event to view product details
        row.addEventListener('click', () => {
            viewProductDetails(item.product_id);
        });
        
        inventoryTable.appendChild(row);
    });
}

/**
 * Update system status indicators
 */
function updateSystemStatus(systemData) {
    // Update status indicators
    updateStatusIndicator('db-status', systemData.database, 'Database');
    updateStatusIndicator('server-status', systemData.uptime_status, 'Server');
    
    // Update system info
    const systemInfo = document.getElementById('system-info');
    if (systemInfo) {
        systemInfo.innerHTML = `
            <div class="system-metric">
                <label>Active Users:</label>
                <span>${systemData.active_users}</span>
            </div>
            <div class="system-metric">
                <label>Recent Orders:</label>
                <span>${systemData.recent_orders} (last hour)</span>
            </div>
            <div class="system-metric">
                <label>Last Activity:</label>
                <span>${systemData.last_activity}</span>
            </div>
            <div class="system-metric">
                <label>Server Time:</label>
                <span>${formatDateTime(systemData.server_time)}</span>
            </div>
        `;
    }
}

/**
 * Update status indicator
 */
function updateStatusIndicator(elementId, status, label) {
    const indicator = document.getElementById(elementId);
    if (!indicator) return;
    
    const statusClass = status === 'healthy' || status === 'online' ? 'healthy' : 'error';
    const statusText = status === 'healthy' || status === 'online' ? 'Online' : 'Error';
    
    indicator.innerHTML = `
        <div class="status-indicator ${statusClass}">
            <div class="status-dot"></div>
            <span>${label}: ${statusText}</span>
        </div>
    `;
}

/**
 * Update payment method breakdown
 */
function updatePaymentBreakdown(paymentData) {
    const breakdownContainer = document.getElementById('payment-breakdown');
    if (!breakdownContainer) return;
    
    breakdownContainer.innerHTML = '';
    
    if (Object.keys(paymentData).length === 0) {
        breakdownContainer.innerHTML = '<div class="no-data">No payments today</div>';
        return;
    }
    
    Object.entries(paymentData).forEach(([method, data]) => {
        const methodDiv = document.createElement('div');
        methodDiv.className = 'payment-method-item';
        methodDiv.innerHTML = `
            <div class="method-name">${method}</div>
            <div class="method-stats">
                <span class="count">${data.count} transactions</span>
                <span class="amount">₱${formatCurrency(data.amount)}</span>
            </div>
        `;
        breakdownContainer.appendChild(methodDiv);
    });
}

/**
 * Update last updated time
 */
function updateLastUpdatedTime(timestamp) {
    const lastUpdated = document.getElementById('last-updated');
    if (lastUpdated) {
        lastUpdated.textContent = `Last updated: ${formatDateTime(timestamp)}`;
    }
}

/**
 * Show loading state
 */
function showLoadingState() {
    const loadingOverlay = document.getElementById('loading-overlay');
    if (loadingOverlay) {
        loadingOverlay.style.display = 'flex';
    }
}

/**
 * Hide loading state
 */
function hideLoadingState() {
    const loadingOverlay = document.getElementById('loading-overlay');
    if (loadingOverlay) {
        loadingOverlay.style.display = 'none';
    }
}

/**
 * Show error state
 */
function showErrorState(message) {
    hideLoadingState();
    
    // Show error message in the main content area
    const mainContent = document.querySelector('.main-content');
    if (mainContent) {
        const errorDiv = document.createElement('div');
        errorDiv.className = 'error-message';
        errorDiv.innerHTML = `
            <div class="error-content">
                <h3>Error Loading Dashboard</h3>
                <p>${message}</p>
                <button onclick="loadDashboardData()" class="retry-btn">Retry</button>
            </div>
        `;
        mainContent.appendChild(errorDiv);
    }
}

/**
 * Setup auto-refresh for dashboard data
 */
function setupAutoRefresh() {
    // Refresh every 30 seconds
    refreshInterval = setInterval(() => {
        loadDashboardData();
    }, 30000);
}

/**
 * Setup event listeners
 */
function setupEventListeners() {
    // Quick action buttons
    const quickActions = document.querySelectorAll('.quick-action-btn');
    quickActions.forEach(btn => {
        btn.addEventListener('click', function() {
            const action = this.dataset.action;
            handleQuickAction(action);
        });
    });
    
    // Refresh button
    const refreshBtn = document.getElementById('refresh-dashboard');
    if (refreshBtn) {
        refreshBtn.addEventListener('click', function() {
            loadDashboardData();
        });
    }
    
    // Auto-refresh toggle
    const autoRefreshToggle = document.getElementById('auto-refresh-toggle');
    if (autoRefreshToggle) {
        autoRefreshToggle.addEventListener('change', function() {
            if (this.checked) {
                setupAutoRefresh();
            } else {
                if (refreshInterval) {
                    clearInterval(refreshInterval);
                    refreshInterval = null;
                }
            }
        });
    }
}

/**
 * Handle quick action buttons
 */
function handleQuickAction(action) {
    switch (action) {
        case 'view-inventory':
            window.location.href = 'inventory.html';
            break;
        case 'manage-users':
            window.location.href = 'usermanagement.html';
            break;
        case 'view-transactions':
            window.location.href = 'transaction.html';
            break;
        case 'sales-report':
            window.location.href = 'sales.html';
            break;
        default:
            console.log('Unknown action:', action);
    }
}

/**
 * View transaction details (navigate to transaction page)
 */
function viewTransactionDetails(orderId) {
    window.location.href = `transaction.html?order_id=${orderId}`;
}

/**
 * View product details (navigate to inventory page)
 */
function viewProductDetails(productId) {
    window.location.href = `inventory.html?product_id=${productId}`;
}

/**
 * Utility function to format currency
 */
function formatCurrency(amount) {
    return parseFloat(amount).toLocaleString('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
}

/**
 * Utility function to format date
 */
function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric'
    });
}

/**
 * Utility function to format date and time
 */
function formatDateTime(dateTimeString) {
    const date = new Date(dateTimeString);
    return date.toLocaleString('en-US', {
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

/**
 * Cleanup when page is unloaded
 */
window.addEventListener('beforeunload', function() {
    if (refreshInterval) {
        clearInterval(refreshInterval);
    }
});
