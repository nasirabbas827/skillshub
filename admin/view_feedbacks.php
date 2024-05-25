<?php
session_start();
include('config.php');

// Check if the user is logged in as an admin
if (!isset($_SESSION["usertype"]) || $_SESSION["usertype"] !== "admin") {
    header("Location: admin_login.php");
    exit;
}

// Fetch all feedbacks with usernames from the database
$sql = "SELECT f.feedback_id, f.user_id, u.username, f.rating, f.comment, f.created_at 
        FROM feedback f 
        INNER JOIN users u ON f.user_id = u.id";
$result = mysqli_query($conn, $sql);

// Function to delete a feedback
function deleteFeedback($conn, $feedback_id) {
    $sql = "DELETE FROM feedback WHERE feedback_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $feedback_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<?php include('admin_navbar.php'); ?>
<div class="container mt-5">
    <h2 class="text-center">All Feedbacks</h2>
    <div class="table-responsive">
        <table class="table table-bordered">
            <thead>
            <tr>
                <th>Feedback ID</th>
                <th>User</th>
                <th>Rating</th>
                <th>Comment</th>
                <th>Created At</th>
                <th>Action</th>
            </tr>
            </thead>
            <tbody>
            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                <tr>
                    <td><?php echo $row['feedback_id']; ?></td>
                    <td><?php echo $row['username']; ?></td>
                    <td><?php echo $row['rating']; ?></td>
                    <td><?php echo $row['comment']; ?></td>
                    <td><?php echo $row['created_at']; ?></td>
                    <td>
                        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                            <input type="hidden" name="feedback_id" value="<?php echo $row['feedback_id']; ?>">
                            <button type="submit" name="delete" class="btn btn-danger">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
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
// Process form submission for feedback deletion
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["delete"])) {
    $feedback_id = $_POST["feedback_id"];
    deleteFeedback($conn, $feedback_id);
    // Refresh the page to reflect changes
    header("Location: view_feedbacks.php");
    exit;
}
?>
