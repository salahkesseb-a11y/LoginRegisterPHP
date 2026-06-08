<?php
require 'connection.php';
session_start();

//Kick out if not logged in or not an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

//Delete Request for students
if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM students WHERE id = ?");
    $stmt->execute([$_GET['delete']]);
    header("Location: main.php");
    exit();
}

//Attendance
if (isset($_GET['present'])) {
    $stmt = $pdo->prepare("INSERT INTO attendance (student_id, status) VALUES (?, 'Present')");
    $stmt->execute([$_GET['present']]);
    header("Location: main.php");
    exit();
}
if (isset($_GET['absent'])) {
    $stmt = $pdo->prepare("INSERT INTO attendance (student_id, status) VALUES (?, 'Absent')");
    $stmt->execute([$_GET['absent']]);
    header("Location: main.php");
    exit();
}

//Add / Edit Student
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $course = trim($_POST['course']);
    $grade = trim($_POST['grade']);

    if (!empty($_POST['id'])) {
        // Update existing record
        $stmt = $pdo->prepare("UPDATE students SET name = ?, course = ?, grade = ? WHERE id = ?");
        $stmt->execute([$name, $course, $grade, $_POST['id']]);
    } else {
        // Insert new record
        $stmt = $pdo->prepare("INSERT INTO students (name, course, grade) VALUES (?, ?, ?)");
        $stmt->execute([$name, $course, $grade]);
    }
    header("Location: main.php");
    exit();
}

// Fetch all students for the table
$stmt = $pdo->query("SELECT * FROM students ORDER BY id DESC");
$students = $stmt->fetchAll();

// Fetch all users for the table
$stmt = $pdo->query("SELECT id, username, type, created_at FROM users ORDER BY id DESC");
$users = $stmt->fetchAll();

// Check if we are editing an existing student
$edit_student = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM students WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $edit_student = $stmt->fetch();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Dashboard</title>
<style>
body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f0f2f5; padding: 20px; color: #333; }
.container { max-width: 1000px; margin: auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
.header { display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #f0f2f5; padding-bottom: 10px; margin-bottom: 20px; }
.btn { padding: 8px 12px; border: none; border-radius: 4px; cursor: pointer; text-decoration: none; display: inline-block; color: white; }
.btn-danger { background: #dc3545; }
.btn-primary { background: #007bff; }
.btn-warning { background: #ffc107; color: black; }
.btn:hover { opacity: 0.9; }
form { background: #f8f9fa; padding: 15px; border-radius: 5px; margin-bottom: 20px; display: flex; gap: 10px; align-items: center; flex-wrap: wrap; }
input[type="text"], input[type="number"] { padding: 8px; border: 1px solid #ccc; border-radius: 4px; flex-grow: 1; }
table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
th { background-color: #f8f9fa; }
.actions a { margin-right: 10px; font-size: 14px; }
h3 { margin-top: 30px; }
</style>
</head>
<body>
<div class="container">
    <div class="header">
        <h2>Admin Dashboard</h2>
        <div>
            <span>Welcome, <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong> (Admin)</span>
            <a href="logout.php" class="btn btn-danger" style="margin-left: 15px;">Logout</a>
        </div>
    </div>

    <!-- Add / Edit Form -->
    <form method="POST" action="main.php">
        <h3><?php echo $edit_student ? "Edit Student" : "Add New Student"; ?></h3>
        <div style="width: 100%; display: flex; gap: 10px;">
            <input type="hidden" name="id" value="<?php echo $edit_student['id'] ?? ''; ?>">
            <input type="text" name="name" placeholder="Student Name" required value="<?php echo htmlspecialchars($edit_student['name'] ?? ''); ?>">
            <input type="text" name="course" placeholder="Course" required value="<?php echo htmlspecialchars($edit_student['course'] ?? ''); ?>">
            <input type="number" step="0.01" name="grade" placeholder="Grade (0-100)" required value="<?php echo htmlspecialchars($edit_student['grade'] ?? ''); ?>">
            <button type="submit" class="btn btn-primary"><?php echo $edit_student ? "Update Record" : "Save Record"; ?></button>
            <?php if ($edit_student): ?>
                <a href="main.php" class="btn btn-warning">Cancel</a>
            <?php endif; ?>
        </div>
    </form>

    <!-- Users Table -->
    <h3>All Users</h3>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Username</th>
                <th>Type</th>
                <th>Created At</th>
            </tr>
        </thead>
        <tbody>
        <?php if (count($users) > 0): ?>
            <?php foreach ($users as $user): ?>
            <tr>
                <td><?php echo $user['id']; ?></td>
                <td><?php echo htmlspecialchars($user['username']); ?></td>
                <td><?php echo $user['type']; ?></td>
                <td><?php echo $user['created_at']; ?></td>
            </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr><td colspan="4" style="text-align:center;color:#777;">No users found</td></tr>
        <?php endif; ?>
        </tbody>
    </table>

    <!-- Students Table -->
    <h3>Students</h3>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Course</th>
                <th>Grade</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php if (count($students) > 0): ?>
            <?php foreach ($students as $student): ?>
            <tr>
                <td><?php echo htmlspecialchars($student['id']); ?></td>
                <td><?php echo htmlspecialchars($student['name']); ?></td>
                <td><?php echo htmlspecialchars($student['course']); ?></td>
                <td><?php echo htmlspecialchars($student['grade']); ?></td>
                <td class="actions">
                    <a href="main.php?edit=<?php echo $student['id']; ?>" style="color: #007bff;">Edit</a>
                    <a href="main.php?delete=<?php echo $student['id']; ?>" style="color: #dc3545;" onclick="return confirm('Are you sure you want to delete this student?');">Delete</a>
                    <a href="main.php?present=<?php echo $student['id']; ?>">Present</a>
                    <a href="main.php?absent=<?php echo $student['id']; ?>">Absent</a>
                </td>
            </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr><td colspan="5" style="text-align:center;color:#777;">No students found</td></tr>
        <?php endif; ?>
        </tbody>
    </table>

    <!-- Attendance Table -->
    <h3>Attendance </h3>
    <table>
        <tr>
            <th>Student ID</th>
            <th>Status</th>
            <th>Date</th>
        </tr>
        <?php
        $stmt = $pdo->query("SELECT * FROM attendance ORDER BY id DESC");
        $attendance = $stmt->fetchAll();
        foreach ($attendance as $row):
        ?>
        <tr>
            <td><?php echo $row['student_id']; ?></td>
            <td><?php echo $row['status']; ?></td>
            <td><?php echo $row['date']; ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>
</body>
</html>