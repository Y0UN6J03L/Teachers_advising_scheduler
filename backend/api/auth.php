<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (isset($input['action'])) {
        switch ($input['action']) {
case 'register':
                $email = trim($input['email']);
                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    echo json_encode(['success' => false, 'message' => 'Invalid email']);
                    break;
                }
                $password = password_hash($input['password'], PASSWORD_BCRYPT);
                $full_name = trim($input['full_name']);
                $role = $input['role'];

 // Check if email exists
$check = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
$check->execute([$email]);
if ($check->fetchColumn() > 0) {
    echo json_encode(['success' => false, 'message' => 'Email already registered']);
    break;
}

$role = strtolower($role);  // Normalize for ENUM

try {
    $stmt = $pdo->prepare("INSERT INTO users (email, password, full_name, role) VALUES (?, ?, ?, ?)");
    $stmt->execute([$email, $password, $full_name, $role]);
    echo json_encode(['success' => true, 'message' => 'User registered']);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Registration failed: ' . $e->getMessage()]);
}
                break;

            case 'login':
                $email = trim($input['email']);
                $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
                $stmt->execute([$email]);
                $user = $stmt->fetch();

                if ($user && password_verify($input['password'], $user['password'])) {
                    session_start();
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['role'] = $user['role'];
                    $_SESSION['full_name'] = $user['full_name'];
                    echo json_encode(['success' => true, 'role' => $user['role']]);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Invalid credentials']);
                }
                break;
        }
    }
}
?>

