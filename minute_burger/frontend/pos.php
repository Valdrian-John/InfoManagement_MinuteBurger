<?php
session_start();
if (!isset($_SESSION['employee_id'])) {
  header("Location: login.php");
  exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Minute Burger Centralized Platform</title>
    <link rel="stylesheet" href="css_frontend.css">
</head>
<body>
    <div id="app-shell">
        <header class="mb-navbar">
            <div class="brand">
                <div class="logo-text">MINUTE BURGER</div>
                <nav>
                    <button class="view-btn active" id="btn-pos" onclick="switchView('pos')">ORDERING</button>
                    <button class="view-btn" id="btn-stock" onclick="switchView('stock')">STOCK MONITOR</button>
                    <button class="view-btn" id="btn-delivery" onclick="switchView('delivery')">DELIVERIES</button>
                    <button class="view-btn" id="btn-waste" onclick="switchView('waste')">WASTE / VOID</button>
                    <a class="view-btn" href="../backend/logout.php">LOGOUT</a>
                </nav>
            </div>
            <div class="staff-status">
                <span id="clock">00:00:00</span> | STORE #3 | CENTRALIZED PLATFORM
            </div>
        </header>

        <main id="view-pos" class="main-screen">
            <section class="menu-container">
                <div class="cat-bar">
                    <button class="cat-link active" onclick="filterMenu('Value', this)">VALUE</button>
                    <button class="cat-link" onclick="filterMenu('Double', this)">DOUBLE</button>
                    <button class="cat-link" onclick="filterMenu('Bigtime', this)">BIGTIME</button>
                    <button class="cat-link" onclick="filterMenu('Hotdog', this)">HOTDOGS</button>
                    <button class="cat-link" onclick="filterMenu('Sides', this)">SIDES</button>
                    <button class="cat-link" onclick="filterMenu('Beverages', this)">BEVERAGES</button>
                    <button class="cat-link" onclick="filterMenu('Extras', this)">EXTRAS</button>
                </div>
                <div class="product-grid" id="productGrid"></div>
            </section>

            <aside class="checkout-panel">
                <div class="cart-header">CURRENT ORDER</div>
                <div class="receipt-list" id="cartItems">
                    <div class="empty-msg">Ready for next customer...</div>
                </div>
                <div class="summary-box">
                    <div class="total">TOTAL: P<span id="totalPrice">0.00</span></div>
                </div>
                <div class="checkout-actions">
                    <button class="btn-void" onclick="clearCart()">VOID ALL</button>
                    <button class="btn-confirm" onclick="processCheckout()">CONFIRM SALE</button>
                </div>
            </aside>
        </main>

        <section id="view-stock" class="admin-view" style="display:none;">
            <h2 style="color: var(--mb-orange); margin-left: 20px;">INVENTORY AUDIT</h2>
            <div id="inventoryTableContainer" class="audit-scroll-box"></div>
        </section>

        <section id="view-delivery" class="admin-view" style="display:none;">
            <h2 style="color: var(--mb-orange); margin-left: 20px;">RAW MATERIAL DELIVERY</h2>
            <div class="waste-controls">
                <div style="flex: 1; min-width: 250px;">
                    <label>Search Raw Material</label><br>
                    <input type="text" id="deliverySearch" placeholder="Type to search..." onkeyup="filterDeliveryOptions()" style="width: 100%; margin-bottom: 5px;">
                    <select id="deliveryItemSelect" size="5" style="width: 100%; height: 100px;"></select>
                </div>
                <div>
                    <label>Quantity</label><br>
                    <input type="number" id="deliveryQty" value="1" min="1">
                </div>
                <button onclick="addToDeliveryList()" class="btn-confirm" style="width: auto; padding: 10px 20px;">ADD TO LIST</button>
            </div>
            <div class="audit-scroll-box" style="margin: 20px; max-height: 250px;">
                <table class="inventory-table">
                    <thead>
                        <tr><th>ITEM</th><th>QTY ADDED</th><th>ACTION</th></tr>
                    </thead>
                    <tbody id="deliveryTableBody"></tbody>
                </table>
            </div>
            <button onclick="confirmDelivery()" class="btn-confirm" style="margin: 20px; width: auto; background: #007bff; padding: 15px 30px;">PROCESS DELIVERY</button>
        </section>

        <section id="view-waste" class="admin-view" style="display:none;">
            <h2 style="color: var(--mb-orange); margin-left: 20px;">WASTE & VOID LOG</h2>
            <div class="waste-controls">
                <div>
                    <label>Select Item</label><br>
                    <select id="wasteItemSelect"></select>
                </div>
                <div>
                    <label>Quantity</label><br>
                    <input type="number" id="wasteQty" value="1" min="1">
                </div>
                <div>
                    <label>Reason</label><br>
                    <select id="wasteReason">
                        <option>Expired</option>
                        <option>Damaged/Dropped</option>
                        <option>Customer Return</option>
                    </select>
                </div>
                <button onclick="addToWaste()" class="btn-confirm" style="width: auto; padding: 10px 20px;">ADD TO LIST</button>
            </div>
            <div class="audit-scroll-box" style="margin: 20px; max-height: 300px;">
                <table class="inventory-table">
                    <thead>
                        <tr><th>ITEM</th><th>QTY</th><th>REASON</th><th>ACTION</th></tr>
                    </thead>
                    <tbody id="wasteTableBody"></tbody>
                </table>
            </div>
            <button onclick="confirmWaste()" class="btn-void" style="margin: 20px; width: auto; padding: 15px 30px;">SUBMIT ALL WASTE</button>
        </section>
    </div>
    <script src="js_frontend.js"></script>
</body>
</html>