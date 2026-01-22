<?php
require_once 'db_connect.php';

$sql = "SELECT * FROM products";
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
    <title>Chibi Bites | Shop</title>
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
                                        
                                        <button onclick="openModal(
                                            '<?php echo addslashes($product['name']); ?>', 
                                            '₱<?php echo number_format($product['price'], 2); ?>', 
                                            '<?php echo $product['image_path']; ?>', 
                                            '<?php echo addslashes($product['description']); ?>'
                                        )">
                                            <p>ORDER</p>
                                        </button>
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
                    <a href="#" aria-label="Facebook">
                        <svg width="30" height="30" viewBox="0 0 24 24" fill="white">
                            <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                        </svg>
                    </a>
                    <a href="#" aria-label="Instagram">
                        <svg width="30" height="30" viewBox="0 0 24 24" fill="white">
                            <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    </footer>
    <!-- <script src="checkout.js"></script> -->
    <script src="script.js"></script>
</body>
</html>