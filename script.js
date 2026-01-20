// Section 1 Slider (Home page hero slider)
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
    const slideInterval = 15000;

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

// Reveal animation on scroll
function initRevealAnimation() {
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
}

// Product Modal Functions
let currentQuantity = 1;
let currentProduct = {};

function openModal(name, price, img, description) {
    const modal = document.getElementById('productModal');
    const modalName = document.getElementById('modalProductName');
    const modalPrice = document.getElementById('modalProductPrice');
    const modalImg = document.getElementById('modalProductImg');
    const modalDesc = document.getElementById('modalProductDescription');
    const quantityDisplay = document.getElementById('quantityDisplay');
    
    if (!modal || !modalName || !modalPrice || !modalImg || !modalDesc || !quantityDisplay) return;
    
    currentProduct = { name, price, img, description };
    currentQuantity = 1;
    
    modalName.textContent = name;
    modalPrice.textContent = price;
    modalImg.src = img;
    modalDesc.textContent = description;
    quantityDisplay.textContent = currentQuantity;
    
    modal.classList.add('active');
    document.body.style.overflow = 'hidden';
}

function closeModal() {
    const modal = document.getElementById('productModal');
    if (!modal) return;
    
    modal.classList.remove('active');
    document.body.style.overflow = 'auto';
}

function increaseQuantity() {
    const quantityDisplay = document.getElementById('quantityDisplay');
    if (!quantityDisplay) return;
    
    currentQuantity++;
    quantityDisplay.textContent = currentQuantity;
}

function decreaseQuantity() {
    const quantityDisplay = document.getElementById('quantityDisplay');
    if (!quantityDisplay) return;
    
    if (currentQuantity > 1) {
        currentQuantity--;
        quantityDisplay.textContent = currentQuantity;
    }
}

// Cart Functions
function initCart() {
    const cartIcon = document.querySelector('a[href="cart.html"]');
    const cartSidebar = document.getElementById('cartSidebar');
    const cartOverlay = document.getElementById('cartOverlay');
    const closeCart = document.getElementById('closeCart');

    if (cartIcon && cartSidebar && cartOverlay) {
        cartIcon.addEventListener('click', (e) => {
            e.preventDefault();
            cartSidebar.classList.add('active');
            cartOverlay.classList.add('active');
            loadCart();
        });
    }

    if (closeCart && cartSidebar && cartOverlay) {
        closeCart.addEventListener('click', () => {
            cartSidebar.classList.remove('active');
            cartOverlay.classList.remove('active');
        });
    }

    if (cartOverlay && cartSidebar) {
        cartOverlay.addEventListener('click', () => {
            cartSidebar.classList.remove('active');
            cartOverlay.classList.remove('active');
        });
    }
}

function updateCartBadge() {
    const cartIcon = document.querySelector('a[href="cart.html"]');
    if (!cartIcon) return;
    
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

function addToCart() {
    const modalName = document.getElementById('modalProductName');
    const modalPrice = document.getElementById('modalProductPrice');
    const modalImg = document.getElementById('modalProductImg');
    const quantityDisplay = document.getElementById('quantityDisplay');
    
    if (!modalName || !modalPrice || !modalImg || !quantityDisplay) return;
    
    const name = modalName.textContent;
    const price = modalPrice.textContent;
    const image = modalImg.src;
    const quantity = parseInt(quantityDisplay.textContent);
    
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
            
            const cartSidebar = document.getElementById('cartSidebar');
            const cartOverlay = document.getElementById('cartOverlay');
            if (cartSidebar && cartOverlay) {
                cartSidebar.classList.add('active');
                cartOverlay.classList.add('active');
            }
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
    
    if (!cartItems || !cartFooter || !cartTotal) return;
    
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

// Auth Modal Functions
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

function checkLoginStatus() {
    const formData = new FormData();
    formData.append('action', 'check');
    
    fetch('auth.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.logged_in) {
            updateUIForLoggedInUser(data.user);
        } else {
            updateUIForGuest();
        }
    })
    .catch(error => {
        console.error('Error checking login:', error);
    });
}

function updateUIForLoggedInUser(user) {
    console.log('User logged in:', user.name);
}

function updateUIForGuest() {
    const checkoutBtn = document.querySelector('.checkout-btn');
    if (checkoutBtn) {
        checkoutBtn.onclick = function() {
            checkAuthBeforeCheckout();
        };
    }
}

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
            proceedToCheckout();
        } else {
            openAuthModal();
        }
    })
    .catch(error => {
        console.error('Error:', error);
        openAuthModal();
    });
}

function proceedToCheckout() {
    alert('Proceeding to checkout...');
}

// Modal close handlers
function initModalHandlers() {
    window.onclick = function(event) {
        const modal = document.getElementById('productModal');
        const authModal = document.getElementById('authModal');
        
        if (modal && event.target === modal) {
            closeModal();
        }
        
        if (authModal && event.target === authModal) {
            closeAuthModal();
        }
    }

    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            closeModal();
            closeAuthModal();
        }
    });
}

// Initialize everything on DOM load
document.addEventListener('DOMContentLoaded', function() {
    initHeroSlider();
    initBestsellerSlider();
    initRevealAnimation();
    initCart();
    initModalHandlers();
    checkLoginStatus();
    updateCartBadge();
});