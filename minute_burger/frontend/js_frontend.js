let inventory = {};
let menuItems = [];

let cart = [];
let wasteCart = [];
let deliveryBatch = [];
let activeCat = 'Value';

async function init() {
  try {
    const response = await fetch('../backend/api.php?action=get_products');
    const data = await response.json();

    menuItems = data.map(p => ({
      id: p.product_id,
      name: p.product_name,
      price: parseFloat(p.selling_price),
      cat: p.category
    }));

    const invResponse = await fetch('../backend/api.php?action=get_inventory');
    const invData = await invResponse.json();

    invData.forEach(item => {
      inventory[item.item_name] = {
        start: item.current_stock,
        delivery: 0,
        current: item.current_stock,
        waste: 0
      };
    });

    renderMenu();
    renderWasteOptions();
    filterDeliveryOptions();

    setInterval(() => {
      const clockEl = document.getElementById('clock');
      if (clockEl) clockEl.innerText = new Date().toLocaleTimeString();
    }, 1000);

    // Show Employees button only for Owner/Senior
    const role = (window.__ROLE__ || "").trim().toLowerCase();
    const btnEmployees = document.getElementById("btn-employees");
    if (btnEmployees && (role === "owner" || role === "senior staff")) {
      btnEmployees.style.display = "inline-block";
    }

    setupAttendanceUI();

    console.log("System initialized with " + menuItems.length + " products.");
  } catch (error) {
    console.error("Initialization failed:", error);
    alert("System Error: Could not load data from database.");
  }
}

function switchView(view) {
  document.querySelectorAll('.main-screen, .admin-view').forEach(v => v.style.display = 'none');
  document.getElementById(`view-${view}`).style.display = (view === 'pos' ? 'grid' : 'block');

  document.querySelectorAll('.view-btn').forEach(btn => btn.classList.remove('active'));
  const btn = document.getElementById(`btn-${view}`);
  if (btn) btn.classList.add('active');

  if (view === 'stock') renderStockAudit();
  if (view === 'employees') {
    loadEmployeeFormOptions();
    loadEmployees();
  }
}

function filterMenu(cat, btn) {
  activeCat = cat;
  document.querySelectorAll('.cat-link').forEach(b => b.classList.remove('active'));
  btn.classList.add('active');
  renderMenu();
}

function renderMenu() {
  const grid = document.getElementById('productGrid');
  grid.innerHTML = '';

  menuItems.filter(i => i.cat === activeCat).forEach(item => {
    const div = document.createElement('div');
    div.className = 'product-card';
    div.innerHTML = `<strong>${item.name}</strong><br>P${item.price.toFixed(2)}`;
    div.onclick = () => { cart.push(item); updateCart(); };
    grid.appendChild(div);
  });
}

function updateCart() {
  const area = document.getElementById('cartItems');
  area.innerHTML = cart.length === 0 ? '<div class="empty-msg">Ready for next customer...</div>' :
    cart.map(i => `
      <div class="cart-line" style="display:flex; justify-content:space-between; margin-bottom: 5px;">
        <span>${i.name}</span>
        <span>P${i.price.toFixed(2)}</span>
      </div>`).join('');

  const total = cart.reduce((s, i) => s + i.price, 0);
  document.getElementById('totalPrice').innerText = total.toFixed(2);
}

async function processCheckout() {
  if (cart.length === 0) return;

  try {
    const response = await fetch('../backend/api.php?action=process_sale', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ cart: cart })
    });

    const result = await response.json();

    if (result.status === "success") {
      alert("SALE RECORDED SUCCESSFULLY");
      cart = [];
      updateCart();
    } else {
      alert("Checkout failed: " + (result.message || "Unknown error"));
    }
  } catch (error) {
    console.error("Network Error:", error);
    alert("System error: Could not connect to the server.");
  }
}

function clearCart() {
  if (confirm("Are you sure you want to void this order?")) {
    cart = [];
    updateCart();
  }
}

