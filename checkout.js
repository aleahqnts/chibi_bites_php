
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


// ==========================================
// CHECKOUT PAGE FUNCTIONALITY
// ==========================================

// Global variables
let selectedPaymentMethod = 'gcash'; // Default payment method

// ==========================================
// DELIVERY FEE CONFIGURATION
// ==========================================
const DELIVERY_FEES = {
  'antipolo': 50,

  // Zone 1 – Near East NCR (6–10 km)
  'marikina': 80,
  'pasig': 80,
  'pateros': 80,

  // Zone 2 – Central NCR (11–15 km)
  'quezon city': 100,
  'mandaluyong': 100,
  'san juan': 100,
  'makati': 100,
  'taguig': 100,

  // Zone 3 – Far NCR (16–20 km)
  'manila': 120,
  'pasay': 120,
  'parañaque': 120,
  'muntinlupa': 120,

  // Zone 4 – Farthest NCR (21–25 km)
  'caloocan': 150,
  'malabon': 150,
  'navotas': 160,
  'valenzuela': 150,
  'las piñas': 150,

  // Fallback
  'default': 170
};


// Function to get delivery fee based on city
function getDeliveryFee(city) {
    if (!city) return DELIVERY_FEES['default'];
    
    const cityLower = city.toLowerCase().trim();
    
    // Check if exact match exists
    if (DELIVERY_FEES[cityLower]) {
        return DELIVERY_FEES[cityLower];
    }
    
    // Check for partial matches
    for (let key in DELIVERY_FEES) {
        if (cityLower.includes(key) || key.includes(cityLower)) {
            return DELIVERY_FEES[key];
        }
    }
    
    // Return default if no match found
    return DELIVERY_FEES['default'];
}

let currentDeliveryFee = DELIVERY_FEES['default']; // Will be updated based on user's city

// ==========================================
// INITIALIZE ON PAGE LOAD
// ==========================================
document.addEventListener('DOMContentLoaded', function() {
    // Only run checkout functions if we're on the checkout page
    const checkoutContainer = document.getElementById('checkoutContainer');
    
    if (checkoutContainer) {
        // We're on the checkout page
        checkUserAuthentication();
        loadCheckoutCart();
        setupPaymentSelection();
    }
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
        if (nameElement) {
            nameElement.textContent = user.fullname || user.name || 'N/A';
        }
        
        // Display phone
        const phoneElement = document.getElementById('checkoutPhone');
        if (phoneElement) {
            phoneElement.textContent = user.phone || 'N/A';
        }
        
        // Display address
        const addressElement = document.getElementById('checkoutAddress');
        if (addressElement) {
            addressElement.textContent = user.address || 'N/A';
        }
        
        // SET DELIVERY FEE BASED ON USER'S CITY
        const userCity = user.city || '';
        currentDeliveryFee = getDeliveryFee(userCity);
        console.log('User city:', userCity, '| Delivery fee:', currentDeliveryFee);
        
        // Update delivery fee display
        updateDeliveryFeeDisplay();
        
        // Reload cart to recalculate totals with new delivery fee
        loadCheckoutCart();
    });
}

