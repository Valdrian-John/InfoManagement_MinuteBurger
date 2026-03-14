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

function require_employee_role() {
    $role = $_SESSION['role_label'] ?? '';
    if ($role !== 'Employee') {
        http_response_code(403);
        echo json_encode(["status" => "error", "message" => "This action is only for Employee role."]);
        exit;
    }
}

function require_owner_or_senior() {
    $role = $_SESSION['role_label'] ?? '';
    if ($role !== 'Owner' && $role !== 'Senior Staff') {
        http_response_code(403);
        echo json_encode(["status"=>"error","message"=>"Forbidden"]);
        exit;
    }
}

function safe_json() {
    $data = json_decode(file_get_contents('php://input'), true);
    return is_array($data) ? $data : [];
}

function get_stockout_category_id(mysqli $conn, string $reason): int {
    $enum = 'Missing';
    $r = strtolower($reason);

    if (str_contains($r, 'expired')) $enum = 'Expired';
    else if (str_contains($r, 'damaged')) $enum = 'Damaged';
    else if (str_contains($r, 'dropped')) $enum = 'Damaged';
    else if (str_contains($r, 'customer')) $enum = 'Missing';

    $stmt = $conn->prepare("SELECT category_id FROM category_stockout WHERE category = ? LIMIT 1");
    if ($stmt) {
        $stmt->bind_param("s", $enum);
        if ($stmt->execute()) {
            $row = $stmt->get_result()->fetch_assoc();
            if ($row && isset($row['category_id'])) return (int)$row['category_id'];
        }
    }
    return 1;
}

/* -------------------- PRODUCTS -------------------- */
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

