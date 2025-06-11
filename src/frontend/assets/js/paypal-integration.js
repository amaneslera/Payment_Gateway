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
    }    /**
     * Initialize PayPal SDK (can be called multiple times after reset)
     */
    async initialize() {
        try {
            console.log('Initializing PayPal SDK...', { 
                isInitialized: this.isInitialized, 
                paypalExists: !!window.paypal 
            });

            // If already initialized and PayPal SDK exists, skip
            if (this.isInitialized && window.paypal && window.paypal.Buttons) {
                console.log('PayPal already initialized and functional, skipping...');
                return;
            }

            // Reset initialization flag to allow fresh start
            this.isInitialized = false;

            // Load PayPal SDK dynamically
            await this.loadPayPalSDK();
            
            // Initialize PayPal buttons
            this.initializePayPalButtons();
            
            this.isInitialized = true;
            console.log('PayPal SDK initialized successfully');
        } catch (error) {
            console.error('Failed to initialize PayPal SDK:', error);
            this.isInitialized = false;
            throw new Error('PayPal initialization failed: ' + error.message);
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
            const clientId = config.clientId || 'AUlZYWnvNng5ugf5eM1WwefdaF-tLsEEq2uJcATLPfkq2SjG8F4nantuA2cGtOq0-pxLr143nzrPUD5h';
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
                apiPath = 'http://localhost/Payment_Gateway/src/backend/api/paypal-payment.php';
            } else if (window.location.hostname === 'localhost') {
                // Both frontend and backend on XAMPP
                apiPath = `${window.location.origin}/Payment_Gateway/src/backend/api/paypal-payment.php`;
            } else {
                // Fallback for other setups
                apiPath = `${window.location.origin}/Payment_Gateway/src/backend/api/paypal-payment.php`;
            }

            console.log('PayPal API URL:', apiPath);
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

            console.log('PayPal API Parsed Result:', result);            if (result.success) {
                this.showCompletionModal(true, 'Payment completed successfully!', result.data);
                
                // Clear cart and reset UI
                if (window.clearCart) {
                    window.clearCart();
                }
                
                // IMMEDIATELY reset PayPal SDK to prevent conflicts
                this.immediatePayPalReset();
                
                // Setup automatic PayPal logout with page refresh for clean state
                this.setupPayPalLogoutWithRefresh();
                
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
    }    /**
     * Setup PayPal logout with clean UX (HIDDEN LOGOUT PROCESS)
     */
    setupPayPalLogoutWithRefresh() {
        // Get the modal and hide the OK button initially
        const modal = this.completionModal;
        if (!modal) return;

        const okBtn = modal.querySelector('#completion-ok-btn');
        if (okBtn) {
            okBtn.style.display = 'none'; // Hide OK button initially
        }

        // Add clean loading message to the success modal
        const modalContent = modal.querySelector('.modal-content');
        if (modalContent && !modalContent.querySelector('.transaction-finalizing')) {
            const finalizingDiv = document.createElement('div');
            finalizingDiv.className = 'transaction-finalizing';
            finalizingDiv.innerHTML = `
                <div style="margin-top: 20px; padding: 20px; background-color: #f8f9fa; border: 1px solid #e9ecef; border-radius: 8px; text-align: center;">
                    <div style="display: flex; justify-content: center; align-items: center; margin-bottom: 15px;">
                        <div class="finalizing-spinner" style="border: 3px solid #f3f3f3; border-top: 3px solid #28a745; border-radius: 50%; width: 30px; height: 30px; animation: spin 1s linear infinite; margin-right: 15px;"></div>
                        <span style="color: #28a745; font-size: 16px; font-weight: 500;">Finalizing your transaction...</span>
                    </div>
                    <p style="color: #6c757d; margin: 0; font-size: 14px;">Please wait while we complete the process</p>
                </div>
            `;
            
            // Insert before the OK button container
            const okBtnContainer = modalContent.querySelector('.modal-actions');
            if (okBtnContainer) {
                modalContent.insertBefore(finalizingDiv, okBtnContainer);
            } else {
                modalContent.appendChild(finalizingDiv);
            }
        }

        // Start the silent logout process immediately
        this.performSilentPayPalLogout();
    }

    /**
     * Perform silent PayPal logout without showing logout details to user
     */
    async performSilentPayPalLogout() {
        try {
            console.log('🤫 Starting silent PayPal logout process...');

            // Step 1: COMPLETELY DESTROY PayPal SDK initialization (silently)
            this.destroyPayPalSDK();

            // Step 2: Clear all PayPal data (silently)
            this.clearAllPayPalData();

            // Step 3: Force logout using popup window method (silently)
            await this.forcePayPalLogoutWithPopup();

            // Step 4: Clear browser cache and storage (silently)
            await this.clearBrowserPayPalCache();

            // Step 5: Use multiple iframe logout attempts (silently)
            await this.performMultipleLogoutAttempts();

            console.log('🔐 Silent PayPal logout completed successfully');

            // Show completion and enable OK button
            this.showTransactionComplete();

            // Schedule page refresh (but let user click OK first if they want)
            setTimeout(() => {
                console.log('🔄 Auto-refreshing page for clean session...');
                window.location.reload(true);
            }, 5000); // Give user 5 seconds to click OK before auto-refresh

        } catch (error) {
            console.error('Silent PayPal logout error:', error);
            // Still show completion even if logout failed
            this.showTransactionComplete();
            
            // Force refresh anyway as fallback
            setTimeout(() => {
                window.location.reload(true);
            }, 3000);
        }
    }    /**
     * Show transaction complete - replace loading with ready state
     */
    showTransactionComplete() {
        const finalizingDiv = document.querySelector('.transaction-finalizing');
        const modal = this.completionModal;
        
        if (finalizingDiv) {
            // Replace loading message with completion
            finalizingDiv.innerHTML = `
                <div style="margin-top: 20px; padding: 20px; background-color: #d4edda; border: 1px solid #c3e6cb; border-radius: 8px; text-align: center;">
                    <div style="display: flex; justify-content: center; align-items: center; margin-bottom: 15px;">
                        <span style="color: #155724; font-size: 24px; margin-right: 10px;">✅</span>
                        <span style="color: #155724; font-size: 16px; font-weight: 600;">Transaction completed successfully!</span>
                    </div>
                    <p style="color: #155724; margin: 0; font-size: 14px;">You can now safely proceed with your next transaction</p>
                </div>
            `;
        }

        // Show OK button now that everything is ready
        if (modal) {
            const okBtn = modal.querySelector('#completion-ok-btn');
            if (okBtn) {
                okBtn.style.display = 'inline-block';
                okBtn.style.animation = 'fadeIn 0.3s ease-in';
                
                // Add fade in animation if not exists
                if (!document.head.querySelector('style[data-fadein]')) {
                    const style = document.createElement('style');
                    style.setAttribute('data-fadein', 'true');
                    style.textContent = `
                        @keyframes fadeIn {
                            from { opacity: 0; transform: translateY(10px); }
                            to { opacity: 1; transform: translateY(0); }
                        }
                    `;
                    document.head.appendChild(style);
                }
            }
        }
    }async performDirectPayPalLogout() {
        // This method is now replaced by performSilentPayPalLogout()
        // Keeping for compatibility but redirecting to silent version
        console.log('🔄 Redirecting to silent PayPal logout...');
        await this.performSilentPayPalLogout();
    }

    /**
     * COMPLETELY DESTROY PayPal SDK initialization to prevent conflicts
     */
    destroyPayPalSDK() {
        console.log('🔥 DESTROYING PayPal SDK completely...');
        
        try {
            // 1. Reset our initialization flag
            this.isInitialized = false;
            
            // 2. Clear PayPal button container completely
            const paypalContainer = document.getElementById('paypal-button-container');
            if (paypalContainer) {
                paypalContainer.innerHTML = '';
                // Remove all event listeners by cloning
                const newContainer = paypalContainer.cloneNode(false);
                paypalContainer.parentNode.replaceChild(newContainer, paypalContainer);
            }
            
            // 3. Remove ALL PayPal-related scripts from DOM
            const scripts = document.querySelectorAll('script[src*="paypal.com"]');
            scripts.forEach(script => {
                if (script.parentNode) {
                    script.parentNode.removeChild(script);
                }
            });
            
            // 4. COMPLETELY DESTROY window.paypal object and all its properties
            if (window.paypal) {
                try {
                    // Try to destroy any active PayPal components
                    if (window.paypal.Buttons && window.paypal.Buttons.driver) {
                        window.paypal.Buttons.driver.cleanup();
                    }
                    if (window.paypal.destroy) {
                        window.paypal.destroy();
                    }
                } catch (e) {
                    console.log('PayPal cleanup methods not available:', e);
                }
                
                // Force delete ALL PayPal properties
                Object.keys(window.paypal).forEach(key => {
                    try {
                        delete window.paypal[key];
                    } catch (e) {
                        window.paypal[key] = null;
                    }
                });
                
                // Delete the entire paypal object
                delete window.paypal;
                window.paypal = null;
                window.paypal = undefined;
            }
            
            // 5. Clear zoid (PayPal's iframe communication library)
            if (window.zoid) {
                try {
                    if (window.zoid.destroyAll) {
                        window.zoid.destroyAll();
                    }
                    if (window.zoid.reset) {
                        window.zoid.reset();
                    }
                    delete window.zoid;
                } catch (e) {
                    console.log('Zoid cleanup failed:', e);
                }
            }
            
            // 6. Clear ALL PayPal-related global variables
            [
                'ppxo', 'PPXO', 'paypalCheckoutReady', 'paypalApi', 'PAYPAL_SDK',
                'paypal_sdk', 'PayPalSDK', 'paypalButtons', 'PayPalButtons'
            ].forEach(global => {
                if (window[global]) {
                    delete window[global];
                    window[global] = null;
                }
            });
            
            // 7. Reset our class instance state
            this.paypalInstance = null;
            
            console.log('✅ PayPal SDK completely destroyed and reset');
              } catch (error) {
            console.error('Error destroying PayPal SDK:', error);
        }
    }

    /**
     * Immediate PayPal reset after successful payment (without logout process)
     */
    immediatePayPalReset() {
        console.log('🔄 Performing immediate PayPal SDK reset after payment...');
        
        try {
            // 1. Reset initialization flag immediately
            this.isInitialized = false;
            
            // 2. Clear PayPal button container
            const paypalContainer = document.getElementById('paypal-button-container');
            if (paypalContainer) {
                paypalContainer.innerHTML = '<div style="text-align: center; color: #666; padding: 20px;">PayPal session reset for security</div>';
            }
            
            // 3. Nullify PayPal instance reference
            this.paypalInstance = null;
            
            // 4. Clear basic PayPal data immediately
            this.clearPayPalData();
            
            console.log('✅ Immediate PayPal reset completed - ready for fresh initialization');
            
        } catch (error) {
            console.error('Error in immediate PayPal reset:', error);
        }
    }

    /**
     * Force PayPal logout using popup window method
     */
    async forcePayPalLogoutWithPopup() {
        return new Promise((resolve) => {
            try {
                // Open PayPal logout in a popup and immediately close it
                const logoutWindow = window.open(
                    'https://www.paypal.com/signin/logout',
                    'paypal_force_logout',
                    'width=300,height=200,left=-1000,top=-1000'
                );
                
                // Also try sandbox logout
                const sandboxLogoutWindow = window.open(
                    'https://www.sandbox.paypal.com/signin/logout',
                    'paypal_sandbox_logout',
                    'width=300,height=200,left=-1000,top=-1000'
                );

                // Close popups after brief delay
                setTimeout(() => {
                    if (logoutWindow && !logoutWindow.closed) {
                        logoutWindow.close();
                    }
                    if (sandboxLogoutWindow && !sandboxLogoutWindow.closed) {
                        sandboxLogoutWindow.close();
                    }
                    resolve();
                }, 1000);

            } catch (e) {
                console.log('Popup logout failed, continuing with other methods:', e);
                resolve();
            }
        });
    }

    /**
     * Clear browser PayPal cache more aggressively
     */
    async clearBrowserPayPalCache() {
        try {
            // Clear all caches if available
            if ('caches' in window) {
                const cacheNames = await caches.keys();
                await Promise.all(
                    cacheNames.map(cacheName => caches.delete(cacheName))
                );
            }

            // Clear service worker caches if available
            if ('serviceWorker' in navigator) {
                const registrations = await navigator.serviceWorker.getRegistrations();
                await Promise.all(
                    registrations.map(registration => registration.unregister())
                );
            }

        } catch (e) {
            console.log('Cache clearing failed:', e);
        }
    }

    /**
     * Perform multiple logout attempts simultaneously
     */
    async performMultipleLogoutAttempts() {
        const logoutUrls = [
            'https://www.paypal.com/signin/logout',
            'https://www.sandbox.paypal.com/signin/logout',
            'https://www.paypal.com/myaccount/home/logout',
            'https://www.paypal.com/webapps/auth/logout',
            'https://www.paypal.com/cgi-bin/webscr?cmd=_logout',
            'https://identity.paypal.com/v1/logout',
            'https://www.sandbox.paypal.com/myaccount/home/logout'
        ];

        // Create multiple hidden iframes simultaneously for faster logout
        const logoutPromises = logoutUrls.map(url => this.createLogoutIframe(url));
        
        // Wait for all logout attempts (max 2 seconds)
        await Promise.race([
            Promise.allSettled(logoutPromises),
            new Promise(resolve => setTimeout(resolve, 2000))
        ]);
    }

    /**
     * Create logout iframe with timeout
     */
    createLogoutIframe(url) {
        return new Promise((resolve) => {
            const iframe = document.createElement('iframe');
            iframe.style.cssText = 'display:none;width:0;height:0;border:none;position:absolute;left:-9999px;top:-9999px;';
            iframe.src = url;
            
            const cleanup = () => {
                if (iframe.parentNode) {
                    iframe.parentNode.removeChild(iframe);
                }
                resolve();
            };

            iframe.onload = iframe.onerror = cleanup;
            
            // Cleanup after timeout
            setTimeout(cleanup, 1500);
            
            document.body.appendChild(iframe);
            console.log(`Direct logout attempt: ${url}`);
        });
    }    /**
     * Show direct logout progress to user
     */
    showDirectLogoutProgress() {
        const modal = this.completionModal;
        if (modal) {
            const content = modal.querySelector('.modal-content');
            if (content) {
                const progressDiv = document.createElement('div');
                progressDiv.className = 'paypal-logout-progress';
                progressDiv.innerHTML = `
                    <div style="text-align: center; padding: 20px; background: #f8f9fa; border-radius: 8px; margin: 15px 0; border: 2px solid #0070ba;">
                        <div style="font-size: 24px; margin-bottom: 10px;">🔐</div>
                        <h4 style="margin: 0 0 10px 0; color: #0070ba;">Logging out from PayPal...</h4>
                        <p style="margin: 0; color: #666; font-size: 14px;">Clearing session data and forcing logout</p>
                        <div style="margin-top: 15px;">
                            <div class="spinner" style="border: 3px solid #f3f3f3; border-top: 3px solid #0070ba; border-radius: 50%; width: 40px; height: 40px; animation: spin 1s linear infinite; margin: 0 auto;"></div>
                        </div>
                        <p style="margin-top: 10px; color: #999; font-size: 12px;">This ensures complete logout for security</p>
                    </div>
                `;
                
                // Add spinner animation if not exists
                if (!document.head.querySelector('style[data-spinner]')) {
                    const style = document.createElement('style');
                    style.setAttribute('data-spinner', 'true');
                    style.textContent = `
                        @keyframes spin {
                            0% { transform: rotate(0deg); }
                            100% { transform: rotate(360deg); }
                        }
                    `;
                    document.head.appendChild(style);
                }
                
                content.appendChild(progressDiv);
            }
        }
    }

    /**
     * Show logout completion message
     */
    showLogoutComplete() {
        const progressDiv = document.querySelector('.paypal-logout-progress');
        if (progressDiv) {
            progressDiv.innerHTML = `
                <div style="text-align: center; padding: 20px; background: #d4edda; border-radius: 8px; margin: 15px 0; border: 2px solid #28a745;">
                    <div style="font-size: 24px; margin-bottom: 10px;">✅</div>
                    <h4 style="margin: 0 0 10px 0; color: #155724;">PayPal Logout Complete!</h4>
                    <p style="margin: 0; color: #155724; font-size: 14px;">Refreshing page for clean session...</p>
                    <div style="margin-top: 10px;">
                        <div style="width: 100%; height: 4px; background: #c3e6cb; border-radius: 2px; overflow: hidden;">
                            <div style="width: 100%; height: 100%; background: #28a745; animation: slideIn 1.5s ease-out;"></div>
                        </div>
                    </div>
                </div>
            `;
            
            // Add slide animation
            if (!document.head.querySelector('style[data-slide]')) {
                const style = document.createElement('style');
                style.setAttribute('data-slide', 'true');
                style.textContent = `
                    @keyframes slideIn {
                        0% { width: 0%; }
                        100% { width: 100%; }
                    }
                `;
                document.head.appendChild(style);
            }
        }
    }

    /**
     * Show manual logout instructions
     */
    performManualLogoutInstructions() {
        const modal = this.completionModal;
        if (modal) {
            const content = modal.querySelector('.modal-content');
            if (content) {
                const instructionsDiv = document.createElement('div');
                instructionsDiv.className = 'manual-logout-instructions';
                instructionsDiv.innerHTML = `
                    <div style="padding: 20px; background: #e2e3e5; border-radius: 8px; margin: 15px 0;">
                        <h4 style="margin: 0 0 15px 0; color: #383d41;">Manual PayPal Logout Instructions</h4>
                        <ol style="margin: 0; color: #383d41; padding-left: 20px;">
                            <li>Open a new tab and go to <strong>paypal.com</strong></li>
                            <li>Click on your profile icon (top right)</li>
                            <li>Select <strong>"Log Out"</strong> from the dropdown</li>
                            <li>Come back to this tab and continue</li>
                        </ol>
                        <div style="margin-top: 15px; text-align: center;">
                            <button onclick="window.open('https://www.paypal.com', '_blank')" style="background: #0070ba; color: white; border: none; padding: 8px 16px; border-radius: 4px; cursor: pointer; margin-right: 10px;">
                                Open PayPal
                            </button>
                            <button onclick="window.location.reload()" style="background: #28a745; color: white; border: none; padding: 8px 16px; border-radius: 4px; cursor: pointer;">
                                Refresh Page
                            </button>
                        </div>
                    </div>
                `;
                content.appendChild(instructionsDiv);
            }
        }
    }    /**
     * Clear ALL PayPal-related data from browser storage (comprehensive)
     */
    clearAllPayPalData() {
        console.log('🧹 Clearing ALL PayPal data comprehensively...');
        
        try {
            // 1. Clear ALL PayPal cookies (extended list)
            const paypalCookies = [
                'LANG', 'X-PP-L7', 'X-PP-SILOVER', 'cookie_prefs', 'nsid', 'login_email',
                'x-pp-s', 'x-csrf-token', 'paypal-csrf-token', 'PP_TOS_ACK', 'PYPF',
                'HaC', 'X-PP-ADS', 'X-PP-CSRF', 'navlns', 'tcs', 'akavpau_ppsd',
                'pp_', 'PP_', 'PAYPAL_', 'paypal_', 'x-pp-', 'X-PP-',
                'ak_bmsc', 'bm_sv', 'ts_c', 'tsrce', 'enforce_policy',
                'KHcl0EuY7AKSMgfvHiYoqu', 'PP_TARGETING_ENABLED'
            ];
            
            // Clear cookies for multiple domains
            const domains = ['', '.paypal.com', '.sandbox.paypal.com', '.paypalobjects.com'];
            const paths = ['/', '/webapps', '/signin', '/myaccount'];
            
            paypalCookies.forEach(cookieName => {
                domains.forEach(domain => {
                    paths.forEach(path => {
                        const cookieString = domain 
                            ? `${cookieName}=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=${path}; domain=${domain};`
                            : `${cookieName}=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=${path};`;
                        document.cookie = cookieString;
                    });
                });
            });
            
            // 2. Clear ALL cookies that contain 'paypal' (case insensitive)
            const allCookies = document.cookie.split(';');
            allCookies.forEach(cookie => {
                const cookieName = cookie.split('=')[0].trim();
                if (cookieName.toLowerCase().includes('paypal') || 
                    cookieName.toLowerCase().includes('pp_') ||
                    cookieName.toLowerCase().includes('pp-')) {
                    domains.forEach(domain => {
                        paths.forEach(path => {
                            const cookieString = domain 
                                ? `${cookieName}=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=${path}; domain=${domain};`
                                : `${cookieName}=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=${path};`;
                            document.cookie = cookieString;
                        });
                    });
                }
            });
            
            // 3. Clear localStorage PayPal items (comprehensive)
            const keysToRemove = [];
            for (let i = 0; i < localStorage.length; i++) {
                const key = localStorage.key(i);
                if (key && (
                    key.toLowerCase().includes('paypal') ||
                    key.toLowerCase().includes('pp_') ||
                    key.toLowerCase().includes('pp-') ||
                    key.toLowerCase().includes('zoid') ||
                    key.includes('paypal') ||
                    key.includes('PayPal') ||
                    key.includes('PAYPAL')
                )) {
                    keysToRemove.push(key);
                }
            }
            keysToRemove.forEach(key => localStorage.removeItem(key));
            
            // 4. Clear sessionStorage PayPal items (comprehensive)
            const sessionKeysToRemove = [];
            for (let i = 0; i < sessionStorage.length; i++) {
                const key = sessionStorage.key(i);
                if (key && (
                    key.toLowerCase().includes('paypal') ||
                    key.toLowerCase().includes('pp_') ||
                    key.toLowerCase().includes('pp-') ||
                    key.toLowerCase().includes('zoid') ||
                    key.includes('paypal') ||
                    key.includes('PayPal') ||
                    key.includes('PAYPAL')
                )) {
                    sessionKeysToRemove.push(key);
                }
            }
            sessionKeysToRemove.forEach(key => sessionStorage.removeItem(key));
            
            // 5. Clear IndexedDB related to PayPal
            if ('indexedDB' in window) {
                const dbNames = ['paypal', 'PayPal', 'pp-sdk', 'braintree', 'zoid'];
                dbNames.forEach(dbName => {
                    try {
                        const deleteReq = indexedDB.deleteDatabase(dbName);
                        deleteReq.onsuccess = () => console.log(`Deleted ${dbName} IndexedDB`);
                        deleteReq.onerror = () => console.log(`Failed to delete ${dbName} IndexedDB`);
                    } catch (e) {
                        console.log(`IndexedDB cleanup failed for ${dbName}:`, e);
                    }
                });
            }
            
            console.log('✅ ALL PayPal data cleared comprehensively');
            
        } catch (error) {
            console.error('Error clearing PayPal data:', error);
        }
    }

    /**
     * Clear PayPal-related data from browser storage (original method - kept for compatibility)
     */
    clearPayPalData() {
        console.log('Clearing PayPal data...');
        
        // Clear PayPal cookies
        const paypalCookies = [
            'LANG', 'X-PP-L7', 'X-PP-SILOVER', 'cookie_prefs', 'nsid', 'login_email', 
            'x-pp-s', 'x-csrf-token', 'paypal-csrf-token', 'PP_TOS_ACK', 'PYPF'
        ];
        
        paypalCookies.forEach(cookieName => {
            document.cookie = `${cookieName}=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;`;
            document.cookie = `${cookieName}=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/; domain=.paypal.com;`;
            document.cookie = `${cookieName}=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/; domain=.sandbox.paypal.com;`;
        });
        
        // Clear localStorage PayPal items
        for (let i = localStorage.length - 1; i >= 0; i--) {
            const key = localStorage.key(i);
            if (key && key.toLowerCase().includes('paypal')) {
                localStorage.removeItem(key);
            }
        }
        
        // Clear sessionStorage PayPal items
        for (let i = sessionStorage.length - 1; i >= 0; i--) {
            const key = sessionStorage.key(i);
            if (key && key.toLowerCase().includes('paypal')) {
                sessionStorage.removeItem(key);
            }
        }
    }
}

// Create global PayPal integration instance
window.PayPalIntegration = new PayPalIntegration();

// Export for module use
if (typeof module !== 'undefined' && module.exports) {
    module.exports = PayPalIntegration;
}
