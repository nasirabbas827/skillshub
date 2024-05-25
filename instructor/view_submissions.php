<?php
session_start();
include('config.php');

// Check if the user is logged in and is an instructor, if not then redirect to login page
if (!isset($_SESSION["id"]) || $_SESSION["usertype"] != 'instructor') {
    header("location: login.php");
    exit;
}

// Fetch submitted assignments for the instructor's courses
$sql = "SELECT s.submission_id, a.title AS assignment_title, u.username AS student_username, s.submission_file, s.submission_date, s.grade, s.feedback 
        FROM Submissions s
        INNER JOIN Assignments a ON s.assignment_id = a.assignment_id
        INNER JOIN users u ON s.user_id = u.id
        INNER JOIN courses c ON a.course_id = c.course_id
        WHERE c.instructor_id = ?";
if ($stmt = mysqli_prepare($conn, $sql)) {
    mysqli_stmt_bind_param($stmt, "i", $_SESSION["id"]);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $submissions = mysqli_fetch_all($result, MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);
}

// Function to update grade and feedback for a submission
function updateSubmission($conn, $submission_id, $grade, $feedback) {
    $sql = "UPDATE Submissions SET grade = ?, feedback = ? WHERE submission_id = ?";
    if ($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "ssi", $grade, $feedback, $submission_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>View Submitted Assignments</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="./css/style.css">
</head>

<body>
<?php include('navbar.php'); ?>
    <div class="container mt-5">
        <h2 class="text-center">View Submitted Assignments</h2>
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Submission ID</th>
                        <th>Assignment Title</th>
                        <th>Student Username</th>
                        <th>Submission File</th>
                        <th>Submission Date</th>
                        <th>Grade</th>
                        <th>Feedback</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($submissions as $submission): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($submission['submission_id']); ?></td>
                            <td><?php echo htmlspecialchars($submission['assignment_title']); ?></td>
                            <td><?php echo htmlspecialchars($submission['student_username']); ?></td>
                            <td><a href="../learner/<?php echo htmlspecialchars($submission['submission_file']); ?>" download>Download File</a></td>
                            <td><?php echo htmlspecialchars($submission['submission_date']); ?></td>
                            <td>
                                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                                    <input type="hidden" name="submission_id" value="<?php echo $submission['submission_id']; ?>">
                                    <input type="text" name="grade" value="<?php echo $submission['grade']; ?>" placeholder="Grade">
                            </td>
                            <td>
                                    <textarea name="feedback" rows="2" cols="30" placeholder="Feedback"><?php echo $submission['feedback']; ?></textarea>
                            </td>
                            <td>
                                    <button type="submit" name="update" class="btn btn-primary">Update</button>
                                </form>
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

<?php
// Process form submission to update grade and feedback
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["update"])) {
    $submission_id = intval($_POST["submission_id"]);
    $grade = $_POST["grade"];
    $feedback = $_POST["feedback"];
    updateSubmission($conn, $submission_id, $grade, $feedback);
    // Refresh the page to reflect changes
    header("location: view_submissions.php");
    exit;
}
?>
