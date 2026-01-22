function updateCartBadge() {
    const formData = new FormData();
    formData.append('action', 'get');
    
    fetch('cart.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.cart) {
            let totalItems = 0;
            data.cart.forEach(item => {
                totalItems += item.quantity;
            });
            
            let badge = document.getElementById('cartBadge');
            if (totalItems > 0) {
                if (!badge) {
                    badge = document.createElement('span');
                    badge.id = 'cartBadge';
                    badge.className = 'cart-badge';
                    cartIcon.style.position = 'relative';
                    cartIcon.appendChild(badge);
                }
                badge.textContent = totalItems;
                badge.style.display = 'flex';
            } else if (badge) {
                badge.style.display = 'none';
            }
        }
    })
    .catch(error => {
        console.error('Error updating badge:', error);
    });
}

function loadCart() {
    const formData = new FormData();
    formData.append('action', 'get');
    
    fetch('cart.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            displayCart(data.cart);
        }
    })
    .catch(error => {
        console.error('Error loading cart:', error);
    });
}

function displayCart(cart) {
    const cartItems = document.getElementById('cartItems');
    const cartFooter = document.getElementById('cartFooter');
    const cartTotal = document.getElementById('cartTotal');
    
    if (!cart || cart.length === 0) {
        cartItems.innerHTML = '<div class="cart-empty">Your cart is empty</div>';
        cartFooter.style.display = 'none';
        return;
    }
    
    let html = '';
    
    cart.forEach((item, index) => {
        html += `
            <div class="cart-item">
                <img src="${item.image}" alt="${item.name}" class="cart-item-img">
                <div class="cart-item-info">
                    <div class="cart-item-name">${item.name}</div>
                    <div class="cart-item-price">${item.price}</div>
                </div>
                <div class="cart-item-controls">
                    <button class="cart-qty-btn" onclick="updateCartQuantity(${index}, ${item.quantity - 1})">−</button>
                    <span class="cart-qty">${item.quantity}</span>
                    <button class="cart-qty-btn" onclick="updateCartQuantity(${index}, ${item.quantity + 1})">+</button>
                    <button class="cart-remove" onclick="removeFromCart(${index})">×</button>
                </div>
            </div>
        `;
    });
    
    cartItems.innerHTML = html;
    
    const total = calculateTotal(cart);
    
    cartTotal.textContent = '₱' + total.toFixed(2);
    cartFooter.style.display = 'block';
}

function updateCartQuantity(index, quantity) {
    if (quantity < 1) {
        removeFromCart(index);
        return;
    }
    
    const formData = new FormData();
    formData.append('action', 'update');
    formData.append('index', index);
    formData.append('quantity', quantity);
    
    fetch('cart.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            displayCart(data.cart);
            updateCartBadge();
        }
    })
    .catch(error => {
        console.error('Error updating cart:', error);
    });
}

function removeFromCart(index) {
    const formData = new FormData();
    formData.append('action', 'remove');
    formData.append('index', index);
    
    fetch('cart.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            displayCart(data.cart);
            updateCartBadge();
        }
    })
    .catch(error => {
        console.error('Error removing from cart:', error);
    });
}

function calculateTotal(cart) {
    let mochiItems = [];
    let specialItems = [];
    
    cart.forEach(item => {
        if (item.name === 'Mochi Bites') {
            specialItems.push(item);
        } else {
            for (let i = 0; i < item.quantity; i++) {
                mochiItems.push(item);
            }
        }
    });
    
    const sets = Math.floor(mochiItems.length / 3);
    const remainder = mochiItems.length % 3;
    
    let total = sets * 100;
    total += remainder * 35;
    
    specialItems.forEach(item => {
        const priceNum = parseFloat(item.price.replace('₱', '').replace(',', ''));
        total += priceNum * item.quantity;
    });
    
    return total;
}


// ==========================================
// CHECKOUT PAGE FUNCTIONALITY
// ==========================================

// Global variables
let selectedPaymentMethod = 'cod'; // Default payment method
const DELIVERY_FEE = 50.00;

// ==========================================
// INITIALIZE ON PAGE LOAD
// ==========================================
document.addEventListener('DOMContentLoaded', function() {
    // Check if user is logged in
    checkUserAuthentication();
    
    // Load cart items
    loadCheckoutCart();
    
    // Set up payment method selection
    setupPaymentSelection();
});

