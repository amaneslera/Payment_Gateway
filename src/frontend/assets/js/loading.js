/**
 * Common loading functionality
 */

function showLoading(message = 'Loading...') {
    let spinnerModal = document.getElementById('spinnerModal');
    
    // Create spinner modal if it doesn't exist
    if (!spinnerModal) {
        spinnerModal = document.createElement('div');
        spinnerModal.id = 'spinnerModal';
        spinnerModal.className = 'spinner-modal';
        spinnerModal.innerHTML = `
            <div class="spinner-container">
                <div class="spinner"></div>
                <p class="loading-text" id="loadingMessage">${message}</p>
            </div>
        `;
        document.body.appendChild(spinnerModal);
    } else {
        // Update the loading message with animation
        const loadingMessage = document.getElementById('loadingMessage');
        if (loadingMessage) {
            // Fade out
            loadingMessage.style.opacity = '0';
            setTimeout(() => {
                // Update text and fade in
                loadingMessage.textContent = message;
                loadingMessage.style.opacity = '1';
            }, 200);
        }
    }
    
    // Show the spinner modal
    spinnerModal.style.display = 'block';
}

function hideLoading() {
    const spinnerModal = document.getElementById('spinnerModal');
    if (spinnerModal) {
        spinnerModal.style.display = 'none';
    }
}

// Add click event listeners to all navigation links
document.addEventListener('DOMContentLoaded', function() {
    const navLinks = document.querySelectorAll('nav a');
    navLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            // Don't show loading for the current page or external links
            if (this.getAttribute('href') !== 'javascript:void(0);' && 
                !this.getAttribute('href').startsWith('http')) {
                showLoading('Navigating to ' + this.textContent.trim() + '...');
            }
        });
    });

    // Also handle quick action buttons that navigate to other pages
    const quickActionBtns = document.querySelectorAll('.quick-action-btn');
    quickActionBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const action = this.dataset.action;
            if (action) {
                let destination = '';
                switch(action) {
                    case 'new-sale':
                        destination = 'Cashier';
                        break;
                    case 'view-inventory':
                        destination = 'Inventory';
                        break;
                    case 'manage-users':
                        destination = 'User Management';
                        break;
                    case 'view-transactions':
                        destination = 'Transactions';
                        break;
                    case 'sales-report':
                        destination = 'Sales Report';
                        break;
                }
                if (destination) {
                    showLoading('Opening ' + destination + '...');
                }
            }
        });
    });
});
