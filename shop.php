<?php
require_once 'db_connect.php';

$sql = "SELECT * FROM products WHERE is_active = 1";
$result = $conn->query($sql);
$products = [];

if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Chibi Bites - Shop</title>   
    <link rel="icon" href="images/logo.png" type="image/x-icon" />
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Coiny&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <!-- Cart Overlay -->
    <div class="cart-overlay" id="cartOverlay"></div>

    <!-- Cart Sidebar -->
    <div class="cart-sidebar" id="cartSidebar">
        <div class="cart-header">
            <h2>My Cart</h2>
            <button class="cart-close" id="closeCart">&times;</button>
        </div>
        <div class="cart-items" id="cartItems">
            <div class="cart-empty">Your cart is empty</div>
        </div>
        <div class="cart-footer" id="cartFooter" style="display: none;">
            <div class="cart-total">
                <span>Total</span>
                <span class="cart-total-amount" id="cartTotal">₱0.00</span>
            </div>
            <button class="checkout-btn" onclick="checkUserAuthentication()">CHECKOUT</button>
        </div>
    </div>

    <nav class="navbar">
        <div class="brand">
            <a href="index.html"><img src="images/logo.png" alt="Logo" class="logo-img"></a>
            <a href="index.html"><img src="images/title.png" alt="Website Title" class="title-img"></a>
        </div>

        <div class="nav-right">
            <ul class="nav-links">
                <li><a href="index.html">Home</a></li>
                <li><a href="shop.php">Shop</a></li>
                <li><a href="faqs.html">FAQs</a></li>
                <li><a href="contact.html">Contact</a></li>
            </ul>
            
            <div class="nav-icons">
                <a href="cart.html">
                    <img src="images/cart.png" alt="Cart" class="icon">
                </a>
                <a href="account.html">
                    <img src="images/acc.png" alt="Account" class="icon">
                </a>
            </div>
        </div>
    </nav>

    <section>
        <div class="sectionshop">
            <h1>Products</h1>
            <div class="mixmatch"><p>Mix and Match 3 flavors for ₱100 pesos!</p></div>

            <!-- Search section -->
            <div class="product-filter-section reveal">
                <div class="search-container">
                    <input type="text" id="productSearch" placeholder="Search products..." class="search-input">
                    <button class="search-btn" onclick="filterProducts()">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="white">
                            <path d="M15.5 14h-.79l-.28-.27C15.41 12.59 16 11.11 16 9.5 16 5.91 13.09 3 9.5 3S3 5.91 3 9.5 5.91 16 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"/>
                        </svg>
                    </button>
                </div>
            </div>
            
            <div class="shop-container reveal">
                <?php if (empty($products)): ?>
                    <p>No products found.</p>
                <?php else: ?>
                    <?php 
                    // 3. Loop through products and group them by 4s to maintain your CSS layout
                    $chunks = array_chunk($products, 4); 
                    foreach ($chunks as $row_products): 
                    ?>
                        <div class="product-container">
                            <?php foreach ($row_products as $product): ?>
                                <div class="product-column">
                                    <div class="product-card">
                                        <img src="<?php echo htmlspecialchars($product['image_path']); ?>" 
                                             alt="<?php echo htmlspecialchars($product['name']); ?>">
                                        <h1><?php echo htmlspecialchars($product['name']); ?></h1>
                                        <p class="edu-school">₱<?php echo number_format($product['price'], 2); ?></p>
                                        

                                            
                                        <?php if ($product['stock'] > 0): ?>
                                            <button onclick="openModal(
                                                '<?php echo addslashes($product['name']); ?>', 
                                                '₱<?php echo number_format($product['price'], 2); ?>', 
                                                '<?php echo $product['image_path']; ?>', 
                                                '<?php echo addslashes($product['description']); ?>',
                                                <?php echo $product['stock']; ?>
                                            )">
                                                <p>ORDER</p>
                                            </button>
                                        <?php else: ?>
                                            <button disabled style="background: #ccc; cursor: not-allowed; opacity: 0.6;">
                                                <p>SOLD OUT</p>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <div id="productModal" class="modal">
        <div class="modal-content">
            <button class="close-btn" onclick="closeModal()">&times;</button>
            
            <h2 id="modalProductName">Product Name</h2>
            <img id="modalProductImg" src="" alt="Product" class="modal-product-img">
            <div class="modal-price" id="modalProductPrice">₱0.00</div>
            <p class="modal-description" id="modalProductDescription">Product description goes here.</p>
            
            <div class="quantity-selector">
                <button class="quantity-btn" onclick="decreaseQuantity()">−</button>
                <span class="quantity-display" id="quantityDisplay">1</span>
                <button class="quantity-btn" onclick="increaseQuantity()">+</button>
            </div>
            
            <button class="add-to-cart-btn" onclick="addToCart()">
                ADD TO CART
            </button>
        </div>