/* -------------------- STOCK MONITOR -------------------- */
function renderStockAudit() {
  const container = document.getElementById('inventoryTableContainer');
  let html = `<table class="inventory-table"><thead><tr>
      <th>ITEM</th>
      <th>START</th>
      <th style="color:#00ff00">DELIVERY</th>
      <th style="color:#ffcc00">WASTE</th>
      <th style="color:red">SOLD</th>
      <th>ENDING</th>
  </tr></thead><tbody>`;

  for (let key in inventory) {
    let item = inventory[key];
    let totalOut = (item.start + item.delivery - item.current);
    let soldUsed = (totalOut - (item.waste || 0)).toFixed(0);

    html += `<tr>
      <td>${key}</td>
      <td>${item.start}</td>
      <td style="color:#00ff00">+${item.delivery}</td>
      <td style="color:#ffcc00">-${item.waste || 0}</td>
      <td style="color:red">-${soldUsed}</td>
      <td style="color:var(--mb-orange); font-weight:bold;">${item.current}</td>
    </tr>`;
  }

  html += `</tbody></table>`;
  container.innerHTML = html;
}

/* -------------------- DELIVERY -------------------- */
function filterDeliveryOptions() {
  const inputEl = document.getElementById('deliverySearch');
  const input = inputEl ? inputEl.value.toLowerCase() : '';
  const select = document.getElementById('deliveryItemSelect');
  if (!select) return;

  select.innerHTML = Object.keys(inventory)
    .filter(k => k.toLowerCase().includes(input))
    .map(k => `<option value="${k}">${k}</option>`).join('');
}

function addToDeliveryList() {
  const item = document.getElementById('deliveryItemSelect').value;
  const qty = parseInt(document.getElementById('deliveryQty').value);
  if (!item) return;
  deliveryBatch.push({ item, qty });
  renderDeliveryTable();
}

function renderDeliveryTable() {
  document.getElementById('deliveryTableBody').innerHTML = deliveryBatch.map((d, idx) => `
    <tr>
      <td>${d.item}</td>
      <td>${d.qty}</td>
      <td><button onclick="deliveryBatch.splice(${idx},1);renderDeliveryTable()">X</button></td>
    </tr>
  `).join('');
}

async function confirmDelivery() {
  if (deliveryBatch.length === 0) return;

  try {
    const response = await fetch('../backend/api.php?action=process_delivery', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ delivery: deliveryBatch })
    });

    const result = await response.json();

    if (result.status === "success") {
      alert("DELIVERY SAVED TO DATABASE");
      location.reload();
    } else {
      alert("Delivery failed: " + (result.message || "Unknown error"));
    }
  } catch (error) {
    console.error("Delivery Error:", error);
    alert("Error saving delivery.");
  }
}

/* -------------------- WASTE -------------------- */
function renderWasteOptions() {
  const select = document.getElementById('wasteItemSelect');
  if (!select) return;
  select.innerHTML = Object.keys(inventory).map(k => `<option value="${k}">${k}</option>`).join('');
}

function addToWaste() {
  const item = document.getElementById('wasteItemSelect').value;
  const qty = parseInt(document.getElementById('wasteQty').value);
  const reason = document.getElementById('wasteReason').value;

  if (!item || qty <= 0) return;

  wasteCart.push({ item, qty, reason });
  renderWasteTable();
}

function renderWasteTable() {
  document.getElementById('wasteTableBody').innerHTML = wasteCart.map((w, idx) => `
    <tr>
      <td>${w.item}</td>
      <td>${w.qty}</td>
      <td>${w.reason}</td>
      <td><button onclick="wasteCart.splice(${idx},1);renderWasteTable()">X</button></td>
    </tr>
  `).join('');
}

