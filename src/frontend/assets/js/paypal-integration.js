/**
 * PayPal SDK Integration for Cashier System
 * Handles PayPal Sandbox integration with proper authentication persistence
 */

class PayPalIntegration {
    constructor() {
        this.isInitialized = false;
        this.currentOrderTotal = 0;
        this.currentCartItems = [];
        this.paypalModal = null;
        this.completionModal = null;
        this.initializeModals();
    }

    /**
     * Initialize PayPal SDK
     */
    async initialize() {
        if (this.isInitialized) return;

        try {
            // Load PayPal SDK dynamically
            await this.loadPayPalSDK();
            
            // Initialize PayPal buttons
            this.initializePayPalButtons();
            
            this.isInitialized = true;
            console.log('PayPal SDK initialized successfully');
        } catch (error) {
            console.error('Failed to initialize PayPal SDK:', error);
            throw new Error('PayPal initialization failed');
        }
    }

    /**
     * Load PayPal SDK script
     */
    loadPayPalSDK() {
        return new Promise((resolve, reject) => {            // Check if PayPal SDK is already loaded
            if (window.paypal) {
                resolve();
                return;
            }            const script = document.createElement('script');
            const config = window.PayPalConfig || {};
            const clientId = config.clientId || 'AYsyJdpE_ODIUfYVD9ghBQmGUH4K5LlZZDJYzFWOCCjLEDk7Ky8dE7fCJ5rXz3_g-Z8V-QJ-j6QJ6J-J';
            const currency = config.currency || 'PHP';
            const components = config.components || 'buttons';
            const enableFunding = config.enableFunding || 'paypal,card,credit';
            
            // Build PayPal SDK URL with all necessary parameters
            script.src = `https://www.paypal.com/sdk/js?client-id=${clientId}&currency=${currency}&components=${components}&enable-funding=${enableFunding}&debug=${config.debug === true ? 'true' : 'false'}`;
            
            // Add attributes for better mobile compatibility
            script.setAttribute('data-mobile-optimization', 'true');
            
            script.onload = resolve;
            script.onerror = reject;
            document.head.appendChild(script);
        });
    }

    /**
     * Initialize PayPal payment buttons
     */
    initializePayPalButtons() {
        const paypalButtonContainer = document.getElementById('paypal-button-container');
        
        if (!paypalButtonContainer) {
            console.error('PayPal button container not found');
            return;
        }

        // Clear any existing PayPal buttons
        paypalButtonContainer.innerHTML = '';        window.paypal.Buttons({
            style: window.PayPalConfig?.style || {
                layout: 'vertical',
                color: 'blue',
                shape: 'rect',
                label: 'paypal'
            },
            
            createOrder: (data, actions) => {
                return actions.order.create({
                    purchase_units: [{
                        amount: {
                            value: this.currentOrderTotal.toFixed(2),
                            currency_code: 'PHP'
                        },
                        description: `POS System Order - ${this.currentCartItems.length} items`
                    }]
                });
            },

            onApprove: async (data, actions) => {
                try {
                    // Show processing status
                    this.showProcessingStatus();

                    // Capture the payment
                    const order = await actions.order.capture();
                    
                    // Process the payment on our backend
                    await this.processPayPalPayment(order);
                    
                } catch (error) {
                    console.error('PayPal payment processing failed:', error);
                    this.showCompletionModal(false, 'Payment processing failed: ' + error.message);
                }
            },

            onError: (err) => {
                console.error('PayPal error:', err);
                this.showCompletionModal(false, 'PayPal payment failed. Please try again.');
            },

            onCancel: (data) => {
                console.log('PayPal payment cancelled:', data);
                this.hidePayPalModal();
            }
        }).render('#paypal-button-container');
    }    /**
     * Show PayPal payment modal
     */
    showPayPalModal(total, cartItems) {
        this.currentOrderTotal = total;
        this.currentCartItems = cartItems;

        // Update modal content
        const totalAmountElement = document.getElementById('paypalTotalAmount');
        if (totalAmountElement) {
            totalAmountElement.value = `₱${total.toFixed(2)}`;
        }

        // Show the modal with proper display setting
        if (this.paypalModal) {
            this.paypalModal.style.display = 'flex';
            
            // Reset scroll position to top when opening
            const modalContent = this.paypalModal.querySelector('.modal-content');
            if (modalContent) {
                modalContent.scrollTop = 0;
            }
            
            // Add keyboard event listener for ESC key
            this.escKeyHandler = (e) => {
                if (e.key === 'Escape') {
                    this.hidePayPalModal();
                }
            };
            document.addEventListener('keydown', this.escKeyHandler);
        }

        // Initialize PayPal if not already done
        if (!this.isInitialized) {
            this.initialize().catch(error => {
                console.error('Failed to initialize PayPal:', error);
                alert('PayPal is currently unavailable. Please try again later.');
            });
        }
    }    /**
     * Hide PayPal payment modal
     */
    hidePayPalModal() {
        if (this.paypalModal) {
            this.paypalModal.style.display = 'none';
            
            // Remove keyboard event listener
            if (this.escKeyHandler) {
                document.removeEventListener('keydown', this.escKeyHandler);
                this.escKeyHandler = null;
            }
        }
    }

