// Hamburger Menu Toggle
const hamburger = document.getElementById('hamburger');
const navLinks = document.getElementById('navLinks');
const mobileMenuOverlay = document.getElementById('mobileMenuOverlay');

if (hamburger) {
    hamburger.addEventListener('click', () => {
        hamburger.classList.toggle('active');
        navLinks.classList.toggle('active');
        mobileMenuOverlay.classList.toggle('active');
    });
}

// Close menu when clicking overlay
if (mobileMenuOverlay) {
    mobileMenuOverlay.addEventListener('click', () => {
        hamburger.classList.remove('active');
        navLinks.classList.remove('active');
        mobileMenuOverlay.classList.remove('active');
    });
}

// Close menu when clicking a link
const navLinkItems = document.querySelectorAll('.nav-links a');
navLinkItems.forEach(link => {
    link.addEventListener('click', () => {
        hamburger.classList.remove('active');
        navLinks.classList.remove('active');
        mobileMenuOverlay.classList.remove('active');
    });
});


function initHeroSlider() {
    const slides = document.querySelectorAll('.section-1 .slider img');
    const navDots = document.querySelectorAll('.section-1 .slider-nav a');
    
    if (slides.length === 0 || navDots.length === 0) return;
    
    let currentSlide = 0;
    const slideInterval = 3000;

    function showSlide(index) {
        slides.forEach(slide => slide.classList.remove('active'));
        navDots.forEach(dot => dot.classList.remove('active'));
        
        slides[index].classList.add('active');
        navDots[index].classList.add('active');
    }

    function nextSlide() {
        currentSlide = (currentSlide + 1) % slides.length;
        showSlide(currentSlide);
    }

    let autoSlide = setInterval(nextSlide, slideInterval);

    navDots.forEach((dot, index) => {
        dot.addEventListener('click', () => {
            currentSlide = index;
            showSlide(currentSlide);
            clearInterval(autoSlide);
            autoSlide = setInterval(nextSlide, slideInterval);
        });
    });
}

// Section 3 Bestseller Slider
function initBestsellerSlider() {
    const slider = document.querySelector('.bestseller-slider');
    const slides = document.querySelectorAll('.bestseller-slider img');
    const navDots = document.querySelectorAll('.section-3 .slider-nav a');
    const textElements = document.querySelectorAll('.bestseller-text');
    
    if (!slider || slides.length === 0 || navDots.length === 0 || textElements.length === 0) return;
    
    let currentSlide = 0;
    const slideInterval = 8000;

    function showSlide(index) {
        textElements.forEach(text => text.classList.remove('active'));
        slider.style.transform = `translateX(-${index * 100}%)`;
        navDots.forEach(dot => dot.classList.remove('active'));
        navDots[index].classList.add('active');
        
        setTimeout(() => {
            textElements[index].classList.add('active');
        }, 1000);
    }

    function nextSlide() {
        currentSlide = (currentSlide + 1) % slides.length;
        showSlide(currentSlide);
    }

    let autoSlide = setInterval(nextSlide, slideInterval);

    navDots.forEach((dot, index) => {
        dot.addEventListener('click', () => {
            currentSlide = index;
            showSlide(currentSlide);
            clearInterval(autoSlide);
            autoSlide = setInterval(nextSlide, slideInterval);
        });
    });
}

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

    initHeroSlider();
    initBestsellerSlider();
    
    // Check login status on page load
    checkLoginStatus();
});

let currentQuantity = 1;
let currentProduct = {};

let currentStock = 0;

function openModal(name, price, image, description, stock) {
    currentProductName = name;
    currentProductPrice = price;
    currentProductImage = image;
    currentStock = stock;
    
    document.getElementById('modalProductName').textContent = name;
    document.getElementById('modalProductPrice').textContent = price;
    document.getElementById('modalProductImg').src = image;
    document.getElementById('modalProductDescription').textContent = description;
    
    // Reset quantity
    currentQuantity = 1;
    document.getElementById('quantityDisplay').textContent = currentQuantity;
    
    // Update button states
    updateQuantityButtons(); // ADD THIS LINE
    
    // Show modal
    document.getElementById('productModal').classList.add('active');
    document.body.style.overflow = 'hidden';
}

function increaseQuantity() {
    if (currentQuantity < currentStock) { // CHECK AGAINST STOCK
        currentQuantity++;
        document.getElementById('quantityDisplay').textContent = currentQuantity;
    } else {
        alert(`Maximum available stock is ${currentStock} pieces`);
    }
}

function closeModal() {
    document.getElementById('productModal').classList.remove('active');
    document.body.style.overflow = '';
    
    // Remove max stock message if exists
    const maxStockMsg = document.getElementById('maxStockMessage');
    if (maxStockMsg) {
        maxStockMsg.remove();
    }
}

function increaseQuantity() {
    if (currentQuantity < currentStock) {
        currentQuantity++;
        document.getElementById('quantityDisplay').textContent = currentQuantity;
        updateQuantityButtons(); // Add this
    }
}

function decreaseQuantity() {
    if (currentQuantity > 1) {
        currentQuantity--;
        document.getElementById('quantityDisplay').textContent = currentQuantity;
        updateQuantityButtons(); // Add this
    }
}

