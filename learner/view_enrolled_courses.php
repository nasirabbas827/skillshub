<?php
session_start();

// Check if the user is logged in, if not then redirect to login page
if (!isset($_SESSION["id"]) || $_SESSION["usertype"] != 'learner') {
    header("location: login.php");
    exit;
}

// Database connection
require_once 'config.php';

// Fetch enrolled courses for the user
$sql = "SELECT courses.*, 
               users.username AS instructor_name,
               (SELECT COUNT(*) FROM Assignments WHERE course_id = courses.course_id) AS total_assignments,
               (SELECT COUNT(*) FROM Quizzes WHERE course_id = courses.course_id) AS total_quizzes,
               (SELECT COUNT(*) FROM Submissions 
                JOIN Assignments ON Submissions.assignment_id = Assignments.assignment_id 
                WHERE Assignments.course_id = courses.course_id AND Submissions.user_id = ?) AS completed_assignments,
               (SELECT COUNT(*) FROM Quiz_Results 
                WHERE Quiz_Results.course_id = courses.course_id AND Quiz_Results.learner_id = ?) AS completed_quizzes,
               (SELECT AVG(grade) FROM Submissions 
                JOIN Assignments ON Submissions.assignment_id = Assignments.assignment_id 
                WHERE Assignments.course_id = courses.course_id AND Submissions.user_id = ?) AS assignment_grade,
               (SELECT GROUP_CONCAT(feedback) FROM Submissions 
                JOIN Assignments ON Submissions.assignment_id = Assignments.assignment_id 
                WHERE Assignments.course_id = courses.course_id AND Submissions.user_id = ?) AS assignment_feedback
        FROM courses
        JOIN Enrollments ON courses.course_id = Enrollments.course_id
        JOIN users ON courses.instructor_id = users.id
        WHERE Enrollments.user_id = ?";
$courses = [];
if ($stmt = $conn->prepare($sql)) {
    $user_id = $_SESSION['id'];
    $stmt->bind_param("iiiii", $user_id, $user_id, $user_id, $user_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $courses[] = $row;
    }
    $stmt->close();
}

// Generate Certificate
if (isset($_GET['course_id']) && isset($_GET['generate_certificate'])) {
    $course_id = $_GET['course_id'];

    // Fetch course details and check completion status
    foreach ($courses as $course) {
        if ($course['course_id'] == $course_id) {
            if ($course['completed_assignments'] == $course['total_assignments'] && $course['completed_quizzes'] > 0) {
                generate_certificate($course);
            } else {
                echo "Course not completed.";
                exit;
            }
        }
    }
}

function generate_certificate($course) {
    require('../fpdf/fpdf.php');

    $pdf = new FPDF();
    $pdf->AddPage();
    $pdf->SetFont('Arial', 'B', 16);
    $pdf->Cell(0, 10, 'Certificate of Completion', 0, 1, 'C');
    $pdf->Ln(10);
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(0, 10, 'This is to certify that', 0, 1, 'C');
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(0, 10, htmlspecialchars($_SESSION["email"]), 0, 1, 'C');
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(0, 10, 'has successfully completed the course', 0, 1, 'C');
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(0, 10, htmlspecialchars($course['title']), 0, 1, 'C');
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(0, 10, 'with all assignments and quizzes completed.', 0, 1, 'C');
    $pdf->Ln(20);
    $pdf->Cell(0, 10, 'Instructor: ' . htmlspecialchars($course['instructor_name']), 0, 1, 'L');
    $pdf->Cell(0, 10, 'Assignments Grade: ' . round($course['assignment_grade'], 2), 0, 1, 'L');
    $pdf->MultiCell(0, 10, 'Assignments Feedback: ' . htmlspecialchars($course['assignment_feedback']), 0, 'L');
    $pdf->Cell(0, 10, 'Date: ' . date('Y-m-d'), 0, 1, 'C');
    $pdf->Output();
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Enrolled Courses</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="./css/style.css">
</head>
<body>
    <?php include('navbar.php'); ?>
    <div class="container mt-5">
        <h2 class="text-center">My Enrolled Courses</h2>
        
        <?php if (count($courses) > 0): ?>
            <div class="list-group mt-4">
                <?php foreach ($courses as $course): ?>
                    <div class="list-group-item">
                        <h5><?php echo htmlspecialchars($course['title']); ?></h5>
                        <p><?php echo htmlspecialchars($course['description']); ?></p>
                        <p>Instructor: <?php echo htmlspecialchars($course['instructor_name']); ?></p>
                        <p>Assignments Completed: <?php echo htmlspecialchars($course['completed_assignments']); ?> / <?php echo htmlspecialchars($course['total_assignments']); ?></p>
                        <p>Quizzes Completed: <?php echo htmlspecialchars($course['completed_quizzes']); ?> / <?php echo htmlspecialchars($course['total_quizzes']); ?></p>
                        <?php if ($course['completed_assignments'] == $course['total_assignments'] && $course['completed_quizzes'] > 0): ?>
                            <a href="view_enrolled_courses.php?course_id=<?php echo $course['course_id']; ?>&generate_certificate=1" class="btn btn-success">Download Certificate</a>
                        <?php else: ?>
                            <p class="text-danger">Complete all assignments and attempt at least one quiz to download the certificate.</p>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p>No enrolled courses found.</p>
        <?php endif; ?>
    </div>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
