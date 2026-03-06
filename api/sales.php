<?php
// ============================================================
// api/sales.php  – GET sales summary + chart data
// ============================================================
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
require_once '../config/db.php';

$type = $_GET['type'] ?? 'summary';

// ── Summary stats ────────────────────────────────────────────
if ($type === 'summary') {
    // Total revenue
    $r1 = $conn->query("SELECT COALESCE(SUM(total_price),0) AS total FROM orders");
    $total_revenue = $r1->fetch_assoc()['total'];

    // Total orders
    $r2 = $conn->query("SELECT COUNT(*) AS cnt FROM orders");
    $total_orders = $r2->fetch_assoc()['cnt'];

    // Total products
    $r3 = $conn->query("SELECT COUNT(*) AS cnt FROM products");
    $total_products = $r3->fetch_assoc()['cnt'];

    // Today revenue
    $r4 = $conn->query("SELECT COALESCE(SUM(total_price),0) AS today FROM orders WHERE DATE(order_date)=CURDATE()");
    $today_revenue = $r4->fetch_assoc()['today'];

    echo json_encode([
        'success'        => true,
        'total_revenue'  => (float)$total_revenue,
        'total_orders'   => (int)$total_orders,
        'total_products' => (int)$total_products,
        'today_revenue'  => (float)$today_revenue,
    ]);

// ── Daily sales (last 14 days) ───────────────────────────────
} elseif ($type === 'daily') {
    $result = $conn->query(
        "SELECT DATE(order_date) AS day, SUM(total_price) AS total
         FROM orders
         WHERE order_date >= DATE_SUB(CURDATE(), INTERVAL 13 DAY)
         GROUP BY DATE(order_date)
         ORDER BY day ASC"
    );
    $rows = [];
    while ($row = $result->fetch_assoc()) {
        $rows[] = ['day' => $row['day'], 'total' => (float)$row['total']];
    }
    echo json_encode(['success' => true, 'data' => $rows]);

// ── Monthly sales (last 6 months) ───────────────────────────
} elseif ($type === 'monthly') {
    $result = $conn->query(
        "SELECT DATE_FORMAT(order_date,'%Y-%m') AS month,
                SUM(total_price) AS total
         FROM orders
         WHERE order_date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
         GROUP BY month
         ORDER BY month ASC"
    );
    $rows = [];
    while ($row = $result->fetch_assoc()) {
        $rows[] = ['month' => $row['month'], 'total' => (float)$row['total']];
    }
    echo json_encode(['success' => true, 'data' => $rows]);

// ── Top products ─────────────────────────────────────────────
} elseif ($type === 'top_products') {
    $result = $conn->query(
        "SELECT p.product_name,
                SUM(o.quantity)    AS total_qty,
                SUM(o.total_price) AS total_revenue
         FROM orders o
         JOIN products p ON o.product_id = p.id
         GROUP BY p.id, p.product_name
         ORDER BY total_revenue DESC
         LIMIT 6"
    );
    $rows = [];
    while ($row = $result->fetch_assoc()) {
        $rows[] = [
            'name'    => $row['product_name'],
            'qty'     => (int)$row['total_qty'],
            'revenue' => (float)$row['total_revenue'],
        ];
    }
    echo json_encode(['success' => true, 'data' => $rows]);
}

$conn->close();
?>
