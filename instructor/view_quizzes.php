<?php
session_start();
include('config.php');

// Check if the user is logged in and is an instructor, if not then redirect to login page
if (!isset($_SESSION["id"]) || $_SESSION["usertype"] != 'instructor') {
    header("location: login.php");
    exit;
}

// Function to delete a quiz
function deleteQuiz($conn, $quiz_id) {
    $sql = "DELETE FROM Quizzes WHERE quiz_id = ?";
    if ($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "i", $quiz_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
}

// Check if quiz deletion is requested
if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET["action"]) && $_GET["action"] == "delete" && isset($_GET["quiz_id"])) {
    $quiz_id = intval($_GET["quiz_id"]);
    deleteQuiz($conn, $quiz_id);
    // Redirect back to view_quizzes.php after deletion
    header("location: view_quizzes.php");
    exit;
}

// Fetch quizzes for the instructor's courses
$sql = "SELECT q.quiz_id, q.title, c.title AS course_title 
        FROM Quizzes q 
        INNER JOIN courses c ON q.course_id = c.course_id 
        WHERE c.instructor_id = ?";
if ($stmt = mysqli_prepare($conn, $sql)) {
    mysqli_stmt_bind_param($stmt, "i", $_SESSION["id"]);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $quizzes = mysqli_fetch_all($result, MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);
}
mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>View Quizzes</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="./css/style.css">
</head>

<body>
<?php include('navbar.php'); ?>
    <div class="container mt-5">
        <h2 class="text-center">View Quizzes</h2>
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Quiz Title</th>
                        <th>Course Title</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($quizzes as $quiz): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($quiz['title']); ?></td>
                            <td><?php echo htmlspecialchars($quiz['course_title']); ?></td>
                            <td>
                                <a href="edit_quiz.php?quiz_id=<?php echo $quiz['quiz_id']; ?>" class="btn btn-primary">Edit</a>
                                <a href="view_quizzes.php?action=delete&quiz_id=<?php echo $quiz['quiz_id']; ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this quiz?')">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>

</html>
