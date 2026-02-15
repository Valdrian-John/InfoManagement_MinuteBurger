<?php
session_start();
header('Content-Type: application/json');

$USERS = [
  'Owner'  => ['admin',  1, 'Owner'],
  'Senior' => ['admin', 2, 'Senior Staff'],
];

$username = isset($_POST['username']) ? trim($_POST['username']) : '';
$password = isset($_POST['password']) ? (string)$_POST['password'] : '';

if ($username === '' || $password === '') {
  http_response_code(400);
  echo json_encode(['success' => false, 'message' => 'Username and password are required.']);
  exit;
}

if (!isset($USERS[$username]) || $USERS[$username][0] !== $password) {
  http_response_code(401);
  echo json_encode(['success' => false, 'message' => 'Invalid credentials.']);
  exit;
}

$_SESSION['employee_id'] = $USERS[$username][1];
$_SESSION['role_label']  = $USERS[$username][2];

echo json_encode(['success' => true, 'employee_id' => $_SESSION['employee_id'], 'role' => $_SESSION['role_label']]);