<?php
session_start();
include('config.php');

// Check if the user is logged in and is an instructor, if not then redirect to login page
if (!isset($_SESSION["id"]) || $_SESSION["usertype"] != 'instructor') {
    header("location: login.php");
    exit;
}

// Fetch quiz results for the instructor's courses
$sql = "SELECT qr.result_id, u.username AS learner_username, c.title AS course_title, qr.total_questions, qr.correct_answers, qr.score, qr.created_at
        FROM Quiz_Results qr
        INNER JOIN users u ON qr.learner_id = u.id
        INNER JOIN courses c ON qr.course_id = c.course_id
        WHERE c.instructor_id = ?";
if ($stmt = mysqli_prepare($conn, $sql)) {
    mysqli_stmt_bind_param($stmt, "i", $_SESSION["id"]);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $quiz_results = mysqli_fetch_all($result, MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);
}
mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Quiz Results</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="./css/style.css">
</head>
<body>
<?php include('navbar.php'); ?>
<div class="container mt-5">
    <h2 class="text-center">View Quiz Results</h2>
    <div class="table-responsive">
        <table class="table table-bordered">
            <thead>
            <tr>
                <th>Result ID</th>
                <th>Learner Username</th>
                <th>Course Title</th>
                <th>Total Questions</th>
                <th>Correct Answers</th>
                <th>Score</th>
                <th>Created At</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($quiz_results as $result): ?>
                <tr>
                    <td><?php echo htmlspecialchars($result['result_id']); ?></td>
                    <td><?php echo htmlspecialchars($result['learner_username']); ?></td>
                    <td><?php echo htmlspecialchars($result['course_title']); ?></td>
                    <td><?php echo htmlspecialchars($result['total_questions']); ?></td>
                    <td><?php echo htmlspecialchars($result['correct_answers']); ?></td>
                    <td><?php echo htmlspecialchars($result['score']); ?></td>
                    <td><?php echo htmlspecialchars($result['created_at']); ?></td>
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
