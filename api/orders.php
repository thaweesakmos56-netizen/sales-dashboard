<?php
// ============================================================
// api/orders.php  – GET orders / POST new order
// ============================================================
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');
require_once '../config/db.php';

$method = $_SERVER['REQUEST_METHOD'];

// ── GET – list orders with optional date filter ──────────────
if ($method === 'GET') {
    $from = $_GET['from'] ?? '';
    $to   = $_GET['to']   ?? '';

    $sql = "SELECT o.id, p.product_name, o.quantity, o.total_price,
                   DATE_FORMAT(o.order_date,'%d/%m/%Y %H:%i') AS order_date
            FROM orders o
            JOIN products p ON o.product_id = p.id";

    $params = [];
    $types  = '';

    if ($from && $to) {
        $sql   .= " WHERE DATE(o.order_date) BETWEEN ? AND ?";
        $types  = 'ss';
        $params = [$from, $to];
    } elseif ($from) {
        $sql   .= " WHERE DATE(o.order_date) >= ?";
        $types  = 's';
        $params = [$from];
    }

    $sql .= " ORDER BY o.order_date DESC LIMIT 100";

    $stmt = $conn->prepare($sql);
    if ($params) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    echo json_encode(['success' => true, 'orders' => $rows]);

// ── POST – create order ──────────────────────────────────────
} elseif ($method === 'POST') {
    $data       = json_decode(file_get_contents('php://input'), true);
    $product_id = intval($data['product_id'] ?? 0);
    $quantity   = intval($data['quantity']   ?? 0);

    if ($product_id <= 0 || $quantity <= 0) {
        echo json_encode(['success' => false, 'message' => 'ข้อมูลไม่ถูกต้อง']);
        exit;
    }

    // Fetch product price & check stock
    $p = $conn->prepare("SELECT price, stock FROM products WHERE id=?");
    $p->bind_param('i', $product_id);
    $p->execute();
    $product = $p->get_result()->fetch_assoc();

    if (!$product) {
        echo json_encode(['success' => false, 'message' => 'ไม่พบสินค้า']);
        exit;
    }

    if ($product['stock'] < $quantity) {
        echo json_encode(['success' => false, 'message' => 'สินค้าคงเหลือไม่เพียงพอ (เหลือ '.$product['stock'].' ชิ้น)']);
        exit;
    }

    // Calculate total
    $total = $product['price'] * $quantity;

    // Insert order
    $stmt = $conn->prepare("INSERT INTO orders (product_id, quantity, total_price) VALUES (?,?,?)");
    $stmt->bind_param('iid', $product_id, $quantity, $total);

    if ($stmt->execute()) {
        // Deduct stock
        $conn->prepare("UPDATE products SET stock = stock - ? WHERE id=?")->execute() ?: null;
        $upd = $conn->prepare("UPDATE products SET stock = stock - ? WHERE id=?");
        $upd->bind_param('ii', $quantity, $product_id);
        $upd->execute();

        echo json_encode([
            'success'     => true,
            'message'     => 'สร้างคำสั่งซื้อสำเร็จ',
            'order_id'    => $stmt->insert_id,
            'total_price' => $total,
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'สร้างคำสั่งซื้อไม่สำเร็จ']);
    }
}

$conn->close();
?>
