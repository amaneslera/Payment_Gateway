document.addEventListener('DOMContentLoaded', function() {
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
            let queryParams = new URLSearchParams();
            
            // Add filters to query parameters
            Object.entries(filters).forEach(([key, value]) => {
                if (value) queryParams.append(key, value);
            });
            
            const queryString = queryParams.toString() ? `?${queryParams.toString()}` : '';
            const response = await fetchWithAuth(`/backend/api/inventory/inventory_api.php${queryString}`);
            
            if (!response) return;
            
            const data = await response.json();
            
            // Check if the response is an error or an array
            if (Array.isArray(data)) {
                displayInventory(data);
            } else if (data.status === 'error') {
                console.error('API Error:', data.message);
                inventoryTableBody.innerHTML = `<tr><td colspan="9" class="text-center">Error: ${data.message}</td></tr>`;
                itemCount.textContent = '0';
            } else {
                console.error('Unexpected response format:', data);
                inventoryTableBody.innerHTML = '<tr><td colspan="9" class="text-center">Error loading inventory data</td></tr>';
                itemCount.textContent = '0';
            }
        } catch (error) {
            console.error('Error fetching inventory:', error);
            inventoryTableBody.innerHTML = '<tr><td colspan="9" class="text-center">Error connecting to server</td></tr>';
            itemCount.textContent = '0';
        }
    }
    
    // Display inventory in table
    function displayInventory(products) {
        inventoryTableBody.innerHTML = '';
        itemCount.textContent = products.length;
        
        products.forEach(product => {
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
            
            // Calculate inventory value
            const inventoryValue = product.price * product.stock_quantity;
            
            row.innerHTML = `
                <td>
                    <div class="action-buttons">
                        <button class="edit-btn" data-id="${product.product_id}">
                            <img src="image/edit-icon.png" alt="Edit">
                        </button>
                        <button class="delete-btn" data-id="${product.product_id}">
                            <img src="image/delete-icon.png" alt="Delete">
                        </button>
                    </div>
                </td>
                <td>${product.product_id}</td>
                <td>${product.name}</td>
                <td>${product.category_name || 'Uncategorized'}</td>
                <td>₱${parseFloat(product.price).toFixed(2)}</td>
                <td>${product.cost_price ? '₱' + parseFloat(product.cost_price).toFixed(2) : 'N/A'}</td>
                <td>${product.stock_quantity}</td>
                <td>₱${parseFloat(inventoryValue).toFixed(2)}</td>
                <td><span class="stock-status ${statusClass}">${product.stock_status}</span></td>
            `;
            
            inventoryTableBody.appendChild(row);
        });
        
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
    }
    
    // Function to open edit modal
    async function openEditModal(productId) {
        try {
            const response = await fetchWithAuth(`/backend/api/inventory/inventory_api.php?product_id=${productId}`);
            
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
                const response = await fetchWithAuth('/backend/api/inventory/inventory_api.php', {
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
            const response = await fetchWithAuth('/backend/api/inventory/inventory_api.php', {
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
            const response = await fetchWithAuth('/backend/api/inventory/inventory_api.php', {
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
            const response = await fetchWithAuth('/backend/api/inventory/inventory_api.php?action=categories');
            
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
            const response = await fetchWithAuth('/backend/api/inventory/inventory_api.php?action=suppliers');
            
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