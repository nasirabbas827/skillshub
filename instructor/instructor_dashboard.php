<?php
session_start();
include('config.php');

// Check if the user is logged in as an instructor
if (!isset($_SESSION["id"]) || $_SESSION["usertype"] != 'instructor') {
    header("location: login.php");
    exit;
}

// Fetch total added courses for the instructor
$instructorId = $_SESSION["id"];
$sqlTotalCourses = "SELECT COUNT(*) AS total_courses FROM courses WHERE instructor_id = ?";
$stmtTotalCourses = mysqli_prepare($conn, $sqlTotalCourses);
mysqli_stmt_bind_param($stmtTotalCourses, "i", $instructorId);
mysqli_stmt_execute($stmtTotalCourses);
$resultTotalCourses = mysqli_stmt_get_result($stmtTotalCourses);
$totalCourses = mysqli_fetch_assoc($resultTotalCourses)['total_courses'];
mysqli_stmt_close($stmtTotalCourses);

// Fetch total enrollments for the instructor's courses
$sqlTotalEnrollments = "SELECT COUNT(*) AS total_enrollments FROM enrollments WHERE course_id IN (SELECT course_id FROM courses WHERE instructor_id = ?)";
$stmtTotalEnrollments = mysqli_prepare($conn, $sqlTotalEnrollments);
mysqli_stmt_bind_param($stmtTotalEnrollments, "i", $instructorId);
mysqli_stmt_execute($stmtTotalEnrollments);
$resultTotalEnrollments = mysqli_stmt_get_result($stmtTotalEnrollments);
$totalEnrollments = mysqli_fetch_assoc($resultTotalEnrollments)['total_enrollments'];
mysqli_stmt_close($stmtTotalEnrollments);

mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Instructor Dashboard</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="./css/style.css">
</head>

<body>
    <?php include('navbar.php'); ?>
    <div class="container mt-5">
        <h2 class="text-center">Instructor Dashboard</h2>
        <p class="text-center">Welcome, <?php echo htmlspecialchars($_SESSION["email"]); ?>!</p>
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Total Added Courses</h5>
                        <p class="card-text"><?php echo $totalCourses; ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Total Enrollments</h5>
                        <p class="card-text"><?php echo $totalEnrollments; ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>

</html>
