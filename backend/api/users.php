<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

session_start();
require '../config/database.php';

// Admin only
if ($_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Admin access required']);
    exit;
}

switch ($_SERVER['REQUEST_METHOD']) {
case 'GET':
        $role = $_GET['role'] ?? null;
        if ($role) {
          $stmt = $pdo->prepare("SELECT id, email, full_name FROM users WHERE role = ? ORDER BY full_name");
          $stmt->execute([$role]);
        } else {
          $stmt = $pdo->query("SELECT id, email, full_name, role FROM users WHERE role != 'admin' ORDER BY created_at DESC");
        }
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        break;

    case 'POST':
        $input = json_decode(file_get_contents('php://input'), true);
        $email = trim($input['email']);
        $password = password_hash($input['password'], PASSWORD_BCRYPT);
        $full_name = trim($input['full_name']);
        $role = $input['role'];

        // Check duplicate email
        $check = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
        $check->execute([$email]);
        if ($check->fetchColumn() > 0) {
            echo json_encode(['success' => false, 'message' => 'Email already exists']);
            break 2;
        }

        $role = strtolower($role);
        $stmt = $pdo->prepare("INSERT INTO users (email, password, full_name, role) VALUES (?, ?, ?, ?)");
        if ($stmt->execute([$email, $password, $full_name, $role])) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'User creation failed']);
        }
        break;

    case 'PUT':
        $input = json_decode(file_get_contents('php://input'), true);
        $id = $input['id'];
        $email = trim($input['email']);
        $full_name = trim($input['full_name']);
        $role = $input['role'];

        $role = strtolower($role);
        $stmt = $pdo->prepare("UPDATE users SET email = ?, full_name = ?, role = ? WHERE id = ?");
        if ($stmt->execute([$email, $full_name, $role, $id])) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false]);
        }
        break;

    case 'DELETE':
        $input = json_decode(file_get_contents('php://input'), true);
        $id = $input['id'];
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ? AND role != 'admin'");
        if ($stmt->execute([$id])) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false]);
        }
        break;
}
?>

