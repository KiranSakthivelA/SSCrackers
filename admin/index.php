<?php
require_once 'auth.php';

// Fetch Products (Inventory)
$result = false;
try {
    $sql = "SELECT * FROM products ORDER BY id DESC";
    $result = $conn->query($sql);
} catch (mysqli_sql_exception $e) {
    // Ignore missing table error
}

// Fetch Orders
$all_orders = [];
$db_error = null;

try {
    $orders_sql = "SELECT * FROM orders ORDER BY created_at DESC";
    $orders_res = $conn->query($orders_sql);
    if ($orders_res) {
        while($o = $orders_res->fetch_assoc()) {
            $all_orders[] = $o;
        }
    }

    // Fetch Order Items
    $all_items = [];
    $items_sql = "SELECT * FROM order_items";
    $items_res = $conn->query($items_sql);
    if ($items_res) {
        while($i = $items_res->fetch_assoc()) {
            $all_items[$i['order_id']][] = $i;
        }
    }
} catch (mysqli_sql_exception $e) {
    // Table doesn't exist yet
    $db_error = "Database tables for orders are missing. Please run the setup script.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - SS Crackers Admin</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&family=Baloo+2:wght@600;700;800&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        :root {
            --primary: #FF4500;
            --primary-dark: #CC3700;
            --secondary: #D4AF37;
            --accent: #FFD600;
            --white: #ffffff;
            --off-white: #FFFBF0;
            --light-gray: #FFF5E0;
            --medium-gray: #FFE0B2;
            --text-dark: #7A2800;
            --text-medium: #5C2E00;
            --text-light: #A0622A;
            --font-main: 'Poppins', sans-serif;
            --font-display: 'Baloo 2', cursive;
            --radius-md: 14px;
            --radius-xl: 32px;
            --shadow-sm: 0 2px 8px rgba(0,104,56,0.10);
            --shadow-md: 0 4px 20px rgba(0,104,56,0.16);
        }
        body { font-family: var(--font-main); background: var(--off-white); margin: 0; padding: 20px; color: var(--text-dark); display: flex; }
        
        /* Sidebar layout */
        .sidebar { width: 250px; background: var(--white); border-right: 1px solid var(--medium-gray); height: 100vh; position: fixed; top: 0; left: 0; padding: 20px 0; display: flex; flex-direction: column; z-index: 100; box-shadow: var(--shadow-sm); }
        .sidebar-brand { padding: 0 20px 20px 20px; font-family: var(--font-display); font-size: 24px; font-weight: 800; color: var(--primary); border-bottom: 1px solid var(--medium-gray); margin-bottom: 20px; }
        .nav-item { padding: 15px 25px; display: flex; align-items: center; gap: 15px; color: var(--text-medium); text-decoration: none; font-weight: 600; cursor: pointer; transition: 0.3s; }
        .nav-item:hover { background: var(--light-gray); color: var(--primary); }
        .nav-item.active { background: var(--light-gray); color: var(--primary); border-right: 4px solid var(--primary); }
        .nav-item i { font-size: 1.2rem; width: 24px; text-align: center; }
        .badge { background: #ef4444; color: white; padding: 2px 8px; border-radius: 20px; font-size: 12px; margin-left: auto; }
        .sidebar-footer { margin-top: auto; padding: 20px; border-top: 1px solid var(--medium-gray); }

        .main-content { margin-left: 250px; flex: 1; padding: 20px; max-width: calc(100vw - 250px); }
        
        .container { background: var(--white); padding: 30px; border-radius: var(--radius-md); box-shadow: var(--shadow-md); border: 1px solid var(--medium-gray); margin-bottom: 30px; }
        .header { display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid var(--medium-gray); padding-bottom: 20px; margin-bottom: 30px; }
        .header h2 { margin: 0; font-size: 28px; color: var(--text-dark); font-weight: 800; font-family: var(--font-display); display: flex; align-items: center; gap: 10px; }
        .header h2 i { color: var(--primary); }
        
        .btn { padding: 10px 24px; font-family: var(--font-main); background: linear-gradient(135deg, var(--primary), var(--secondary)); color: white; text-decoration: none; border-radius: var(--radius-xl); font-weight: 600; font-size: 0.95rem; box-shadow: 0 4px 15px rgba(230,57,70,0.35); transition: all 0.3s; display: inline-flex; align-items: center; gap: 8px; border: 2px solid transparent; cursor: pointer; }
        .btn:hover { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(230,57,70,0.45); }
        .btn-outline { background: transparent; color: var(--primary); border: 2px solid var(--primary); box-shadow: none; }
        .btn-outline:hover { background: var(--primary); color: white; transform: translateY(-2px); }
        .btn-sm { padding: 6px 12px; font-size: 0.85rem; border-radius: 6px; }
        
        .table-container { overflow-x: auto; border-radius: var(--radius-md); border: 1px solid var(--medium-gray); background: var(--white); }
        table { width: 100%; border-collapse: collapse; white-space: nowrap; }
        th, td { text-align: left; padding: 16px 20px; border-bottom: 1px solid var(--medium-gray); }
        th { background: var(--light-gray); font-weight: 700; color: var(--text-dark); font-size: 14px; text-transform: uppercase; letter-spacing: 0.5px; }
        tr:last-child td { border-bottom: none; }
        tr:hover { background: var(--off-white); }
        
        .img-thumb { width: 48px; height: 48px; object-fit: cover; border-radius: 8px; box-shadow: var(--shadow-sm); border: 2px solid var(--light-gray); }
        .no-img { width: 48px; height: 48px; background: var(--light-gray); color: var(--primary); border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 20px; }
        
        .product-name { font-weight: 700; color: var(--text-dark); }
        .category-badge { display: inline-block; padding: 6px 16px; background: linear-gradient(135deg, var(--primary), var(--secondary)); color: white; border-radius: 20px; font-size: 12px; font-weight: 700; text-transform: uppercase; box-shadow: var(--shadow-sm); }
        .price { font-weight: 700; color: #10b981; font-size: 1.1rem; }
        .mrp { color: var(--text-light); text-decoration: line-through; font-size: 14px; font-weight: 500; }
        
        .actions a, .actions button { margin-right: 10px; color: var(--text-medium); text-decoration: none; transition: color 0.2s; font-size: 16px; background:none; border:none; cursor:pointer; }
        .actions a.edit:hover { color: #3b82f6; }
        .actions a.delete:hover { color: #ef4444; }
        
        .status-badge { padding: 6px 12px; border-radius: 20px; font-size: 12px; font-weight: 700; }
        .status-Pending { background: #fef3c7; color: #92400e; }
        .status-Completed { background: #d1fae5; color: #065f46; }
        .status-Cancelled { background: #fee2e2; color: #991b1b; }

        .tab-pane { display: none; }
        .tab-pane.active { display: block; animation: fadeIn 0.3s; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }

        /* Modal */
        .modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.6); z-index: 1000; display: none; align-items: center; justify-content: center; }
        .modal-overlay.active { display: flex; }
        .modal { background: #fff; width: 90%; max-width: 600px; border-radius: 12px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1); overflow: hidden; max-height: 90vh; display: flex; flex-direction: column; }
        .modal-header { padding: 20px; border-bottom: 1px solid var(--medium-gray); display: flex; justify-content: space-between; align-items: center; }
        .modal-header h3 { font-size: 1.2rem; font-weight: 700; margin: 0; }
        .modal-close { background: transparent; border: none; font-size: 1.5rem; color: var(--text-light); cursor: pointer; }
        .modal-body { padding: 20px; overflow-y: auto; flex: 1; }
        .modal-body p { margin-bottom: 5px; }

        /* Mobile */
        .mobile-toggle { display: none; }
        @media (max-width: 768px) {
            .sidebar { transform: translateX(-100%); transition: 0.3s; }
            .sidebar.open { transform: translateX(0); }
            .main-content { margin-left: 0; max-width: 100%; padding: 15px; }
            .mobile-toggle { display: inline-block; background: transparent; border: none; font-size: 24px; color: var(--primary); cursor: pointer; margin-right: 15px; }
            .header { flex-direction: column; align-items: flex-start; gap: 15px; }
            .header h2 { font-size: 22px; }
        }
    </style>
</head>
<body>

    <div class="sidebar" id="sidebar">
        <div class="sidebar-brand"><i class="fas fa-fire"></i> SS Admin</div>
        
        <?php $pending_count = count(array_filter($all_orders, fn($o) => $o['status'] === 'Pending')); ?>
        
        <div class="nav-item active" onclick="switchTab('bookings')">
            <i class="fas fa-calendar-check"></i> Bookings
            <?php if($pending_count > 0): ?><span class="badge"><?= $pending_count ?></span><?php endif; ?>
        </div>
        <div class="nav-item" onclick="switchTab('payments')">
            <i class="fas fa-credit-card"></i> Payments
        </div>
        <div class="nav-item" onclick="switchTab('history')">
            <i class="fas fa-history"></i> History
        </div>
        <div class="nav-item" onclick="switchTab('inventory')">
            <i class="fas fa-boxes"></i> Inventory
        </div>
        
        <div class="sidebar-footer">
            <a href="logout.php" class="btn btn-outline" style="width: 100%; justify-content:center;"><i class="fas fa-sign-out-alt"></i> Logout</a>
            <button onclick="toggleSidebar()" class="mobile-toggle" style="margin-top:15px; width:100%;">Close Sidebar</button>
        </div>
    </div>

    <div class="main-content">
        <button class="mobile-toggle" onclick="toggleSidebar()"><i class="fas fa-bars"></i> Menu</button>

        <?php if($db_error): ?>
        <div style="background: #fee2e2; border-left: 4px solid #ef4444; padding: 20px; border-radius: 8px; margin-bottom: 20px;">
            <h3 style="color: #991b1b; margin-top: 0;">Database Setup Required</h3>
            <p style="color: #991b1b;"><?= $db_error ?></p>
            <a href="../api/setup_db.php" target="_blank" class="btn" style="margin-top: 10px; background: #ef4444;">Run Setup Script Now</a>
            <p style="font-size: 13px; margin-top: 10px; color: #666;">After running the setup, refresh this page.</p>
        </div>
        <?php endif; ?>

        <!-- BOOKINGS TAB -->
        <div id="tab-bookings" class="tab-pane active container">
            <div class="header">
                <h2><i class="fas fa-calendar-check"></i> New Bookings</h2>
            </div>
            <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Order No</th>
                        <th>Date</th>
                        <th>Customer</th>
                        <th>Phone</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $has_pending = false;
                    foreach($all_orders as $order): 
                        if($order['status'] !== 'Pending') continue;
                        $has_pending = true;
                    ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($order['order_number']) ?></strong></td>
                        <td><?= date('d M Y, h:i A', strtotime($order['created_at'])) ?></td>
                        <td><?= htmlspecialchars($order['customer_name']) ?></td>
                        <td><?= htmlspecialchars($order['customer_phone']) ?></td>
                        <td class="price">₹<?= number_format($order['total_amount'], 2) ?></td>
                        <td><span class="status-badge status-<?= $order['status'] ?>"><?= $order['status'] ?></span></td>
                        <td class="actions">
                            <button onclick="viewOrder(<?= $order['id'] ?>)" title="View Details"><i class="fas fa-eye"></i></button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if(!$has_pending): ?>
                        <tr><td colspan="7" style="text-align:center; padding: 30px;">No new bookings.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
            </div>
        </div>

        <!-- PAYMENTS TAB -->
        <div id="tab-payments" class="tab-pane container">
            <div class="header">
                <h2><i class="fas fa-credit-card"></i> Payment Status</h2>
            </div>
            <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Order No</th>
                        <th>Customer</th>
                        <th>Amount</th>
                        <th>Method</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($all_orders as $order): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($order['order_number']) ?></strong></td>
                        <td><?= htmlspecialchars($order['customer_name']) ?></td>
                        <td class="price">₹<?= number_format($order['total_amount'], 2) ?></td>
                        <td></td>
                        <td><span class="status-badge status-<?= $order['status'] === 'Completed' ? 'Completed' : 'Pending' ?>"><?= $order['status'] === 'Completed' ? 'Paid' : 'Unpaid' ?></span></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            </div>
        </div>

        <!-- HISTORY TAB -->
        <div id="tab-history" class="tab-pane container">
            <div class="header">
                <h2><i class="fas fa-history"></i> Order History</h2>
            </div>
            <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Order No</th>
                        <th>Date</th>
                        <th>Customer</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $has_history = false;
                    foreach($all_orders as $order): 
                        if($order['status'] === 'Pending') continue;
                        $has_history = true;
                    ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($order['order_number']) ?></strong></td>
                        <td><?= date('d M Y', strtotime($order['created_at'])) ?></td>
                        <td><?= htmlspecialchars($order['customer_name']) ?></td>
                        <td class="price">₹<?= number_format($order['total_amount'], 2) ?></td>
                        <td><span class="status-badge status-<?= $order['status'] ?>"><?= $order['status'] ?></span></td>
                        <td class="actions">
                            <button onclick="viewOrder(<?= $order['id'] ?>)" title="View Details"><i class="fas fa-eye"></i></button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if(!$has_history): ?>
                        <tr><td colspan="6" style="text-align:center; padding: 30px;">No completed orders yet.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
            </div>
        </div>

        <!-- INVENTORY TAB -->
        <div id="tab-inventory" class="tab-pane container">
            <div class="header">
                <h2><i class="fas fa-boxes"></i> Inventory Management</h2>
                <a href="add_product.php" class="btn"><i class="fas fa-plus"></i> Add Product</a>
            </div>
            
            <div class="inventory-toolbar" style="display: flex; gap: 15px; margin-bottom: 20px; align-items: center; background: var(--light-gray); padding: 15px 20px; border-radius: var(--radius-md); border: 1px solid var(--medium-gray);">
                <div style="flex: 1; position: relative;">
                    <i class="fas fa-search" style="position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: var(--text-light);"></i>
                    <input type="text" id="searchInput" placeholder="Search products by name..." style="width: 100%; padding: 12px 15px 12px 40px; border: 2px solid var(--medium-gray); border-radius: 8px; font-family: var(--font-main); font-size: 14px; color: var(--text-dark);">
                </div>
                <div style="width: 250px;">
                    <select id="categoryFilter" style="width: 100%; padding: 12px 15px; border: 2px solid var(--medium-gray); border-radius: 8px; font-family: var(--font-main); font-size: 14px; color: var(--text-dark); cursor: pointer;">
                        <option value="all">All Categories</option>
                        <option value="bijili">Bijili & Strings</option>
                        <option value="bombs">Bombs</option>
                        <option value="flower">Flower Pots</option>
                        <option value="chakkra">Ground Chakkra</option>
                        <option value="rocket">Rockets</option>
                        <option value="aerial">Aerial / Sky Shots</option>
                        <option value="fancy">Fancy / Kids Items</option>
                        <option value="sparkler">Sparklers</option>
                    </select>
                </div>
            </div>

            <div class="table-container">
            <table id="inventoryTable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Image</th>
                        <th>Name</th>
                        <th>Category</th>
                        <th>MRP</th>
                        <th>Price</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="inventoryBody">
                    <?php if ($result->num_rows > 0): ?>
                        <?php $sno = 1; while($row = $result->fetch_assoc()): ?>
                            <tr class="inventory-row" data-category="<?= htmlspecialchars($row['category']) ?>">
                                <td><?= $sno++ ?></td>
                                <td>
                                    <?php if($row['image_url']): ?>
                                        <img src="<?= strpos($_SERVER['HTTP_HOST'], 'localhost') !== false ? '../' : 'https://sscrackers.in/' ?><?= htmlspecialchars($row['image_url']) ?>" class="img-thumb">
                                    <?php else: ?>
                                        <div class="no-img"><i class="fas fa-image"></i></div>
                                    <?php endif; ?>
                                </td>
                                <td class="product-name"><?= htmlspecialchars($row['name']) ?></td>
                                <td><span class="category-badge"><?= htmlspecialchars($row['category']) ?></span></td>
                                <td class="mrp">₹<?= number_format($row['mrp'], 2) ?></td>
                                <td class="price">₹<?= number_format($row['price'], 2) ?></td>
                                <td class="actions">
                                    <a href="edit_product.php?id=<?= $row['id'] ?>" class="edit" title="Edit"><i class="fas fa-pen"></i></a>
                                    <a href="delete_product.php?id=<?= $row['id'] ?>" class="delete" title="Delete" onclick="return confirm('Are you sure you want to delete this product?');"><i class="fas fa-trash"></i></a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="7" style="text-align:center;">No products found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
            </div>
        </div>

    </div>

    <!-- Order Modal -->
    <div class="modal-overlay" id="orderModal">
        <div class="modal">
            <div class="modal-header">
                <h3 id="modalTitle">Order Details</h3>
                <button class="modal-close" onclick="closeModal()"><i class="fas fa-times"></i></button>
            </div>
            <div class="modal-body" id="modalBody"></div>
        </div>
    </div>

    <script>
        const ordersData = <?= json_encode($all_orders) ?>;
        const itemsData = <?= json_encode($all_items) ?>;

        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('open');
        }

        function switchTab(tabId) {
            document.querySelectorAll('.tab-pane').forEach(el => el.classList.remove('active'));
            document.getElementById('tab-' + tabId).classList.add('active');
            
            document.querySelectorAll('.nav-item').forEach(el => el.classList.remove('active'));
            event.currentTarget.classList.add('active');
            
            if(window.innerWidth <= 768) {
                toggleSidebar();
            }
        }

        function viewOrder(id) {
            const order = ordersData.find(o => parseInt(o.id) === id);
            if(!order) return;
            const items = itemsData[id] || [];

            let itemsHtml = `
                <table style="width:100%; border-collapse:collapse; margin-top:15px; margin-bottom:20px; font-size:14px;">
                    <tr style="border-bottom:1px solid #ccc; text-align:left;">
                        <th style="padding:8px 0;">Product</th>
                        <th style="padding:8px 0;">Qty</th>
                        <th style="padding:8px 0;">Price</th>
                        <th style="padding:8px 0; text-align:right;">Total</th>
                    </tr>
            `;
            items.forEach(i => {
                itemsHtml += `
                    <tr style="border-bottom:1px solid #eee;">
                        <td style="padding:8px 0;">${i.product_name}</td>
                        <td style="padding:8px 0;">${i.qty}</td>
                        <td style="padding:8px 0;">₹${parseFloat(i.price).toLocaleString('en-IN')}</td>
                        <td style="padding:8px 0; text-align:right;"><strong>₹${(i.qty * i.price).toLocaleString('en-IN')}</strong></td>
                    </tr>
                `;
            });
            itemsHtml += `
                <tr>
                    <td colspan="3" style="text-align:right; padding-top:15px; font-weight:bold;">Grand Total:</td>
                    <td style="text-align:right; padding-top:15px; font-weight:bold; color:#10b981; font-size:18px;">₹${parseFloat(order.total_amount).toLocaleString('en-IN')}</td>
                </tr>
            </table>`;

            let actionsHtml = '';
            if(order.status === 'Pending') {
                actionsHtml = `
                    <div style="display:flex; justify-content:flex-end; gap:10px; border-top:1px solid #eee; padding-top:15px;">
                        <form method="POST" action="update_order.php">
                            <input type="hidden" name="order_id" value="${order.id}">
                            <input type="hidden" name="status" value="Cancelled">
                            <button type="submit" class="btn btn-outline" style="color:#ef4444; border-color:#ef4444;" onclick="return confirm('Cancel this order?');">Cancel Order</button>
                        </form>
                        <form method="POST" action="update_order.php">
                            <input type="hidden" name="order_id" value="${order.id}">
                            <input type="hidden" name="status" value="Completed">
                            <button type="submit" class="btn" style="background:#10b981;">Accept & Mark Paid</button>
                        </form>
                    </div>
                `;
            }

            document.getElementById('modalBody').innerHTML = `
                <div style="display:flex; justify-content:space-between; margin-bottom:15px;">
                    <div>
                        <h4 style="margin:0 0 5px 0; color:var(--text-dark);">Order No: ${order.order_number}</h4>
                        <span class="status-badge status-${order.status}">${order.status}</span>
                    </div>
                    <div style="text-align:right; color:#666; font-size:13px;">
                        ${new Date(order.created_at).toLocaleString('en-IN')}
                    </div>
                </div>
                
                <div style="background:var(--off-white); padding:15px; border-radius:8px; margin-bottom:15px;">
                    <h5 style="margin:0 0 10px 0; border-bottom:1px solid #ccc; padding-bottom:5px;">Customer Details</h5>
                    <p><strong>Name:</strong> ${order.customer_name}</p>
                    <p><strong>Phone:</strong> ${order.customer_phone}</p>
                    <p><strong>Email:</strong> ${order.customer_email || 'N/A'}</p>
                    <p><strong>Address:</strong> ${order.customer_address}, ${order.customer_city} - ${order.customer_pin}</p>
                </div>
                
                <h5 style="margin:0 0 5px 0; border-bottom:1px solid #ccc; padding-bottom:5px;">Order Items</h5>
                ${itemsHtml}
                ${actionsHtml}
            `;
            document.getElementById('orderModal').classList.add('active');
        }

        function closeModal() {
            document.getElementById('orderModal').classList.remove('active');
        }

        // --- Inventory Search & Filter Logic ---
        document.addEventListener('DOMContentLoaded', () => {
            const searchInput = document.getElementById('searchInput');
            const categoryFilter = document.getElementById('categoryFilter');
            const inventoryRows = document.querySelectorAll('.inventory-row');

            function filterInventory() {
                const searchTerm = searchInput.value.toLowerCase();
                const selectedCat = categoryFilter.value;

                inventoryRows.forEach(row => {
                    const name = row.querySelector('.product-name').textContent.toLowerCase();
                    const category = row.getAttribute('data-category');
                    
                    const matchesSearch = name.includes(searchTerm);
                    const matchesCat = selectedCat === 'all' || category === selectedCat;

                    if (matchesSearch && matchesCat) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            }

            if(searchInput) searchInput.addEventListener('input', filterInventory);
            if(categoryFilter) categoryFilter.addEventListener('change', filterInventory);
        });
    </script>
</body>
</html>
