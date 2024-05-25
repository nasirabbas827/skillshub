<?php
session_start();
include('config.php');

// Check if the user is logged in and is an instructor, if not then redirect to login page
if (!isset($_SESSION["id"]) || $_SESSION["usertype"] != 'instructor') {
    header("location: login.php");
    exit;
}

// Handle delete operation
if (isset($_GET['delete'])) {
    $material_id = intval($_GET['delete']);
    $sql = "DELETE FROM Course_Materials WHERE material_id = ?";
    if ($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "i", $material_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
    header("location: view_materials.php");
    exit;
}

// Fetch materials added by the instructor
$sql = "SELECT cm.material_id, cm.course_id, c.title AS course_title, cm.material_type, cm.material_url, cm.title, cm.description, cm.upload_date 
        FROM Course_Materials cm
        JOIN courses c ON cm.course_id = c.course_id
        WHERE c.instructor_id = ?";
if ($stmt = mysqli_prepare($conn, $sql)) {
    mysqli_stmt_bind_param($stmt, "i", $_SESSION["id"]);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $materials = mysqli_fetch_all($result, MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);
}
mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>View Course Materials</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="./css/style.css">
</head>

<body>
<?php include('navbar.php'); ?>
    <div class="container mt-5">
        <h2 class="text-center">Your Course Materials</h2>
        <p class="text-center">Here you can view, edit, or delete your course materials.</p>
        <div class="row">
            <?php foreach ($materials as $material): ?>
                <div class="col-md-4">
                    <div class="card mb-4">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($material['title']); ?></h5>
                            <p class="card-text"><strong>Course:</strong> <?php echo htmlspecialchars($material['course_title']); ?></p>
                            <p class="card-text"><strong>Type:</strong> <?php echo htmlspecialchars($material['material_type']); ?></p>
                            <p class="card-text"><?php echo htmlspecialchars($material['description']); ?></p>
                            <p class="card-text"><small class="text-muted">Uploaded on <?php echo htmlspecialchars($material['upload_date']); ?></small></p>
                            <?php if ($material['material_type'] == 'video'): ?>
                                <a href="<?php echo htmlspecialchars($material['material_url']); ?>" class="btn btn-primary">View Video</a>
                            <?php else: ?>
                                <a href="<?php echo htmlspecialchars($material['material_url']); ?>" class="btn btn-primary" download>Download File</a>
                            <?php endif; ?>
                            <a href="edit_material.php?material_id=<?php echo $material['material_id']; ?>" class="btn btn-secondary">Edit</a>
                            <a href="view_materials.php?delete=<?php echo $material['material_id']; ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this material?');">Delete</a>
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
