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

// Fetch quizzes for the course
$sql = "SELECT * FROM Quizzes WHERE course_id = ?";
$quizzes = [];
if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("i", $course_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $quizzes[] = $row;
    }
    $stmt->close();
}

// Fetch quiz results for the user
$sql = "SELECT * FROM Quiz_Results WHERE learner_id = ? AND course_id = ?";
$quiz_results = [];
if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("ii", $_SESSION['id'], $course_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $quiz_results[$row['course_id']] = $row;
    }
    $stmt->close();
}

// Handle quiz submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $total_questions = count($quizzes);
    $correct_answers = 0;
    foreach ($quizzes as $quiz) {
        $selected_option = $_POST['quiz_' . $quiz['quiz_id']];
        if ($selected_option == $quiz['correct_option']) {
            $correct_answers++;
        }
    }
    $score = ($correct_answers / $total_questions) * 100;

    // Insert quiz result into the database
    $sql = "INSERT INTO Quiz_Results (learner_id, course_id, total_questions, correct_answers, score) VALUES (?, ?, ?, ?, ?)";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("iiiii", $_SESSION['id'], $course_id, $total_questions, $correct_answers, $score);
        $stmt->execute();
        $stmt->close();
    }

    // Reload the page to display the results
    header("location: view_quizzes.php?course_id=$course_id");
    exit;
}
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
    <div class="container mt-5 mb-5">
        <h2 class="text-center">Quizzes</h2>
        
        <?php if (isset($quiz_results[$course_id])): ?>
            <?php $result = $quiz_results[$course_id]; ?>
            <div class="alert alert-success text-center">
                <h4>Quiz Results</h4>
                <p>Total Questions: <?php echo htmlspecialchars($result['total_questions']); ?></p>
                <p>Correct Answers: <?php echo htmlspecialchars($result['correct_answers']); ?></p>
                <p>Score: <?php echo htmlspecialchars($result['score']); ?>%</p>
            </div>
        <?php else: ?>
            <form method="POST" class="mt-4">
                <?php foreach ($quizzes as $quiz): ?>
                    <div class="card mb-3">
                        <div class="card-body">
                            <h5><?php echo htmlspecialchars($quiz['title']); ?></h5>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="quiz_<?php echo $quiz['quiz_id']; ?>" id="quiz_<?php echo $quiz['quiz_id']; ?>_1" value="1" required>
                                <label class="form-check-label" for="quiz_<?php echo $quiz['quiz_id']; ?>_1"><?php echo htmlspecialchars($quiz['option_one']); ?></label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="quiz_<?php echo $quiz['quiz_id']; ?>" id="quiz_<?php echo $quiz['quiz_id']; ?>_2" value="2" required>
                                <label class="form-check-label" for="quiz_<?php echo $quiz['quiz_id']; ?>_2"><?php echo htmlspecialchars($quiz['option_two']); ?></label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="quiz_<?php echo $quiz['quiz_id']; ?>" id="quiz_<?php echo $quiz['quiz_id']; ?>_3" value="3" required>
                                <label class="form-check-label" for="quiz_<?php echo $quiz['quiz_id']; ?>_3"><?php echo htmlspecialchars($quiz['option_three']); ?></label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="quiz_<?php echo $quiz['quiz_id']; ?>" id="quiz_<?php echo $quiz['quiz_id']; ?>_4" value="4" required>
                                <label class="form-check-label" for="quiz_<?php echo $quiz['quiz_id']; ?>_4"><?php echo htmlspecialchars($quiz['option_four']); ?></label>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
                <button type="submit" class="btn btn-primary btn-block">Submit Quiz</button>
            </form>
        <?php endif; ?>
    </div>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>

</html>