async function confirmWaste() {
  if (wasteCart.length === 0) return;

  try {
    const response = await fetch('../backend/api.php?action=process_waste', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ waste: wasteCart })
    });

    const result = await response.json();

    if (result.status === "success") {
      alert("WASTE LOGGED SUCCESSFULLY");
      wasteCart = [];
      location.reload();
    } else {
      alert("Waste failed: " + (result.message || "Unknown error"));
    }
  } catch (error) {
    console.error("Waste Error:", error);
    alert("Error saving waste log.");
  }
}

/* -------------------- EMPLOYEES (OWNER/SENIOR) -------------------- */
let empOptionsLoaded = false;

async function loadEmployeeFormOptions() {
  if (empOptionsLoaded) return;

  const role = (window.__ROLE__ || "").trim().toLowerCase();
  if (!(role === "owner" || role === "senior staff")) return;

  try {
    const res = await fetch("../backend/api.php?action=get_employee_form_options");
    const data = await res.json().catch(() => ({}));
    if (!res.ok || data.status !== "success") return;

    const g = document.getElementById("empAddGender");
    const h = document.getElementById("empAddHea");
    const c = document.getElementById("empAddCivil");

    if (g) g.innerHTML = data.genders.map(x => `<option value="${x.gender_id}">${x.gender_title}</option>`).join("");
    if (h) h.innerHTML = data.hea.map(x => `<option value="${x.hea_id}">${x.hea_title}</option>`).join("");
    if (c) c.innerHTML = data.civil_status.map(x => `<option value="${x.civil_status_id}">${x.civil_status}</option>`).join("");

    empOptionsLoaded = true;
  } catch {}
}

async function loadEmployees() {
  const role = (window.__ROLE__ || "").trim().toLowerCase();
  if (!(role === "owner" || role === "senior staff")) return;

  const body = document.getElementById("employeesTableBody");
  const msg = document.getElementById("empMsg");
  if (!body) return;

  try {
    const res = await fetch("../backend/api.php?action=get_employees");
    const data = await res.json().catch(() => ({}));
    if (!res.ok || data.status !== "success") {
      if (msg) msg.textContent = data.message || "Failed to load employees.";
      return;
    }

    body.innerHTML = data.employees.map(e => {
      const isProtected = (parseInt(e.employee_id, 10) === 1 || parseInt(e.employee_id, 10) === 2);
      const isActive = (String(e.status).toLowerCase() === "active");
      const nextStatusId = isActive ? 2 : 1;

      return `
        <tr>
          <td>${e.employee_id}</td>
          <td>${e.name}</td>
          <td style="font-weight:900; color:${isActive ? "#00ff00" : "#ff6666"}">${e.status}</td>
          <td>
            ${isProtected
              ? `<span style="color:#aaa; font-weight:800;">Locked</span>`
              : `<button onclick="setEmployeeStatus(${e.employee_id}, ${nextStatusId})">
                    ${isActive ? "Set Inactive" : "Set Active"}
                 </button>`
            }
          </td>
        </tr>
      `;
    }).join("");

    if (msg) msg.textContent = "";
  } catch {
    if (msg) msg.textContent = "Failed to load employees.";
  }
}

async function addEmployee() {
  const msg = document.getElementById("empMsg");

  const name = document.getElementById("empAddName")?.value.trim();
  const gender_id = parseInt(document.getElementById("empAddGender")?.value, 10);
  const date_of_birth = document.getElementById("empAddDob")?.value;
  const hea_id = parseInt(document.getElementById("empAddHea")?.value, 10);
  const civil_status_id = parseInt(document.getElementById("empAddCivil")?.value, 10);
  const contact_no = document.getElementById("empAddContact")?.value.trim() || "";
  const address = document.getElementById("empAddAddress")?.value.trim() || "";

  if (!name || !gender_id || !date_of_birth || !hea_id || !civil_status_id) {
    if (msg) msg.textContent = "Please fill in all required fields (*).";
    return;
  }

  try {
    const res = await fetch("../backend/api.php?action=add_employee", {
      method: "POST",
      headers: { "Content-Type":"application/json" },
      body: JSON.stringify({ name, gender_id, date_of_birth, hea_id, civil_status_id, contact_no, address })
    });

    const data = await res.json().catch(() => ({}));
    if (!res.ok || data.status !== "success") {
      if (msg) msg.textContent = data.message || "Add employee failed.";
      return;
    }

    // clear inputs
    document.getElementById("empAddName").value = "";
    document.getElementById("empAddDob").value = "";
    document.getElementById("empAddContact").value = "";
    document.getElementById("empAddAddress").value = "";

    if (msg) msg.textContent = "Employee added successfully.";
    loadEmployees();
  } catch {
    if (msg) msg.textContent = "Add employee failed (network/server).";
  }
}

