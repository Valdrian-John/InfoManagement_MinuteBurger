<?php
include 'db.php';
header('Content-Type: application/json');

$action = $_GET['action'] ?? '';

if ($action == 'get_products') {
    $sql = "SELECT p.*, cp.category FROM product p 
            JOIN category_product cp ON p.category_id = cp.category_id";
    $result = $conn->query($sql);
    echo json_encode($result->fetch_all(MYSQLI_ASSOC));
}

if ($action == 'get_inventory') {
    $result = $conn->query("SELECT * FROM inventoryitem");
    echo json_encode($result->fetch_all(MYSQLI_ASSOC));
}

if ($action == 'process_sale') {
    $data = json_decode(file_get_contents('php://input'), true);
    $cart = $data['cart'];
    $employee_id = 1;

    $conn->begin_transaction();

    try {
        $total = array_reduce($cart, function($sum, $item) { return $sum + $item['price']; }, 0);
        $stmt = $conn->prepare("INSERT INTO orders (employee_id, order_date, total_amount) VALUES (?, NOW(), ?)");
        $stmt->bind_param("id", $employee_id, $total);
        $stmt->execute();
        $order_id = $stmt->insert_id;

        foreach ($cart as $item) {
            $stmt_detail = $conn->prepare("INSERT INTO order_details (order_id, product_id, price, quantity, sub_total) VALUES (?, ?, ?, 1, ?)");
            $stmt_detail->bind_param("iidd", $order_id, $item['id'], $item['price'], $item['price']);
            $stmt_detail->execute();

            $recipe_sql = "SELECT item_id, unit_quantity FROM make_product WHERE product_id = ?";
            $res_recipe = $conn->prepare($recipe_sql);
            $res_recipe->bind_param("i", $item['id']);
            $res_recipe->execute();
            $recipe = $res_recipe->get_result();

            while ($ingred = $recipe->fetch_assoc()) {
                $update_inv = $conn->prepare("UPDATE inventoryitem SET current_stock = current_stock - ? WHERE item_id = ?");
                $update_inv->bind_param("di", $ingred['unit_quantity'], $ingred['item_id']);
                $update_inv->execute();
            }
        }

        $conn->commit();
        echo json_encode(["status" => "success", "message" => "Sale completed!"]);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(["status" => "error", "message" => $e->getMessage()]);
    }
}

if ($action == 'process_delivery') {
    $data = json_decode(file_get_contents('php://input'), true);
    foreach ($data['delivery'] as $item) {
        $stmt = $conn->prepare("UPDATE inventoryitem SET current_stock = current_stock + ? WHERE item_name = ?");
        $stmt->bind_param("is", $item['qty'], $item['item']);
        $stmt->execute();
    }
    echo json_encode(["status" => "success"]);
}

if ($action == 'process_waste') {
    $data = json_decode(file_get_contents('php://input'), true);
    $employee_id = 1;
    
    $conn->begin_transaction();
    try {
        foreach ($data['waste'] as $w) {
            $stmt_find = $conn->prepare("SELECT item_id FROM inventoryitem WHERE item_name = ?");
            $stmt_find->bind_param("s", $w['item']);
            $stmt_find->execute();
            $item_id = $stmt_find->get_result()->fetch_assoc()['item_id'];

            $stmt_inv = $conn->prepare("UPDATE inventoryitem SET current_stock = current_stock - ? WHERE item_id = ?");
            $stmt_inv->bind_param("ii", $w['qty'], $item_id);
            $stmt_inv->execute();

            $stmt_log = $conn->prepare("INSERT INTO stockout (date, employee_id, item_id, category_id, description) VALUES (NOW(), ?, ?, 1, ?)");
            $stmt_log->bind_param("iis", $employee_id, $item_id, $w['reason']);
            $stmt_log->execute();
        }
        $conn->commit();
        echo json_encode(["status" => "success"]);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(["status" => "error", "message" => $e->getMessage()]);
    }
}
?>