// ==========================================
// UPDATE DELIVERY FEE DISPLAY
// ==========================================
function updateDeliveryFeeDisplay() {
    const deliveryFeeElement = document.querySelector('.summary-item:nth-child(2) span:last-child');
    if (deliveryFeeElement) {
        deliveryFeeElement.textContent = '₱' + currentDeliveryFee.toFixed(2);
    }
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

function displayCheckoutItems(cart) {
    const orderItemsContainer = document.getElementById('orderItems');
    
    if (!orderItemsContainer) return;
    
    let html = '';
    
    cart.forEach((item, index) => {
        let itemTotal;
        
        if (item.name === 'Mochi Bites') {
            // Mochi Bites at regular price
            const priceNum = parseFloat(item.price.replace('₱', '').replace(',', ''));
            itemTotal = priceNum * item.quantity;
        } else {
            // Regular mochi with mix & match pricing
            const sets = Math.floor(item.quantity / 3);
            const remainder = item.quantity % 3;
            itemTotal = (sets * 100) + (remainder * 35);
        }
        
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
    const total = subtotal + currentDeliveryFee; // Use dynamic fee
    
    // Update subtotal
    const subtotalElement = document.getElementById('summarySubtotal');
    if (subtotalElement) {
        subtotalElement.textContent = '₱' + subtotal.toFixed(2);
    }
    
    // Update delivery fee
    updateDeliveryFeeDisplay();
    
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
// PLACE ORDER - SHOW PAYMENT MODAL
// ==========================================
let currentOrderData = null;

function placeOrder() {
    // Disable button to prevent double submission
    const placeOrderBtn = document.querySelector('.place-order-btn');
    placeOrderBtn.disabled = true;
    placeOrderBtn.textContent = 'PROCESSING...';
    
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
            // Calculate totals with dynamic delivery fee
            const subtotal = calculateTotal(data.cart);
            const total = subtotal + currentDeliveryFee; // Use dynamic fee
            
            // Store order data for later
            currentOrderData = {
                cart: data.cart,
                total: total,
                paymentMethod: selectedPaymentMethod
            };
            
            // Show payment modal
            showPaymentModal(total, selectedPaymentMethod);
            
            // Re-enable button
            placeOrderBtn.disabled = false;
            placeOrderBtn.textContent = 'PLACE ORDER';
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
// SHOW ORDER SUCCESS MODAL
// ==========================================
function showOrderSuccessModal(orderId) {
    // Update order ID in modal
    const orderIdDisplay = document.getElementById('displayOrderId');
    if (orderIdDisplay) {
        orderIdDisplay.textContent = '#' + orderId;
    }
    
    // Show modal
    const modal = document.getElementById('orderSuccessModal');
    if (modal) {
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
    }
    
    // Reset the place order button
    const placeOrderBtn = document.querySelector('.place-order-btn');
    if (placeOrderBtn) {
        placeOrderBtn.disabled = false;
        placeOrderBtn.textContent = 'PLACE ORDER';
    }
}

// ==========================================
// SUBMIT ORDER TO DATABASE
// ==========================================
/*function submitOrder(cart, total, paymentMethod) {
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
            
            // Show success modal instead of alert
            showOrderSuccessModal(data.order_id);
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
}*/

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

// ==========================================
// PAYMENT MODAL FUNCTIONS
// ==========================================
let selectedFile = null;

function showPaymentModal(total, paymentMethod) {
    // Set total amount
    document.getElementById('modalTotalAmount').textContent = '₱' + total.toFixed(2);
    
    // Set QR code based on payment method
    const qrImage = document.getElementById('qrCodeImage');
    if (paymentMethod === 'gcash') {
        qrImage.src = 'images/gcash_qrcode.png';
    } else if (paymentMethod === 'bank_transfer') {
        qrImage.src = 'images/maribank_qrcode.png';
    }
    
    // Show modal
    document.getElementById('paymentModal').classList.add('active');
    document.body.style.overflow = 'hidden';
    
    // Reset file upload
    selectedFile = null;
    document.getElementById('paymentProof').value = '';
    document.getElementById('fileName').textContent = '';
    document.getElementById('filePreview').innerHTML = '';
    document.getElementById('confirmPaymentBtn').disabled = true;
}

function closePaymentModal() {
    document.getElementById('paymentModal').classList.remove('active');
    document.body.style.overflow = '';
    selectedFile = null;
}

function handleFileSelect(event) {
    const file = event.target.files[0];
    
    if (!file) return;
    
    // Validate file type
    const validTypes = ['image/jpeg', 'image/jpg', 'image/png'];
    if (!validTypes.includes(file.type)) {
        alert('Please upload only JPG or PNG images');
        event.target.value = '';
        return;
    }
    
    // Validate file size (max 5MB)
    if (file.size > 5 * 1024 * 1024) {
        alert('File size must be less than 5MB');
        event.target.value = '';
        return;
    }
    
    selectedFile = file;
    
    // Display file name
    document.getElementById('fileName').textContent = file.name;
    
    // Show preview
    const reader = new FileReader();
    reader.onload = function(e) {
        document.getElementById('filePreview').innerHTML = 
            `<img src="${e.target.result}" alt="Payment Proof Preview">`;
    };
    reader.readAsDataURL(file);
    
    // Enable confirm button
    document.getElementById('confirmPaymentBtn').disabled = false;
}

function confirmPayment() {
    if (!selectedFile || !currentOrderData) {
        alert('Please upload payment proof');
        return;
    }
    
    const confirmBtn = document.getElementById('confirmPaymentBtn');
    confirmBtn.disabled = true;
    confirmBtn.textContent = 'UPLOADING...';
    
    // Create form data with order and file
    const formData = new FormData();
    formData.append('action', 'place_order');
    formData.append('cart', JSON.stringify(currentOrderData.cart));
    formData.append('total', currentOrderData.total);
    formData.append('payment_method', currentOrderData.paymentMethod);
    formData.append('payment_proof', selectedFile);
    
    fetch('process_order.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Clear cart
            clearCart();
            
            // Close payment modal
            closePaymentModal();
            
            // Show success modal
            showOrderSuccessModal(data.order_id);
            
            // Reset
            currentOrderData = null;
            selectedFile = null;
        } else {
            alert('Error placing order: ' + (data.message || 'Unknown error'));
            confirmBtn.disabled = false;
            confirmBtn.textContent = 'CONFIRM PAYMENT';
        }
    })
    .catch(error => {
        console.error('Error submitting order:', error);
        alert('Error submitting order. Please try again.');
        confirmBtn.disabled = false;
        confirmBtn.textContent = 'CONFIRM PAYMENT';
    });
}