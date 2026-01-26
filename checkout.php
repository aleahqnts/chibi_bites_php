<!DOCTYPE html>
<html lang="en">
<head>
    <title>Chibi Bites - Checkout</title>
    <link rel="icon" href="images/logo.png" type="image/x-icon" />
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Coiny&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <style>
        .checkout-page {
            min-height: 100vh;
            padding: 110px 20px 50px;
            background: var(--white);
            display: flex;
            justify-content: center;
            align-items: flex-start;
        }

        .checkout-container {
            max-width: 1200px;
            width: 100%;
            display: grid;
            grid-template-columns: 1fr 400px;
            gap: 20px;
            opacity: 0;
            animation: fadeInUp 0.5s ease forwards;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .checkout-section {
            background: white;
            padding: 20px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            border: 1px solid #e0e0e0;
            overflow: auto;
            max-height: 70vh;
        }

        .checkout-section h2 {
            font-family: 'Coiny', cursive;
            color: var(--primary-brown);
            font-size: 28px;
            margin-bottom: 10px;
        }

        .section-title {
            font-family: 'Coiny', cursive;
            color: var(--primary-brown);
            font-size: 36px;
            text-align: center;
            margin-bottom: 0px;
            grid-column: 1 / -1;
        }

        .info-display {
            background: var(--light-pink);
            padding: 20px;
            border-radius: 15px;
            margin-bottom: 20px;
        }

        .info-item {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid rgba(124, 71, 74, 0.1);
        }

        .info-item:last-child {
            border-bottom: none;
        }

        .info-label {
            font-family: 'Montserrat', sans-serif;
            font-weight: 600;
            color: var(--primary-brown);
            font-size: 14px;
        }

        .info-value {
            font-family: 'Montserrat', sans-serif;
            color: var(--primary-brown);
            font-size: 14px;
            text-align: right;
        }

        .order-item {
            display: flex;
            gap: 15px;
            padding: 15px;
            background: white;
            border-radius: 12px;
            margin-bottom: 10px;
            border: 1px solid #e0e0e0;
        }

        .order-item-img {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 8px;
            flex-shrink: 0;
        }

        .order-item-info {
            flex: 1;
        }

        .order-item-name {
            font-family: 'Montserrat', sans-serif;
            font-weight: 600;
            color: var(--primary-brown);
            font-size: 16px;
            margin-bottom: 5px;
        }

        .order-item-price {
            font-family: 'Montserrat', sans-serif;
            color: gray;
            font-size: 14px;
        }

        .order-item-qty {
            font-family: 'Montserrat', sans-serif;
            font-weight: 600;
            color: var(--primary-brown);
            font-size: 14px;
            align-self: center;
        }

        .payment-methods {
            display: grid;
            gap: 15px;
            margin-bottom: 20px;
        }

        .payment-option {
            display: flex;
            align-items: center;
            padding: 15px;
            border: 2px solid #e0e0e0;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.3s;
        }

        .payment-option:hover {
            border-color: var(--primary-pink);
            background: var(--light-pink);
        }

        .payment-option.selected {
            border-color: var(--primary-pink);
            background: var(--light-pink);
        }

        .payment-option input[type="radio"] {
            margin-right: 15px;
            width: 20px;
            height: 20px;
            cursor: pointer;
        }

        .payment-option label {
            font-family: 'Montserrat', sans-serif;
            font-weight: 600;
            color: var(--primary-brown);
            font-size: 16px;
            cursor: pointer;
            flex: 1;
        }

        .order-summary {
            position: sticky;
            top: 100px;
        }

        .summary-item {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            font-family: 'Montserrat', sans-serif;
            color: var(--primary-brown);
        }

        .summary-item.total {
            border-top: 2px solid var(--primary-brown);
            margin-top: 10px;
            padding-top: 15px;
            font-weight: 700;
            font-size: 20px;
        }

        .place-order-btn {
            width: 100%;
            padding: 18px;
            background: var(--primary-pink);
            border: 2px solid var(--primary-pink);
            border-radius: 25px;
            color: var(--primary-brown);
            font-family: 'Montserrat', sans-serif;
            font-weight: 600;
            font-size: 18px;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 20px;
        }

        .place-order-btn:hover {
            background: white;
            border-color: var(--primary-brown);
            color: var(--primary-brown);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }

        .place-order-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            transform: none;
        }

        .empty-cart {
            grid-column: 1 / -1;
            text-align: center;
            padding: 60px 20px;
        }

        .empty-cart h2 {
            font-family: 'Coiny', cursive;
            color: var(--primary-brown);
            font-size: 32px;
            margin-bottom: 15px;
        }

        .empty-cart p {
            font-family: 'Montserrat', sans-serif;
            color: var(--primary-brown);
            margin-bottom: 25px;
        }

        .empty-cart a {
            display: inline-block;
            padding: 15px 40px;
            background: var(--primary-pink);
            border: 2px solid var(--primary-pink);
            border-radius: 25px;
            color: var(--primary-brown);
            font-family: 'Montserrat', sans-serif;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s;
        }

        .empty-cart a:hover {
            background: white;
            border-color: var(--primary-brown);
        }

        @media (max-width: 968px) {
            .checkout-container {
                grid-template-columns: 1fr;
            }

            .section-title {
                font-size: 28px;
            }

            .order-summary {
                position: static;
            }
        }

        /* Order Success Modal */
        .order-success-modal {
            display: none;
            position: fixed;
            z-index: 10002;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.8);
            animation: fadeIn 0.3s ease;
        }

        .order-success-modal.active {
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .order-success-content {
            background-color: white;
            padding: 50px 40px;
            border-radius: 20px;
            max-width: 500px;
            width: 90%;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            animation: slideUp 0.3s ease;
            border-left: 5px solid pink;
            border-right: 5px solid pink;
        }

        .success-icon-large {
            width: 80px;
            height: 80px;
            margin: 0 auto 25px;
            background: #9aa559;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            animation: scaleIn 0.5s ease;
        }

        .success-icon-large svg {
            width: 40px;
            height: 40px;
            fill: white;
        }

        .order-success-content h2 {
            font-family: 'Coiny', cursive;
            color: #6b4b50;
            font-size: 36px;
            margin-bottom: 15px;
        }

        .order-id-display {
            font-family: 'Montserrat', sans-serif;
            font-size: 20px;
            font-weight: 700;
            color: #6b4b50;
            margin: 20px 0;
            padding: 15px;
            background: var(--light-pink);
            border-radius: 12px;
        }

        .order-success-content p {
            font-family: 'Montserrat', sans-serif;
            color: #6b4b50;
            font-size: 16px;
            margin-bottom: 30px;
            line-height: 1.6;
        }

        .order-success-btn {
            width: 100%;
            max-width: 300px;
            padding: 15px;
            background-color: #9aa559;
            color: white;
            border: 2px solid #9aa559;
            border-radius: 25px;
            font-family: 'Montserrat', sans-serif;
            font-weight: 600;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
            margin: 5px;
        }

        .order-success-btn:hover {
            background-color: pink;
            border-color: pink;
            color: black;
        }

        .order-success-btn.secondary {
            background-color: white;
            color: #6b4b50;
            border-color: #6b4b50;
        }

        .order-success-btn.secondary:hover {
            background-color: #6b4b50;
            color: white;
        }

        /* Payment Modal Styles */
        .payment-modal-content {
            background-color: white;
            padding: 50px 40px 40px;
            border-radius: 20px;
            max-width: 500px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            animation: slideUp 0.3s ease;
            border-left: 5px solid pink;
            
            position: relative;
        }

        .payment-modal-content .close-modal {
            position: absolute;
            top: 15px;
            right: 15px;
            background: #f5f5f5;
            border: none;
            font-size: 28px;
            cursor: pointer;
            color: #666;
            transition: all 0.3s;
            width: 35px;
            height: 35px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .payment-modal-content .close-modal:hover {
            background: var(--primary-pink);
            color: white;
        }

        /* Responsive close button position */
        @media (max-width: 600px) {
            .payment-modal-content .close-modal {
                right: calc((100vw - 90vw) / 2 + 15px);
            }
            
            .payment-modal-content {
                padding: 50px 30px 30px;
            }
        }

        .payment-modal-content h2 {
            font-family: 'Coiny', cursive;
            color: #6b4b50;
            font-size: 28px;
            margin-bottom: 20px;
            text-align: center;
        }

        .payment-summary {
            background: var(--light-pink);
            padding: 15px 40px 15px 40px;
            border-radius: 12px;
            margin-bottom: 25px;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-family: 'Montserrat', sans-serif;
            font-size: 18px;
            font-weight: 600;
            color: #6b4b50;
        }

        .total-amount {
            font-size: 20px;
            color: var(--primary-brown);
            font-family: 'Montserrat', sans-serif;
        }

        .qr-code-section {
            text-align: center;
            margin-bottom: 25px;
        }

        .payment-instruction {
            font-family: 'Montserrat', sans-serif;
            color: #6b4b50;
            margin-bottom: 15px;
            font-size: 14px;
        }

        .qr-code-img {
            width: 300px;
            height: 420px;
            border: 3px solid var(--primary-pink);
            border-radius: 12px;
            
            background: white;

        }

        .upload-section {
            margin-bottom: 25px;
        }

        .upload-label {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 15px;
            background: var(--light-pink);
            border: 2px dashed var(--primary-pink);
            border-radius: 12px;
            cursor: pointer;
            font-family: 'Montserrat', sans-serif;
            font-weight: 600;
            color: #6b4b50;
            transition: all 0.3s;
        }

        .upload-label:hover {
            background: var(--primary-pink);
            color: var(--primary-brown);
        }

        .file-name {
            margin-top: 10px;
            font-family: 'Montserrat', sans-serif;
            font-size: 14px;
            color: #6b4b50;
            text-align: center;
        }

        .file-preview {
            margin-top: 15px;
            text-align: center;
        }

        .file-preview img {
            max-width: 100%;
            max-height: 200px;
            border-radius: 12px;
        }

        .confirm-payment-btn {
            width: 100%;
            padding: 15px;
            background-color: #9aa559;
            color: white;
            border: 2px solid #9aa559;
            border-radius: 25px;
            font-family: 'Montserrat', sans-serif;
            font-weight: 600;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s;
        }

        .confirm-payment-btn:hover:not(:disabled) {
            background-color: #737c41;
            border-color: #737c41;
            color: white;
        }

        .confirm-payment-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        @keyframes scaleIn {
            0% {
                transform: scale(0);
                opacity: 0;
            }
            50% {
                transform: scale(1.1);
            }
            100% {
                transform: scale(1);
                opacity: 1;
            }
        }

        @keyframes slideUp {
            from { 
                transform: translateY(50px);
                opacity: 0;
            }
            to { 
                transform: translateY(0);
                opacity: 1;
            }
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
    </style>
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
                <a href="cart.html" onclick="return false;">
                    <img src="images/cart.png" alt="Cart" class="icon">
                </a>
                <a href="account.html">
                    <img src="images/acc.png" alt="Account" class="icon">
                </a>
            </div>
        </div>
    </nav>

 <!-- Payment Confirmation Modal -->
<div id="paymentModal" class="order-success-modal">
    <div class="payment-modal-content">
        <button class="close-modal" onclick="closePaymentModal()">&times;</button>
        
        <h2>Complete Your Payment</h2>
        
        <div class="payment-summary">
            <div class="summary-row">
                <span>Order Total:</span>
                <span class="total-amount" id="modalTotalAmount">₱0.00</span>
            </div>
        </div>

        <div class="qr-code-section" id="qrCodeSection">
           
            <img id="qrCodeImage" src="" alt="Payment QR Code" class="qr-code-img">
        </div>

        <div class="upload-section">
            <label for="paymentProof" class="upload-label">
                <svg viewBox="0 0 24 24" width="24" height="24">
                    <path fill="currentColor" d="M9 16h6v-6h4l-7-7-7 7h4zm-4 2h14v2H5z"/>
                </svg>
                Upload Proof of Payment
            </label>
            <input type="file" id="paymentProof" accept="image/png,image/jpeg,image/jpg" style="display: none;" onchange="handleFileSelect(event)">
            <div id="fileName" class="file-name"></div>
            <div id="filePreview" class="file-preview"></div>
        </div>

        <button class="confirm-payment-btn" id="confirmPaymentBtn" onclick="confirmPayment()" disabled>
            CONFIRM PAYMENT
        </button>
    </div>
</div>

<!-- Order Success Modal -->
<div id="orderSuccessModal" class="order-success-modal">
    <div class="order-success-content">
        <div class="success-icon-large">
            <svg viewBox="0 0 24 24">
                <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41L9 16.17z"/>
            </svg>
        </div>
        <h2>Order Placed!</h2>
        <div class="order-id-display">
            Order ID: <span id="displayOrderId">#0000</span>
        </div>
        <p>Thank you for your order! We'll start preparing your delicious mochi right away. You can track your order status in your account.</p>
        <a href="account.html" class="order-success-btn">VIEW MY ORDERS</a>
        <a href="shop.php" class="order-success-btn secondary">CONTINUE SHOPPING</a>
    </div>
</div>

    <div class="checkout-page">
        <div class="checkout-container" id="checkoutContainer">
            <h1 class="section-title">Checkout</h1>

            <!-- Left Column: Delivery & Payment -->
            <div>
                <div class="checkout-section">
                    <h2>Delivery Information</h2>
                    <div class="info-display" id="deliveryInfo">
                        <div class="info-item">
                            <span class="info-label">Name:</span>
                            <span class="info-value" id="checkoutName">Loading...</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Phone:</span>
                            <span class="info-value" id="checkoutPhone">Loading...</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Address:</span>
                            <span class="info-value" id="checkoutAddress">Loading...</span>
                        </div>
                    </div>
                </div>

                <div class="checkout-section">
                    <h2>Payment Method</h2>
                    <div class="payment-methods">
                        <div class="payment-option selected" onclick="selectPayment('gcash')">
                            <input type="radio" name="payment" value="gcash" checked>
                            <label>GCash</label>
                        </div>
                        <div class="payment-option" onclick="selectPayment('bank_transfer')">
                            <input type="radio" name="payment" value="bank_transfer">
                            <label>Bank Transfer</label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Order Summary -->
            <div class="checkout-section order-summary">
                <h2>Order Summary</h2>
                <div id="orderItems"></div>
                
                <div style="margin-top: 20px;">
                    <div class="summary-item">
                        <span>Subtotal:</span>
                        <span id="summarySubtotal">₱0.00</span>
                    </div>
                    <div class="summary-item">
                        <span>Delivery Fee:</span>
                        <span>₱50.00</span>
                    </div>
                    <div class="summary-item total">
                        <span>Total:</span>
                        <span id="summaryTotal">₱0.00</span>
                    </div>
                </div>

                <button class="place-order-btn" onclick="placeOrder()">PLACE ORDER</button>
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


    <script src="checkout.js"></script>
    <!-- <script src="script.js"></script> -->
</body>
</html>