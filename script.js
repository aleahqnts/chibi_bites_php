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
	if (event.target === modal) {
		closeModal();
	}
}

// Close modal with Escape key
document.addEventListener('keydown', function(event) {
	if (event.key === 'Escape') {
		closeModal();
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
            // Reset quantity
            currentQuantity = 1;
            // Show cart
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

function displayCart(cart) {
    const cartItems = document.getElementById('cartItems');
    const cartFooter = document.getElementById('cartFooter');
    const cartTotal = document.getElementById('cartTotal');
    
    if (!cart || cart.length === 0) {
        cartItems.innerHTML = '<div class="cart-empty">Your cart is empty</div>';
        cartFooter.style.display = 'none';
        return;
    }
    
    let total = 0;
    let html = '';
    
    cart.forEach((item, index) => {
        const priceNum = parseFloat(item.price.replace('₱', '').replace(',', ''));
        const subtotal = priceNum * item.quantity;
        total += subtotal;
        
        html += `
            <div class="cart-item">
                <div class="cart-item-info">
                    <div class="cart-item-name">${item.name}</div>
                    <div class="cart-item-price">${item.price}</div>
                </div>
                <div class="cart-item-controls">
                    <button class="cart-qty-btn" onclick="updateCartQuantity(${index}, ${item.quantity - 1})">−</button>
                    <span class="cart-qty">${item.quantity}</span>
                    <button class="cart-qty-btn" onclick="updateCartQuantity(${index}, ${item.quantity + 1})">+</button>
                    <button class="cart-remove" onclick="removeFromCart(${index})">🗑</button>
                </div>
            </div>
        `;
    });
    
    cartItems.innerHTML = html;
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
        }
    })
    .catch(error => {
        console.error('Error removing from cart:', error);
    });
}