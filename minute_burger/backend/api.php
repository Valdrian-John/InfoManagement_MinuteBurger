<?php
session_start();
include 'db.php';
header('Content-Type: application/json');

$action = $_GET['action'] ?? '';

function require_login() {
    if (!isset($_SESSION['employee_id'])) {
        http_response_code(401);
        echo json_encode(["status" => "error", "message" => "Not logged in."]);
        exit;
    }
    return (int)$_SESSION['employee_id'];
}

if ($action === 'get_products') {

    $sql = "SELECT p.*, cp.category 
            FROM product p
            JOIN category_product cp ON p.category_id = cp.category_id";
    $result = $conn->query($sql);

    if (!$result) {
        http_response_code(500);
        echo json_encode(["status" => "error", "message" => $conn->error]);
        exit;
    }

    echo json_encode($result->fetch_all(MYSQLI_ASSOC));
    exit;

} elseif ($action === 'get_inventory') {

    $result = $conn->query("SELECT * FROM inventoryitem");

    if (!$result) {
        http_response_code(500);
        echo json_encode(["status" => "error", "message" => $conn->error]);
        exit;
    }

    echo json_encode($result->fetch_all(MYSQLI_ASSOC));
    exit;

} elseif ($action === 'process_sale') {

    $employee_id = require_login();

    $data = json_decode(file_get_contents('php://input'), true);
    $cart = $data['cart'] ?? [];

    if (!is_array($cart) || count($cart) === 0) {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "Cart is empty or invalid."]);
        exit;
    }

    $conn->begin_transaction();

    try {
        $total = 0;
        foreach ($cart as $item) {
            $price = isset($item['price']) ? (float)$item['price'] : 0;
            $qty   = isset($item['qty']) ? (int)$item['qty'] : 1;
            if ($qty < 1) $qty = 1;
            $total += ($price * $qty);
        }

        // orders.order_date is DATE → use CURDATE()
        $stmt = $conn->prepare("INSERT INTO orders (employee_id, order_date, total_amount) VALUES (?, CURDATE(), ?)");
        if (!$stmt) throw new Exception($conn->error);

        $stmt->bind_param("id", $employee_id, $total);
        if (!$stmt->execute()) throw new Exception($stmt->error);

        $order_id = $stmt->insert_id;

        foreach ($cart as $item) {
            $product_id = isset($item['id']) ? (int)$item['id'] : 0;
            $price      = isset($item['price']) ? (float)$item['price'] : 0;
            $qty        = isset($item['qty']) ? (int)$item['qty'] : 1;
            if ($qty < 1) $qty = 1;

            if ($product_id <= 0) {
                throw new Exception("Invalid product id in cart.");
            }

            $sub_total = $price * $qty;

            $stmt_detail = $conn->prepare("
                INSERT INTO order_details (order_id, product_id, price, quantity, sub_total)
                VALUES (?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE
                  quantity = quantity + VALUES(quantity),
                  sub_total = sub_total + VALUES(sub_total)
            ");
            if (!$stmt_detail) throw new Exception($conn->error);

            $stmt_detail->bind_param("iidid", $order_id, $product_id, $price, $qty, $sub_total);
            if (!$stmt_detail->execute()) throw new Exception($stmt_detail->error);

            // Fetch recipe
            $res_recipe = $conn->prepare("SELECT item_id, unit_quantity FROM make_product WHERE product_id = ?");
            if (!$res_recipe) throw new Exception($conn->error);

            $res_recipe->bind_param("i", $product_id);
            if (!$res_recipe->execute()) throw new Exception($res_recipe->error);

            $recipe = $res_recipe->get_result();

            // Deduct inventory: qty * unit_quantity
            while ($ingred = $recipe->fetch_assoc()) {
                $item_id = (int)$ingred['item_id'];
                $unit_qty = (float)$ingred['unit_quantity'];

                $deduct = $unit_qty * $qty;

                $update_inv = $conn->prepare("
                    UPDATE inventoryitem
                    SET current_stock = current_stock - ?
                    WHERE item_id = ?
                ");
                if (!$update_inv) throw new Exception($conn->error);

                $update_inv->bind_param("di", $deduct, $item_id);
                if (!$update_inv->execute()) throw new Exception($update_inv->error);
            }
        }

        $conn->commit();
        echo json_encode(["status" => "success", "message" => "Sale completed!", "order_id" => $order_id]);
        exit;

    } catch (Exception $e) {
        $conn->rollback();
        http_response_code(500);
        echo json_encode(["status" => "error", "message" => $e->getMessage()]);
        exit;
    }

} elseif ($action === 'process_delivery') {

    $data = json_decode(file_get_contents('php://input'), true);
    $delivery = $data['delivery'] ?? [];

    if (!is_array($delivery) || count($delivery) === 0) {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "Delivery list is empty or invalid."]);
        exit;
    }

    foreach ($delivery as $item) {
        $qty = isset($item['qty']) ? (int)$item['qty'] : 0;
        $name = isset($item['item']) ? (string)$item['item'] : '';

        if ($qty <= 0 || $name === '') continue;

        $stmt = $conn->prepare("UPDATE inventoryitem SET current_stock = current_stock + ? WHERE item_name = ?");
        if (!$stmt) {
            http_response_code(500);
            echo json_encode(["status" => "error", "message" => $conn->error]);
            exit;
        }

        $stmt->bind_param("is", $qty, $name);
        if (!$stmt->execute()) {
            http_response_code(500);
            echo json_encode(["status" => "error", "message" => $stmt->error]);
            exit;
        }
    }

    echo json_encode(["status" => "success"]);
    exit;

} elseif ($action === 'process_waste') {

    $employee_id = require_login();

    $data = json_decode(file_get_contents('php://input'), true);
    $waste = $data['waste'] ?? [];

    if (!is_array($waste) || count($waste) === 0) {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "Waste list is empty or invalid."]);
        exit;
    }

    $conn->begin_transaction();

    try {
        foreach ($waste as $w) {
            $item_name = isset($w['item']) ? (string)$w['item'] : '';
            $qty = isset($w['qty']) ? (int)$w['qty'] : 0;
            $reason = isset($w['reason']) ? (string)$w['reason'] : '';

            if ($item_name === '' || $qty <= 0) continue;

            $stmt_find = $conn->prepare("SELECT item_id FROM inventoryitem WHERE item_name = ?");
            if (!$stmt_find) throw new Exception($conn->error);

            $stmt_find->bind_param("s", $item_name);
            if (!$stmt_find->execute()) throw new Exception($stmt_find->error);

            $row = $stmt_find->get_result()->fetch_assoc();
            if (!$row) throw new Exception("Item not found: " . $item_name);

            $item_id = (int)$row['item_id'];

            $stmt_inv = $conn->prepare("UPDATE inventoryitem SET current_stock = current_stock - ? WHERE item_id = ?");
            if (!$stmt_inv) throw new Exception($conn->error);

            $stmt_inv->bind_param("ii", $qty, $item_id);
            if (!$stmt_inv->execute()) throw new Exception($stmt_inv->error);

            $stmt_log = $conn->prepare("
                INSERT INTO stockout (date, employee_id, item_id, category_id, description)
                VALUES (CURDATE(), ?, ?, 1, ?)
            ");
            if (!$stmt_log) throw new Exception($conn->error);

            $stmt_log->bind_param("iis", $employee_id, $item_id, $reason);
            if (!$stmt_log->execute()) throw new Exception($stmt_log->error);
        }

        $conn->commit();
        echo json_encode(["status" => "success"]);
        exit;

    } catch (Exception $e) {
        $conn->rollback();
        http_response_code(500);
        echo json_encode(["status" => "error", "message" => $e->getMessage()]);
        exit;
    }

} else {

    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Invalid action."]);
    exit;
}