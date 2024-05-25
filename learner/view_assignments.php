<?php
session_start();

// Check if the user is logged in, if not then redirect to login page
if (!isset($_SESSION["id"]) || $_SESSION["usertype"] != 'learner') {
    header("location: login.php");
    exit;
}

// Database connection
require_once 'config.php';

// Get course ID from URL
$course_id = isset($_GET['course_id']) ? $_GET['course_id'] : 0;

// Handle assignment submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $assignment_id = $_POST['assignment_id'];
    $user_id = $_SESSION['id'];
    $target_dir = "uploads/";
    $target_file = $target_dir . basename($_FILES["submission_file"]["name"]);
    move_uploaded_file($_FILES["submission_file"]["tmp_name"], $target_file);

    $sql = "INSERT INTO Submissions (assignment_id, user_id, submission_file) VALUES (?, ?, ?)";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("iis", $assignment_id, $user_id, $target_file);
        $stmt->execute();
        $stmt->close();
    }
}

// Fetch assignments for the course
$sql = "SELECT * FROM Assignments WHERE course_id = ?";
$assignments = [];
if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("i", $course_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $assignments[] = $row;
    }
    $stmt->close();
}

// Fetch submissions for the user
$sql = "SELECT * FROM Submissions WHERE user_id = ?";
$submissions = [];
if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("i", $_SESSION['id']);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $submissions[$row['assignment_id']] = $row;
    }
    $stmt->close();
}
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
        <h2 class="text-center">Assignments</h2>
        
        <div class="list-group mt-4">
            <?php if (count($assignments) > 0): ?>
                <?php foreach ($assignments as $assignment): ?>
                    <div class="list-group-item">
                        <h5><?php echo htmlspecialchars($assignment['title']); ?></h5>
                        <p><?php echo htmlspecialchars($assignment['description']); ?></p>
                        <a href="../instructor/<?php echo htmlspecialchars($assignment['assignment_file']); ?>" class="btn btn-info" download>Download Assignment File</a>

                        <p>Due Date: <?php echo htmlspecialchars($assignment['due_date']); ?></p>
                        
                        <?php
                        $assignment_id = $assignment['assignment_id'];
                        $submission = isset($submissions[$assignment_id]) ? $submissions[$assignment_id] : null;
                        $due_date = strtotime($assignment['due_date']);
                        $current_date = time();
                        ?>
                        
                        <?php if ($submission): ?>
                            <p>Submission Date: <?php echo htmlspecialchars($submission['submission_date']); ?></p>
                            <p>Grade: <?php echo htmlspecialchars($submission['grade']); ?></p>
                            <p>Feedback: <?php echo htmlspecialchars($submission['feedback']); ?></p>
                            <a href="<?php echo htmlspecialchars($submission['submission_file']); ?>" class="btn btn-primary" target="_blank">View Submission</a>
                        <?php elseif ($current_date <= $due_date): ?>
                            <form method="POST" enctype="multipart/form-data" class="mt-3">
                                <input type="hidden" name="assignment_id" value="<?php echo $assignment['assignment_id']; ?>">
                                <div class="form-group">
                                    <input type="file" name="submission_file" class="form-control" required>
                                </div>
                                <button type="submit" class="btn btn-success">Submit Assignment</button>
                            </form>
                        <?php else: ?>
                            <p class="text-danger">Due date has passed. You can no longer submit this assignment.</p>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No assignments available for this course.</p>
            <?php endif; ?>
        </div>
    </div>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>

</html>
