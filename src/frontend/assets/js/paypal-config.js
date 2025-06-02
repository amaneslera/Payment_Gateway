/**
 * PayPal Configuration for Sandbox Environment
 * This file contains PayPal SDK configuration settings
 */

window.PayPalConfig = {    
    // PayPal Sandbox Client ID (used for frontend SDK initialization)
    clientId: 'AUlZYWnvNng5ugf5eM1WwefdaF-tLsEEq2uJcATLPfkq2SjG8F4nantuA2cGtOq0-pxLr143nzrPUD5h',
    
    // Note: Client Secret is stored securely on server-side in config.php for API verification
      // Currency (Philippines Peso)
    currency: 'PHP',
    
    // Environment (sandbox for testing, production for live)
    environment: 'sandbox',
    
    // SDK components to load
    components: 'buttons',
    
    // Intent (capture for immediate payment, authorize for delayed capture)
    intent: 'capture',
    
    // Enable funding sources - enable all payment methods for best user experience
    enableFunding: 'paypal,card,credit',
    
    // Disable funding sources (uncomment if needed)
    // disableFunding: '',
    
    // Business account email (your PayPal business account)
    businessEmail: 'sb-o43of30863329@business.example.com',
    
    // Return URLs (for reference, SDK handles redirects automatically)
    returnUrl: window.location.origin + '/Payment_Gateway/src/frontend/pages/cashier.html',
    cancelUrl: window.location.origin + '/Payment_Gateway/src/frontend/pages/cashier.html',
    
    // API endpoints
    apiEndpoints: {
        createPayment: '/Payment_Gateway/src/backend/api/paypal-payment.php',
        capturePayment: '/Payment_Gateway/src/backend/api/paypal-capture.php',
        refundPayment: '/Payment_Gateway/src/backend/api/paypal-refund.php'
    },
      // Style configuration for PayPal buttons
    style: {
        layout: 'vertical',  // vertical layout works better in modal
        color: 'blue',
        shape: 'rect',
        label: 'paypal',
        height: 45          // Slightly larger buttons for better touch targets
    },
    
    // Debug mode (set to false for production)
    debug: true
};

// Export for module use
if (typeof module !== 'undefined' && module.exports) {
    module.exports = window.PayPalConfig;
}
