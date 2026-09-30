<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$current_user_id = $_SESSION['user_id'];

// هناال IP Business Tier واسم ملف API 
$apiURL = "http://192.168.100.10/it331_project/BL/api_task.php";

$error_message = '';
$success_message = '';

// إرسال طلب CRUD للـ Business Tier API
function callAPI($data) {
    global $apiURL;
    $ch = curl_init($apiURL);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    $response = curl_exec($ch);
    curl_close($ch);
    return json_decode($response, true);
}

// حذف مهمة
if (isset($_GET['delete_id'])) {
    $response = callAPI([
        'action' => 'delete',
        'task_id' => intval($_GET['delete_id']),
        'user_id' => $current_user_id
    ]);
    if (!empty($response['success'])) {
        header("Location: view_data.php?status=deleted");
        exit();
    } else {
        $error_message = $response['message'] ?? 'Failed to delete task.';
    }
}

// إكمال مهمة
if (isset($_GET['complete_id'])) {
    $response = callAPI([
        'action' => 'complete',
        'task_id' => intval($_GET['complete_id']),
        'user_id' => $current_user_id
    ]);
    if (!empty($response['success'])) {
        header("Location: view_data.php?status=completed");
        exit();
    } else {
        $error_message = $response['message'] ?? 'Failed to update task.';
    }
}

// إضافة مهمة جديدة
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['title'])) {
    $postData = [
        'action' => 'add',
        'user_id' => $current_user_id,
        'title' => trim($_POST['title']),
        'course' => trim($_POST['course']),
        'priority' => $_POST['priority'],
        'due_date' => $_POST['due_date'],
        'description' => trim($_POST['description']),
        'status' => 'Pending'
    ];
    $response = callAPI($postData);
    if (!empty($response['success'])) {
        header("Location: confirmation.php?task=saved");
        exit();
    } else {
        $error_message = $response['message'] ?? 'Failed to add task.';
    }
}

// جلب المهام الخاصة بالمستخدم
$response = callAPI([
    'action' => 'list',
    'user_id' => $current_user_id
]);

$tasks = $response['tasks'] ?? [];

?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Tasks • Student Task Tracker</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="navbar">
  <div class="inner container">
    <div class="brand"><span class="dot"></span> Student Task Tracker</div>
    <div class="stack">
      <a href="home.html">Home</a>
      <a href="logout.php">Logout</a>
      <a href="data_entry.php">New Task</a>
      <a href="view_data.php" class="active">Tasks</a>
    </div>
  </div>
</div>

<div class="container">
  <?php if (isset($_SESSION['first_name'])): ?>
    <h3 style="text-align:center; color:var(--primary); margin-bottom:20px;">Hey there, <?php echo htmlspecialchars($_SESSION['first_name']); ?>!</h3>
  <?php endif; ?>

  <?php if ($error_message): ?>
      <p style="color: red; text-align: center;"><?php echo htmlspecialchars($error_message); ?></p>
  <?php endif; ?>

  <?php if (isset($_GET['status'])): ?>
    <div style="text-align: center; margin-bottom: 20px;">
        <?php if ($_GET['status'] == 'deleted'): ?>
            <p style="color: var(--danger);">Task deleted successfully.</p>
        <?php elseif ($_GET['status'] == 'completed'): ?>
            <p style="color: var(--success);">Task marked as complete.</p>
        <?php endif; ?>
    </div>
  <?php endif; ?>

  <?php if (empty($tasks)): ?>
    <p style="text-align: center;">You have no tasks! <a href="data_entry.php">Add a new one</a>.</p>
  <?php else: ?>
  <div class="table-wrap">
  <table>
    <thead>
      <tr>
        <th>Title</th>
        <th>Course</th>
        <th>Due Date</th> 
        <th>Priority</th>
        <th>Status</th>

<th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($tasks as $task): 
          $pill_class = '';
          if ($task['status'] == 'Completed') $pill_class = 'success';
          elseif ($task['priority'] == 'High') $pill_class = 'danger';
          elseif ($task['priority'] == 'Medium') $pill_class = 'pending';
          elseif ($task['priority'] == 'Low') $pill_class = 'dd';
      ?>
        <tr>
          <td>
              <strong><?php echo htmlspecialchars($task['title']); ?></strong>
              <?php if (!empty($task['description'])): ?>
                <p class="small" style="color:var(--muted); margin-top:4px; max-width:300px;"><?php echo nl2br(htmlspecialchars($task['description'])); ?></p>
              <?php endif; ?>
          </td>
          <td><?php echo htmlspecialchars($task['course']); ?></td>
          <td><?php echo htmlspecialchars($task['due_date']); ?></td>
          <td><?php echo htmlspecialchars($task['priority']); ?></td>
          <td><span class="pill <?php echo $pill_class; ?>"><?php echo htmlspecialchars($task['status']); ?></span></td>
          <td>
            <a class="btn ghost" href="view_data.php?complete_id=<?php echo $task['task_id']; ?>">Complete</a>
            <a class="btn ghost" style="color:var(--danger)" href="view_data.php?delete_id=<?php echo $task['task_id']; ?>" onclick="return confirm('Are you sure you want to delete this task?');">Delete</a>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  </div>
  <?php endif; ?>
</div>
</body>
</html>