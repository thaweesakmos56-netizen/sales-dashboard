/**
 * Sales Dashboard – app.js
 * Handles all API calls, chart rendering, and UI interactions
 */

const API = 'api/';
let charts = {};
let allProducts = [];
let refreshInterval;

// ============================================================
// INIT
// ============================================================
document.addEventListener('DOMContentLoaded', () => {
    navigateTo('dashboard');
    updateClock();
    setInterval(updateClock, 1000);
    // Auto-refresh every 30 seconds
    refreshInterval = setInterval(() => {
        const activePage = document.querySelector('.page.active')?.id;
        if (activePage === 'page-dashboard') loadDashboard();
    }, 30000);
});

// ============================================================
// NAVIGATION
// ============================================================
function navigateTo(page) {
    document.querySelectorAll('.page').forEach(p => p.classList.remove('active'));
    document.querySelectorAll('.nav-item').forEach(n => n.classList.remove('active'));

    const pageEl = document.getElementById('page-' + page);
    const navEl  = document.querySelector(`[data-page="${page}"]`);
    if (pageEl) pageEl.classList.add('active');
    if (navEl)  navEl.classList.add('active');

    document.querySelector('.topbar-title').textContent = {
        dashboard : '📊 Sales Dashboard',
        products  : '📦 จัดการสินค้า',
        orders    : '🛒 คำสั่งซื้อ',
    }[page] || 'Dashboard';

    if (page === 'dashboard') loadDashboard();
    if (page === 'products')  loadProducts();
    if (page === 'orders')    loadOrders();
}

// ============================================================
// CLOCK
// ============================================================
function updateClock() {
    const el = document.getElementById('clock');
    if (el) el.textContent = new Date().toLocaleString('th-TH', {
        dateStyle: 'medium', timeStyle: 'short'
    });
}

// ============================================================
// DASHBOARD
// ============================================================
async function loadDashboard() {
    try {
        const [summary, daily, monthly, topProducts] = await Promise.all([
            fetchJSON(API + 'sales.php?type=summary'),
            fetchJSON(API + 'sales.php?type=daily'),
            fetchJSON(API + 'sales.php?type=monthly'),
            fetchJSON(API + 'sales.php?type=top_products'),
        ]);

        // Stats
        setEl('stat-revenue',  formatMoney(summary.total_revenue));
        setEl('stat-orders',   summary.total_orders.toLocaleString());
        setEl('stat-products', summary.total_products.toLocaleString());
        setEl('stat-today',    formatMoney(summary.today_revenue));

        // Charts
        renderDailyChart(daily.data || []);
        renderMonthlyChart(monthly.data || []);
        renderTopChart(topProducts.data || []);

    } catch (e) {
        console.error('Dashboard load error:', e);
    }
}

function renderDailyChart(data) {
    const ctx = document.getElementById('chart-daily')?.getContext('2d');
    if (!ctx) return;
    if (charts.daily) charts.daily.destroy();

    const labels = data.map(d => {
        const dt = new Date(d.day);
        return dt.toLocaleDateString('th-TH', { day: 'numeric', month: 'short' });
    });
    const values = data.map(d => d.total);

    charts.daily = new Chart(ctx, {
        type: 'line',
        data: {
            labels,
            datasets: [{
                label: 'ยอดขาย (฿)',
                data: values,
                borderColor: '#00d4ff',
                backgroundColor: 'rgba(0,212,255,0.08)',
                borderWidth: 2,
                pointBackgroundColor: '#00d4ff',
                pointRadius: 4,
                tension: 0.4,
                fill: true,
            }]
        },
        options: chartOptions('฿')
    });
}

function renderMonthlyChart(data) {
    const ctx = document.getElementById('chart-monthly')?.getContext('2d');
    if (!ctx) return;
    if (charts.monthly) charts.monthly.destroy();

    const labels = data.map(d => {
        const [y, m] = d.month.split('-');
        return new Date(y, m-1).toLocaleDateString('th-TH', { month: 'short', year: '2-digit' });
    });

    charts.monthly = new Chart(ctx, {
        type: 'bar',
        data: {
            labels,
            datasets: [{
                label: 'ยอดขายรายเดือน (฿)',
                data: data.map(d => d.total),
                backgroundColor: 'rgba(0,229,160,0.7)',
                borderColor: '#00e5a0',
                borderWidth: 1,
                borderRadius: 6,
            }]
        },
        options: chartOptions('฿')
    });
}

