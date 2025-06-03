// API_BASE_URL is already defined in Submission.js which is loaded before this file
// Removed duplicate declaration to fix the JavaScript error

// Utility function for authenticated API requests
async function fetchWithAuth(url, options = {}) {
    const token = localStorage.getItem('jwt_token');
    
    if (!token) {
        console.error('No authentication token found');
        return null;
    }
    
    // Add authorization header
    const headers = options.headers || {};
    headers['Authorization'] = `Bearer ${token}`;
    
    try {
        const response = await fetch(url, { ...options, headers });
        
        if (response.status === 401) {
            // Token expired or invalid
            localStorage.clear();
            window.location.href = 'login.html';
            return null;
        }
        
        return response;
    } catch (error) {
        console.error('API request failed:', error);
        return null;
    }
}

document.addEventListener('DOMContentLoaded', function() {
    console.log('Inventory Page Loaded');
      // Authentication check
    const token = localStorage.getItem('jwt_token');
    const userRole = localStorage.getItem('user_role');
    
    // Simple validation without redirect to prevent refresh loops
    if (!token || (userRole !== 'Admin' && userRole !== 'admin')) {
        console.error('Authentication failed or insufficient permissions');
        document.body.innerHTML = '<div style="text-align: center; margin-top: 100px;"><h1>Access Denied</h1><p>You do not have permission to access this page.</p><a href="login.html">Back to Login</a></div>';
        return;
    }
    
    // Elements
    const inventoryTableBody = document.getElementById('inventoryTableBody');
    const itemCount = document.getElementById('itemCount');
    const searchInput = document.getElementById('searchInput');
    const categoryFilter = document.getElementById('categoryFilter');
    const stockFilter = document.getElementById('stockFilter');
    const addItemBtn = document.getElementById('addItemBtn');
      // Fetch and display inventory data
    async function fetchInventory(filters = {}) {
        try {
            // Show loading indicator
            inventoryTableBody.innerHTML = '<tr><td colspan="9" class="text-center">Loading inventory data...</td></tr>';
            
            let queryParams = new URLSearchParams();
            
            // Add filters to query parameters
            Object.entries(filters).forEach(([key, value]) => {
                if (value) queryParams.append(key, value);
            });
            
            const queryString = queryParams.toString() ? `?${queryParams.toString()}` : '';
              // Update the API URL to match the project structure
            const apiUrl = `${API_BASE_URL}/backend/api/inventory/inventory_api.php${queryString}`;
            console.log('Fetching inventory from:', apiUrl);
            
            // Add a small delay to prevent rapid refreshes
            await new Promise(resolve => setTimeout(resolve, 100));
            
            // Make the API request
            const response = await fetchWithAuth(apiUrl);
              if (!response) {
                inventoryTableBody.innerHTML = '<tr><td colspan="9" class="text-center">Failed to fetch inventory data. Please check your connection.</td></tr>';
                
                // Reset statistics to 0 when request fails
                updateInventoryStats(0, 0, 0, 0);
                return;
            }
            
            const data = await response.json();
            
            // Check if the response is an error or an array
            if (Array.isArray(data)) {
                displayInventory(data);            } else if (data.status === 'error') {
                console.error('API Error:', data.message);
                inventoryTableBody.innerHTML = `<tr><td colspan="9" class="text-center">Error: ${data.message}</td></tr>`;
                
                // Reset statistics to 0 when there's an error
                updateInventoryStats(0, 0, 0, 0);
                
                // If it's a database connection error, show more specific message
                if (data.message.includes('Database connection failed')) {
                    inventoryTableBody.innerHTML = '<tr><td colspan="9" class="text-center">Database connection failed. Please check server configuration.</td></tr>';
                }
            } else {
                console.error('Unexpected response format:', data);
                inventoryTableBody.innerHTML = '<tr><td colspan="9" class="text-center">Error loading inventory data</td></tr>';
                
                // Reset statistics to 0 for unexpected response
                updateInventoryStats(0, 0, 0, 0);
            }} catch (error) {
            console.error('Error fetching inventory:', error);
            inventoryTableBody.innerHTML = '<tr><td colspan="9" class="text-center">Error connecting to server</td></tr>';
            
            // Reset statistics to 0 when there's an error
            updateInventoryStats(0, 0, 0, 0);
        }
    }
      // Display inventory in table
    function displayInventory(products) {
        inventoryTableBody.innerHTML = '';
        
        // Calculate inventory statistics
        let lowStockCount = 0;
        let outOfStockCount = 0;
        let totalInventoryValue = 0;
        
        products.forEach(product => {
            // Count stock status
            if (product.stock_status === 'Out of Stock') {
                outOfStockCount++;
            } else if (product.stock_status === 'Low Stock') {
                lowStockCount++;
            }
            
            // Calculate total inventory value
            const inventoryValue = parseFloat(product.price) * parseInt(product.stock_quantity);
            totalInventoryValue += inventoryValue;
            
            const row = document.createElement('tr');
            
            // Define status class based on stock status
            let statusClass = '';
            if (product.stock_status === 'Out of Stock') {
                statusClass = 'out-of-stock';
            } else if (product.stock_status === 'Low Stock') {
                statusClass = 'low-stock';
            } else {
                statusClass = 'in-stock';
            }
            
            // Calculate inventory value for display
            const displayInventoryValue = product.price * product.stock_quantity;
              row.innerHTML = `
                <td>
                    <div class="action-buttons">
                        <button class="edit-btn" data-id="${product.product_id}">Edit</button>
                        <button class="delete-btn" data-id="${product.product_id}">Delete</button>
                    </div>
                </td>
                <td>${product.product_id}</td>
                <td>${product.name}</td>
                <td>${product.category_name || 'Uncategorized'}</td>
                <td>₱${parseFloat(product.price).toFixed(2)}</td>
                <td>${product.cost_price ? '₱' + parseFloat(product.cost_price).toFixed(2) : 'N/A'}</td>
                <td>${product.stock_quantity}</td>
                <td>₱${parseFloat(displayInventoryValue).toFixed(2)}</td>
                <td><span class="stock-status ${statusClass}">${product.stock_status}</span></td>
            `;
            
            inventoryTableBody.appendChild(row);
        });

        // Update inventory statistics
        updateInventoryStats(products.length, lowStockCount, outOfStockCount, totalInventoryValue);

        // Add event listeners to buttons
        document.querySelectorAll('.edit-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const productId = this.getAttribute('data-id');
                openEditModal(productId);
            });
        });
        
        document.querySelectorAll('.delete-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const productId = this.getAttribute('data-id');
                confirmDelete(productId);
            });
        });
    }    // Function to update inventory statistics
    function updateInventoryStats(totalProducts, lowStock, outOfStock, totalValue) {
        // Update the statistics cards
        document.getElementById('itemCount').textContent = totalProducts || 0;
        document.getElementById('lowStockCount').textContent = lowStock || 0;
        document.getElementById('outOfStockCount').textContent = outOfStock || 0;
        document.getElementById('totalValue').textContent = `₱${(totalValue || 0).toFixed(2)}`;
        
        console.log('Inventory stats updated:', {
            totalProducts: totalProducts || 0,
            lowStock: lowStock || 0,
            outOfStock: outOfStock || 0,
            totalValue: (totalValue || 0).toFixed(2)
        });
    }
    
    // Function to open edit modal
    async function openEditModal(productId) {
        try {
            const response = await fetchWithAuth(`${API_BASE_URL}/backend/api/inventory/inventory_api.php?product_id=${productId}`);
            
            if (!response) return;
            
            const product = await response.json();
            
            // Populate form fields
            document.getElementById('editProductId').value = product.product_id;
            document.getElementById('editName').value = product.name;
            document.getElementById('editCategory').value = product.category_id || '';
            document.getElementById('editPrice').value = product.price;
            document.getElementById('editCostPrice').value = product.cost_price || '';
            document.getElementById('editQuantity').value = product.stock_quantity;
            document.getElementById('editBarcode').value = product.barcode || '';
            document.getElementById('editSku').value = product.sku || '';
            document.getElementById('editDescription').value = product.description || '';
            document.getElementById('editMinStockLevel').value = product.min_stock_level || 5;
            
            // Handle food item fields
            document.getElementById('editIsFood').checked = product.is_food == 1;
            const expiryField = document.querySelector('.edit-expiry-field');
            expiryField.style.display = product.is_food == 1 ? 'block' : 'none';
            
            if (product.expiry_date) {
                document.getElementById('editExpiryDate').value = product.expiry_date;
            }
            
            // Show the modal
            document.getElementById('editProductModal').style.display = 'flex';
        } catch (error) {
            console.error('Error fetching product details:', error);
        }
    }
    
    // Function to handle product deletion
    async function confirmDelete(productId) {
        if (confirm('Are you sure you want to delete this product?')) {
            try {
                const response = await fetchWithAuth(`${API_BASE_URL}/backend/api/inventory/inventory_api.php`, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ product_id: productId })
                });
                
                if (!response) return;
                
                const result = await response.json();
                
                if (result.status === 'success') {
                    alert('Product deleted successfully!');
                    // Refresh inventory with current filters
                    applyFilters();
                } else {
                    alert(`Error: ${result.message}`);
                }
            } catch (error) {
                console.error('Error deleting product:', error);
            }
        }
    }
    
    // Filter handling
    function applyFilters() {
        const filters = {
            category_id: categoryFilter.value || '',
            search: searchInput.value || ''
        };
        
        // Add stock filter if selected
        if (stockFilter.value === 'low_stock') {
            filters.low_stock = 1;
        } else if (stockFilter.value === 'out_of_stock') {
            filters.out_of_stock = 1;
        }
        
        fetchInventory(filters);
    }
      // Event listeners for filters
    searchInput.addEventListener('input', debounce(applyFilters, 300));
    categoryFilter.addEventListener('change', applyFilters);
    stockFilter.addEventListener('change', applyFilters);
    
    // Add refresh button handler
    document.getElementById('refreshBtn').addEventListener('click', function() {
        console.log('Refresh button clicked');
        // Clear any existing filters and reload all inventory
        searchInput.value = '';
        categoryFilter.value = '';
        stockFilter.value = '';
        fetchInventory();
    });
    
    // Add product form handling
    document.getElementById('addProductForm').addEventListener('submit', async function(event) {
        event.preventDefault();
        
        const formData = {
            name: document.getElementById('productName').value,
            category_id: document.getElementById('productCategory').value || null,
            price: parseFloat(document.getElementById('productPrice').value),
            cost_price: document.getElementById('productCostPrice').value ? 
                parseFloat(document.getElementById('productCostPrice').value) : null,
            stock_quantity: parseInt(document.getElementById('productQuantity').value),
            barcode: document.getElementById('productBarcode').value || null,
            sku: document.getElementById('productSku').value || null,
            description: document.getElementById('productDescription').value || null,
            min_stock_level: parseInt(document.getElementById('productMinStockLevel').value) || 5,
            is_food: document.getElementById('isFood').checked ? 1 : 0,
            supplier_id: document.getElementById('productSupplier').value || null
        };
        
        // Add expiry date for food items
        if (formData.is_food && document.getElementById('expiryDate').value) {
            formData.expiry_date = document.getElementById('expiryDate').value;
        }
        
        try {
            const response = await fetchWithAuth(`${API_BASE_URL}/backend/api/inventory/inventory_api.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(formData)
            });
            
            if (!response) return;
            
            const result = await response.json();
            
            if (result.status === 'success') {
                alert('Product added successfully!');
                document.getElementById('addProductForm').reset();
                document.getElementById('addProductModal').style.display = 'none';
                applyFilters();
            } else {
                alert(`Error: ${result.message}`);
            }
        } catch (error) {
            console.error('Error adding product:', error);
        }
    });
    
    // Edit product form handling
    document.getElementById('editProductForm').addEventListener('submit', async function(event) {
        event.preventDefault();
        
        const formData = {
            product_id: document.getElementById('editProductId').value,
            name: document.getElementById('editName').value,
            category_id: document.getElementById('editCategory').value || null,
            price: parseFloat(document.getElementById('editPrice').value),
            cost_price: document.getElementById('editCostPrice').value ? 
                parseFloat(document.getElementById('editCostPrice').value) : null,
            stock_quantity: parseInt(document.getElementById('editQuantity').value),
            barcode: document.getElementById('editBarcode').value || null,
            sku: document.getElementById('editSku').value || null,
            description: document.getElementById('editDescription').value || null,
            min_stock_level: parseInt(document.getElementById('editMinStockLevel').value) || 5,
            is_food: document.getElementById('editIsFood').checked ? 1 : 0
        };
        
        // Add expiry date for food items
        if (formData.is_food && document.getElementById('editExpiryDate').value) {
            formData.expiry_date = document.getElementById('editExpiryDate').value;
        }
        
        try {
            const response = await fetchWithAuth(`${API_BASE_URL}/backend/api/inventory/inventory_api.php`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(formData)
            });
            
            if (!response) return;
            
            const result = await response.json();
            
            if (result.status === 'success') {
                alert('Product updated successfully!');
                document.getElementById('editProductModal').style.display = 'none';
                applyFilters();
            } else {
                alert(`Error: ${result.message}`);
            }
        } catch (error) {
            console.error('Error updating product:', error);
        }
    });
    
    // Toggle food expiry date fields
    document.getElementById('isFood').addEventListener('change', function() {
        document.querySelector('.expiry-field').style.display = 
            this.checked ? 'block' : 'none';
    });
    
    document.getElementById('editIsFood').addEventListener('change', function() {
        document.querySelector('.edit-expiry-field').style.display = 
            this.checked ? 'block' : 'none';
    });
    
    // Populate dropdowns
    async function loadCategories() {
        try {
            const response = await fetchWithAuth(`${API_BASE_URL}/backend/api/inventory/inventory_api.php?action=categories`);
            
            if (!response) return;
            
            const data = await response.json();
            
            // Check if the response is an array
            if (!Array.isArray(data)) {
                if (data.status === 'error') {
                    console.error('API Error:', data.message);
                } else {
                    console.error('Unexpected response format:', data);
                }
                return;
            }
            
            const categories = data;
            const addSelect = document.getElementById('productCategory');
            const editSelect = document.getElementById('editCategory');
            const filterSelect = document.getElementById('categoryFilter');
            
            // Clear existing options (except the first one for filter)
            while (addSelect.options.length > 0) {
                addSelect.remove(0);
            }
            
            while (editSelect.options.length > 0) {
                editSelect.remove(0);
            }
            
            while (filterSelect.options.length > 1) {
                filterSelect.remove(1);
            }
            
            // Add empty option
            addSelect.add(new Option('-- Select Category --', ''));
            editSelect.add(new Option('-- Select Category --', ''));
            
            // Add categories to dropdowns
            categories.forEach(category => {
                addSelect.add(new Option(category.category_name, category.category_id));
                editSelect.add(new Option(category.category_name, category.category_id));
                filterSelect.add(new Option(category.category_name, category.category_id));
            });
        } catch (error) {
            console.error('Error loading categories:', error);
        }
    }
    
    // Load suppliers
    async function loadSuppliers() {
        try {
            const response = await fetchWithAuth(`${API_BASE_URL}/backend/api/inventory/inventory_api.php?action=suppliers`);
            
            if (!response) return;
            
            const data = await response.json();
            
            // Check if the response is an array
            if (!Array.isArray(data)) {
                if (data.status === 'error') {
                    console.error('API Error:', data.message);
                } else {
                    console.error('Unexpected response format:', data);
                }
                return;
            }
            
            const suppliers = data;
            const addSelect = document.getElementById('productSupplier');
            const editSelect = document.getElementById('editSupplier');
            
            // Clear existing options
            while (addSelect.options.length > 0) {
                addSelect.remove(0);
            }
            
            while (editSelect.options.length > 0) {
                editSelect.remove(0);
            }
            
            // Add empty option
            addSelect.add(new Option('-- Select Supplier --', ''));
            editSelect.add(new Option('-- Select Supplier --', ''));
            
            // Add suppliers to dropdowns
            suppliers.forEach(supplier => {
                addSelect.add(new Option(supplier.name, supplier.supplier_id));
                editSelect.add(new Option(supplier.name, supplier.supplier_id));
            });
        } catch (error) {
            console.error('Error loading suppliers:', error);
        }
    }
    
    // Debounce function to avoid excessive API calls
    function debounce(func, wait) {
        let timeout;
        return function(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }
    
    // Show add product modal
    addItemBtn.addEventListener('click', function() {
        document.getElementById('addProductModal').style.display = 'flex';
    });
    
    // Category and Supplier Modal Button Handlers
    document.getElementById('addNewCategoryBtn').addEventListener('click', function(e) {
        e.preventDefault();
        document.getElementById('addCategoryModal').style.display = 'flex';
    });
    
    document.getElementById('editNewCategoryBtn').addEventListener('click', function(e) {
        e.preventDefault();
        document.getElementById('addCategoryModal').style.display = 'flex';
    });
    
    document.getElementById('addNewSupplierBtn').addEventListener('click', function(e) {
        e.preventDefault();
        document.getElementById('addSupplierModal').style.display = 'flex';
    });
    
    document.getElementById('editNewSupplierBtn').addEventListener('click', function(e) {
        e.preventDefault();
        document.getElementById('addSupplierModal').style.display = 'flex';
    });
    
    // Add Category Form Handling
    document.getElementById('addCategoryForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        const categoryName = document.getElementById('categoryName').value;
        const categoryDescription = document.getElementById('categoryDescription').value;
        
        if (!categoryName) {
            alert('Category name is required');
            return;
        }
        
        try {
            const response = await fetchWithAuth(`${API_BASE_URL}/backend/api/inventory/inventory_api.php?action=categories`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    category_name: categoryName,
                    description: categoryDescription
                })
            });
            
            if (!response) return;
            
            const result = await response.json();
            
            if (result.status === 'success') {
                alert('Category added successfully!');
                document.getElementById('addCategoryForm').reset();
                document.getElementById('addCategoryModal').style.display = 'none';
                
                // Reload categories
                loadCategories();
            } else {
                alert(`Error: ${result.message}`);
            }
        } catch (error) {
            console.error('Error adding category:', error);
            alert('Failed to add category. Please try again.');
        }
    });
    
    // Add Supplier Form Handling
    document.getElementById('addSupplierForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        const supplierName = document.getElementById('supplierName').value;
        const contactPerson = document.getElementById('contactPerson').value;
        const supplierPhone = document.getElementById('supplierPhone').value;
        const supplierEmail = document.getElementById('supplierEmail').value;
        const supplierAddress = document.getElementById('supplierAddress').value;
        
        if (!supplierName) {
            alert('Supplier name is required');
            return;
        }
        
        try {
            const response = await fetchWithAuth(`${API_BASE_URL}/backend/api/inventory/inventory_api.php?action=suppliers`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    name: supplierName,
                    contact_person: contactPerson,
                    phone: supplierPhone,
                    email: supplierEmail,
                    address: supplierAddress
                })
            });
            
            if (!response) return;
            
            const result = await response.json();
            
            if (result.status === 'success') {
                alert('Supplier added successfully!');
                document.getElementById('addSupplierForm').reset();
                document.getElementById('addSupplierModal').style.display = 'none';
                
                // Reload suppliers
                loadSuppliers();
            } else {
                alert(`Error: ${result.message}`);
            }
        } catch (error) {
            console.error('Error adding supplier:', error);
            alert('Failed to add supplier. Please try again.');
        }
    });
    
    // Close modals
    document.querySelectorAll('.close-modal').forEach(closeBtn => {
        closeBtn.addEventListener('click', function() {
            this.closest('.modal').style.display = 'none';
        });
    });
    
    // Close modals with Escape key
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            document.querySelectorAll('.modal').forEach(modal => {
                modal.style.display = 'none';
            });
        }
    });
    
    // Initialize the page
    loadCategories();
    loadSuppliers();
    fetchInventory();
});