    <?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

session_start();
require '../config/database.php';

$user_id = $_SESSION['user_id'] ?? 0;

switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET':
if (isset($_GET['day']) && !empty($_GET['day'])) {
            $day = $_GET['day'];
            if ($_SESSION['role'] === 'admin') {
                if (isset($_GET['user_id']) && !empty($_GET['user_id'])) {
                    $user_id = (int)$_GET['user_id'];
                    $stmt = $pdo->prepare("SELECT * FROM schedules WHERE teacher_id = ? AND day = ? ORDER BY time_start");
                    $stmt->execute([$user_id, $day]);
                } else {
                    $stmt = $pdo->prepare("SELECT * FROM schedules WHERE day = ? ORDER BY time_start");
                    $stmt->execute([$day]);
                }
            } else {
                $stmt = $pdo->prepare("SELECT * FROM schedules WHERE teacher_id = ? AND day = ? ORDER BY time_start");
                $stmt->execute([$user_id, $day]);
            }
        } else if ($_SESSION['role'] === 'admin') {
            if (isset($_GET['user_id']) && !empty($_GET['user_id'])) {
                $user_id = (int)$_GET['user_id'];
                $stmt = $pdo->prepare("SELECT s.*, u.full_name FROM schedules s JOIN users u ON s.teacher_id = u.id WHERE s.teacher_id = ? ORDER BY s.day, s.time_start");
                $stmt->execute([$user_id]);
            } else {
                $stmt = $pdo->query("SELECT s.*, u.full_name FROM schedules s JOIN users u ON s.teacher_id = u.id ORDER BY s.created_at DESC");
            }
        } else {
            $stmt = $pdo->prepare("SELECT * FROM schedules WHERE teacher_id = ? ORDER BY day, time_start");
            $stmt->execute([$user_id]);
        }
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        break;

    case 'POST':
        if ($_SESSION['role'] !== 'teacher') {
            echo json_encode(['success' => false, 'message' => 'Teachers only']);
            break;
        }
        $input = json_decode(file_get_contents('php://input'), true);
        $stmt = $pdo->prepare("INSERT INTO schedules (teacher_id, day, time_start, time_end, description) VALUES (?, ?, ?, ?, ?)");
        if ($stmt->execute([$user_id, $input['day'], $input['time_start'], $input['time_end'], $input['description']])) {
            echo json_encode(['success' => true]);
        }
        break;

    case 'PUT':
        $input = json_decode(file_get_contents('php://input'), true);
        $stmt = $pdo->prepare("UPDATE schedules SET day = ?, time_start = ?, time_end = ?, description = ? WHERE id = ? AND teacher_id = ?");
        if ($stmt->execute([$input['day'], $input['time_start'], $input['time_end'], $input['description'], $input['id'], $user_id])) {
            echo json_encode(['success' => true]);
        }
        break;

    case 'DELETE':
        $input = json_decode(file_get_contents('php://input'), true);
        $stmt = $pdo->prepare("DELETE FROM schedules WHERE id = ? AND teacher_id = ?");
        if ($stmt->execute([$input['id'], $user_id])) {
            echo json_encode(['success' => true]);
        }
        break;
}
?>

