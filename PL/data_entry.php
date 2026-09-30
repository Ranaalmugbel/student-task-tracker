<?php
session_start();

if (!isset($_SESSION['user_id'])) {
  header("Location: login.php");
  exit();
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>New Task • Student Task Tracker</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <div class="navbar">
    <div class="inner container">
      <div class="brand"><span class="dot"></span> Student Task Tracker</div>
      <div class="stack">
        <a href="home.html">Home</a>
        <a href="logout.php">Logout</a>
        <a href="data_entry.php" class="active">New Task</a>
        <a href="view_data.php">Tasks</a>

      </div>
    </div>
  </div>

  <div class="container">
     <?php if (isset($_SESSION['first_name'])): ?>
    <h3 style="text-align:center; color:var(--primary); margin-bottom:20px;">Hey there, <?php echo htmlspecialchars($_SESSION['first_name']); ?>!</h3>
  <?php endif; ?>
    
    <div class="card" style="max-width:720px; margin:0 auto;">
      <h2>Add a Task</h2>
      <form id="taskForm" action="http://192.168.100.10/it331_project/BL/api_task.php" method="post" novalidate>
          <input type="hidden" name="user_id" value="<?php echo $_SESSION['user_id']; ?>">
          <input type="hidden" name="action" value="add">
        <div class="row-2">
          <input class="input" id="task_title" placeholder="Task title (e.g., IT331 Phase 2 )" name="title" required minlength="3" maxlength="150">
          <input class="input" id="course" placeholder="Course (e.g., IT331)" name="course" required maxlength="20">
        </div>

        <select id="priority" name="priority" required>
          <option value="" disabled selected>Priority</option>
          <option>High</option>
          <option>Medium</option>
          <option>Low</option>
        </select>

        <input class="input" id="due_date" type="date" name="due_date" required>
        <textarea class="input" id="description" name="description" placeholder="Description (optional)"></textarea>

        <div class="actions">
          <button class="btn" type="submit">Add Task</button>
        </div>
      </form>
    </div>
  </div>

<script>
document.addEventListener("DOMContentLoaded", function () {
  const form = document.getElementById("taskForm");
  if (!form) return;

  const title = document.getElementById("task_title");
  const course = document.getElementById("course");
  const priority = document.getElementById("priority");
  const dueDateInput = document.getElementById("due_date");

  form.addEventListener("submit", function (e) {
    let valid = true;
    [title, course, priority, dueDateInput].forEach(f => {
      if (f) f.style.borderColor = "";
    });

    if (title.value.trim().length < 3) {
      title.style.borderColor = "#c81e1e";
      valid = false;
    }

    if (course.value.trim() === "") {
      course.style.borderColor = "#c81e1e";
      valid = false;
    }

    if (!priority.value) {
      priority.style.borderColor = "#c81e1e";
      valid = false;
    }

    if (!dueDateInput.value) {
      dueDateInput.style.borderColor = "#c81e1e";
      valid = false;
    } else {
      const dueDate = new Date(dueDateInput.value);
      const today = new Date();
      today.setHours(0, 0, 0, 0);
      if (dueDate < today) {
        dueDateInput.style.borderColor = "#c81e1e";
        alert("Due date cannot be in the past.");
        valid = false;
      }
    }

    if (!valid) {
      e.preventDefault();
    }
  });
});
</script>

</body>
</html>
