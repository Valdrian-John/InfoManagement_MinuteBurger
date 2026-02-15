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
            if(clockEl) clockEl.innerText = new Date().toLocaleTimeString(); 
        }, 1000);

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
    document.getElementById(`btn-${view}`).classList.add('active');
    
    if (view === 'stock') renderStockAudit();
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
            alert("Checkout failed: " + result.message);
        }
    } catch (error) {
        console.error("Network Error:", error);
        alert("System error: Could not connect to the server.");
    }
}

function clearCart() { 
    if(confirm("Are you sure you want to void this order?")) {
        cart = []; 
        updateCart(); 
    }
}

// STOCK MONITOR LOGIC
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

// DELIVERY LOGIC
function filterDeliveryOptions() {
    const input = document.getElementById('deliverySearch').value.toLowerCase();
    const select = document.getElementById('deliveryItemSelect');
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
        <tr><td>${d.item}</td><td>${d.qty}</td><td><button onclick="deliveryBatch.splice(${idx},1);renderDeliveryTable()">X</button></td></tr>
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
        }
    } catch (error) {
        console.error("Delivery Error:", error);
        alert("Error saving delivery.");
    }
}

// WASTE LOGIC
function renderWasteOptions() {
    document.getElementById('wasteItemSelect').innerHTML = Object.keys(inventory).map(k => `<option value="${k}">${k}</option>`).join('');
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
        }
    } catch (error) {
        console.error("Waste Error:", error);
        alert("Error saving waste log.");
    }
}

window.onload = init;