async function setEmployeeStatus(employee_id, status_id) {
  const msg = document.getElementById("empMsg");

  try {
    const res = await fetch("../backend/api.php?action=set_employee_status", {
      method: "POST",
      headers: { "Content-Type":"application/json" },
      body: JSON.stringify({ employee_id, status_id })
    });

    const data = await res.json().catch(() => ({}));
    if (!res.ok || data.status !== "success") {
      if (msg) msg.textContent = data.message || "Update failed.";
      return;
    }

    if (msg) msg.textContent = "Status updated.";
    loadEmployees();
  } catch {
    if (msg) msg.textContent = "Update failed (network/server).";
  }
}

/* -------------------- ATTENDANCE (EMPLOYEE ONLY) -------------------- */
async function setupAttendanceUI() {
  const role = (window.__ROLE__ || "").trim().toLowerCase();
  if (role !== "employee") return;

  const modal = document.getElementById("clockModal");
  const btnClockIn = document.getElementById("btnClockIn");
  const empSelect = document.getElementById("empSelect");
  const err = document.getElementById("clockErr");
  const btnClockOut = document.getElementById("btnClockOut");

  if (btnClockOut) btnClockOut.style.display = "inline-block";
  if (!modal || !btnClockIn || !empSelect || !err) return;

  // load active employees for dropdown
  try {
    const empRes = await fetch("../backend/api.php?action=get_employees&only_active=1");
    const empData = await empRes.json().catch(() => ({}));

    if (empRes.ok && empData.status === "success") {
      empSelect.innerHTML =
        `<option value="">-- Select your name --</option>` +
        empData.employees.map(e => `<option value="${e.employee_id}">${e.name}</option>`).join("");
    } else {
      empSelect.innerHTML = `<option value="">(Failed to load employees)</option>`;
    }
  } catch {
    empSelect.innerHTML = `<option value="">(Failed to load employees)</option>`;
  }

  // show modal only if not clocked in
  try {
    const res = await fetch("../backend/api.php?action=clock_status");
    const data = await res.json().catch(() => ({}));

    if (res.ok && data.status === "success") {
      modal.style.display = data.active ? "none" : "block";
    } else {
      modal.style.display = "block";
    }
  } catch {
    modal.style.display = "block";
  }

  btnClockIn.addEventListener("click", async () => {
    err.style.display = "none";
    err.textContent = "";

    const selected = parseInt(empSelect.value, 10);
    if (!selected) {
      err.textContent = "Please select your name.";
      err.style.display = "block";
      return;
    }

    const res = await fetch("../backend/api.php?action=clock_in", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ employee_id: selected })
    });

    const data = await res.json().catch(() => ({}));

    if (!res.ok || data.status !== "success") {
      err.textContent = data.message || "Clock-in failed.";
      err.style.display = "block";
      return;
    }

    modal.style.display = "none";
    alert("Clocked in!");
  });

  btnClockOut?.addEventListener("click", async () => {
    const res = await fetch("../backend/api.php?action=clock_out", { method: "POST" });
    const data = await res.json().catch(() => ({}));

    if (!res.ok || data.status !== "success") {
      alert(data.message || "Clock-out failed.");
      return;
    }

    alert("Clocked out! Please logout after.");
  });
}

window.onload = init;