document.addEventListener('DOMContentLoaded', function() {    // Check if user is authenticated and has either Admin or Cashier role
    const userRole = localStorage.getItem('user_role');
    if (!userRole || !['admin', 'cashier'].includes(userRole.toLowerCase())) {
        alert('Access denied. You must be an Admin or Cashier to access this page.');
        window.location.href = 'login.html';
        return;
    }
    
    // Get DOM elements
    const cashierName = document.getElementById('cashierName');
    const codeInput = document.getElementById('code');
    const numButtons = document.querySelectorAll('.num-btn');
    const clearBtn = document.querySelector('.clear-btn');
    const enterBtn = document.getElementById('enterBtn');
    const logoutBtn = document.getElementById('logoutBtn');
    const productTableBody = document.getElementById('productTableBody');
    const totalAmount = document.getElementById('totalAmount');
    
    // Modals
    const paypalBtn = document.getElementById('paypalBtn');
    const paypalModal = document.getElementById('paypalModal');
    const cancelBtn = document.getElementById('cancelBtn');
    const okBtn = document.getElementById('okBtn');
    
    const cashBtn = document.getElementById('cashBtn');
    const cashModal = document.getElementById('cashModal');
    const cashReceived = document.getElementById('cashReceived');
    const changeAmount = document.getElementById('changeAmount');
    const modalTotalAmount = document.getElementById('modalTotalAmount');
    const completeBtn = document.getElementById('completeBtn');
    const cancelCashBtn = document.getElementById('cancelCashBtn');
    
    const skuBtn = document.getElementById('skuBtn');
    const skuModal = document.getElementById('skuModal');
    const skuInput = document.getElementById('skuInput');
    const quantityInput = document.getElementById('quantityInput');
    const addItemBtn = document.getElementById('addItemBtn');
    const cancelSkuBtn = document.getElementById('cancelSkuBtn');
    
    // Quantity Modal elements
    const quantityModal = document.getElementById('quantityModal');
    const quantityModalProductName = document.getElementById('quantityModalProductName');
    const quantityModalPrice = document.getElementById('quantityModalPrice');
    const quantityModalInput = document.getElementById('quantityModalInput');
    const quantityModalTotal = document.getElementById('quantityModalTotal');
    const addToCartBtn = document.getElementById('addToCartBtn');
    const cancelQuantityBtn = document.getElementById('cancelQuantityBtn');
    
    // Close buttons for modals
    const closeButtons = document.querySelectorAll('.close');
    
    // Set cashier name from localStorage
    cashierName.textContent = localStorage.getItem('username') || 'Cashier';
    
    // Shopping cart for products
    let cart = [];
    let currentTotal = 0;
    
    // Current product being processed
    let currentProduct = null;
    
    // API base URL
    const isLocalDev = window.location.hostname === '127.0.0.1' || window.location.hostname === 'localhost';
    const isLiveServer = isLocalDev && window.location.port === '5500';
    
    // Update API path to match your backend structure
    const API_BASE_URL = isLiveServer 
        ? 'http://localhost/Payment_Gateway/src' // Add /src to match backend structure
        : ''; // Using XAMPP directly
    
    // Event listeners for number buttons
    numButtons.forEach(button => {
        button.addEventListener('click', function() {
            codeInput.value += this.textContent;
        });
    });
    
    // Clear button functionality
    clearBtn.addEventListener('click', function() {
        codeInput.value = '';
    });
    
    // Enter button functionality - search for product by code
    enterBtn.addEventListener('click', function() {
        const code = codeInput.value.trim();
        if (code) {
            fetchProductByCode(code);
        }
    });
    
    // Also allow pressing Enter key in the code input
    codeInput.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            const code = codeInput.value.trim();
            if (code) {
                fetchProductByCode(code);
            }
        }
    });
    
    // Fetch product by code/barcode
    function fetchProductByCode(code) {
        const token = localStorage.getItem('jwt_token');
        
        // Log the full URL to debug
        const url = `${API_BASE_URL}/backend/api/products/product_api.php?code=${code}`;
        console.log('Fetching product from:', url);
        
        fetch(url, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${token}`
            }
        })
        .then(response => {
            console.log('Response status:', response.status);
            
            // Clone the response so we can log the body
            const clonedResponse = response.clone();
            
            // Log the raw response text for debugging
            clonedResponse.text().then(text => {
                console.log('Response raw body length:', text.length);
                console.log('Response raw body (first 200 chars):', text.substring(0, 200));
                
                // Try to parse it as JSON to see if it's valid
                try {
                    const jsonData = JSON.parse(text);
                    console.log('Response parsed as JSON:', jsonData);
                } catch (e) {
                    console.error('Response is not valid JSON:', e);
                    // Log the exact character position where parsing failed
                    console.error('JSON parse error position:', e.message);
                }
            }).catch(err => {
                console.error('Error reading response text:', err);
            });
            
            // Now continue with the original response
            return response.text().then(text => {
                // Check for empty response
                if (!text || text.trim() === '') {
                    throw new Error('Empty response from server');
                }
                
                // Remove any leading/trailing whitespace
                text = text.trim();
                
                // Look for any non-JSON content before the first '{'
                const firstBrace = text.indexOf('{');
                if (firstBrace > 0) {
                    console.warn('Unexpected content before JSON:', text.substring(0, firstBrace));
                    // Try to extract just the JSON part
                    text = text.substring(firstBrace);
                }
                
                try {
                    return JSON.parse(text);
                } catch (e) {
                    console.error('JSON parse error:', e);
                    throw new Error(`Invalid JSON response from server`);
                }
            });
        })
        .then(data => {
            console.log('Product data received:', data);
            
            if (!data) {
                throw new Error('No data received from server');
            }
            
            if (data.status === 'success' && data.product) {
                // Store the current product
                currentProduct = data.product;
                
                // Show quantity modal instead of directly adding to cart
                showQuantityModal(data.product);
                
                codeInput.value = ''; // Clear the input
            } else {
                alert(data.message || 'Product not found. Please try another code.');
            }
        })
        .catch(error => {
            console.error('Error fetching product:', error);
            alert(`Failed to fetch product: ${error.message}`);
        });
    }
    
    // Function to show quantity modal
    function showQuantityModal(product) {
        // Set the product details in the modal
        quantityModalProductName.value = product.name;
        quantityModalPrice.value = `₱${parseFloat(product.price).toFixed(2)}`;
        quantityModalInput.value = 1;
        
        // Calculate initial total
        updateQuantityModalTotal();
        
        // Show the modal
        quantityModal.style.display = 'block';
        
        // Focus on the quantity input
        quantityModalInput.focus();
        quantityModalInput.select();
    }
    
    // Update total in quantity modal when quantity changes
    quantityModalInput.addEventListener('input', updateQuantityModalTotal);

    function updateQuantityModalTotal() {
        if (currentProduct) {
            const quantity = parseInt(quantityModalInput.value) || 1;
            const total = parseFloat(currentProduct.price) * quantity;
            quantityModalTotal.value = `₱${total.toFixed(2)}`;
        }
    }

    // Add to cart button in quantity modal
    addToCartBtn.addEventListener('click', function() {
        if (currentProduct) {
            const quantity = parseInt(quantityModalInput.value) || 1;
            addProductToCart(currentProduct, quantity);
            quantityModal.style.display = 'none';
            currentProduct = null;
        }
    });

    // Cancel button in quantity modal
    cancelQuantityBtn.addEventListener('click', function() {
        quantityModal.style.display = 'none';
        currentProduct = null;
    });

    // Also handle Enter key in quantity input
    quantityModalInput.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            addToCartBtn.click();
        }
    });

    // Add product to cart
    function addProductToCart(product, quantity) {
        // Check if product is already in cart
        const existingProductIndex = cart.findIndex(item => item.product_id === product.product_id);
        
        if (existingProductIndex >= 0) {
            // Update quantity if product exists
            cart[existingProductIndex].quantity += parseInt(quantity);
            
            // Move the updated product to the top of the cart
            const updatedItem = cart.splice(existingProductIndex, 1)[0];
            cart.unshift(updatedItem);
        } else {
            // Add new product to cart at the beginning of the array (top of the list)
            cart.unshift({
                product_id: product.product_id,
                name: product.name,
                price: parseFloat(product.price),
                quantity: parseInt(quantity)
            });
        }
        
        // Update the UI
        updateCartDisplay();
        
        // Highlight the top item (the newly added/updated product)
        const firstRow = productTableBody.querySelector('tr');
        if (firstRow) {
            // Remove highlight from all rows
            document.querySelectorAll('.product-table tbody tr').forEach(row => {
                row.classList.remove('selected');
            });
            // Add highlight to the first row
            firstRow.classList.add('selected');
            // Ensure the top row is visible
            productTableBody.scrollTop = 0;
        }
    }
      // Update the cart display and total
    function updateCartDisplay() {
        productTableBody.innerHTML = '';
        currentTotal = 0;
        
        // Calculate total
        cart.forEach(item => {
            const itemTotal = item.price * item.quantity;
            currentTotal += itemTotal;
        });
        
        // Update total amount
        totalAmount.textContent = `₱${currentTotal.toFixed(2)}`;
          // Get the table wrapper for scrolling
        const tableWrapper = document.querySelector('.table-wrapper');
        
        // Reset scroll position to top
        tableWrapper.scrollTop = 0;
        
        // Add items to the table
        cart.forEach((item, index) => {
            const row = document.createElement('tr');
            row.dataset.productId = item.product_id; // Store product ID for reference
              row.innerHTML = `
                <td>
                    <div class="quantity-control">
                        <button class="qty-btn qty-minus">-</button>
                        <span class="qty-value">${item.quantity}</span>
                        <button class="qty-btn qty-plus">+</button>
                    </div>
                </td>
                <td>${item.product_id}</td>
                <td>${item.name}</td>
                <td>₱${item.price.toFixed(2)}</td>
            `;
            
            // Add click event to select/highlight row
            row.addEventListener('click', function(e) {
                // Don't handle if clicked on a button
                if (e.target.tagName === 'BUTTON') return;
                
                document.querySelectorAll('.product-table tbody tr').forEach(r => {
                    r.classList.remove('selected');
                });
                this.classList.add('selected');
            });
            
            productTableBody.appendChild(row);
            
            // Add quantity adjustment buttons functionality
            const plusBtn = row.querySelector('.qty-plus');
            const minusBtn = row.querySelector('.qty-minus');
            
            plusBtn.addEventListener('click', function() {
                cart[index].quantity += 1;
                updateCartDisplay();
                // Keep the same row selected
                document.querySelector(`tr[data-product-id="${item.product_id}"]`).classList.add('selected');
            });
            
            minusBtn.addEventListener('click', function() {
                if (cart[index].quantity > 1) {
                    cart[index].quantity -= 1;
                    updateCartDisplay();
                    // Keep the same row selected
                    document.querySelector(`tr[data-product-id="${item.product_id}"]`).classList.add('selected');
                } else {
                    // Remove item if quantity would be 0
                    if (confirm(`Remove ${item.name} from cart?`)) {
                        cart.splice(index, 1);
                        updateCartDisplay();
                    }
                }            });
        });
        
        // We already scrolled to the top at the beginning of the function
        // This ensures the newest item is always visible at the top
    }
    
    // SKU button and modal
    skuBtn.addEventListener('click', function() {
        skuModal.style.display = 'block';
        skuInput.focus();
    });
    
    // Update SKU modal to use quantity modal
    addItemBtn.addEventListener('click', function() {
        const sku = skuInput.value.trim();
        
        if (sku) {
            // Use the same fetchProductByCode function for consistency
            const url = `${API_BASE_URL}/backend/api/products/product_api.php?code=${sku}`;
            const token = localStorage.getItem('jwt_token');
            
            fetch(url, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${token}`
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`Server responded with status: ${response.status}`);
                }
                
                return response.text().then(text => {
                    if (!text || text.trim() === '') {
                        throw new Error('Empty response from server');
                    }
                    
                    try {
                        return JSON.parse(text);
                    } catch (e) {
                        throw new Error(`Invalid JSON response: ${text.substring(0, 100)}...`);
                    }
                });
            })
            .then(data => {
                if (data.status === 'success' && data.product) {
                    // Set the current product
                    currentProduct = data.product;
                    
                    // Close the SKU modal
                    skuModal.style.display = 'none';
                    
                    // Show quantity modal with pre-filled quantity
                    showQuantityModal(data.product);
                    quantityModalInput.value = quantityInput.value || 1;
                    updateQuantityModalTotal();
                    
                    // Reset the SKU input fields
                    skuInput.value = '';
                    quantityInput.value = '1';
                } else {
                    alert(data.message || 'Product not found. Please try another SKU.');
                }
            })
            .catch(error => {
                console.error('Error fetching product by SKU:', error);
                alert(`Failed to fetch product: ${error.message}`);
            });
        } else {
            alert('Please enter a valid SKU.');
        }
    });

    // PayPal Modal elements
    const paypalTotalAmount = document.getElementById('paypalTotalAmount');
    const paypalNumber = document.getElementById('paypalNumber');
    const paypalPin = document.getElementById('paypalPin');
    
    // PayPal button and modal
    paypalBtn.addEventListener('click', function() {
        if (cart.length === 0) {
            alert('Please add items to your cart before payment.');
            return;
        }
        // Set the total amount in the PayPal modal
        paypalTotalAmount.value = `₱${currentTotal.toFixed(2)}`;
        
        // Display the modal
        paypalModal.style.display = 'block';
        
        // Focus on the PayPal number field
        paypalNumber.focus();
    });
    
    // OK button in PayPal modal
    okBtn.addEventListener('click', function() {
        // Validate PayPal fields
        if (!paypalNumber.value.trim()) {
            alert('Please enter your PayPal number.');
            paypalNumber.focus();
            return;
        }
        
        if (!paypalPin.value.trim()) {
            alert('Please enter your PIN.');
            paypalPin.focus();
            return;
        }
        
        // Process the PayPal payment
        processSale('paypal');
    });
    
    // Also handle Enter key in PayPal PIN input
    paypalPin.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            okBtn.click();
        }
    });

    // Process the sale/transaction
    function processSale(paymentMethod) {
        const token = localStorage.getItem('jwt_token');
        
        const saleData = {
            payment_method: paymentMethod,
            total_amount: currentTotal,
            items: cart
        };
        
        // Add PayPal specific info if using PayPal
        if (paymentMethod === 'paypal') {
            saleData.paypal_number = paypalNumber.value.trim();
            // Note: In a real system, you'd handle the PIN more securely
        }
        
        fetch(`${API_BASE_URL}/transactions/transaction_api.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${token}`
            },
            body: JSON.stringify(saleData)
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                alert('Transaction completed successfully!');
                // Reset cart and UI
                cart = [];
                updateCartDisplay();
                // Close modals
                paypalModal.style.display = 'none';
                cashModal.style.display = 'none';
                // Reset inputs
                cashReceived.value = '';
                changeAmount.value = '';
            } else {
                alert(`Transaction failed: ${data.message}`);
            }
        })
        .catch(error => {
            console.error('Error processing sale:', error);
            alert('Failed to process the transaction. Please try again.');
        });
    }
    
    // Cancel buttons for modals
    cancelBtn.addEventListener('click', function() {
        paypalModal.style.display = 'none';
    });
    
    cancelCashBtn.addEventListener('click', function() {
        cashModal.style.display = 'none';
    });
    
    cancelSkuBtn.addEventListener('click', function() {
        skuModal.style.display = 'none';
    });
    
    // Close buttons for all modals
    closeButtons.forEach(button => {
        button.addEventListener('click', function() {
            this.closest('.modal').style.display = 'none';
        });
    });
    
    // Close modal when clicking outside
    window.addEventListener('click', function(event) {
        const modals = document.querySelectorAll('.modal');
        modals.forEach(modal => {
            if (event.target === modal) {
                modal.style.display = 'none';
            }
        });
    });
      // Logout button with confirmation
    logoutBtn.addEventListener('click', function() {
        if (confirm('Are you sure you want to log out? Any unsaved transaction will be lost.')) {
            logout(); // Function from auth.js
        }
    });
    
    // Cash button and modal
    cashBtn.addEventListener('click', function() {
        console.log('Cash button clicked!');
        
        if (cart.length === 0) {
            alert('Please add items to your cart before payment.');
            return;
        }
        
        // Set the total amount in the cash modal
        console.log('Setting total amount:', currentTotal.toFixed(2));
        modalTotalAmount.value = `₱${currentTotal.toFixed(2)}`;
        
        // Clear previous inputs
        cashReceived.value = '';
        changeAmount.value = '';
        
        // Display the modal with CSS flex display to ensure it's visible
        cashModal.style.display = 'flex';
        
        // Focus on the cash received field
        setTimeout(() => {
            cashReceived.focus();
        }, 100);
        
        console.log('Cash modal should be visible now');
    });
    
    // Quick amount buttons
    const quickAmountButtons = document.querySelectorAll('.quick-amount');
    if (quickAmountButtons) {
        quickAmountButtons.forEach(button => {
            button.addEventListener('click', function() {
                const amount = this.getAttribute('data-amount');
                if (amount === 'exact') {
                    // Set exact amount
                    cashReceived.value = parseFloat(modalTotalAmount.value.replace('₱', ''));
                } else {
                    // Add to current value or set if empty
                    const currentVal = cashReceived.value ? parseFloat(cashReceived.value) : 0;
                    cashReceived.value = currentVal + parseFloat(amount);
                }
                calculateChange();
            });
        });
    }

    // Calculate change function
    function calculateChange() {
        const total = parseFloat(modalTotalAmount.value.replace('₱', ''));
        const received = parseFloat(cashReceived.value);
        
        if (!isNaN(received) && received >= total) {
            const change = received - total;
            changeAmount.value = '₱' + change.toFixed(2);
            completeBtn.disabled = false;
        } else {
            changeAmount.value = '';
            completeBtn.disabled = true;
        }
    }

    // Update cash received input
    if (cashReceived) {
        cashReceived.addEventListener('input', calculateChange);
    }

    // Complete payment button
    if (completeBtn) {
        completeBtn.addEventListener('click', function() {
            const total = parseFloat(modalTotalAmount.value.replace('₱', ''));
            const received = parseFloat(cashReceived.value);
            const change = parseFloat(changeAmount.value.replace('₱', ''));
            
            if (isNaN(received) || received < total) {
                alert('Please enter a valid amount.');
                return;
            }

            // Save payment details
            processCashPayment(total, received, change);
        });
    }

    // Create an order and return the order ID
    function createOrder(total) {
        const token = localStorage.getItem('jwt_token');
        
        // Prepare order data
        const orderData = {
            items: cart.map(item => ({
                product_id: item.product_id,
                quantity: item.quantity,
                price: item.price
            })),
            total_amount: total,
            payment_status: 'Pending'
        };
        
        console.log('Creating order with data:', orderData);
        
        const url = `${API_BASE_URL}/backend/api/orders/create.php`;
        console.log('Sending order to:', url);
        
        // Call the API to create an order
        return fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${token}`
            },
            body: JSON.stringify(orderData)
        })
        .then(response => {
            if (!response.ok) {
                return response.text().then(text => {
                    console.error('Server returned error:', response.status, text);
                    try {
                        // Try to parse as JSON if possible
                        const errorData = JSON.parse(text);
                        throw new Error(errorData.message || 'Server error');
                    } catch (e) {
                        if (text) {
                            throw new Error(`Server error (${response.status}): ${text.substring(0, 100)}`);
                        } else {
                            throw new Error(`Server error (${response.status}): Empty response`);
                        }
                    }
                });
            }            // Try more robust response handling
            return response.text().then(text => {
                console.log('Raw response from order creation:', text);
                
                if (!text || text.trim() === '') {
                    console.error('Server returned empty response');
                    throw new Error('Empty response from server when creating order');
                }
                
                try {
                    const json = JSON.parse(text);
                    console.log('Parsed JSON response:', json);
                    return json;
                } catch (e) {
                    console.error('Failed to parse JSON response:', text);
                    console.error('Parse error details:', e);
                    // Log the response for better debugging
                    console.error('First 500 characters of response:', text.substring(0, 500));
                    
                    // Check if this is a PHP error message
                    if (text.includes('<br />') || text.includes('Fatal error')) {
                        const errorMatch = text.match(/<b>(.*?)<\/b>: (.*?) in/);
                        if (errorMatch) {
                            throw new Error(`PHP Error: ${errorMatch[1]} - ${errorMatch[2]}`);
                        }
                    }
                    
                    throw new Error(`Could not parse server response: ${e.message}`);
                }
            });
        })
        .then(data => {
            console.log('Order created successfully:', data);
            if (data.success && data.order_id) {
                return data.order_id;
            } else {
                throw new Error(data.message || 'Failed to create order');
            }
        });
    }

    function processCashPayment(total, cashReceived, changeAmount) {
        // First create the order
        createOrder(total).then(orderId => {
            // Then process the payment
            const paymentData = {
                order_id: orderId,
                payment_method: 'Cash',
                cash_received: cashReceived,
                transaction_status: 'Success'
                // Using the authenticated user from JWT token instead of cashier_id
            };
            
            // Fix the URL to match the actual file location and add debugging
            console.log('Sending payment request to:', `${API_BASE_URL}/backend/api/payments.php`);
            console.log('Payment data:', paymentData);
            
            return fetch(`${API_BASE_URL}/backend/api/payments.php`, {
                method: 'POST',
                headers: { 
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${localStorage.getItem('jwt_token')}`
                },
                body: JSON.stringify(paymentData)
            })            .then(response => {
                // Check if response is valid before parsing JSON
                if (!response.ok) {
                    return response.text().then(text => {
                        // Log the complete response for debugging
                        console.error('Server error response:', text);
                        console.error('Response status:', response.status);
                        console.error('Response headers:', [...response.headers].map(h => h.join(': ')).join('\n'));
                        
                        // Try to extract error message from HTML if possible
                        let errorMsg = text;
                        if (text.includes('<br />')) {
                            // Extract the PHP error message from the HTML
                            const errorMatch = text.match(/<b>.*?<\/b>: (.*?) in/);
                            if (errorMatch && errorMatch[1]) {
                                errorMsg = errorMatch[1];
                            }
                        }
                        
                        throw new Error(`Server returned ${response.status}: ${errorMsg}`);
                    });
                }
                
                // More robust JSON parsing
                return response.text().then(text => {
                    console.log('Raw payment response:', text);
                    
                    if (!text || text.trim() === '') {
                        throw new Error('Empty response from payment server');
                    }
                    
                    try {
                        return JSON.parse(text);
                    } catch (e) {
                        console.error('Failed to parse payment response JSON:', text);
                        throw new Error(`Invalid JSON in payment response: ${e.message}`);
                    }
                });
            });
        })
        .then(data => {
            if (data.success) {
                // Clear cart
                cart = [];
                updateCartDisplay();
                cashModal.style.display = 'none';
                
                // Show success notification
                alert('Payment completed successfully!');
                
                // Print receipt option
                if (confirm('Print receipt?')) {
                    // Simple approach for now
                    alert('Receipt printing functionality will be added in the future');
                }
            } else {
                alert('Error processing payment: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while processing payment.');
        });
    }
});