// ADD THIS NEW FUNCTION
function updateQuantityButtons() {
    const increaseBtn = document.querySelector('.quantity-selector .quantity-btn:last-child');
    const decreaseBtn = document.querySelector('.quantity-selector .quantity-btn:first-child');
    const addToCartBtn = document.querySelector('.add-to-cart-btn');
    
    // Disable increase button if at max stock
    if (currentQuantity >= currentStock) {
        increaseBtn.disabled = true;
        increaseBtn.style.opacity = '0.5';
        increaseBtn.style.cursor = 'not-allowed';
        
       
        // Show max stock message BELOW quantity selector
            let maxStockMsg = document.getElementById('maxStockMessage');
            if (!maxStockMsg) {
                maxStockMsg = document.createElement('p');
                maxStockMsg.id = 'maxStockMessage';
                maxStockMsg.style.cssText = 'color: #d32f2f; font-size: 13px; margin-top: 0px; text-align: center; font-weight: 600; display: block; width: 100%;';
                maxStockMsg.textContent = `Maximum available: ${currentStock} pcs`;
                
                // Insert AFTER the quantity selector, not inside it
                const quantitySelector = document.querySelector('.quantity-selector');
                quantitySelector.parentNode.insertBefore(maxStockMsg, quantitySelector.nextSibling);
                }
    } else {
        increaseBtn.disabled = false;
        increaseBtn.style.opacity = '1';
        increaseBtn.style.cursor = 'pointer';
        
        // Remove max stock message
        const maxStockMsg = document.getElementById('maxStockMessage');
        if (maxStockMsg) {
            maxStockMsg.remove();
        }
    }
    
    // Disable decrease button if at 1
    if (currentQuantity <= 1) {
        decreaseBtn.disabled = true;
        decreaseBtn.style.opacity = '0.5';
        decreaseBtn.style.cursor = 'not-allowed';
    } else {
        decreaseBtn.disabled = false;
        decreaseBtn.style.opacity = '1';
        decreaseBtn.style.cursor = 'pointer';
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

                document.getElementById('orderCount').textContent = data.user.order_count;
                document.getElementById('totalSpent').textContent = '₱' + parseFloat(data.user.total_spent).toLocaleString(undefined, {minimumFractionDigits: 2});
                
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
    const formData = new FormData();
    formData.append('action', 'logout');
    
    fetch('auth.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            localStorage.removeItem('user');
            sessionStorage.removeItem('user');
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

// Function to open order history modal and fetch orders
function openHistoryModal() {
    const modal = document.getElementById('historyModal');
    const list = document.getElementById('historyList');
    const table = document.getElementById('historyTable');
    const loading = document.getElementById('historyLoading');
    const empty = document.getElementById('historyEmpty');

    modal.classList.add('active');
    document.body.style.overflow = 'hidden';

    // Fetch orders
    const formData = new FormData();
    formData.append('action', 'check'); // Using our check action which now returns history

    fetch('auth.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        loading.style.display = 'none';
        
        if (data.success && data.user.history && data.user.history.length > 0) {
            table.style.display = 'table';
            empty.style.display = 'none';
            
            list.innerHTML = data.user.history.map(order => {
                const dateObj = new Date(order.created_at);
                
                // Format Date: MM/DD/YYYY
                const date = dateObj.toLocaleDateString();
                
                // Format Time: 12-hour format (e.g., 1:30 PM)
                const time = dateObj.toLocaleTimeString([], { 
                    hour: '2-digit', 
                    minute: '2-digit', 
                    hour12: true 
                });

                return `
                    <tr>
                        <td>#${order.id}</td>
                        <td>${date}</td>
                        <td>${time}</td>
                        <td>₱${parseFloat(order.total_amount).toFixed(2)}</td>
                        <td><span class="status-badge status-${order.status}">${order.status}</span></td>
                    </tr>
                `;
            }).join('');
        } else {
            table.style.display = 'none';
            empty.style.display = 'block';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        loading.innerText = "Error loading history.";
    });
}

function closeHistoryModal() {
    document.getElementById('historyModal').classList.remove('active');
    document.body.style.overflow = 'auto';
}

function checkUserAuthentication() {
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
            window.location.href = 'checkout.php';
        } else {
            // User is not logged in, show auth modal
            openAuthModal();
        }
    })
    .catch(error => {
        console.error('Error checking authentication:', error);
        openAuthModal();
    });
}

// Open Edit Profile Modal
function openEditModal() {
    const modal = document.getElementById('editModal');
    modal.classList.add('active');
    document.body.style.overflow = 'hidden';
    
    // Pre-fill form with current data
    const formData = new FormData();
    formData.append('action', 'check');
    
    fetch('auth.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.user) {
            document.getElementById('editFullname').value = data.user.fullname || '';
            document.getElementById('editPhone').value = data.user.phone || '';
            document.getElementById('editStreet').value = data.user.street || '';
            document.getElementById('editCity').value = data.user.city || '';
            document.getElementById('editZipcode').value = data.user.zipcode || '';
        }
    })
    .catch(error => console.error('Error loading profile:', error));
}