</div>
    </section>

        <!-- Authentication Modal -->
        <div id="authModal" class="auth-modal">
            <div class="auth-modal-content">
                <button class="auth-close-btn" onclick="closeAuthModal()">&times;</button>
                <h2>Login Required</h2>
                <p>Please login or create an account to proceed with checkout.</p>
                <div class="auth-modal-buttons">
                    <a href="login.html" class="auth-btn auth-btn-primary">LOGIN</a>
                    <a href="signup.html" class="auth-btn auth-btn-secondary">SIGN UP</a>
                </div>
            </div>
        </div>

<footer class="site-footer">
    <div class="footer-container">
        <div class="footer-column">
            <h3>Contact us</h3>
            <p>09164087819</p>
            <p><b>Or come visit us at:</b><br>15 Marikina-Infanta Hwy, Antipolo, 1870 Rizal</p>
        </div>
        
        <div class="footer-column">
            <h3>Quick Links</h3>
            <ul class="footer-links">
                <li><a href="index.html">Home</a></li>
                <li><a href="shop.php">Order</a></li>
                <li><a href="faqs.html">FAQS</a></li>
                <li><a href="contact.html">Contact</a></li>
            </ul>
        </div>
        
        <div class="footer-column footer-logo-column">
		
		<p>&copy; 2026 Chibi Bites.</p>
		<div class="footer-logos">
            <img src="images/logo.png" alt="Chibi Bites Logo" class="footer-logo-circle">
			<img src="images/title.png" alt="Chibi Bites Logo" class="footer-logo-text">
		</div>
            <div class="social-icons">
                <a href="https://www.facebook.com/people/Chibi-Bites/61583803474911/?_rdc=1&_rdr#" target="_blank" aria-label="Facebook">
                    <svg width="30" height="30" viewBox="0 0 24 24" fill="white">
                        <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                    </svg>
                </a>
                <!-- TikTok Icon -->
                <a href="https://www.tiktok.com/@chibibites.ph?_r=1&_t=ZS-93OUXSIboQy" target="_blank" aria-label="TikTok">
                    <svg width="30" height="30" viewBox="0 0 24 24" fill="white">
                        <path d="M19.59 6.69a4.83 4.83 0 0 1-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 0 1-5.2 1.74 2.89 2.89 0 0 1 2.31-4.64c.3.002.6.053.89.15V9.4a6.33 6.33 0 0 0-1-.05A6.34 6.34 0 0 0 5 20.1a6.34 6.34 0 0 0 10.86-4.43v-7a8.16 8.16 0 0 0 4.77 1.52v-3.4a4.85 4.85 0 0 1-1-.1z"/>
                    </svg>
                </a>
                <!-- Messenger Icon (Optional) -->
                <a href="https://m.me/61583803474911" target="_blank" aria-label="Messenger">
                    <svg width="30" height="30" viewBox="0 0 24 24" fill="white">
                        <path d="M12 2C6.477 2 2 6.145 2 11.258c0 2.91 1.453 5.503 3.735 7.189.196.145.318.375.321.621l.024 2.185c.005.474.52.766.924.516l2.428-1.503c.188-.117.416-.145.626-.08 1.25.385 2.583.593 3.942.593 5.523 0 10-4.145 10-9.258S17.523 2 12 2zm1.18 12.197l-2.553-2.723-4.984 2.723 5.483-5.823 2.62 2.723 4.917-2.723-5.483 5.823z"/>
                    </svg>
                </a>
            </div>
        </div>
    </div>
</footer>
    
    <script src="script.js?v=99"></script>
</body>
</html>