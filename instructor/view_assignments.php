<?php
session_start();
include('config.php');

// Check if the user is logged in and is an instructor, if not then redirect to login page
if (!isset($_SESSION["id"]) || $_SESSION["usertype"] != 'instructor') {
    header("location: login.php");
    exit;
}

// Handle delete operation
if (isset($_GET['delete'])) {
    $assignment_id = intval($_GET['delete']);
    $sql = "DELETE FROM Assignments WHERE assignment_id = ?";
    if ($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "i", $assignment_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
    header("location: view_assignments.php");
    exit;
}

// Fetch assignments added by the instructor
$sql = "SELECT a.assignment_id, a.course_id, c.title AS course_title, a.title, a.description, a.assignment_file, a.due_date, a.date_created 
        FROM Assignments a
        JOIN courses c ON a.course_id = c.course_id
        WHERE c.instructor_id = ?";
if ($stmt = mysqli_prepare($conn, $sql)) {
    mysqli_stmt_bind_param($stmt, "i", $_SESSION["id"]);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $assignments = mysqli_fetch_all($result, MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);
}
mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>View Assignments</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="./css/style.css">
</head>

<body>
<?php include('navbar.php'); ?>
    <div class="container mt-5">
        <h2 class="text-center">Your Assignments</h2>
        <p class="text-center">Here you can view, edit, or delete your assignments.</p>
        <div class="row">
            <?php foreach ($assignments as $assignment): ?>
                <div class="col-md-4">
                    <div class="card mb-4">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($assignment['title']); ?></h5>
                            <p class="card-text"><strong>Course:</strong> <?php echo htmlspecialchars($assignment['course_title']); ?></p>
                            <p class="card-text"><?php echo htmlspecialchars($assignment['description']); ?></p>
                            <p class="card-text"><strong>Due Date:</strong> <?php echo htmlspecialchars($assignment['due_date']); ?></p>
                            <p class="card-text"><small class="text-muted">Created on <?php echo htmlspecialchars($assignment['date_created']); ?></small></p>
                            <?php if (!empty($assignment['assignment_file'])): ?>
                                <a href="<?php echo htmlspecialchars($assignment['assignment_file']); ?>" class="btn btn-primary" download>Download Assignment</a>
                            <?php endif; ?>
                            <a href="edit_assignment.php?assignment_id=<?php echo $assignment['assignment_id']; ?>" class="btn btn-secondary">Edit</a>
                            <a href="view_assignments.php?delete=<?php echo $assignment['assignment_id']; ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this assignment?');">Delete</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>

</html>
