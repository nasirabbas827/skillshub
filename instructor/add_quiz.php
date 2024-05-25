<?php
session_start();
include('config.php');

// Check if the user is logged in and is an instructor, if not then redirect to login page
if (!isset($_SESSION["id"]) || $_SESSION["usertype"] != 'instructor') {
    header("location: login.php");
    exit;
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $course_id = intval($_POST["course_id"]);
    $title = $_POST["title"];
    $option_one = $_POST["option_one"];
    $option_two = $_POST["option_two"];
    $option_three = $_POST["option_three"];
    $option_four = $_POST["option_four"];
    $correct_option = intval($_POST["correct_option"]);

    $sql = "INSERT INTO Quizzes (course_id, title, option_one, option_two, option_three, option_four, correct_option) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    if ($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "isssssi", $course_id, $title, $option_one, $option_two, $option_three, $option_four, $correct_option);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }

    header("location: view_quizzes.php");
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
    <title>Add Quiz</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="./css/style.css">
</head>

<body>
<?php include('navbar.php'); ?>
    <div class="container mt-5">
        <h2 class="text-center">Add Quiz</h2>
        <p class="text-center">Please fill in the details to add a quiz to your course.</p>

        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="form-group">
                <label>Course</label>
                <select name="course_id" class="form-control" required>
                    <?php foreach ($courses as $course): ?>
                        <option value="<?php echo $course['course_id']; ?>"><?php echo htmlspecialchars($course['title']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Title</label>
                <input type="text" name="title" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Option One</label>
                <input type="text" name="option_one" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Option Two</label>
                <input type="text" name="option_two" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Option Three</label>
                <input type="text" name="option_three" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Option Four</label>
                <input type="text" name="option_four" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Correct Option (1, 2, 3, or 4)</label>
                <input type="number" name="correct_option" class="form-control" min="1" max="4" required>
            </div>
            <div class="form-group text-center">
                <input type="submit" class="btn btn-primary" value="Add Quiz">
                <a class="btn btn-outline-dark" href="view_quizzes.php">View Quizzes</a>
            </div>
        </form>
    </div>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>

</html>
