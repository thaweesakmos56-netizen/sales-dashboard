<?php
// ============================================================
// api/products.php  – GET / POST / PUT / DELETE products
// ============================================================
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');
require_once '../config/db.php';

$method = $_SERVER['REQUEST_METHOD'];

// ── GET – list products (with optional search) ───────────────
if ($method === 'GET') {
    $search = trim($_GET['search'] ?? '');
    if ($search !== '') {
        $s = "%$search%";
        $stmt = $conn->prepare(
            "SELECT * FROM products WHERE product_name LIKE ? ORDER BY id DESC"
        );
        $stmt->bind_param('s', $s);
    } else {
        $stmt = $conn->prepare("SELECT * FROM products ORDER BY id DESC");
    }
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    echo json_encode(['success' => true, 'products' => $rows]);

// ── POST – add product ───────────────────────────────────────
} elseif ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $name  = trim($data['product_name'] ?? '');
    $price = floatval($data['price'] ?? 0);
    $stock = intval($data['stock'] ?? 0);

    if (empty($name) || $price <= 0) {
        echo json_encode(['success' => false, 'message' => 'กรุณากรอกชื่อสินค้าและราคา']);
        exit;
    }

    $stmt = $conn->prepare("INSERT INTO products (product_name, price, stock) VALUES (?,?,?)");
    $stmt->bind_param('sdi', $name, $price, $stock);
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'id' => $stmt->insert_id, 'message' => 'เพิ่มสินค้าสำเร็จ']);
    } else {
        echo json_encode(['success' => false, 'message' => 'เพิ่มไม่สำเร็จ']);
    }

// ── PUT – update product ─────────────────────────────────────
} elseif ($method === 'PUT') {
    $data  = json_decode(file_get_contents('php://input'), true);
    $id    = intval($data['id'] ?? 0);
    $name  = trim($data['product_name'] ?? '');
    $price = floatval($data['price'] ?? 0);
    $stock = intval($data['stock'] ?? 0);

    if ($id <= 0 || empty($name) || $price <= 0) {
        echo json_encode(['success' => false, 'message' => 'ข้อมูลไม่ถูกต้อง']);
        exit;
    }

    $stmt = $conn->prepare("UPDATE products SET product_name=?, price=?, stock=? WHERE id=?");
    $stmt->bind_param('sdii', $name, $price, $stock, $id);
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'แก้ไขสินค้าสำเร็จ']);
    } else {
        echo json_encode(['success' => false, 'message' => 'แก้ไขไม่สำเร็จ']);
    }

// ── DELETE – remove product ──────────────────────────────────
} elseif ($method === 'DELETE') {
    $id = intval($_GET['id'] ?? 0);
    if ($id <= 0) {
        echo json_encode(['success' => false, 'message' => 'ไม่พบสินค้า']);
        exit;
    }
    $stmt = $conn->prepare("DELETE FROM products WHERE id=?");
    $stmt->bind_param('i', $id);
    if ($stmt->execute() && $stmt->affected_rows > 0) {
        echo json_encode(['success' => true, 'message' => 'ลบสินค้าสำเร็จ']);
    } else {
        echo json_encode(['success' => false, 'message' => 'ลบไม่สำเร็จ']);
    }
}

$conn->close();
?>
