<?php
session_start();

// Simple admin authentication - you should enhance this
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin_login.php');
    exit;
}

require_once 'db_connect.php';

// Get all orders with user information
$orders_query = "
    SELECT o.*, u.fullname, u.email, u.phone 
    FROM orders o 
    JOIN users u ON o.user_id = u.id 
    ORDER BY o.created_at DESC
";
$orders_result = $conn->query($orders_query);

// Get statistics
$stats_query = "
    SELECT 
        COUNT(*) as total_orders,
        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_orders,
        SUM(CASE WHEN status = 'delivered' THEN 1 ELSE 0 END) as delivered_orders,
        SUM(total_amount) as total_revenue,
        COUNT(DISTINCT user_id) as total_customers
    FROM orders
";
$stats_result = $conn->query($stats_query);
$stats = $stats_result->fetch_assoc();

// Get all users
$users_query = "SELECT * FROM users ORDER BY created_at DESC";
$users_result = $conn->query($users_query);

// Get all products
$products_query = "SELECT * FROM products ORDER BY id ASC";
$products_result = $conn->query($products_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Admin Panel - Chibi Bites</title>
    <link rel="icon" href="images/logo.png" type="image/x-icon" />
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Coiny&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-brown: #7C474A;
            --primary-pink: #FEBBCC;
            --light-pink: #FFE7F0;
            --accent-green: #9aa559;
            --white: #ffffff;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Montserrat', sans-serif;
            background: #f5f5f5;
        }

        .admin-header {
            background: var(--primary-brown);
            color: white;
            padding: 20px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .admin-header h1 {
            font-family: 'Coiny', cursive;
            font-size: 32px;
        }

        .logout-btn {
            background: var(--primary-pink);
            color: var(--primary-brown);
            border: none;
            padding: 10px 25px;
            border-radius: 20px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s;
        }

        .logout-btn:hover {
            background: white;
            transform: translateY(-2px);
        }

        .admin-container {
            max-width: 1400px;
            margin: 30px auto;
            padding: 0 20px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
            border-left: 4px solid var(--primary-pink);
        }

        .stat-number {
            font-family: 'Coiny', cursive;
            font-size: 36px;
            color: var(--primary-brown);
            margin-bottom: 5px;
        }

        .stat-label {
            color: #666;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            border-bottom: 2px solid #e0e0e0;
        }

        .tab-btn {
            background: none;
            border: none;
            padding: 15px 30px;
            font-family: 'Montserrat', sans-serif;
            font-weight: 600;
            color: #666;
            cursor: pointer;
            border-bottom: 3px solid transparent;
            transition: all 0.3s;
        }

        .tab-btn.active {
            color: var(--primary-brown);
            border-bottom-color: var(--primary-pink);
        }

        .tab-content {
            display: none;
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .tab-content.active {
            display: block;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            background: var(--light-pink);
            color: var(--primary-brown);
            padding: 15px;
            text-align: left;
            font-weight: 600;
            position: sticky;
            top: 0;
        }

        td {
            padding: 15px;
            border-bottom: 1px solid #e0e0e0;
        }

        tr:hover {
            background: #f9f9f9;
        }

        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            display: inline-block;
        }

        .status-pending { background: #fff3cd; color: #856404; }
        .status-confirmed { background: #d1ecf1; color: #0c5460; }
        .status-processing { background: #cce5ff; color: #004085; }
        .status-ready { background: #d4edda; color: #155724; }
        .status-out_for_delivery { background: #bee5eb; color: #0c5460; }
        .status-delivered { background: #d4edda; color: #155724; }
        .status-cancelled { background: #f8d7da; color: #721c24; }
        .status-refunded { background: #e2e3e5; color: #383d41; }

        .action-btn {
            background: var(--primary-pink);
            color: var(--primary-brown);
            border: none;
            padding: 8px 15px;
            border-radius: 15px;
            cursor: pointer;
            font-size: 12px;
            font-weight: 600;
            margin-right: 5px;
            transition: all 0.3s;
        }

        .action-btn:hover {
            background: var(--primary-brown);
            color: white;
        }

        .action-btn.view {
            background: #e7f3ff;
            color: #0066cc;
        }

        .action-btn.delete {
            background: #ffe7e7;
            color: #cc0000;
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            align-items: center;
            justify-content: center;
        }

        .modal.active {
            display: flex;
        }

        .modal-content {
            background: white;
            padding: 30px;
            border-radius: 20px;
            max-width: 600px;
            width: 90%;
            max-height: 80vh;
            overflow-y: auto;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .modal-header h2 {
            font-family: 'Coiny', cursive;
            color: var(--primary-brown);
        }

        .close-modal {
            background: none;
            border: none;
            font-size: 30px;
            cursor: pointer;
            color: #999;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--primary-brown);
        }

        .form-group select,
        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-family: 'Montserrat', sans-serif;
        }

        .form-group select:focus,
        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--primary-pink);
        }

        .submit-btn {
            width: 100%;
            padding: 15px;
            background: var(--primary-pink);
            color: var(--primary-brown);
            border: none;
            border-radius: 25px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }

        .submit-btn:hover {
            background: var(--primary-brown);
            color: white;
        }

        .order-items {
            background: var(--light-pink);
            padding: 15px;
            border-radius: 10px;
            margin-top: 10px;
        }

        .order-items h4 {
            color: var(--primary-brown);
            margin-bottom: 10px;
        }

        .order-item {
            background: white;
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 8px;
            display: flex;
            justify-content: space-between;
        }

        .search-box {
            margin-bottom: 20px;
        }

        .search-box input {
            width: 100%;
            padding: 12px 20px;
            border: 2px solid #e0e0e0;
            border-radius: 25px;
            font-family: 'Montserrat', sans-serif;
        }

        .search-box input:focus {
            outline: none;
            border-color: var(--primary-pink);
        }

        .filter-group {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }

        .filter-btn {
            padding: 8px 20px;
            background: white;
            border: 2px solid #e0e0e0;
            border-radius: 20px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s;
        }

        .filter-btn.active {
            background: var(--primary-pink);
            border-color: var(--primary-pink);
            color: var(--primary-brown);
        }

        @media (max-width: 768px) {
            .admin-header {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }

            .tabs {
                flex-wrap: wrap;
            }

            .tab-btn {
                padding: 10px 15px;
                font-size: 14px;
            }

            table {
                font-size: 12px;
            }

            th, td {
                padding: 10px 5px;
            }
        }
    </style>
</head>
<body>
    <div class="admin-header">
        <div style="display: flex; align-items: center; gap: 15px;">
            <img src="images/logo.png" alt="Logo" style="height: 50px; width: auto;">
            <h1>Chibi Bites Admin</h1>
        </div>
        <button class="logout-btn" onclick="logout()">Logout</button>
    </div>

    <div class="admin-container">
        <!-- Statistics -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['total_orders']; ?></div>
                <div class="stat-label">Total Orders</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['pending_orders']; ?></div>
                <div class="stat-label">Pending Orders</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['delivered_orders']; ?></div>
                <div class="stat-label">Delivered Orders</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">₱<?php echo number_format($stats['total_revenue'], 2); ?></div>
                <div class="stat-label">Total Revenue</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['total_customers']; ?></div>
                <div class="stat-label">Total Customers</div>
            </div>
        </div>

        <!-- Tabs -->
        <div class="tabs">
            <button class="tab-btn active" onclick="switchTab('orders')">Orders</button>
            <button class="tab-btn" onclick="switchTab('users')">Users</button>
            <button class="tab-btn" onclick="switchTab('products')">Products</button>
        </div>

        <!-- Orders Tab -->
        <div id="orders-tab" class="tab-content active">
            <div class="search-box">
                <input type="text" id="orderSearch" placeholder="Search by Order ID, Customer Name, or Email..." onkeyup="filterOrders()">
            </div>

            <div class="filter-group">
                <button class="filter-btn active" onclick="filterByStatus('all')">All</button>
                <button class="filter-btn" onclick="filterByStatus('pending')">Pending</button>
                <button class="filter-btn" onclick="filterByStatus('confirmed')">Confirmed</button>
                <button class="filter-btn" onclick="filterByStatus('processing')">Processing</button>
                <button class="filter-btn" onclick="filterByStatus('ready')">Ready</button>
                <button class="filter-btn" onclick="filterByStatus('out_for_delivery')">Out for Delivery</button>
                <button class="filter-btn" onclick="filterByStatus('delivered')">Delivered</button>
                <button class="filter-btn" onclick="filterByStatus('cancelled')">Cancelled</button>
            </div>

            <table id="ordersTable">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Phone</th>
                        <th>Total</th>
                        <th>Payment</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($order = $orders_result->fetch_assoc()): ?>
                    <tr class="order-row" data-status="<?php echo $order['status']; ?>">
                        <td>#<?php echo $order['id']; ?></td>
                        <td><?php echo htmlspecialchars($order['fullname']); ?></td>
                        <td><?php echo htmlspecialchars($order['phone']); ?></td>
                        <td>₱<?php echo number_format($order['total_amount'], 2); ?></td>
                        <td><?php echo strtoupper($order['payment_method']); ?></td>
                        <td>
                            <span class="status-badge status-<?php echo $order['status']; ?>">
                                <?php echo str_replace('_', ' ', $order['status']); ?>
                            </span>
                        </td>
                        <td><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                        <td>
                            <button class="action-btn view" onclick="viewOrder(<?php echo $order['id']; ?>)">View</button>
                            <button class="action-btn" onclick="updateOrderStatus(<?php echo $order['id']; ?>)">Update Status</button>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <!-- Users Tab -->
        <div id="users-tab" class="tab-content">
            <div class="search-box">
                <input type="text" id="userSearch" placeholder="Search by Name or Email..." onkeyup="filterUsers()">
            </div>

            <table id="usersTable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Address</th>
                        <th>Joined</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($user = $users_result->fetch_assoc()): ?>
                    <tr class="user-row">
                        <td><?php echo $user['id']; ?></td>
                        <td><?php echo htmlspecialchars($user['fullname']); ?></td>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        <td><?php echo htmlspecialchars($user['phone']); ?></td>
                        <td><?php echo htmlspecialchars($user['street'] . ', ' . $user['city']); ?></td>
                        <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                        <td>
                            <button class="action-btn view" onclick="viewUser(<?php echo $user['id']; ?>)">View Details</button>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <!-- Products Tab -->
        <div id="products-tab" class="tab-content">
            <button class="action-btn" onclick="addProduct()" style="margin-bottom: 20px;">+ Add New Product</button>

            <table id="productsTable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Price</th>
                        <th>Description</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($product = $products_result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $product['id']; ?></td>
                        <td><?php echo htmlspecialchars($product['name']); ?></td>
                        <td>₱<?php echo number_format($product['price'], 2); ?></td>
                        <td><?php echo htmlspecialchars(substr($product['description'], 0, 50)) . '...'; ?></td>
                        <td>
                            <span class="status-badge <?php echo $product['is_active'] ? 'status-delivered' : 'status-cancelled'; ?>">
                                <?php echo $product['is_active'] ? 'Active' : 'Inactive'; ?>
                            </span>
                        </td>
                        <td>
                            <button class="action-btn" onclick="editProduct(<?php echo $product['id']; ?>)">Edit</button>
                            <button class="action-btn delete" onclick="toggleProductStatus(<?php echo $product['id']; ?>, <?php echo $product['is_active']; ?>)">
                                <?php echo $product['is_active'] ? 'Deactivate' : 'Activate'; ?>
                            </button>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Update Order Status Modal -->
    <div id="statusModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Update Order Status</h2>
                <button class="close-modal" onclick="closeModal('statusModal')">&times;</button>
            </div>
            <form id="statusForm">
                <input type="hidden" id="statusOrderId">
                
                <div class="form-group">
                    <label>Order ID</label>
                    <input type="text" id="displayOrderId" readonly>
                </div>

                <div class="form-group">
                    <label>New Status</label>
                    <select id="newStatus" required>
                        <option value="">Select Status...</option>
                        <option value="pending">Pending</option>
                        <option value="confirmed">Confirmed</option>
                        <option value="processing">Processing</option>
                        <option value="ready">Ready</option>
                        <option value="out_for_delivery">Out for Delivery</option>
                        <option value="delivered">Delivered</option>
                        <option value="cancelled">Cancelled</option>
                        <option value="refunded">Refunded</option>
                    </select>
                </div>

                <button type="submit" class="submit-btn">Update Status</button>
            </form>
        </div>
    </div>

    <!-- View Order Modal -->
    <div id="viewOrderModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Order Details</h2>
                <button class="close-modal" onclick="closeModal('viewOrderModal')">&times;</button>
            </div>
            <div id="orderDetails"></div>
        </div>
    </div>

    <!-- Edit Product Modal -->
    <div id="editProductModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Edit Product</h2>
                <button class="close-modal" onclick="closeModal('editProductModal')">&times;</button>
            </div>
            <form id="editProductForm">
                <input type="hidden" id="editProductId">
                
                <div class="form-group">
                    <label>Product Name</label>
                    <input type="text" id="editProductName" required>
                </div>

                <div class="form-group">
                    <label>Price (₱)</label>
                    <input type="number" id="editProductPrice" step="0.01" required>
                </div>

                <div class="form-group">
                    <label>Description</label>
                    <textarea id="editProductDescription" rows="4" required></textarea>
                </div>

                <div class="form-group">
                    <label>Image Path</label>
                    <input type="text" id="editProductImage" required>
                </div>

                <button type="submit" class="submit-btn">Update Product</button>
            </form>
        </div>
    </div>

    <script>
        // Tab Switching
        function switchTab(tabName) {
            document.querySelectorAll('.tab-content').forEach(tab => tab.classList.remove('active'));
            document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
            
            document.getElementById(tabName + '-tab').classList.add('active');
            event.target.classList.add('active');
        }

        // Filter Orders by Status
        let currentFilter = 'all';
        function filterByStatus(status) {
            currentFilter = status;
            document.querySelectorAll('.filter-btn').forEach(btn => btn.classList.remove('active'));
            event.target.classList.add('active');
            
            const rows = document.querySelectorAll('.order-row');
            rows.forEach(row => {
                if (status === 'all' || row.dataset.status === status) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }

        // Search Orders
        function filterOrders() {
            const searchTerm = document.getElementById('orderSearch').value.toLowerCase();
            const rows = document.querySelectorAll('.order-row');
            
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                const matchesSearch = text.includes(searchTerm);
                const matchesFilter = currentFilter === 'all' || row.dataset.status === currentFilter;
                
                if (matchesSearch && matchesFilter) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }

        // Search Users
        function filterUsers() {
            const searchTerm = document.getElementById('userSearch').value.toLowerCase();
            const rows = document.querySelectorAll('.user-row');
            
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                if (text.includes(searchTerm)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }

        // Update Order Status
        function updateOrderStatus(orderId) {
            document.getElementById('statusOrderId').value = orderId;
            document.getElementById('displayOrderId').value = '#' + orderId;
            document.getElementById('statusModal').classList.add('active');
        }

        document.getElementById('statusForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const orderId = document.getElementById('statusOrderId').value;
            const newStatus = document.getElementById('newStatus').value;
            
            const formData = new FormData();
            formData.append('action', 'update_status');
            formData.append('order_id', orderId);
            formData.append('status', newStatus);
            
            fetch('admin_actions.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Order status updated successfully!');
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error updating status');
            });
        });

        // View Order Details
        function viewOrder(orderId) {
            fetch('admin_actions.php?action=get_order&order_id=' + orderId)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const order = data.order;
                    const items = data.items;
                    
                    let html = `
                        <p><strong>Order ID:</strong> #${order.id}</p>
                        <p><strong>Customer:</strong> ${order.fullname}</p>
                        <p><strong>Email:</strong> ${order.email}</p>
                        <p><strong>Phone:</strong> ${order.phone}</p>
                        <p><strong>Address:</strong> ${order.delivery_address}</p>
                        <p><strong>Payment Method:</strong> ${order.payment_method.toUpperCase()}</p>
                        <p><strong>Status:</strong> <span class="status-badge status-${order.status}">${order.status}</span></p>
                        <p><strong>Total:</strong> ₱${parseFloat(order.total_amount).toFixed(2)}</p>
                        <p><strong>Date:</strong> ${new Date(order.created_at).toLocaleString()}</p>
                        
                        <div class="order-items">
                            <h4>Order Items:</h4>
                    `;
                    
                    items.forEach(item => {
                        html += `
                            <div class="order-item">
                                <span>${item.product_name} x${item.quantity}</span>
                                <span>₱${parseFloat(item.price).toFixed(2)}</span>
                            </div>
                        `;
                    });
                    
                    html += '</div>';
                    
                    document.getElementById('orderDetails').innerHTML = html;
                    document.getElementById('viewOrderModal').classList.add('active');
                }
            });
        }

        // View User Details
        function viewUser(userId) {
            alert('View user details for ID: ' + userId);
            // Implement user details modal similar to order details
        }

        // Close Modal
        function closeModal(modalId) {
            document.getElementById(modalId).classList.remove('active');
        }

        // Close modal on outside click
        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                event.target.classList.remove('active');
            }
        }

        // Product Management (placeholder functions)
        function addProduct() {
            alert('Add product functionality - to be implemented');
        }

        function editProduct(productId) {
            // Fetch product details
            fetch('admin_actions.php?action=get_product&product_id=' + productId)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const product = data.product;
                    
                    document.getElementById('editProductId').value = product.id;
                    document.getElementById('editProductName').value = product.name;
                    document.getElementById('editProductPrice').value = product.price;
                    document.getElementById('editProductDescription').value = product.description;
                    document.getElementById('editProductImage').value = product.image_path;
                    
                    document.getElementById('editProductModal').classList.add('active');
                } else {
                    alert('Error loading product: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error loading product');
            });
        }

        // Handle Edit Product Form Submission
        document.getElementById('editProductForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData();
            formData.append('action', 'update_product');
            formData.append('id', document.getElementById('editProductId').value);
            formData.append('name', document.getElementById('editProductName').value);
            formData.append('price', document.getElementById('editProductPrice').value);
            formData.append('description', document.getElementById('editProductDescription').value);
            formData.append('image_path', document.getElementById('editProductImage').value);
            
            fetch('admin_actions.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Product updated successfully!');
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error updating product');
            });
        });

        function toggleProductStatus(productId, currentStatus) {
            const action = currentStatus ? 'deactivate' : 'activate';
            if (confirm(`Are you sure you want to ${action} this product?`)) {
                const formData = new FormData();
                formData.append('action', 'toggle_product');
                formData.append('id', productId);
                formData.append('is_active', currentStatus);
                
                fetch('admin_actions.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Product status updated successfully!');
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error updating product status');
                });
            }
        }

        // Logout
        function logout() {
            if (confirm('Are you sure you want to logout?')) {
                window.location.href = 'admin_logout.php';
            }
        }
    </script>
</body>
</html>