document.addEventListener('DOMContentLoaded', function() {
    function reveal() {
        const reveals = document.querySelectorAll('.reveal');
        reveals.forEach(element => {
            const windowHeight = window.innerHeight;
            const elementTop = element.getBoundingClientRect().top;
            const elementVisible = 150;

            if (elementTop < windowHeight - elementVisible) {
                element.classList.add('active');
            }
        });
    }

    window.addEventListener('scroll', reveal);
    reveal();
    
    // Check login status on page load
    checkLoginStatus();
});

let currentQuantity = 1;
let currentProduct = {};

function openModal(name, price, img, description) {
    currentProduct = { name, price, img, description };
    currentQuantity = 1;
    
    document.getElementById('modalProductName').textContent = name;
    document.getElementById('modalProductPrice').textContent = price;
    document.getElementById('modalProductImg').src = img;
    document.getElementById('modalProductDescription').textContent = description;
    document.getElementById('quantityDisplay').textContent = currentQuantity;
    
    document.getElementById('productModal').classList.add('active');
    document.body.style.overflow = 'hidden';
}

function closeModal() {
    document.getElementById('productModal').classList.remove('active');
    document.body.style.overflow = 'auto';
}

function increaseQuantity() {
    currentQuantity++;
    document.getElementById('quantityDisplay').textContent = currentQuantity;
}

function decreaseQuantity() {
    if (currentQuantity > 1) {
        currentQuantity--;
        document.getElementById('quantityDisplay').textContent = currentQuantity;
    }
}

// Close modal when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById('productModal');
    const authModal = document.getElementById('authModal');
    
    if (event.target === modal) {
        closeModal();
    }
    
    if (event.target === authModal) {
        closeAuthModal();
    }
}

// Close modal with Escape key
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeModal();
        closeAuthModal();
    }
});

// Cart functionality
const cartIcon = document.querySelector('a[href="cart.html"]');
const cartSidebar = document.getElementById('cartSidebar');
const cartOverlay = document.getElementById('cartOverlay');
const closeCart = document.getElementById('closeCart');

// Open cart when clicking cart icon
if (cartIcon) {
    cartIcon.addEventListener('click', (e) => {
        e.preventDefault();
        cartSidebar.classList.add('active');
        cartOverlay.classList.add('active');
        loadCart();
    });
}

// Close cart
if (closeCart) {
    closeCart.addEventListener('click', () => {
        cartSidebar.classList.remove('active');
        cartOverlay.classList.remove('active');
    });
}

if (cartOverlay) {
    cartOverlay.addEventListener('click', () => {
        cartSidebar.classList.remove('active');
        cartOverlay.classList.remove('active');
    });
}

// Update cart badge count
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

