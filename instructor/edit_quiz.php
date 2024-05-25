<?php
session_start();
include('config.php');

// Check if the user is logged in and is an instructor, if not then redirect to login page
if (!isset($_SESSION["id"]) || $_SESSION["usertype"] != 'instructor') {
    header("location: login.php");
    exit;
}

// Fetch quiz details based on the provided quiz_id
if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET["quiz_id"])) {
    $quiz_id = intval($_GET["quiz_id"]);
    $sql = "SELECT * FROM Quizzes WHERE quiz_id = ?";
    if ($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "i", $quiz_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $quiz = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
    }
}

// Handle form submission to update quiz details
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["update_quiz"])) {
    $quiz_id = intval($_POST["quiz_id"]);
    $title = $_POST["title"];
    $option_one = $_POST["option_one"];
    $option_two = $_POST["option_two"];
    $option_three = $_POST["option_three"];
    $option_four = $_POST["option_four"];
    $correct_option = intval($_POST["correct_option"]);

    $sql = "UPDATE Quizzes SET title = ?, option_one = ?, option_two = ?, option_three = ?, option_four = ?, correct_option = ? WHERE quiz_id = ?";
    if ($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "sssssii", $title, $option_one, $option_two, $option_three, $option_four, $correct_option, $quiz_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }

    // Redirect back to view_quizzes.php after updating the quiz
    header("location: view_quizzes.php");
    exit;
}

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Edit Quiz</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="./css/style.css">
</head>

<body>
<?php include('navbar.php'); ?>
    <div class="container mt-5">
        <h2 class="text-center">Edit Quiz</h2>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <input type="hidden" name="quiz_id" value="<?php echo $quiz['quiz_id']; ?>">
            <div class="form-group">
                <label>Title</label>
                <input type="text" name="title" class="form-control" value="<?php echo htmlspecialchars($quiz['title']); ?>" required>
            </div>
            <div class="form-group">
                <label>Option One</label>
                <input type="text" name="option_one" class="form-control" value="<?php echo htmlspecialchars($quiz['option_one']); ?>" required>
            </div>
            <div class="form-group">
                <label>Option Two</label>
                <input type="text" name="option_two" class="form-control" value="<?php echo htmlspecialchars($quiz['option_two']); ?>" required>
            </div>
            <div class="form-group">
                <label>Option Three</label>
                <input type="text" name="option_three" class="form-control" value="<?php echo htmlspecialchars($quiz['option_three']); ?>" required>
            </div>
            <div class="form-group">
                <label>Option Four</label>
                <input type="text" name="option_four" class="form-control" value="<?php echo htmlspecialchars($quiz['option_four']); ?>" required>
            </div>
            <div class="form-group">
                <label>Correct Option (1, 2, 3, or 4)</label>
                <input type="number" name="correct_option" class="form-control" value="<?php echo $quiz['correct_option']; ?>" min="1" max="4" required>
            </div>
            <div class="form-group text-center">
                <button type="submit" name="update_quiz" class="btn btn-primary">Update Quiz</button>
            </div>
        </form>
    </div>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>

</html>