/* -------------------- SALES -------------------- */
} elseif ($action === 'process_sale') {

    $employee_id = require_login();

    $data = safe_json();
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

            if ($product_id <= 0) throw new Exception("Invalid product id in cart.");

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

            $res_recipe = $conn->prepare("SELECT item_id, unit_quantity FROM make_product WHERE product_id = ?");
            if (!$res_recipe) throw new Exception($conn->error);

            $res_recipe->bind_param("i", $product_id);
            if (!$res_recipe->execute()) throw new Exception($res_recipe->error);

            $recipe = $res_recipe->get_result();

            while ($ingred = $recipe->fetch_assoc()) {
                $item_id = (int)$ingred['item_id'];
                $unit_qty = (float)$ingred['unit_quantity'];
                $deduct = $unit_qty * $qty;

                $update_inv = $conn->prepare("UPDATE inventoryitem SET current_stock = current_stock - ? WHERE item_id = ?");
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

/* -------------------- DELIVERY -------------------- */
} elseif ($action === 'process_delivery') {

    $data = safe_json();
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

/* -------------------- WASTE -------------------- */
} elseif ($action === 'process_waste') {

    $employee_id = require_login();

    $data = safe_json();
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

            $category_id = get_stockout_category_id($conn, $reason);

            $stmt_log = $conn->prepare("
                INSERT INTO stockout (date, employee_id, item_id, category_id, description)
                VALUES (CURDATE(), ?, ?, ?, ?)
            ");
            if (!$stmt_log) throw new Exception($conn->error);

            $stmt_log->bind_param("iiis", $employee_id, $item_id, $category_id, $reason);
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

/* -------------------- EMPLOYEES (OWNER/SENIOR) -------------------- */
} elseif ($action === 'get_employees') {

    require_login();

    $onlyActive = ($_GET['only_active'] ?? '0') === '1';

    if ($onlyActive) {
        $sql = "SELECT e.employee_id, e.name, s.status
                FROM employee e
                JOIN status s ON e.status_id = s.status_id
                WHERE e.status_id = 1
                AND e.employee_id NOT IN (1,2,3)
                ORDER BY e.name";
    } else {
        require_owner_or_senior();
        $sql = "SELECT e.employee_id, e.name, s.status, e.status_id
                FROM employee e
                JOIN status s ON e.status_id = s.status_id
                ORDER BY e.status_id ASC, e.name ASC";
    }

    $result = $conn->query($sql);
    if (!$result) {
        http_response_code(500);
        echo json_encode(["status" => "error", "message" => $conn->error]);
        exit;
    }

    echo json_encode(["status" => "success", "employees" => $result->fetch_all(MYSQLI_ASSOC)]);
    exit;

} elseif ($action === 'get_employee_form_options') {

    require_login();
    require_owner_or_senior();

    $g = $conn->query("SELECT gender_id, gender_title FROM gender ORDER BY gender_id");
    $h = $conn->query("SELECT hea_id, hea_title FROM highest_educational_attainment ORDER BY hea_id");
    $c = $conn->query("SELECT civil_status_id, civil_status FROM civil_status ORDER BY civil_status_id");

    if (!$g || !$h || !$c) {
        http_response_code(500);
        echo json_encode(["status"=>"error","message"=>$conn->error]);
        exit;
    }

    echo json_encode([
        "status" => "success",
        "genders" => $g->fetch_all(MYSQLI_ASSOC),
        "hea" => $h->fetch_all(MYSQLI_ASSOC),
        "civil_status" => $c->fetch_all(MYSQLI_ASSOC),
    ]);
    exit;

} elseif ($action === 'add_employee') {

    require_login();
    require_owner_or_senior();

    $data = safe_json();

    $name = trim($data['name'] ?? '');
    $gender_id = (int)($data['gender_id'] ?? 0);
    $dob = $data['date_of_birth'] ?? '';
    $hea_id = (int)($data['hea_id'] ?? 0);
    $civil_status_id = (int)($data['civil_status_id'] ?? 0);
    $contact_no = trim($data['contact_no'] ?? '');
    $address = trim($data['address'] ?? '');

    if ($name === '' || $gender_id <= 0 || $hea_id <= 0 || $civil_status_id <= 0 || $dob === '') {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "Please fill in all required fields."]);
        exit;
    }

    $role_id = 1;    // Trainee
    $status_id = 1;  // Active

    $stmt = $conn->prepare("
        INSERT INTO employee
        (name, gender_id, date_of_birth, hea_id, civil_status_id, contact_no, address, role_id, date_hired, status_id)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, CURDATE(), ?)
    ");
    if (!$stmt) {
        http_response_code(500);
        echo json_encode(["status" => "error", "message" => $conn->error]);
        exit;
    }

    $stmt->bind_param("sisisssii", $name, $gender_id, $dob, $hea_id, $civil_status_id, $contact_no, $address, $role_id, $status_id);

    if (!$stmt->execute()) {
        http_response_code(500);
        echo json_encode(["status"=>"error","message"=>$stmt->error]);
        exit;
    }

    echo json_encode(["status" => "success", "employee_id" => $stmt->insert_id]);
    exit;

} elseif ($action === 'set_employee_status') {

    require_login();
    require_owner_or_senior();

    $data = safe_json();
    $id = (int)($data['employee_id'] ?? 0);
    $status_id = (int)($data['status_id'] ?? 0); // 1=Active, 2=Inactive

    if ($id <= 0 || !in_array($status_id, [1, 2], true)) {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "Invalid employee or status."]);
        exit;
    }

    // protect Owner + Senior accounts from being disabled (avoid lockout)
    if (in_array($id, [1, 2], true)) {
        http_response_code(400);
        echo json_encode(["status"=>"error","message"=>"Owner/Senior accounts cannot be disabled."]);
        exit;
    }

    $stmt = $conn->prepare("UPDATE employee SET status_id=? WHERE employee_id=?");
    if (!$stmt) {
        http_response_code(500);
        echo json_encode(["status"=>"error","message"=>$conn->error]);
        exit;
    }

    $stmt->bind_param("ii", $status_id, $id);

    if (!$stmt->execute()) {
        http_response_code(500);
        echo json_encode(["status"=>"error","message"=>$stmt->error]);
        exit;
    }

    echo json_encode(["status" => "success"]);
    exit;