    /**
     * Process PayPal payment on backend
     */
    async processPayPalPayment(paypalOrder) {
        try {            const token = localStorage.getItem('jwt_token');
            if (!token) {
                throw new Error('Authentication required');
            }

            const paymentData = {
                payment_method: 'paypal',
                paypal_transaction_id: paypalOrder.id,
                paypal_order_details: paypalOrder,
                cart_items: this.currentCartItems,
                total_amount: this.currentOrderTotal
            };            // API path for Live Server frontend + XAMPP htdocs backend setup
            let apiPath;
            
            // Check if we're running on Live Server (typically port 5500)
            if (window.location.port === '5500' || window.location.hostname === '127.0.0.1') {
                // Frontend on Live Server, backend on XAMPP htdocs
                // Use test version temporarily
                apiPath = 'http://localhost/Payment_Gateway/paypal-payment-test.php';
            } else if (window.location.hostname === 'localhost') {
                // Both frontend and backend on XAMPP
                apiPath = `${window.location.origin}/Payment_Gateway/paypal-payment-test.php`;
            } else {
                // Fallback for other setups
                apiPath = `${window.location.origin}/Payment_Gateway/src/backend/api/paypal-payment.php`;
            }

            console.log('PayPal API URL (TEST VERSION):', apiPath);
            console.log('Payment Data:', JSON.stringify(paymentData, null, 2));const response = await fetch(apiPath, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${token}`
                },
                body: JSON.stringify(paymentData)
            });

            console.log('PayPal API Response Status:', response.status, response.statusText);

            if (!response.ok) {
                const errorText = await response.text();
                console.error('PayPal API Error Response:', errorText);
                throw new Error(`HTTP ${response.status}: ${errorText || response.statusText}`);
            }

            const responseText = await response.text();
            console.log('PayPal API Response Text:', responseText);

            let result;
            try {
                result = JSON.parse(responseText);
            } catch (parseError) {
                console.error('Failed to parse PayPal API response as JSON:', parseError);
                throw new Error('Invalid response from server: ' + responseText.substring(0, 100));
            }

            console.log('PayPal API Parsed Result:', result);

            if (result.success) {
                this.showCompletionModal(true, 'Payment completed successfully!', result.data);
                
                // Clear cart and reset UI
                if (window.clearCart) {
                    window.clearCart();
                }
            } else {
                throw new Error(result.message || 'Payment processing failed');
            }

        } catch (error) {
            console.error('Backend payment processing failed:', error);
            this.showCompletionModal(false, error.message);
        }
    }

    /**
     * Show processing status
     */
    showProcessingStatus() {
        // Create or show processing overlay
        let processingOverlay = document.getElementById('paypal-processing-overlay');
        
        if (!processingOverlay) {
            processingOverlay = document.createElement('div');
            processingOverlay.id = 'paypal-processing-overlay';
            processingOverlay.innerHTML = `
                <div class="processing-content">
                    <div class="spinner"></div>
                    <p>Processing your PayPal payment...</p>
                </div>
            `;
            processingOverlay.style.cssText = `
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0, 0, 0, 0.7);
                display: flex;
                justify-content: center;
                align-items: center;
                z-index: 10000;
            `;
            document.body.appendChild(processingOverlay);
        }

        processingOverlay.style.display = 'flex';
    }

    /**
     * Hide processing status
     */
    hideProcessingStatus() {
        const processingOverlay = document.getElementById('paypal-processing-overlay');
        if (processingOverlay) {
            processingOverlay.style.display = 'none';
        }
    }

    /**
     * Show transaction completion modal
     */
    showCompletionModal(success, message, transactionData = null) {
        this.hideProcessingStatus();
        this.hidePayPalModal();

        // Create completion modal if it doesn't exist
        if (!this.completionModal) {
            this.createCompletionModal();
        }

        const modal = this.completionModal;
        const statusIcon = modal.querySelector('.status-icon');
        const statusMessage = modal.querySelector('.status-message');
        const transactionDetails = modal.querySelector('.transaction-details');

        // Update modal content
        statusIcon.innerHTML = success ? '✅' : '❌';
        statusIcon.className = `status-icon ${success ? 'success' : 'error'}`;
        statusMessage.textContent = message;

        // Show transaction details if successful
        if (success && transactionData) {
            transactionDetails.innerHTML = `
                <div class="detail-row">
                    <span>Transaction ID:</span>
                    <span>${transactionData.paypal_transaction_id || 'N/A'}</span>
                </div>
                <div class="detail-row">
                    <span>Amount:</span>
                    <span>₱${this.currentOrderTotal.toFixed(2)}</span>
                </div>
                <div class="detail-row">
                    <span>Status:</span>
                    <span class="status-success">Completed</span>
                </div>
                <div class="detail-row">
                    <span>Payment Method:</span>
                    <span>PayPal</span>
                </div>
            `;
            transactionDetails.style.display = 'block';
        } else {
            transactionDetails.style.display = 'none';
        }

        // Show modal
        modal.style.display = 'block';
    }

    /**
     * Create transaction completion modal
     */
    createCompletionModal() {
        const modal = document.createElement('div');
        modal.id = 'paypal-completion-modal';
        modal.className = 'modal';
        modal.innerHTML = `
            <div class="modal-content completion-modal-content">
                <div class="completion-header">
                    <div class="status-icon"></div>
                    <h2>Payment Status</h2>
                </div>
                <div class="status-message"></div>
                <div class="transaction-details"></div>
                <div class="modal-actions">
                    <button id="completion-ok-btn" class="ok-button">OK</button>
                </div>
            </div>
        `;

        // Add styles
        modal.style.cssText = `
            display: none;
            position: fixed;
            z-index: 9999;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.4);
        `;

        document.body.appendChild(modal);
        this.completionModal = modal;

        // Add event listener for OK button
        const okButton = modal.querySelector('#completion-ok-btn');
        okButton.addEventListener('click', () => {
            modal.style.display = 'none';
        });

        // Close modal when clicking outside
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                modal.style.display = 'none';
            }
        });
    }    /**
     * Initialize modal references
     */    initializeModals() {
        // Get reference to existing PayPal modal
        this.paypalModal = document.getElementById('paypalModal');
        
        // Ensure proper scrolling behavior for PayPal modal
        if (this.paypalModal) {
            // Add class to enable proper styling from our CSS fix
            this.paypalModal.querySelector('.modal-content').classList.add('paypal-modal-content');
            
            // Handle closing with proper cleanup
            const closeButton = this.paypalModal.querySelector('.close');
            if (closeButton) {
                closeButton.addEventListener('click', () => {
                    this.hidePayPalModal();
                });
            }
            
            // Handle cancel button click
            const cancelButton = document.getElementById('paypalCancelBtn');
            if (cancelButton) {
                cancelButton.addEventListener('click', () => {
                    this.hidePayPalModal();
                });
            }
            
            // Handle clicks outside the modal content
            this.paypalModal.addEventListener('click', (event) => {
                if (event.target === this.paypalModal) {
                    this.hidePayPalModal();
                }
            });
        }
    }
}

// Create global PayPal integration instance
window.PayPalIntegration = new PayPalIntegration();

// Export for module use
if (typeof module !== 'undefined' && module.exports) {
    module.exports = PayPalIntegration;
}
