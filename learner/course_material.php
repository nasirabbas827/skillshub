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

// Fetch course details
$sql = "SELECT * FROM courses WHERE course_id = ?";
if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("i", $course_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $course = $result->fetch_assoc();
    $stmt->close();
}

// Fetch course materials
$sql = "SELECT * FROM Course_Materials WHERE course_id = ?";
$materials = [];
if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("i", $course_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $materials[] = $row;
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Course Material</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="./css/style.css">
</head>

<body>
    <?php include('navbar.php'); ?>
    <div class="container mt-5">
        <h2 class="text-center"><?php echo htmlspecialchars($course['title']); ?></h2>
        <img src="../instructor/<?php echo htmlspecialchars($course['picture']); ?>" class="img-fluid mx-auto d-block" alt="<?php echo htmlspecialchars($course['title']); ?>" style="max-width: 600px;">
        <p class="text-center mt-3"><?php echo htmlspecialchars($course['description']); ?></p>

        <div class="text-center mt-4">
            <a href="#materials" class="btn btn-primary">View Materials</a>
            <a href="view_assignments.php?course_id=<?php echo $course_id; ?>" class="btn btn-secondary">View Assignments</a>
            <a href="view_quizzes.php?course_id=<?php echo $course_id; ?>" class="btn btn-info">View Quizzes</a>
        </div>

        <div id="materials" class="mt-5">
            <h3>Course Materials</h3>
            <?php if (count($materials) > 0): ?>
                <ul class="list-group">
                    <?php foreach ($materials as $material): ?>
                        <li class="list-group-item m-2">
                            <h5><?php echo htmlspecialchars($material['title']); ?></h5>
                            <p><?php echo htmlspecialchars($material['description']); ?></p>
                            <a href="<?php echo htmlspecialchars($material['material_url']); ?>" target="_blank" class="btn btn-outline-primary">View <?php echo ucfirst($material['material_type']); ?></a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p>No materials available for this course.</p>
            <?php endif; ?>
        </div>
    </div>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>

</html>