// ==========================================
// CHECK USER AUTHENTICATION
// ==========================================
function checkUserAuthentication() {
    console.log('Checking authentication...');
    
    const formData = new FormData();
    formData.append('action', 'check');
    
    fetch('auth.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        console.log('Response received');
        return response.json();
    })
    .then(data => {
        console.log('Auth response:', data);
        console.log('data.user:', data.user);
        console.log('data.user.fullname:', data.user ? data.user.fullname : 'undefined');
        
        if (data.success && data.logged_in && data.user) {
            console.log('User is logged in, displaying info...');
            console.log('About to call displayUserInfo with:', data.user);
            // User is logged in, display their information
            displayUserInfo(data.user); // ✅ THIS LINE EXISTS BUT...
        } else {
            // ...redirects to shop
        }
    })
}


// ==========================================
// DISPLAY USER INFORMATION
// ==========================================
function displayUserInfo(user) {
    console.log('displayUserInfo called with user:', user);
    
    if (!user) {
        console.error('ERROR: user object is null or undefined!');
        return;
    }
    
    // Wait for DOM to be fully ready
    requestAnimationFrame(() => {
        // Display name
        const nameElement = document.getElementById('checkoutName');
        console.log('Name element found:', nameElement);
        if (nameElement) {
            nameElement.textContent = user.fullname || user.name || 'N/A';
            console.log('✓ Name set to:', nameElement.textContent);
        } else {
            console.error('❌ checkoutName element not found!');
        }
        
        // Display phone
        const phoneElement = document.getElementById('checkoutPhone');
        console.log('Phone element found:', phoneElement);
        if (phoneElement) {
            phoneElement.textContent = user.phone || 'N/A';
            console.log('✓ Phone set to:', phoneElement.textContent);
        } else {
            console.error('❌ checkoutPhone element not found!');
        }
        
        // Display address
        const addressElement = document.getElementById('checkoutAddress');
        console.log('Address element found:', addressElement);
        if (addressElement) {
            addressElement.textContent = user.address || 'N/A';
            console.log('✓ Address set to:', addressElement.textContent);
        } else {
            console.error('❌ checkoutAddress element not found!');
        }
    });
}

// ==========================================
// LOAD CART ITEMS FOR CHECKOUT
// ==========================================
function loadCheckoutCart() {
    const formData = new FormData();
    formData.append('action', 'get');
    
    fetch('cart.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            if (data.cart && data.cart.length > 0) {
                displayCheckoutItems(data.cart);
                calculateCheckoutTotals(data.cart);
            } else {
                displayEmptyCart();
            }
        } else {
            console.error('Error loading cart');
            displayEmptyCart();
        }
    })
    .catch(error => {
        console.error('Error fetching cart:', error);
        displayEmptyCart();
    });
}

// ==========================================
// DISPLAY CART ITEMS IN CHECKOUT
// ==========================================
function displayCheckoutItems(cart) {
    const orderItemsContainer = document.getElementById('orderItems');
    
    if (!orderItemsContainer) return;
    
    let html = '';
    
    cart.forEach((item, index) => {
        const priceNum = parseFloat(item.price.replace('₱', '').replace(',', ''));
        const itemTotal = priceNum * item.quantity;
        
        html += `
            <div class="order-item">
                <img src="${item.image}" alt="${item.name}" class="order-item-img">
                <div class="order-item-info">
                    <div class="order-item-name">${item.name}</div>
                    <div class="order-item-price">${item.price} × ${item.quantity}</div>
                </div>
                <div class="order-item-qty">₱${itemTotal.toFixed(2)}</div>
            </div>
        `;
    });
    
    orderItemsContainer.innerHTML = html;
}

// ==========================================
// CALCULATE MIX & MATCH TOTAL
// Same logic as in script.js
// ==========================================
function calculateTotal(cart) {
    let mochiItems = [];
    let specialItems = [];
    
    // Separate regular mochi from special items (Mochi Bites)
    cart.forEach(item => {
        if (item.name === 'Mochi Bites') {
            specialItems.push(item);
        } else {
            // Add each quantity as a separate item for mix & match calculation
            for (let i = 0; i < item.quantity; i++) {
                mochiItems.push(item);
            }
        }
    });
    
    // Calculate mix & match pricing: 3 mochi = ₱100
    const sets = Math.floor(mochiItems.length / 3);
    const remainder = mochiItems.length % 3;
    
    let total = sets * 100; // ₱100 per set of 3
    total += remainder * 35; // ₱35 per individual mochi
    
    // Add special items at regular price
    specialItems.forEach(item => {
        const priceNum = parseFloat(item.price.replace('₱', '').replace(',', ''));
        total += priceNum * item.quantity;
    });
    
    return total;
}

