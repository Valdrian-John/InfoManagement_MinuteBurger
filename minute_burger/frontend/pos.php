<?php
session_start();
if (!isset($_SESSION['employee_id'])) {
  header("Location: login.php");
  exit;
}
$role = $_SESSION['role_label'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Minute Burger Centralized Platform</title>
  <link rel="stylesheet" href="css_frontend.css">

  <script>
    window.__ROLE__ = "<?php echo htmlspecialchars($role, ENT_QUOTES); ?>";
  </script>
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

          <!-- NEW: Employees (Owner/Senior only; JS will show) -->
          <button class="view-btn" id="btn-employees" onclick="switchView('employees')" style="display:none;">LIST OF EMPLOYEES</button>

          <button id="btnClockOut" class="view-btn" style="display:none;">Clock Out</button>
          <a class="view-btn" href="../backend/logout.php">Logout</a>
        </nav>
      </div>

      <div class="staff-status">
        <span id="clock">00:00:00</span> | STORE #3 | CENTRALIZED PLATFORM | ROLE: <?php echo htmlspecialchars($role); ?>
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

    <!-- NEW: EMPLOYEES VIEW -->
    <section id="view-employees" class="admin-view" style="display:none;">
      <h2 style="color: var(--mb-orange); margin-left: 20px;">LIST OF EMPLOYEES</h2>

      <div class="waste-controls" style="align-items:flex-end;">
        <div style="flex:1; min-width:240px;">
          <label>Full Name *</label><br>
          <input id="empAddName" type="text" placeholder="e.g. Juan Dela Cruz" style="width:100%;">
        </div>

        <div style="min-width:180px;">
          <label>Gender *</label><br>
          <select id="empAddGender" style="width:100%;"></select>
        </div>

        <div style="min-width:200px;">
          <label>Date of Birth *</label><br>
          <input id="empAddDob" type="date" style="width:100%;">
        </div>

        <div style="min-width:220px;">
          <label>Highest Educ. Attainment *</label><br>
          <select id="empAddHea" style="width:100%;"></select>
        </div>

        <div style="min-width:200px;">
          <label>Civil Status *</label><br>
          <select id="empAddCivil" style="width:100%;"></select>
        </div>

        <div style="min-width:200px;">
          <label>Contact No</label><br>
          <input id="empAddContact" type="text" placeholder="09xxxxxxxxx" style="width:100%;">
        </div>

        <div style="flex:1; min-width:280px;">
          <label>Address</label><br>
          <input id="empAddAddress" type="text" placeholder="Optional" style="width:100%;">
        </div>

        <button class="btn-confirm" style="width:auto; padding: 12px 20px;" onclick="addEmployee()">ADD</button>
      </div>

      <div style="margin: 10px 20px; color:#ccc; font-weight:700;" id="empMsg"></div>

      <div class="audit-scroll-box" style="margin: 20px;">
        <table class="inventory-table">
          <thead>
            <tr><th>ID</th><th>NAME</th><th>STATUS</th><th>ACTION</th></tr>
          </thead>
          <tbody id="employeesTableBody"></tbody>
        </table>
      </div>
    </section>

  </div>

  <!-- ATTENDANCE MODAL (EMPLOYEE ONLY) -->
  <div id="clockModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,.7); z-index:9999;">
    <div style="max-width:420px; margin:10% auto; background:var(--mb-grey); border:1px solid #333; border-radius:10px; padding:20px; color:#fff;">
      <h2 style="margin:0 0 10px; color:var(--mb-yellow);">Clock In</h2>
      <p style="margin:0 0 14px; color:#ccc;">Select your name to clock in.</p>

      <label style="display:block; margin-bottom:6px; font-weight:700;">Employee</label>
      <select id="empSelect" style="width:100%; padding:12px; border-radius:6px; border:1px solid #444; background:#333; color:#fff;">
        <option value="">Loading employees...</option>
      </select>

      <div style="display:flex; gap:10px; margin-top:14px;">
        <button id="btnClockIn" class="view-btn" style="background:var(--mb-orange); color:#000; flex:1; font-weight:900;">CLOCK IN</button>
      </div>

      <div id="clockErr" style="display:none; margin-top:12px; padding:10px; border:1px solid rgba(220,53,69,.35); background:rgba(220,53,69,.12); color:#ffb3ba; border-radius:6px; font-weight:700;"></div>
    </div>
  </div>

  <script src="js_frontend.js"></script>
</body>
</html>