<?php
session_start();
include('config.php');

// Check if the user is logged in and is an instructor, if not then redirect to login page
if (!isset($_SESSION["id"]) || $_SESSION["usertype"] != 'instructor') {
    header("location: login.php");
    exit;
}

// Get assignment_id from URL
$assignment_id = isset($_GET['assignment_id']) ? intval($_GET['assignment_id']) : 0;

// Fetch the assignment details
$sql = "SELECT course_id, title, description, assignment_file, due_date FROM Assignments WHERE assignment_id = ?";
if ($stmt = mysqli_prepare($conn, $sql)) {
    mysqli_stmt_bind_param($stmt, "i", $assignment_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $assignment = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $course_id = intval($_POST["course_id"]);
    $title = $_POST["title"];
    $description = $_POST["description"];
    $due_date = $_POST["due_date"];
    $assignment_file = $_POST["current_assignment_file"];

    if (!empty($_FILES["assignment_file"]["name"])) {
        $target_dir = "uploads/assignments/";
        $target_file = $target_dir . basename($_FILES["assignment_file"]["name"]);
        if (move_uploaded_file($_FILES["assignment_file"]["tmp_name"], $target_file)) {
            $assignment_file = $target_file;
        }
    }

    $sql = "UPDATE Assignments SET course_id = ?, title = ?, description = ?, assignment_file = ?, due_date = ? WHERE assignment_id = ?";
    if ($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "issssi", $course_id, $title, $description, $assignment_file, $due_date, $assignment_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }

    header("location: view_assignments.php");
    exit;
}

// Fetch instructor's courses
$sql = "SELECT course_id, title FROM courses WHERE instructor_id = ?";
if ($stmt = mysqli_prepare($conn, $sql)) {
    mysqli_stmt_bind_param($stmt, "i", $_SESSION["id"]);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $courses = mysqli_fetch_all($result, MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);
}
mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Edit Assignment</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="./css/style.css">
</head>

<body>
<?php include('navbar.php'); ?>
    <div class="container mt-5">
        <h2 class="text-center">Edit Assignment</h2>
        <p class="text-center">Please edit the details of the assignment.</p>

        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . "?assignment_id=" . $assignment_id; ?>" method="post" enctype="multipart/form-data">
            <div class="form-group">
                <label>Course</label>
                <select name="course_id" class="form-control" required>
                    <?php foreach ($courses as $course): ?>
                        <option value="<?php echo $course['course_id']; ?>" <?php echo ($assignment['course_id'] == $course['course_id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($course['title']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Title</label>
                <input type="text" name="title" class="form-control" value="<?php echo htmlspecialchars($assignment['title']); ?>" required>
            </div>
            <div class="form-group">
                <label>Description</label>
                <textarea name="description" class="form-control" required><?php echo htmlspecialchars($assignment['description']); ?></textarea>
            </div>
            <div class="form-group">
                <label>Assignment File</label>
                <input type="file" name="assignment_file" class="form-control">
                <input type="hidden" name="current_assignment_file" value="<?php echo htmlspecialchars($assignment['assignment_file']); ?>">
            </div>
            <div class="form-group">
                <label>Due Date</label>
                <input type="date" name="due_date" class="form-control" value="<?php echo htmlspecialchars($assignment['due_date']); ?>" required>
            </div>
            <div class="form-group text-center">
                <input type="submit" class="btn btn-primary" value="Save Changes">
            </div>
        </form>
    </div>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>

</html>
