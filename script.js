/**
 * AWE Electronics Online Store - Custom JavaScript
 */

// Wait for DOM to be fully loaded
document.addEventListener('DOMContentLoaded', function() {
    // Get base URL for API calls
    const baseUrl = '/awe-electronics';
    
    // Initialize components based on current page
    initializeCart();
    initializeProductDetail();
    initializeCheckout();
    initializePayment();
    initializeAdminPages();
    
    // Initialize cart functionality
    function initializeCart() {
        document.querySelectorAll('.add-to-cart-btn').forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
        
                const productId = this.dataset.productId;
                const quantityInput = document.querySelector(`#quantity-${productId}`) || document.querySelector('#product-quantity');
                const quantity = quantityInput ? parseInt(quantityInput.value) : 1;
                if(quantity < 1) {
                    alert('Please select a valid quantity');
                    return;
                }
                
                // Show loading state
                this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Adding...';
                this.disabled = true;
                
                // Send request to add to cart
                fetch(`${baseUrl}/api/cart/add`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ product_id: productId, quantity: quantity })
                })
                .then(response => response.json())
                .then(data => {
                    if(data.success) {
                        // Update cart counter
                        updateCartCounter(data.cart.item_count);
                        
                        // Show success message
                        showAlert('success', 'Product added to cart successfully!');
                        
                        // Reset button state
                        this.innerHTML = '<i class="fas fa-check"></i> Added';
                        setTimeout(() => {
                            this.innerHTML = '<i class="fas fa-cart-plus"></i> Add to Cart';
                            this.disabled = false;
                        }, 2000);
                    } else {
                        showAlert('danger', 'Failed to add product to cart.');
                        this.innerHTML = '<i class="fas fa-cart-plus"></i> Add to Cart';
                        this.disabled = false;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showAlert('danger', 'An error occurred while adding to cart.');
                    this.innerHTML = '<i class="fas fa-cart-plus"></i> Add to Cart';
                    this.disabled = false;
                });
            });
        });
        
        // Cart quantity update buttons
        document.querySelectorAll('.cart-quantity-input').forEach(input => {
            input.addEventListener('change', function() {
                const productId = this.dataset.productId;
                const newQuantity = parseInt(this.value, 10);
                
                if(newQuantity < 1) {
                    this.value = 1;
                    return;
                }
                
                // Show loading indicator
                const row = this.closest('.cart-item');
                row.classList.add('opacity-50');
                
                // Update cart
                fetch(`${baseUrl}/api/cart/update`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ product_id: productId, quantity: newQuantity })
                })
                .then(response => response.json())
                .then(data => {
                    if(data.success) {
                        // Update cart counter and subtotal
                        updateCartCounter(data.cart.item_count);
                        updateCartSubtotal(data.cart.subtotal);
                        
                        // Update item subtotal
                        const price = parseFloat(row.querySelector('.cart-item-price').dataset.price);
                        const subtotal = price * newQuantity;
                        row.querySelector('.cart-item-subtotal').textContent = `$${subtotal.toFixed(2)}`;
                        
                        // Remove loading state
                        row.classList.remove('opacity-50');
                    } else {
                        showAlert('danger', 'Failed to update cart.');
                        row.classList.remove('opacity-50');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showAlert('danger', 'An error occurred while updating cart.');
                    row.classList.remove('opacity-50');
                });
            });
        });
        
        // Cart remove buttons
        document.querySelectorAll('.cart-remove-btn').forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                
                const productId = this.dataset.productId;
                const row = this.closest('.cart-item');
                
                // Show loading state
                row.classList.add('opacity-50');
                
                // Remove from cart
                fetch(`${baseUrl}/api/cart/remove`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ product_id: productId })
                })
                .then(response => response.json())
                .then(data => {
                    if(data.success) {
                        // Update cart counter and subtotal
                        updateCartCounter(data.cart.item_count);
                        updateCartSubtotal(data.cart.subtotal);
                        
                        // Remove row with animation
                        row.style.height = `${row.offsetHeight}px`;
                        row.style.overflow = 'hidden';
                        setTimeout(() => {
                            row.style.height = '0';
                            row.style.marginTop = '0';
                            row.style.marginBottom = '0';
                            row.style.paddingTop = '0';
                            row.style.paddingBottom = '0';
                            row.style.transition = 'all 0.3s ease-out';
                        }, 10);
                        setTimeout(() => {
                            row.remove();
                            
                            // Check if cart is empty
                            if(data.cart.item_count === 0) {
                                document.querySelector('.cart-items-container').innerHTML = `
                                    <div class="alert alert-info">
                                        Your cart is empty. <a href="${baseUrl}/">Continue shopping</a>
                                    </div>
                                `;
                                document.querySelector('.cart-summary').classList.add('d-none');
                            }
                        }, 300);
                    } else {
                        showAlert('danger', 'Failed to remove item from cart.');
                        row.classList.remove('opacity-50');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showAlert('danger', 'An error occurred while removing from cart.');
                    row.classList.remove('opacity-50');
                });
            });
        });
    }
    
    // Initialize product detail page
    function initializeProductDetail() {
        const quantityInput = document.getElementById('product-quantity');
        if(quantityInput) {
            // Increase/decrease buttons
            document.querySelector('.quantity-decrease').addEventListener('click', function() {
                if(quantityInput.value > 1) {
                    quantityInput.value = parseInt(quantityInput.value, 10) - 1;
                }
            });
            
            document.querySelector('.quantity-increase').addEventListener('click', function() {
                quantityInput.value = parseInt(quantityInput.value, 10) + 1;
            });
        }
    }
    
    // Initialize checkout page
    function initializeCheckout() {
        const checkoutForm = document.getElementById('checkout-form');
        if(checkoutForm) {
            checkoutForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                // Show loading state
                const submitBtn = this.querySelector('button[type="submit"]');
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
                submitBtn.disabled = true;
                
                // Submit form normally (form action handles the rest)
                this.submit();
            });
        }
    }
    
    // Initialize payment page
    function initializePayment() {
        const paymentBtn = document.getElementById('payment-button');
        if(paymentBtn) {
            paymentBtn.addEventListener('click', function(e) {
                e.preventDefault();
                
                const orderId = this.dataset.orderId;
                const amount = this.dataset.amount;
                const method = document.getElementById('payment-method').value;
                
                // Show loading state
                this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing Payment...';
                this.disabled = true;
                
                // Process payment
                fetch(`${baseUrl}/api/payment/process`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ order_id: orderId, amount: amount, method: method })
                })
                .then(response => response.json())
                .then(data => {
                    if(data.success) {
                        // Show success message
                        document.getElementById('payment-status-container').innerHTML = `
                            <div class="alert alert-success">
                                <h4><i class="fas fa-check-circle"></i> Payment Successful!</h4>
                                <p>Your payment has been processed successfully. Order status has been updated.</p>
                            </div>
                        `;
                        
                        // Update payment status
                        document.getElementById('payment-status').innerHTML = `
                            <span class="payment-status payment-completed">Completed</span>
                        `;
                        
                        // Update order status
                        document.getElementById('order-status').innerHTML = `
                            <span class="order-status status-processing">Processing</span>
                        `;
                        
                        // Show invoice button
                        document.getElementById('invoice-container').classList.remove('d-none');
                        
                        // Hide payment form
                        document.getElementById('payment-form').classList.add('d-none');
                    } else {
                        // Show error message
                        document.getElementById('payment-status-container').innerHTML = `
                            <div class="alert alert-danger">
                                <h4><i class="fas fa-times-circle"></i> Payment Failed</h4>
                                <p>Your payment could not be processed. Please try again or use a different payment method.</p>
                            </div>
                        `;
                        
                        // Reset button
                        this.innerHTML = '<i class="fas fa-credit-card"></i> Try Again';
                        this.disabled = false;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    document.getElementById('payment-status-container').innerHTML = `
                        <div class="alert alert-danger">
                            <h4><i class="fas fa-times-circle"></i> Error</h4>
                            <p>An error occurred while processing your payment. Please try again later.</p>
                        </div>
                    `;
                    this.innerHTML = '<i class="fas fa-credit-card"></i> Try Again';
                    this.disabled = false;
                });
            });
        }
    }
    
    // Initialize admin pages
    function initializeAdminPages() {
        // Stock update form
        document.querySelectorAll('.stock-update-form').forEach(form => {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const productId = this.dataset.productId;
                const stockInput = this.querySelector('.stock-input');
                const newStock = parseInt(stockInput.value, 10);
                
                if(isNaN(newStock) || newStock < 0) {
                    alert('Please enter a valid stock quantity');
                    return;
                }
                
                // Show loading state
                const submitBtn = this.querySelector('button[type="submit"]');
                const originalBtnText = submitBtn.innerHTML;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
                submitBtn.disabled = true;
                
                // Update stock
                fetch(`${baseUrl}/api/inventory/update`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ product_id: productId, stock: newStock })
                })
                .then(response => response.json())
                .then(data => {
                    if(data.success) {
                        // Show success indicator
                        submitBtn.innerHTML = '<i class="fas fa-check"></i>';
                        setTimeout(() => {
                            submitBtn.innerHTML = originalBtnText;
                            submitBtn.disabled = false;
                        }, 2000);
                        
                        // Update stock display if exists
                        const stockDisplay = document.querySelector(`.stock-display-${productId}`);
                        if(stockDisplay) {
                            stockDisplay.textContent = newStock;
                            
                            // Update stock status class
                            const stockStatusEl = document.querySelector(`.stock-status-${productId}`);
                            if(stockStatusEl) {
                                stockStatusEl.classList.remove('in-stock', 'low-stock', 'out-of-stock');
                                
                                if(newStock === 0) {
                                    stockStatusEl.classList.add('out-of-stock');
                                    stockStatusEl.textContent = 'Out of Stock';
                                } else if(newStock <= 5) {
                                    stockStatusEl.classList.add('low-stock');
                                    stockStatusEl.textContent = 'Low Stock';
                                } else {
                                    stockStatusEl.classList.add('in-stock');
                                    stockStatusEl.textContent = 'In Stock';
                                }
                            }
                        }
                    } else {
                        alert('Failed to update stock.');
                        submitBtn.innerHTML = originalBtnText;
                        submitBtn.disabled = false;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while updating stock.');
                    submitBtn.innerHTML = originalBtnText;
                    submitBtn.disabled = false;
                });
            });
        });
        
        // Product form validation
        const productForm = document.getElementById('product-form');
        if(productForm) {
            productForm.addEventListener('submit', function(e) {
                const nameInput = this.querySelector('#product-name');
                const priceInput = this.querySelector('#product-price');
                const stockInput = this.querySelector('#product-stock');
                
                if(!nameInput.value.trim()) {
                    e.preventDefault();
                    alert('Please enter a product name');
                    nameInput.focus();
                    return;
                }
                
                if(isNaN(parseFloat(priceInput.value)) || parseFloat(priceInput.value) <= 0) {
                    e.preventDefault();
                    alert('Please enter a valid price');
                    priceInput.focus();
                    return;
                }
                
                if(isNaN(parseInt(stockInput.value, 10)) || parseInt(stockInput.value, 10) < 0) {
                    e.preventDefault();
                    alert('Please enter a valid stock quantity');
                    stockInput.focus();
                    return;
                }
            });
        }
    }
    
    // Helper Functions
    
    // Update cart counter
    function updateCartCounter(count) {
        const counter = document.querySelector('.fa-shopping-cart + .badge');
        if(counter) {
            counter.textContent = count;
            
            if(count > 0) {
                counter.classList.remove('d-none');
            } else {
                counter.classList.add('d-none');
            }
        }
    }
    
    // Update cart subtotal
    function updateCartSubtotal(subtotal) {
        const subtotalElement = document.getElementById('cart-subtotal');
        if(subtotalElement) {
            subtotalElement.textContent = `$${parseFloat(subtotal).toFixed(2)}`;
        }
    }
    
    // Show alert message
    function showAlert(type, message) {
        // Create alert element
        const alertEl = document.createElement('div');
        alertEl.className = `alert alert-${type} alert-dismissible fade show position-fixed top-0 end-0 m-3`;
        alertEl.style.zIndex = 1050;
        alertEl.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        `;
        
        // Add to document
        document.body.appendChild(alertEl);
        
        // Remove after 5 seconds
        setTimeout(() => {
            alertEl.classList.remove('show');
            setTimeout(() => alertEl.remove(), 300);
        }, 5000);
    }
});