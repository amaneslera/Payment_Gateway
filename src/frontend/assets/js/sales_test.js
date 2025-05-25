// Test version of sales.js using test API endpoint
const API_BASE_URL = 'http://localhost/Payment_Gateway/src/backend/api/sales/sales_api_test.php';

document.addEventListener('DOMContentLoaded', function() {
    console.log('Sales page loaded - using test API');
    
    // Load initial data
    loadSalesSummary();
    loadSalesTrends();
    loadCategories();
    
    // Set up event listeners
    setupEventListeners();
});

function setupEventListeners() {
    // Date range picker
    const startDate = document.getElementById('startDate');
    const endDate = document.getElementById('endDate');
    
    if (startDate && endDate) {
        startDate.addEventListener('change', loadSalesSummary);
        endDate.addEventListener('change', loadSalesSummary);
    }
    
    // Category filter
    const categoryFilter = document.getElementById('categoryFilter');
    if (categoryFilter) {
        categoryFilter.addEventListener('change', function() {
            loadSalesSummary();
            loadSalesTrends();
        });
    }
    
    // Export button
    const exportBtn = document.getElementById('exportBtn');
    if (exportBtn) {
        exportBtn.addEventListener('click', exportSalesData);
    }
}

async function loadSalesSummary() {
    try {
        console.log('Loading sales summary...');
        
        const startDate = document.getElementById('startDate')?.value || '';
        const endDate = document.getElementById('endDate')?.value || '';
        const categoryId = document.getElementById('categoryFilter')?.value || '';
        
        const params = new URLSearchParams({ action: 'summary' });
        if (startDate) params.append('start_date', startDate);
        if (endDate) params.append('end_date', endDate);
        if (categoryId) params.append('category_id', categoryId);
        
        const response = await fetch(`${API_BASE_URL}?${params}`);
        console.log('Response status:', response.status);
        
        const text = await response.text();
        console.log('Raw response:', text);
        
        const data = JSON.parse(text);
        console.log('Parsed data:', data);
        
        if (data.success) {
            updateSummaryCards(data.data);
        } else {
            throw new Error(data.message || 'Failed to load sales summary');
        }
    } catch (error) {
        console.error('Error loading sales summary:', error);
        showNotification('Error loading sales summary: ' + error.message, 'error');
    }
}

async function loadSalesTrends() {
    try {
        console.log('Loading sales trends...');
        
        const params = new URLSearchParams({ action: 'sales_trends', days: 7 });
        
        const response = await fetch(`${API_BASE_URL}?${params}`);
        const text = await response.text();
        console.log('Trends raw response:', text);
        
        const data = JSON.parse(text);
        console.log('Trends data:', data);
        
        if (data.success) {
            updateTrendsChart(data.data);
        } else {
            throw new Error(data.message || 'Failed to load sales trends');
        }
    } catch (error) {
        console.error('Error loading sales trends:', error);
        showNotification('Error loading sales trends: ' + error.message, 'error');
    }
}

async function loadCategories() {
    try {
        console.log('Loading categories...');
        
        const response = await fetch(`${API_BASE_URL}?action=categories`);
        const text = await response.text();
        console.log('Categories raw response:', text);
        
        const data = JSON.parse(text);
        console.log('Categories data:', data);
        
        if (data.success) {
            updateCategoryFilter(data.data);
        } else {
            throw new Error(data.message || 'Failed to load categories');
        }
    } catch (error) {
        console.error('Error loading categories:', error);
        showNotification('Error loading categories: ' + error.message, 'error');
    }
}

function updateSummaryCards(data) {
    console.log('Updating summary cards with:', data);
    
    // Update summary cards
    const current = data.current || {};
    
    updateCard('totalSales', current.total_sales || 0, 'currency');
    updateCard('totalTransactions', current.total_transactions || 0, 'number');
    updateCard('avgSale', current.average_sale || 0, 'currency');
    updateCard('totalItems', current.total_items_sold || 0, 'number');
}

function updateCard(elementId, value, type) {
    const element = document.getElementById(elementId);
    if (element) {
        let formattedValue;
        switch (type) {
            case 'currency':
                formattedValue = '$' + parseFloat(value || 0).toFixed(2);
                break;
            case 'number':
                formattedValue = parseInt(value || 0).toLocaleString();
                break;
            default:
                formattedValue = value;
        }
        element.textContent = formattedValue;
        console.log(`Updated ${elementId} to ${formattedValue}`);
    } else {
        console.warn(`Element ${elementId} not found`);
    }
}

function updateTrendsChart(data) {
    console.log('Updating trends chart with:', data);
    
    // Simple implementation - just show in console for now
    const trendsContainer = document.getElementById('trendsChart');
    if (trendsContainer) {
        trendsContainer.innerHTML = '<div>Sales trends data loaded successfully. Check console for details.</div>';
    }
}

function updateCategoryFilter(categories) {
    console.log('Updating category filter with:', categories);
    
    const categoryFilter = document.getElementById('categoryFilter');
    if (categoryFilter) {
        // Clear existing options except "All Categories"
        const options = categoryFilter.querySelectorAll('option:not([value=""])');
        options.forEach(option => option.remove());
        
        // Add category options
        categories.forEach(category => {
            const option = document.createElement('option');
            option.value = category.category_id;
            option.textContent = category.category_name;
            categoryFilter.appendChild(option);
        });
        
        console.log('Category filter updated');
    }
}

function showNotification(message, type = 'info') {
    console.log(`${type.toUpperCase()}: ${message}`);
    
    // Simple notification - you can enhance this
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.textContent = message;
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 10px 20px;
        border-radius: 4px;
        color: white;
        z-index: 1000;
        background-color: ${type === 'error' ? '#dc3545' : '#28a745'};
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        if (notification.parentNode) {
            notification.parentNode.removeChild(notification);
        }
    }, 5000);
}

function exportSalesData() {
    console.log('Export functionality not implemented yet');
    showNotification('Export functionality will be implemented soon', 'info');
}