function closeEditModal() {
    document.getElementById('editModal').classList.remove('active');
    document.body.style.overflow = 'auto';
}

// Handle Edit Profile Form Submission
document.addEventListener('DOMContentLoaded', function() {
    const editForm = document.getElementById('editProfileForm');
    if (editForm) {
        editForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData();
            formData.append('action', 'update_profile');
            formData.append('fullname', document.getElementById('editFullname').value);
            formData.append('phone', document.getElementById('editPhone').value);
            formData.append('street', document.getElementById('editStreet').value);
            formData.append('city', document.getElementById('editCity').value);
            formData.append('zipcode', document.getElementById('editZipcode').value);
            
            fetch('auth.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    openSuccessModal();
                    
                    checkLoginStatus(); // Refresh profile display
                } else {
                    alert('Error updating profile: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error updating profile. Please try again.');
            });
        });
    }
});

function checkUserAuthenticationA() {
    const formData = new FormData();
    formData.append('action', 'check');
    
    fetch('auth.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.logged_in) {
            // User is logged in, go to checkout
            window.location.href = 'checkout.php';
        } else {
            // User is not logged in, show auth modal
            openAuthModal();
        }
    })
    .catch(error => {
        console.error('Error checking authentication:', error);
        openAuthModal();
    });
}

// Close modals with Escape key
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        // Close product modal
        const productModal = document.getElementById('productModal');
        if (productModal && productModal.classList.contains('active')) {
            closeModal();
        }
        
        // Close auth modal
        const authModal = document.getElementById('authModal');
        if (authModal && authModal.classList.contains('active')) {
            closeAuthModal();
        }
        
        // Close history modal
        const historyModal = document.getElementById('historyModal');
        if (historyModal && historyModal.classList.contains('active')) {
            closeHistoryModal();
        }
        
        // Close edit profile modal
        const editModal = document.getElementById('editModal');
        if (editModal && editModal.classList.contains('active')) {
            closeEditModal();
        }
    }
});

// Close modal when clicking outside (on overlay)
window.onclick = function(event) {
    // Product modal
    const productModal = document.getElementById('productModal');
    if (event.target === productModal) {
        closeModal();
    }
    
    // Auth modal
    const authModal = document.getElementById('authModal');
    if (event.target === authModal) {
        closeAuthModal();
    }
    
    // History modal
    const historyModal = document.getElementById('historyModal');
    if (event.target === historyModal) {
        closeHistoryModal();
    }
    
    // Edit modal
    const editModal = document.getElementById('editModal');
    if (event.target === editModal) {
        closeEditModal();
    }
}

// Function to close history modal
function closeHistoryModal() {
    const modal = document.getElementById('historyModal');
    if (modal) {
        modal.classList.remove('active');
        document.body.style.overflow = 'auto';
    }
}

// Function to close edit modal
function closeEditModal() {
    const modal = document.getElementById('editModal');
    if (modal) {
        modal.classList.remove('active');
        document.body.style.overflow = 'auto';
    }
}

// Function to close product modal
function closeModal() {
    const modal = document.getElementById('productModal');
    if (modal) {
        modal.classList.remove('active');
        document.body.style.overflow = 'auto';
    }
}

// Function to close auth modal
function closeAuthModal() {
    const authModal = document.getElementById('authModal');
    if (authModal) {
        authModal.classList.remove('active');
        document.body.style.overflow = 'auto';
    }
}

// Open logout modal
function openLogoutModal() {
    document.getElementById('logoutModal').classList.add('active');
}

// Close logout modal
function closeLogoutModal() {
    document.getElementById('logoutModal').classList.remove('active');
}

// Confirm logout - call your existing logout function
function confirmLogout() {
    closeLogoutModal();
    logout(); // Your existing logout function
}

// Close modal when clicking outside (add this event listener)
document.addEventListener('DOMContentLoaded', function() {
    const logoutModal = document.getElementById('logoutModal');
    if (logoutModal) {
        logoutModal.addEventListener('click', function(e) {
            if (e.target === this) {
                closeLogoutModal();
            }
        });
    }
});

function openSuccessModal() {
    document.getElementById('successModal').classList.add('active');
}

function closeSuccessModal() {
    document.getElementById('successModal').classList.remove('active');
    closeEditModal(); // Also close the edit modal
}

// Check stock availability before checkout
function checkUserAuthentication() {
    // First verify stock availability
    const formData = new FormData();
    formData.append('action', 'get');
    
    fetch('cart.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.cart) {
            // Validate stock for each item
            let hasStockIssue = false;
            
            data.cart.forEach(item => {
                // You'd need to add an endpoint to check current stock
                // For now, this assumes stock is managed correctly
            });
            
            // Proceed with authentication check
            proceedToCheckout();
        }
    });
}

function proceedToCheckout() {
    const authFormData = new FormData();
    authFormData.append('action', 'check');
    
    fetch('auth.php', {
        method: 'POST',
        body: authFormData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.logged_in) {
            window.location.href = 'checkout.php';
        } else {
            document.getElementById('authModal').classList.add('active');
        }
    });
}