<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Sales Dashboard</title>
<link rel="stylesheet" href="assets/css/style.css">
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
</head>
<body>
<div class="app">

<!-- ===== SIDEBAR ===== -->
<aside class="sidebar">
    <div class="sidebar-logo">
        <div class="logo-icon">📈</div>
        <div class="logo-text">Sales Dashboard</div>
        <div class="logo-sub">ระบบวิเคราะห์ยอดขาย</div>
    </div>

    <nav class="sidebar-nav">
        <div class="nav-label">เมนูหลัก</div>
        <button class="nav-item" data-page="dashboard" onclick="navigateTo('dashboard')">
            <span class="nav-icon">📊</span> Dashboard
        </button>
        <button class="nav-item" data-page="products" onclick="navigateTo('products')">
            <span class="nav-icon">📦</span> สินค้า
        </button>
        <button class="nav-item" data-page="orders" onclick="navigateTo('orders')">
            <span class="nav-icon">🛒</span> คำสั่งซื้อ
        </button>
    </nav>

    <div class="sidebar-footer">Sales Dashboard v1.0</div>
</aside>

<!-- ===== MAIN ===== -->
<div class="main">

    <!-- Topbar -->
    <div class="topbar">
        <div class="topbar-title">📊 Sales Dashboard</div>
        <div class="topbar-right">
            <div class="live-badge"><div class="live-dot"></div> Live</div>
            <div class="topbar-time" id="clock"></div>
        </div>
    </div>

    <div class="content">

        <!-- ======================================
             PAGE: DASHBOARD
             ====================================== -->
        <div class="page" id="page-dashboard">
            <div class="page-header">
                <h2>ภาพรวมยอดขาย</h2>
                <p>ข้อมูลอัพเดทอัตโนมัติทุก 30 วินาที</p>
            </div>

            <!-- Stat Cards -->
            <div class="stat-grid">
                <div class="stat-card cyan">
                    <div class="stat-label">ยอดขายรวม</div>
                    <div class="stat-value cyan" id="stat-revenue">฿0</div>
                    <div class="stat-sub">ทั้งหมดในระบบ</div>
                </div>
                <div class="stat-card green">
                    <div class="stat-label">ยอดขายวันนี้</div>
                    <div class="stat-value green" id="stat-today">฿0</div>
                    <div class="stat-sub">วันที่ <?= date('d/m/Y') ?></div>
                </div>
                <div class="stat-card yellow">
                    <div class="stat-label">คำสั่งซื้อทั้งหมด</div>
                    <div class="stat-value yellow" id="stat-orders">0</div>
                    <div class="stat-sub">รายการ</div>
                </div>
                <div class="stat-card purple">
                    <div class="stat-label">สินค้าในระบบ</div>
                    <div class="stat-value purple" id="stat-products">0</div>
                    <div class="stat-sub">รายการสินค้า</div>
                </div>
            </div>

            <!-- Charts Row 1 -->
            <div class="chart-grid" style="grid-template-columns:2fr 1fr">
                <div class="chart-card">
                    <div class="chart-title">📈 ยอดขายรายวัน</div>
                    <div class="chart-sub">14 วันย้อนหลัง</div>
                    <div class="chart-wrap"><canvas id="chart-daily"></canvas></div>
                </div>
                <div class="chart-card">
                    <div class="chart-title">🏆 สินค้าขายดี</div>
                    <div class="chart-sub">จัดอันดับตามยอดขาย</div>
                    <div class="chart-wrap"><canvas id="chart-top"></canvas></div>
                </div>
            </div>

            <!-- Charts Row 2 -->
            <div class="chart-grid" style="grid-template-columns:1fr">
                <div class="chart-card">
                    <div class="chart-title">📅 ยอดขายรายเดือน</div>
                    <div class="chart-sub">6 เดือนย้อนหลัง</div>
                    <div class="chart-wrap"><canvas id="chart-monthly"></canvas></div>
                </div>
            </div>
        </div>

        <!-- ======================================
             PAGE: PRODUCTS
             ====================================== -->
        <div class="page" id="page-products">
            <div class="page-header">
                <h2>จัดการสินค้า</h2>
                <p>เพิ่ม แก้ไข และลบสินค้าในระบบ</p>
            </div>

            <div class="filter-bar">
                <div class="search-wrap">
                    <span class="search-icon">🔍</span>
                    <input type="text" placeholder="ค้นหาสินค้า..." oninput="onProductSearch(this.value)">
                </div>
                <button class="btn btn-cyan" onclick="openAddProduct()">+ เพิ่มสินค้า</button>
            </div>

            <div class="table-card">
                <div class="table-header">
                    <h3>รายการสินค้า</h3>
                </div>
                <table class="tbl">
                    <thead>
                        <tr>
                            <th>ชื่อสินค้า</th>
                            <th>ราคา</th>
                            <th>สต็อก</th>
                            <th>วันที่เพิ่ม</th>
                            <th>จัดการ</th>
                        </tr>
                    </thead>
                    <tbody id="product-tbody">
                        <tr><td colspan="5" style="text-align:center;padding:40px;color:var(--t3)">กำลังโหลด...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ======================================
             PAGE: ORDERS
             ====================================== -->
        <div class="page" id="page-orders">
            <div class="page-header">
                <h2>คำสั่งซื้อ</h2>
                <p>บันทึกและดูประวัติคำสั่งซื้อ</p>
            </div>

            <div class="filter-bar">
                <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap">
                    <label style="font-size:.82rem;color:var(--t2)">จาก</label>
                    <input type="date" id="filter-from" style="background:var(--bg3);border:1px solid var(--border);color:var(--t1);border-radius:var(--rs);padding:8px 12px;font-size:.88rem;outline:none">
                    <label style="font-size:.82rem;color:var(--t2)">ถึง</label>
                    <input type="date" id="filter-to"   style="background:var(--bg3);border:1px solid var(--border);color:var(--t1);border-radius:var(--rs);padding:8px 12px;font-size:.88rem;outline:none">
                    <button class="btn btn-ghost" onclick="fetchOrders()">🔍 กรอง</button>
                </div>
                <button class="btn btn-cyan" onclick="showModal('modal-order')">+ สร้างคำสั่งซื้อ</button>
            </div>

            <div class="table-card">
                <div class="table-header">
                    <h3>รายการคำสั่งซื้อ</h3>
                </div>
                <table class="tbl">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>สินค้า</th>
                            <th>จำนวน</th>
                            <th>ยอดรวม</th>
                            <th>วันที่</th>
                        </tr>
                    </thead>
                    <tbody id="order-tbody">
                        <tr><td colspan="5" style="text-align:center;padding:40px;color:var(--t3)">กำลังโหลด...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>

    </div><!-- /content -->