// ==========================================
// CALCULATE AND DISPLAY TOTALS
// ==========================================
function calculateCheckoutTotals(cart) {
    const subtotal = calculateTotal(cart);
    const total = subtotal + DELIVERY_FEE;
    
    // Update subtotal
    const subtotalElement = document.getElementById('summarySubtotal');
    if (subtotalElement) {
        subtotalElement.textContent = '₱' + subtotal.toFixed(2);
    }
    
    // Update total
    const totalElement = document.getElementById('summaryTotal');
    if (totalElement) {
        totalElement.textContent = '₱' + total.toFixed(2);
    }
}

// ==========================================
// DISPLAY EMPTY CART MESSAGE
// ==========================================
function displayEmptyCart() {
    const checkoutContainer = document.getElementById('checkoutContainer');
    
    if (!checkoutContainer) return;
    
    checkoutContainer.innerHTML = `
        <div class="empty-cart">
            <h2>Your cart is empty</h2>
            <p>Add some delicious mochi to your cart before checking out!</p>
            <a href="shop.php">Continue Shopping</a>
        </div>
    `;
}

// ==========================================
// PAYMENT METHOD SELECTION
// ==========================================
function setupPaymentSelection() {
    // This function is called on page load
    // The onclick handlers are already in the HTML
}

function selectPayment(method) {
    selectedPaymentMethod = method;
    
    // Update UI
    const allOptions = document.querySelectorAll('.payment-option');
    allOptions.forEach(option => {
        option.classList.remove('selected');
    });
    
    // Add selected class to clicked option
    const selectedOption = document.querySelector(`.payment-option input[value="${method}"]`).closest('.payment-option');
    selectedOption.classList.add('selected');
    
    // Update radio button
    document.querySelector(`input[value="${method}"]`).checked = true;
}

// ==========================================
// PLACE ORDER
// ==========================================
function placeOrder() {
    // Disable button to prevent double submission
    const placeOrderBtn = document.querySelector('.place-order-btn');
    placeOrderBtn.disabled = true;
    placeOrderBtn.textContent = 'PLACING ORDER...';
    
    // Get cart data
    const formData = new FormData();
    formData.append('action', 'get');
    
    fetch('cart.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.cart && data.cart.length > 0) {
            // Calculate totals
            const subtotal = calculateTotal(data.cart);
            const total = subtotal + DELIVERY_FEE;
            
            // Submit order to server
            submitOrder(data.cart, total, selectedPaymentMethod);
        } else {
            alert('Your cart is empty!');
            placeOrderBtn.disabled = false;
            placeOrderBtn.textContent = 'PLACE ORDER';
        }
    })
    .catch(error => {
        console.error('Error getting cart:', error);
        alert('Error processing order. Please try again.');
        placeOrderBtn.disabled = false;
        placeOrderBtn.textContent = 'PLACE ORDER';
    });
}

// ==========================================
// SUBMIT ORDER TO DATABASE
// ==========================================
function submitOrder(cart, total, paymentMethod) {
    const formData = new FormData();
    formData.append('action', 'place_order');
    formData.append('cart', JSON.stringify(cart));
    formData.append('total', total);
    formData.append('payment_method', paymentMethod);
    
    fetch('process_order.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Clear cart
            clearCart();
            
            // Show success message and redirect
            alert('Order placed successfully! Order ID: #' + data.order_id);
            window.location.href = 'account.html';
        } else {
            alert('Error placing order: ' + (data.message || 'Unknown error'));
            const placeOrderBtn = document.querySelector('.place-order-btn');
            placeOrderBtn.disabled = false;
            placeOrderBtn.textContent = 'PLACE ORDER';
        }
    })
    .catch(error => {
        console.error('Error submitting order:', error);
        alert('Error submitting order. Please try again.');
        const placeOrderBtn = document.querySelector('.place-order-btn');
        placeOrderBtn.disabled = false;
        placeOrderBtn.textContent = 'PLACE ORDER';
    });
}

// ==========================================
// CLEAR CART AFTER ORDER
// ==========================================
function clearCart() {
    const formData = new FormData();
    formData.append('action', 'clear');
    
    fetch('cart.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        console.log('Cart cleared');
    })
    .catch(error => {
        console.error('Error clearing cart:', error);
    });
}