/* -------------------- ATTENDANCE: STATUS -------------------- */
} elseif ($action === 'clock_status') {

    require_login();
    require_employee_role();

    $active_id = (int)($_SESSION['active_employee_id'] ?? 0);

    if ($active_id <= 0) {
        echo json_encode(["status"=>"success","active"=>false]);
        exit;
    }

    $stmt = $conn->prepare("SELECT attendance_id, employee_name, clock_in, clock_out
                            FROM employee_attendance
                            WHERE employee_id = ?
                            ORDER BY clock_in DESC
                            LIMIT 1");
    if (!$stmt) {
        http_response_code(500);
        echo json_encode(["status" => "error", "message" => $conn->error]);
        exit;
    }

    $stmt->bind_param("i", $active_id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();

    $active = false;
    if ($row && empty($row['clock_out'])) $active = true;

    echo json_encode([
        "status" => "success",
        "active" => $active,
        "employee_name" => $row['employee_name'] ?? null,
        "clock_in" => $row['clock_in'] ?? null,
        "clock_out" => $row['clock_out'] ?? null
    ]);
    exit;

/* -------------------- ATTENDANCE: CLOCK IN (DROPDOWN) -------------------- */
} elseif ($action === 'clock_in') {

    require_login();
    require_employee_role();

    $data = safe_json();
    $selected_id = (int)($data['employee_id'] ?? 0);

    if ($selected_id <= 0) {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "Please select your name."]);
        exit;
    }

    // must be active employee
    $s = $conn->prepare("SELECT name FROM employee WHERE employee_id=? AND status_id=1 LIMIT 1");
    if (!$s) {
        http_response_code(500);
        echo json_encode(["status"=>"error","message"=>$conn->error]);
        exit;
    }
    $s->bind_param("i", $selected_id);
    $s->execute();
    $emp = $s->get_result()->fetch_assoc();

    if (!$emp) {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "Employee inactive or not found."]);
        exit;
    }

    // prevent double clock-in for that selected employee
    $check = $conn->prepare("SELECT attendance_id FROM employee_attendance
                             WHERE employee_id = ? AND clock_out IS NULL
                             ORDER BY clock_in DESC LIMIT 1");
    if (!$check) {
        http_response_code(500);
        echo json_encode(["status" => "error", "message" => $conn->error]);
        exit;
    }

    $check->bind_param("i", $selected_id);
    $check->execute();
    $open = $check->get_result()->fetch_assoc();

    $_SESSION['active_employee_id'] = $selected_id;
    $_SESSION['employee_name'] = $emp['name'];

    if ($open) {
        echo json_encode(["status" => "success", "message" => "Already clocked in.", "attendance_id" => (int)$open['attendance_id']]);
        exit;
    }

    $stmt = $conn->prepare("INSERT INTO employee_attendance (employee_id, employee_name, clock_in) VALUES (?, ?, NOW())");
    if (!$stmt) {
        http_response_code(500);
        echo json_encode(["status" => "error", "message" => $conn->error]);
        exit;
    }

    $stmt->bind_param("is", $selected_id, $emp['name']);
    $stmt->execute();

    echo json_encode(["status" => "success", "message" => "Clocked in!", "attendance_id" => $stmt->insert_id]);
    exit;

/* -------------------- ATTENDANCE: CLOCK OUT -------------------- */
} elseif ($action === 'clock_out') {

    require_login();
    require_employee_role();

    $active_id = (int)($_SESSION['active_employee_id'] ?? 0);
    if ($active_id <= 0) {
        http_response_code(400);
        echo json_encode(["status"=>"error","message"=>"No selected employee. Clock in first."]);
        exit;
    }

    $stmt = $conn->prepare("
        UPDATE employee_attendance
        SET clock_out = NOW()
        WHERE employee_id = ? AND clock_out IS NULL
        ORDER BY clock_in DESC
        LIMIT 1
    ");
    if (!$stmt) {
        http_response_code(500);
        echo json_encode(["status" => "error", "message" => $conn->error]);
        exit;
    }

    $stmt->bind_param("i", $active_id);
    $stmt->execute();

    if ($stmt->affected_rows <= 0) {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "No active clock-in found."]);
        exit;
    }

    echo json_encode(["status" => "success", "message" => "Clocked out!"]);
    exit;

} else {

    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Invalid action."]);
    exit;
}