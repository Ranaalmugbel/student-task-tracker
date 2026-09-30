<?php
/**
 * Business Layer API for Tasks
 * Handles Add / Delete / Complete / List operations
 */

header('Content-Type: application/json');

// DB Config
$servername = "192.168.100.10";
$username   = "remote_user";
$password   = "Aa123";
$dbname     = "tasksdb";
$port       = 3306;

// Connect to DB
$conn = new mysqli($servername, $username, $password, $dbname, $port);
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed.']);
    exit();
}

// Read POST data
$action = $_POST['action'] ?? '';
$user_id = intval($_POST['user_id'] ?? 0);

// Validate user_id
if ($user_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid user ID.']);
    exit();
}

// Helper function to sanitize input
function sanitize($str) {
    return htmlspecialchars(trim($str));
}

// ====== CRUD Operations ======
switch ($action) {

    // Add Task
    case 'add':
        $title       = sanitize($_POST['title'] ?? '');
        $course      = sanitize($_POST['course'] ?? '');
        $priority    = sanitize($_POST['priority'] ?? '');
        $due_date    = sanitize($_POST['due_date'] ?? '');
        $description = sanitize($_POST['description'] ?? '');
        $status      = sanitize($_POST['status'] ?? 'Pending');

        if ($title === '' || $course === '' || $priority === '' || $due_date === '') {
            echo json_encode(['success' => false, 'message' => 'Missing required fields.']);
            exit();
        }

        $stmt = $conn->prepare("INSERT INTO tasks (user_id, title, course, priority, due_date, description, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("issssss", $user_id, $title, $course, $priority, $due_date, $description, $status);

       if ($stmt->execute()) {
    $stmt->close();
    // إعادة التوجيه للبرزنتيشن لاير بعد الإضافة
    header("Location: ../PL/view_data.php?status=added");
    exit();
} else {
    $stmt->close();
    echo "Error: " . $stmt->error;
}

    // Delete Task
    case 'delete':
        $task_id = intval($_POST['task_id'] ?? 0);
        if ($task_id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid task ID.']);
            exit();
        }

        $stmt = $conn->prepare("DELETE FROM tasks WHERE task_id = ? AND user_id = ?");
        $stmt->bind_param("ii", $task_id, $user_id);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Task not found or already deleted.']);
        }
        $stmt->close();
        break;

    // Complete Task
    case 'complete':
        $task_id = intval($_POST['task_id'] ?? 0);
        if ($task_id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid task ID.']);
            exit();
        }

        $stmt = $conn->prepare("UPDATE tasks SET status = 'Completed' WHERE task_id = ? AND user_id = ?");
        $stmt->bind_param("ii", $task_id, $user_id);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Task not found or already completed.']);
        }
        $stmt->close();
        break;

    // List Tasks
    case 'list':
        $tasks = [];
        $stmt = $conn->prepare("SELECT task_id, title, course, priority, due_date, description, status FROM tasks WHERE user_id = ? ORDER BY due_date ASC");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $tasks[] = $row;
        }
        $stmt->close();
        echo json_encode(['success' => true, 'tasks' => $tasks]);
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action.']);
        break;
}

$conn->close();