// Add to cart function
function addToCart() {
    const name = document.getElementById('modalProductName').textContent;
    const price = document.getElementById('modalProductPrice').textContent;
    const image = document.getElementById('modalProductImg').src;
    const quantity = parseInt(document.getElementById('quantityDisplay').textContent);
    
    const formData = new FormData();
    formData.append('action', 'add');
    formData.append('name', name);
    formData.append('price', price);
    formData.append('image', image);
    formData.append('quantity', quantity);
    
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
            closeModal();
            currentQuantity = 1;
            updateCartBadge();
            cartSidebar.classList.add('active');
            cartOverlay.classList.add('active');
            loadCart();
        } else {
            console.error('Error from server:', data.error);
            alert('Error adding to cart. Please try again.');
        }
    })
    .catch(error => {
        console.error('Error adding to cart:', error);
        alert('Error adding to cart. Please make sure you are using a PHP server.');
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

// Mix & Match pricing: 3 mochi (except Mochi Bites) = ₱100
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

// AUTH MODAL FUNCTIONS
function openAuthModal() {
    const authModal = document.getElementById('authModal');
    if (authModal) {
        authModal.classList.add('active');
        document.body.style.overflow = 'hidden';
    }
}

function closeAuthModal() {
    const authModal = document.getElementById('authModal');
    if (authModal) {
        authModal.classList.remove('active');
        document.body.style.overflow = 'auto';
    }
}

// --- ACCOUNT & AUTHENTICATION LOGIC ---

/**
 * Enhanced checkLoginStatus to handle profile UI updates if on account.html
 */
function checkLoginStatus() {
    const profileSection = document.getElementById('profileSection');
    const loginSection = document.getElementById('loginSection');
    
    // If these elements don't exist, we aren't on the account page, 
    // but we might still want to check status for other things
    const isAccountPage = !!(profileSection && loginSection);

    const formData = new FormData();
    formData.append('action', 'check');
    
    fetch('auth.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.logged_in && data.user) {
            // Update UI if on Account Page
            if (isAccountPage) {
                profileSection.style.display = 'block';
                loginSection.style.display = 'none';
                
                // Map database fields to UI
                // Note: using data.user.fullname to match your SQL schema
                document.getElementById('profileName').textContent = data.user.fullname || data.user.name;
                document.getElementById('profileEmail').textContent = data.user.email;
                document.getElementById('profilePhone').textContent = data.user.phone || 'Not provided';
                
                // Combine street and city for address display
                const address = data.user.street ? `${data.user.street}, ${data.user.city}` : data.user.address;
                document.getElementById('profileAddress').textContent = address;
                
                // Set initials icon
                const initials = getInitials(data.user.fullname || data.user.name);
                document.getElementById('userInitials').textContent = initials;
            }
        } else {
            if (isAccountPage) {
                profileSection.style.display = 'none';
                loginSection.style.display = 'block';
            }
        }
    })
    .catch(error => {
        console.error('Error checking login status:', error);
        if (isAccountPage) {
            profileSection.style.display = 'none';
            loginSection.style.display = 'block';
        }
    });
}

function getInitials(name) {
    if (!name) return "?";
    return name.split(' ')
        .map(word => word.charAt(0))
        .join('')
        .toUpperCase()
        .substring(0, 2);
}

function logout() {
    if (!confirm('Are you sure you want to logout?')) {
        return;
    }
    
    const formData = new FormData();
    formData.append('action', 'logout');
    
    fetch('auth.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.href = 'account.html';
        } else {
            alert('Error logging out. Please try again.');
        }
    })
    .catch(error => {
        console.error('Error logging out:', error);
        alert('Error logging out. Please try again.');
    });
}

// Initial Cart Badge Update
if (typeof updateCartBadge === 'function') {
    updateCartBadge();
}

// Check authentication before checkout
function checkAuthBeforeCheckout() {
    const formData = new FormData();
    formData.append('action', 'check');
    
    fetch('auth.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.logged_in) {
            // User is logged in, proceed to checkout
            proceedToCheckout();
        } else {
            // User is not logged in, show auth modal
            openAuthModal();
        }
    })
    .catch(error => {
        console.error('Error:', error);
        openAuthModal();
    });
}

function proceedToCheckout() {
    // Proceed with checkout process
    alert('Proceeding to checkout...');
    // You can redirect to a checkout page or show checkout form
    // window.location.href = 'checkout.html';
}

// Load cart badge on page load
updateCartBadge();

// Function to fetch products from auth.php and display them
function loadProductsFromDB() {
    const formData = new FormData();
    formData.append('action', 'get_products');

    fetch('auth.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const container = document.getElementById('product-grid');
            let html = '';

            data.products.forEach(product => {
                // We use backticks (``) to create a template for each product card
                html += `
                    <div class="product-column">
                        <div class="product-card">
                            <img src="${product.image_path}" alt="${product.name}">
                            <h1>${product.name}</h1>
                            <p class="edu-school">₱${parseFloat(product.price).toFixed(2)}</p>
                            <button onclick="openModal('${product.name}', '₱${product.price}', '${product.image_path}', '${product.description}')">
                                <p>ORDER</p>
                            </button>
                        </div>
                    </div>`;
            });

            container.innerHTML = html;
            
            // Re-trigger the reveal animation for the new elements
            if (typeof reveal === 'function') reveal();
        }
    })
    .catch(error => console.error('Error loading products:', error));
}

// Call this function when the page loads
document.addEventListener('DOMContentLoaded', function() {
    loadProductsFromDB();
});