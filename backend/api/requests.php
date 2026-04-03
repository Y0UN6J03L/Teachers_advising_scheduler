<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT');
header('Access-Control-Allow-Headers: Content-Type');

session_start();
require '../config/database.php';

$user_id = $_SESSION['user_id'] ?? 0;

switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET':
if (isset($_GET['day']) && !empty($_GET['day'])) {
            $day = $_GET['day'];
            if ($_SESSION['role'] === 'teacher') {
                $stmt = $pdo->prepare("SELECT r.*, u.full_name as student_name FROM requests r JOIN users u ON r.student_id = u.id WHERE teacher_id = ? AND DATE(r.created_at) = CURDATE() AND DAYNAME(r.created_at) = ? ORDER BY created_at DESC");
                $stmt->execute([$user_id, $day]);
            } else {
                $stmt = $pdo->prepare("SELECT r.*, u.full_name as teacher_name FROM requests r JOIN users u ON r.teacher_id = u.id WHERE student_id = ? AND DATE(r.created_at) = CURDATE() AND DAYNAME(r.created_at) = ? ORDER BY created_at DESC");
                $stmt->execute([$user_id, $day]);
            }
        } else if ($_SESSION['role'] === 'teacher') {
            $stmt = $pdo->prepare("SELECT r.*, u.full_name as student_name FROM requests r JOIN users u ON r.student_id = u.id WHERE teacher_id = ? ORDER BY created_at DESC");
            $stmt->execute([$user_id]);
        } else {
            $stmt = $pdo->prepare("SELECT r.*, u.full_name as teacher_name FROM requests r JOIN users u ON r.teacher_id = u.id WHERE student_id = ? ORDER BY created_at DESC");
            $stmt->execute([$user_id]);
        }
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        break;

    case 'POST':
        if ($_SESSION['role'] !== 'student') {
            echo json_encode(['success' => false, 'message' => 'Students only']);
            break;
        }
        $input = json_decode(file_get_contents('php://input'), true);
        $stmt = $pdo->prepare("INSERT INTO requests (student_id, teacher_id, message) VALUES (?, ?, ?)");
        if ($stmt->execute([$user_id, $input['teacher_id'], $input['message']])) {
            echo json_encode(['success' => true]);
        }
        break;

    case 'PUT':
        if ($_SESSION['role'] !== 'teacher') {
            echo json_encode(['success' => false, 'message' => 'Teachers only']);
            break;
        }
        $input = json_decode(file_get_contents('php://input'), true);
        $stmt = $pdo->prepare("UPDATE requests SET status = ? WHERE id = ? AND teacher_id = ?");
        if ($stmt->execute([$input['status'], $input['id'], $user_id])) {
            echo json_encode(['success' => true]);
        }
        break;
}
?>

