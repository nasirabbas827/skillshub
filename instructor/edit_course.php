<?php
session_start();
include('config.php');

// Check if the user is logged in and is an instructor, if not then redirect to login page
if (!isset($_SESSION["id"]) || $_SESSION["usertype"] != 'instructor') {
    header("location: login.php");
    exit;
}

// Get course_id from URL
$course_id = isset($_GET['course_id']) ? intval($_GET['course_id']) : 0;

// Fetch the course details
$sql = "SELECT picture, title, description FROM courses WHERE course_id = ? AND instructor_id = ?";
if ($stmt = mysqli_prepare($conn, $sql)) {
    mysqli_stmt_bind_param($stmt, "ii", $course_id, $_SESSION["id"]);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $course = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $picture = $_POST["current_picture"];
    if (!empty($_FILES["picture"]["name"])) {
        $target_dir = "uploads/";
        $target_file = $target_dir . basename($_FILES["picture"]["name"]);
        if (move_uploaded_file($_FILES["picture"]["tmp_name"], $target_file)) {
            $picture = $target_file;
        }
    }
    $title = $_POST["title"];
    $description = $_POST["description"];
    $sql = "UPDATE courses SET picture = ?, title = ?, description = ? WHERE course_id = ? AND instructor_id = ?";
    if ($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "sssii", $picture, $title, $description, $course_id, $_SESSION["id"]);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
    header("location: view_courses.php");
    exit;
}
mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Edit Course</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="./css/style.css">
</head>

<body>
<?php include('navbar.php'); ?>
    <div class="container mt-5">
        <h2 class="text-center">Edit Course</h2>
        <p class="text-center">Please edit the details of the course.</p>

        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . "?course_id=" . $course_id; ?>" method="post" enctype="multipart/form-data">
            <div class="form-group">
                <label>Course Picture</label>
                <input type="file" name="picture" class="form-control">
                <input type="hidden" name="current_picture" value="<?php echo htmlspecialchars($course['picture']); ?>">
                <?php if ($course['picture']): ?>
                    <img src="<?php echo htmlspecialchars($course['picture']); ?>" class="img-thumbnail mt-2" width="150" alt="Current Course Picture">
                <?php endif; ?>
            </div>
            <div class="form-group">
                <label>Title</label>
                <input type="text" name="title" class="form-control" value="<?php echo htmlspecialchars($course['title']); ?>">
            </div>
            <div class="form-group">
                <label>Description</label>
                <textarea name="description" class="form-control"><?php echo htmlspecialchars($course['description']); ?></textarea>
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