</div><!-- /main -->
</div><!-- /app -->

<!-- ===== MODAL: ADD/EDIT PRODUCT ===== -->
<div class="modal-overlay" id="modal-product" style="display:none">
    <div class="modal-box">
        <div class="modal-head">
            <h3 id="modal-product-title">เพิ่มสินค้าใหม่</h3>
            <button class="modal-close" onclick="hideModal('modal-product')">&times;</button>
        </div>
        <div class="modal-body">
            <input type="hidden" id="prod-id">
            <div class="form-group">
                <label>ชื่อสินค้า *</label>
                <input type="text" id="prod-name" placeholder="เช่น iPhone 15 Pro">
            </div>
            <div class="form-grid">
                <div class="form-group">
                    <label>ราคา (฿) *</label>
                    <input type="number" id="prod-price" placeholder="0.00" min="0" step="0.01">
                </div>
                <div class="form-group">
                    <label>จำนวนสต็อก</label>
                    <input type="number" id="prod-stock" placeholder="0" min="0">
                </div>
            </div>
        </div>
        <div class="modal-foot">
            <button class="btn btn-ghost" onclick="hideModal('modal-product')">ยกเลิก</button>
            <button class="btn btn-cyan" onclick="saveProduct()">💾 บันทึก</button>
        </div>
    </div>
</div>

<!-- ===== MODAL: ADD ORDER ===== -->
<div class="modal-overlay" id="modal-order" style="display:none">
    <div class="modal-box">
        <div class="modal-head">
            <h3>สร้างคำสั่งซื้อ</h3>
            <button class="modal-close" onclick="hideModal('modal-order')">&times;</button>
        </div>
        <div class="modal-body">
            <div class="form-group">
                <label>สินค้า *</label>
                <select id="order-product" style="background:var(--bg3);border:1px solid var(--border);color:var(--t1);border-radius:var(--rs);padding:10px 14px;font-size:.92rem;outline:none;width:100%">
                    <option value="">-- เลือกสินค้า --</option>
                </select>
            </div>
            <div class="form-group">
                <label>จำนวน *</label>
                <input type="number" id="order-qty" placeholder="1" min="1" value="1">
            </div>
        </div>
        <div class="modal-foot">
            <button class="btn btn-ghost" onclick="hideModal('modal-order')">ยกเลิก</button>
            <button class="btn btn-cyan" onclick="saveOrder()">✅ ยืนยันคำสั่งซื้อ</button>
        </div>
    </div>
</div>

<!-- Toast -->
<div class="toast" id="toast"></div>

<script src="assets/js/app.js"></script>
</body>
</html>
