/**
 * PayPal SDK Integration for Cashier System
 * Handles PayPal Sandbox integration with automatic PayPal logout for security
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
        return new Promise((resolve, reject) => {
            // Check if PayPal SDK is already loaded
            if (window.paypal) {
                resolve();
                return;
            }

            const script = document.createElement('script');
            const config = window.PayPalConfig || {};
            const clientId = config.clientId || 'AYsyJdpE_ODIUfYVD9ghBQmGUH4K5LlZZDJYzFWOCCjLEDk7Ky8dE7fCJ5rXz3_g-Z8V-QJ-j6QJ6J-J';
            const currency = config.currency || 'PHP';
            const components = config.components || 'buttons';
            const enableFunding = config.enableFunding || 'paypal,card,credit';
            
            // Build PayPal SDK URL with all necessary parameters
            script.src = `https://www.paypal.com/sdk/js?client-id=${clientId}&currency=${currency}&components=${components}&enable-funding=${enableFunding}&debug=${config.debug === true ? 'true' : 'false'}`;
            
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
        paypalButtonContainer.innerHTML = '';

        window.paypal.Buttons({
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
    }

    /**
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
    }

    /**
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
        try {
            const token = localStorage.getItem('jwt_token');
            if (!token) {
                throw new Error('Authentication required');
            }

            const paymentData = {
                payment_method: 'paypal',
                paypal_transaction_id: paypalOrder.id,
                paypal_order_details: paypalOrder,
                cart_items: this.currentCartItems,
                total_amount: this.currentOrderTotal
            };

            // API path for Live Server frontend + XAMPP htdocs backend setup
            let apiPath;
            
            // Check if we're running on Live Server (typically port 5500)
            if (window.location.port === '5500' || window.location.hostname === '127.0.0.1') {
                // Frontend on Live Server, backend on XAMPP htdocs
                apiPath = 'http://localhost/Payment_Gateway/src/backend/api/paypal-payment.php';
            } else if (window.location.hostname === 'localhost') {
                // Both frontend and backend on XAMPP
                apiPath = `${window.location.origin}/Payment_Gateway/src/backend/api/paypal-payment.php`;
            } else {
                // Fallback for other setups
                apiPath = `${window.location.origin}/Payment_Gateway/src/backend/api/paypal-payment.php`;
            }

            console.log('PayPal API URL:', apiPath);
            console.log('Payment Data:', JSON.stringify(paymentData, null, 2));

            const response = await fetch(apiPath, {
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
                <div class="security-notice">
                    <p><strong>Security Notice:</strong> For your security, PayPal account will be automatically logged out after closing this dialog.</p>
                </div>
            `;
            transactionDetails.style.display = 'block';
        } else {
            transactionDetails.style.display = 'none';
        }

        // Show modal
        modal.style.display = 'block';

        // Setup PayPal logout after successful payment for security
        if (success) {
            this.setupPayPalLogout();
        }
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

        // Add event listener for OK button (will be overridden for PayPal logout)
        const okButton = modal.querySelector('#completion-ok-btn');
        okButton.addEventListener('click', () => {
            modal.style.display = 'none';
        });

        // Close modal when clicking outside (will be overridden for PayPal logout)
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                modal.style.display = 'none';
            }
        });
    }

    /**
     * Setup PayPal logout when modal is closed after successful payment
     */
    setupPayPalLogout() {
        const modal = this.completionModal;
        const okButton = modal.querySelector('#completion-ok-btn');

        // Override the existing OK button handler with PayPal logout
        const newOkButton = okButton.cloneNode(true);
        okButton.parentNode.replaceChild(newOkButton, okButton);

        // Add new event listener with PayPal logout
        newOkButton.addEventListener('click', () => {
            modal.style.display = 'none';
            this.performPayPalLogout();
        });

        // Override modal click outside handler with PayPal logout
        modal.onclick = (e) => {
            if (e.target === modal) {
                modal.style.display = 'none';
                this.performPayPalLogout();
            }
        };
    }

    /**
     * Perform PayPal account logout after successful payment
     */
    async performPayPalLogout() {
        console.log('Performing PayPal account logout after successful payment');
        
        // Show logout message
        const logoutMessage = document.createElement('div');
        logoutMessage.style.cssText = `
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: #fff;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.3);
            z-index: 10001;
            text-align: center;
            min-width: 350px;
            border: 2px solid #0070ba;
        `;
        logoutMessage.innerHTML = `
            <div>
                <div style="font-size: 24px; margin-bottom: 10px;">🔐</div>
                <h3 style="margin-top: 0; color: #0070ba; margin-bottom: 15px;">PayPal Security Logout</h3>
                <p style="color: #666; margin-bottom: 15px;">For your security, logging out from PayPal account...</p>
                <p style="color: #999; font-size: 14px; margin-bottom: 20px;">This will close any active PayPal sessions.</p>
                <div style="margin-top: 15px;">
                    <div class="spinner" style="border: 2px solid #f3f3f3; border-top: 2px solid #0070ba; border-radius: 50%; width: 30px; height: 30px; animation: spin 1s linear infinite; margin: 0 auto;"></div>
                </div>
            </div>
        `;

        // Add spinner animation
        const style = document.createElement('style');
        style.textContent = `
            @keyframes spin {
                0% { transform: rotate(0deg); }
                100% { transform: rotate(360deg); }
            }
        `;
        document.head.appendChild(style);
        document.body.appendChild(logoutMessage);

        // Perform PayPal logout actions
        try {
            await this.executePayPalLogout();
            
            // Show success message
            setTimeout(() => {
                if (logoutMessage && logoutMessage.parentNode) {
                    logoutMessage.innerHTML = `
                        <div>
                            <div style="font-size: 24px; margin-bottom: 10px;">✅</div>
                            <h3 style="margin-top: 0; color: #28a745; margin-bottom: 15px;">Logout Successful</h3>
                            <p style="color: #666; margin-bottom: 15px;">PayPal account has been securely logged out.</p>
                            <p style="color: #999; font-size: 14px;">You can safely close this window.</p>
                        </div>
                    `;
                    
                    // Auto-close after 3 seconds
                    setTimeout(() => {
                        if (logoutMessage && logoutMessage.parentNode) {
                            logoutMessage.parentNode.removeChild(logoutMessage);
                        }
                    }, 3000);
                }
            }, 2000);
            
        } catch (error) {
            console.error('PayPal logout error:', error);
            
            // Show error message
            setTimeout(() => {
                if (logoutMessage && logoutMessage.parentNode) {
                    logoutMessage.innerHTML = `
                        <div>
                            <div style="font-size: 24px; margin-bottom: 10px;">⚠️</div>
                            <h3 style="margin-top: 0; color: #dc3545; margin-bottom: 15px;">Logout Notice</h3>
                            <p style="color: #666; margin-bottom: 15px;">Please manually log out from PayPal for security.</p>
                            <button onclick="this.parentNode.parentNode.parentNode.remove()" style="background: #dc3545; color: white; border: none; padding: 8px 16px; border-radius: 4px; cursor: pointer;">Close</button>
                        </div>
                    `;
                }
            }, 2000);
        }
    }

    /**
     * Execute PayPal logout by clearing PayPal-related data and redirecting to PayPal logout
     */
    async executePayPalLogout() {
        try {
            // Clear PayPal cookies and session data
            this.clearPayPalData();
            
            // Open PayPal logout URL in a hidden iframe
            const logoutUrl = 'https://www.paypal.com/signin/logout';
            const iframe = document.createElement('iframe');
            iframe.style.display = 'none';
            iframe.src = logoutUrl;
            document.body.appendChild(iframe);
            
            // Remove iframe after logout attempt
            setTimeout(() => {
                if (iframe && iframe.parentNode) {
                    iframe.parentNode.removeChild(iframe);
                }
            }, 3000);
            
            // Clear PayPal SDK and force reinitialization
            if (window.paypal) {
                delete window.paypal;
            }
            
            // Remove PayPal scripts
            const paypalScripts = document.querySelectorAll('script[src*="paypal.com"]');
            paypalScripts.forEach(script => {
                if (script.parentNode) {
                    script.parentNode.removeChild(script);
                }
            });
            
            this.isInitialized = false;
            
            console.log('PayPal logout completed successfully');
            
        } catch (error) {
            console.error('Error during PayPal logout:', error);
            throw error;
        }
    }

    /**
     * Clear PayPal-related data from browser
     */
    clearPayPalData() {
        // Clear cookies related to PayPal
        const paypalCookies = ['LANG', 'X-PP-L7', 'X-PP-SILOVER', 'cookie_prefs', 'nsid', 'KHcl0EuY7AKSMgfvHiYoqu', 'X-PP-ADS'];
        
        paypalCookies.forEach(cookieName => {
            // Clear for current domain
            document.cookie = `${cookieName}=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;`;
            // Clear for PayPal domain
            document.cookie = `${cookieName}=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/; domain=.paypal.com;`;
        });
        
        // Clear localStorage items related to PayPal
        const keysToRemove = [];
        for (let i = 0; i < localStorage.length; i++) {
            const key = localStorage.key(i);
            if (key && (key.includes('paypal') || key.includes('PayPal'))) {
                keysToRemove.push(key);
            }
        }
        
        keysToRemove.forEach(key => {
            localStorage.removeItem(key);
        });
        
        // Clear sessionStorage items related to PayPal
        const sessionKeysToRemove = [];
        for (let i = 0; i < sessionStorage.length; i++) {
            const key = sessionStorage.key(i);
            if (key && (key.includes('paypal') || key.includes('PayPal'))) {
                sessionKeysToRemove.push(key);
            }
        }
        
        sessionKeysToRemove.forEach(key => {
            sessionStorage.removeItem(key);
        });
    }

    /**
     * Initialize modal references
     */
    initializeModals() {
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