function renderTopChart(data) {
    const ctx = document.getElementById('chart-top')?.getContext('2d');
    if (!ctx) return;
    if (charts.top) charts.top.destroy();

    const colors = ['#00d4ff','#00e5a0','#ffd166','#a78bfa','#ff4d6d','#ff9f43'];

    charts.top = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: data.map(d => d.name),
            datasets: [{
                data: data.map(d => d.revenue),
                backgroundColor: colors.map(c => c + 'cc'),
                borderColor: colors,
                borderWidth: 2,
                hoverOffset: 8,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { color: '#7a849e', font: { family: 'Outfit', size: 11 }, padding: 12 }
                },
                tooltip: {
                    callbacks: {
                        label: ctx => ` ${formatMoney(ctx.raw)}`
                    }
                }
            }
        }
    });
}

function chartOptions(prefix = '') {
    return {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { display: false },
            tooltip: {
                backgroundColor: '#151d2e',
                borderColor: 'rgba(255,255,255,0.08)',
                borderWidth: 1,
                titleColor: '#e8edf5',
                bodyColor: '#7a849e',
                padding: 12,
                callbacks: {
                    label: ctx => ` ${formatMoney(ctx.raw)}`
                }
            }
        },
        scales: {
            x: {
                grid: { color: 'rgba(255,255,255,0.04)' },
                ticks: { color: '#7a849e', font: { family: 'Outfit', size: 11 } }
            },
            y: {
                grid: { color: 'rgba(255,255,255,0.04)' },
                ticks: {
                    color: '#7a849e',
                    font: { family: 'Outfit', size: 11 },
                    callback: v => formatMoneyShort(v)
                }
            }
        }
    };
}

// ============================================================
// PRODUCTS
// ============================================================
async function loadProducts(search = '') {
    const url = API + 'products.php' + (search ? `?search=${encodeURIComponent(search)}` : '');
    const res = await fetchJSON(url);
    allProducts = res.products || [];
    renderProductTable(allProducts);
}

function renderProductTable(products) {
    const tbody = document.getElementById('product-tbody');
    if (!tbody) return;

    if (products.length === 0) {
        tbody.innerHTML = `<tr><td colspan="5"><div class="empty"><div class="empty-icon">📦</div><h4>ไม่พบสินค้า</h4></div></td></tr>`;
        return;
    }

    tbody.innerHTML = products.map(p => `
        <tr>
            <td>${escHtml(p.product_name)}</td>
            <td>${formatMoney(p.price)}</td>
            <td>
                <span class="badge ${p.stock > 10 ? 'badge-green' : p.stock > 0 ? 'badge-yellow' : 'badge-red'}">
                    ${p.stock} ชิ้น
                </span>
            </td>
            <td>${formatDate(p.created_at)}</td>
            <td>
                <div style="display:flex;gap:6px">
                    <button class="btn btn-ghost btn-sm" onclick="openEditProduct(${p.id})">✏️ แก้ไข</button>
                    <button class="btn btn-red btn-sm"   onclick="deleteProduct(${p.id},'${escHtml(p.product_name)}')">🗑️ ลบ</button>
                </div>
            </td>
        </tr>
    `).join('');
}

function openAddProduct() {
    document.getElementById('modal-product-title').textContent = 'เพิ่มสินค้าใหม่';
    document.getElementById('prod-id').value = '';
    document.getElementById('prod-name').value  = '';
    document.getElementById('prod-price').value = '';
    document.getElementById('prod-stock').value = '';
    showModal('modal-product');
}

async function openEditProduct(id) {
    const p = allProducts.find(x => x.id == id);
    if (!p) return;
    document.getElementById('modal-product-title').textContent = 'แก้ไขสินค้า';
    document.getElementById('prod-id').value    = p.id;
    document.getElementById('prod-name').value  = p.product_name;
    document.getElementById('prod-price').value = p.price;
    document.getElementById('prod-stock').value = p.stock;
    showModal('modal-product');
}

async function saveProduct() {
    const id    = document.getElementById('prod-id').value;
    const name  = document.getElementById('prod-name').value.trim();
    const price = parseFloat(document.getElementById('prod-price').value);
    const stock = parseInt(document.getElementById('prod-stock').value);

    if (!name || isNaN(price) || price <= 0) {
        showToast('กรุณากรอกข้อมูลให้ครบ', 'error'); return;
    }

    const method = id ? 'PUT' : 'POST';
    const body   = { product_name: name, price, stock: stock || 0 };
    if (id) body.id = id;

    const res = await fetchJSON(API + 'products.php', { method, body: JSON.stringify(body) });
    if (res.success) {
        showToast(res.message, 'success');
        hideModal('modal-product');
        loadProducts();
    } else {
        showToast(res.message, 'error');
    }
}

