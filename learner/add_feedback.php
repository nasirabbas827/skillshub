<?php
session_start();

// Check if the user is logged in, if not then redirect to login page
if (!isset($_SESSION["id"])) {
    header("location: login.php");
    exit;
}

// Database connection
require_once 'config.php';

// Define variables and initialize with empty values
$rating = $comment = "";
$rating_err = $comment_err = "";

// Processing form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate rating
    if (empty(trim($_POST["rating"]))) {
        $rating_err = "Please enter your rating.";
    } else {
        $rating = trim($_POST["rating"]);
    }

    // Validate comment
    if (empty(trim($_POST["comment"]))) {
        $comment_err = "Please enter your comment.";
    } else {
        $comment = trim($_POST["comment"]);
    }

    // Check input errors before inserting into database
    if (empty($rating_err) && empty($comment_err)) {
        // Prepare an insert statement
        $sql = "INSERT INTO feedback (user_id, rating, comment) VALUES (?, ?, ?)";

        if ($stmt = $conn->prepare($sql)) {
            // Bind variables to the prepared statement as parameters
            $stmt->bind_param("iis", $_SESSION['id'], $rating, $comment);

            // Attempt to execute the prepared statement
            if ($stmt->execute()) {
                // Redirect to feedback submitted page
                echo "Feedback Submitted Successfully";

            } else {
                echo "Something went wrong. Please try again later.";
            }

            // Close statement
            $stmt->close();
        }
    }

    // Close connection
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Feedback Form</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="./css/style.css">
</head>
<body>
    <?php include('navbar.php'); ?>
    <div class="container mt-5">
        <h2 class="text-center">Feedback Form</h2>
        <p>Please fill in your feedback below:</p>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="form-group">
                <label>Rating</label>
                <input type="number" class="form-control <?php echo (!empty($rating_err)) ? 'is-invalid' : ''; ?>" name="rating" min="1" max="5" value="<?php echo $rating; ?>">
                <span class="invalid-feedback"><?php echo $rating_err; ?></span>
            </div>
            <div class="form-group">
                <label>Comment</label>
                <textarea class="form-control <?php echo (!empty($comment_err)) ? 'is-invalid' : ''; ?>" name="comment" rows="5"><?php echo $comment; ?></textarea>
                <span class="invalid-feedback"><?php echo $comment_err; ?></span>
            </div>
            <div class="form-group">
                <input type="submit" class="btn btn-primary" value="Submit">
            </div>
        </form>
    </div>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
