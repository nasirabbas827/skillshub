<?php
session_start();

// Check if the user is logged in, if not then redirect to login page
if (!isset($_SESSION["id"]) || $_SESSION["usertype"] != 'learner') {
    header("location: login.php");
    exit;
}

// Database connection
require_once 'config.php';

// Handle enrollment and cancellation
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $course_id = $_POST['course_id'];
    $user_id = $_SESSION['id'];

    if (isset($_POST['enroll'])) {
        $sql = "INSERT INTO enrollments (user_id, course_id) VALUES (?, ?)";
    } elseif (isset($_POST['cancel'])) {
        $sql = "DELETE FROM enrollments WHERE user_id = ? AND course_id = ?";
    }

    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("ii", $user_id, $course_id);
        $stmt->execute();
        $stmt->close();
    }
}

// Fetch all courses
$search = isset($_GET['search']) ? $_GET['search'] : '';
$sql = "SELECT * FROM courses WHERE title LIKE ?";
$courses = [];
if ($stmt = $conn->prepare($sql)) {
    $search_param = '%' . $search . '%';
    $stmt->bind_param("s", $search_param);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $courses[] = $row;
    }
    $stmt->close();
}

// Fetch enrolled courses
$sql = "SELECT course_id FROM enrollments WHERE user_id = ?";
$enrolled_courses = [];
if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("i", $_SESSION['id']);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $enrolled_courses[] = $row['course_id'];
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Learner Dashboard</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="./css/style.css">
</head>

<body>
    <?php include('navbar.php'); ?>
    <div class="container mt-5">
        <h2 class="text-center">Learner Dashboard</h2>
        <p class="text-center">Welcome, <?php echo htmlspecialchars($_SESSION["email"]); ?>!</p>

        <form class="form-inline my-2 my-lg-0 justify-content-center" method="GET">
            <input class="form-control mr-sm-2" type="search" placeholder="Search courses" aria-label="Search" name="search" value="<?php echo htmlspecialchars($search); ?>">
            <button class="btn btn-outline-success my-2 my-sm-0" type="submit">Search</button>
        </form>

        <div class="row mt-4">
            <?php foreach ($courses as $course): ?>
                <div class="col-md-4 mb-4">
                    <div class="card">
                        <img src="../instructor/<?php echo htmlspecialchars($course['picture']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($course['title']); ?>">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($course['title']); ?></h5>
                            <p class="card-text"><?php echo htmlspecialchars($course['description']); ?></p>
                            <?php if (in_array($course['course_id'], $enrolled_courses)): ?>
                                <form method="POST">
                                    <input type="hidden" name="course_id" value="<?php echo $course['course_id']; ?>">
                                    <button type="submit" name="cancel" class="btn btn-danger m-2">Cancel Enrollment</button>
                                    <a href="course_material.php?course_id=<?php echo $course['course_id']; ?>" class="btn btn-primary">View Course Materials</a>
                                </form>
                            <?php else: ?>
                                <form method="POST">
                                    <input type="hidden" name="course_id" value="<?php echo $course['course_id']; ?>">
                                    <button type="submit" name="enroll" class="btn btn-success">Enroll Now</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>

</html>