async function deleteProduct(id, name) {
    if (!confirm(`ลบสินค้า "${name}" ใช่หรือไม่?`)) return;
    const res = await fetchJSON(API + `products.php?id=${id}`, { method: 'DELETE' });
    if (res.success) { showToast(res.message, 'success'); loadProducts(); }
    else showToast(res.message, 'error');
}

// ============================================================
// ORDERS
// ============================================================
async function loadOrders() {
    // Load products for dropdown
    const pRes = await fetchJSON(API + 'products.php');
    allProducts = pRes.products || [];
    const sel = document.getElementById('order-product');
    if (sel) {
        sel.innerHTML = '<option value="">-- เลือกสินค้า --</option>' +
            allProducts.map(p => `<option value="${p.id}">${escHtml(p.product_name)} (฿${formatMoney(p.price)})</option>`).join('');
    }

    fetchOrders();
}

async function fetchOrders() {
    const from = document.getElementById('filter-from')?.value || '';
    const to   = document.getElementById('filter-to')?.value   || '';
    let url = API + 'orders.php';
    if (from) url += `?from=${from}`;
    if (from && to) url += `&to=${to}`;

    const res = await fetchJSON(url);
    renderOrderTable(res.orders || []);
}

function renderOrderTable(orders) {
    const tbody = document.getElementById('order-tbody');
    if (!tbody) return;

    if (orders.length === 0) {
        tbody.innerHTML = `<tr><td colspan="5"><div class="empty"><div class="empty-icon">🛒</div><h4>ไม่พบคำสั่งซื้อ</h4></div></td></tr>`;
        return;
    }

    tbody.innerHTML = orders.map(o => `
        <tr>
            <td>#${o.id}</td>
            <td>${escHtml(o.product_name)}</td>
            <td>${o.quantity} ชิ้น</td>
            <td style="color:var(--cyan);font-weight:600">${formatMoney(o.total_price)}</td>
            <td>${o.order_date}</td>
        </tr>
    `).join('');
}

async function saveOrder() {
    const product_id = document.getElementById('order-product')?.value;
    const quantity   = parseInt(document.getElementById('order-qty')?.value);

    if (!product_id || isNaN(quantity) || quantity <= 0) {
        showToast('กรุณาเลือกสินค้าและจำนวน', 'error'); return;
    }

    const res = await fetchJSON(API + 'orders.php', {
        method: 'POST',
        body: JSON.stringify({ product_id, quantity })
    });

    if (res.success) {
        showToast(`สร้างคำสั่งซื้อสำเร็จ ยอด ${formatMoney(res.total_price)}`, 'success');
        hideModal('modal-order');
        loadOrders();
    } else {
        showToast(res.message, 'error');
    }
}

// ============================================================
// HELPERS
// ============================================================
async function fetchJSON(url, options = {}) {
    const headers = { 'Content-Type': 'application/json', ...(options.headers || {}) };
    const res = await fetch(url, { ...options, headers });
    return res.json();
}

function setEl(id, val) {
    const el = document.getElementById(id);
    if (el) el.textContent = val;
}

function formatMoney(n) {
    return '฿' + parseFloat(n || 0).toLocaleString('th-TH', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
}

function formatMoneyShort(n) {
    if (n >= 1000000) return '฿' + (n/1000000).toFixed(1) + 'M';
    if (n >= 1000)    return '฿' + (n/1000).toFixed(0) + 'K';
    return '฿' + n;
}

function formatDate(dt) {
    if (!dt) return '-';
    return new Date(dt).toLocaleDateString('th-TH', { day: 'numeric', month: 'short', year: '2-digit' });
}

function escHtml(str) {
    return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

function showModal(id)  { const m = document.getElementById(id); if(m) m.style.display='flex'; }
function hideModal(id)  { const m = document.getElementById(id); if(m) m.style.display='none'; }

function showToast(msg, type = 'info') {
    const t = document.getElementById('toast');
    if (!t) return;
    t.textContent = msg;
    t.className = `toast ${type} show`;
    setTimeout(() => t.classList.remove('show'), 3000);
}

// Close modal on overlay click
document.addEventListener('click', e => {
    if (e.target.classList.contains('modal-overlay')) {
        e.target.style.display = 'none';
    }
});

// Product search debounce
let searchTimer;
function onProductSearch(val) {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => loadProducts(val), 300);
}
