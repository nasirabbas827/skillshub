<?php
session_start();
include('config.php');

// Check if the user is logged in and is an instructor, if not then redirect to login page
if (!isset($_SESSION["id"]) || $_SESSION["usertype"] != 'instructor') {
    header("location: login.php");
    exit;
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $course_id = intval($_POST["course_id"]);
    $material_type = $_POST["material_type"];
    $title = $_POST["title"];
    $description = $_POST["description"];
    $material_url = "";

    if ($material_type == "video") {
        $material_url = $_POST["material_url"];
    } else {
        if (!empty($_FILES["material_file"]["name"])) {
            $target_dir = "uploads/";
            $target_file = $target_dir . basename($_FILES["material_file"]["name"]);
            if (move_uploaded_file($_FILES["material_file"]["tmp_name"], $target_file)) {
                $material_url = $target_file;
            }
        }
    }

    $sql = "INSERT INTO Course_Materials (course_id, material_type, material_url, title, description) VALUES (?, ?, ?, ?, ?)";
    if ($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "issss", $course_id, $material_type, $material_url, $title, $description);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }

    header("location: view_materials.php");
    exit;
}

// Fetch instructor's courses
$sql = "SELECT course_id, title FROM courses WHERE instructor_id = ?";
if ($stmt = mysqli_prepare($conn, $sql)) {
    mysqli_stmt_bind_param($stmt, "i", $_SESSION["id"]);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $courses = mysqli_fetch_all($result, MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);
}
mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Add Course Material</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="./css/style.css">
    <script>
        function toggleFileInput() {
            const materialType = document.getElementById('material_type').value;
            const materialUrlField = document.getElementById('material_url_field');
            const materialFileField = document.getElementById('material_file_field');
            if (materialType === 'video') {
                materialUrlField.style.display = 'block';
                materialFileField.style.display = 'none';
            } else {
                materialUrlField.style.display = 'none';
                materialFileField.style.display = 'block';
            }
        }
    </script>
</head>

<body>
<?php include('navbar.php'); ?>
    <div class="container mt-5">
        <h2 class="text-center">Add Course Material</h2>
        <p class="text-center">Please fill in the details to add material to your course.</p>

        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" enctype="multipart/form-data">
            <div class="form-group">
                <label>Course</label>
                <select name="course_id" class="form-control" required>
                    <?php foreach ($courses as $course): ?>
                        <option value="<?php echo $course['course_id']; ?>"><?php echo htmlspecialchars($course['title']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Material Type</label>
                <select name="material_type" id="material_type" class="form-control" onchange="toggleFileInput()" required>
                    <option value="video">Video</option>
                    <option value="presentation">Presentation</option>
                    <option value="reading material">Reading Material</option>
                </select>
            </div>
            <div class="form-group" id="material_url_field" style="display: none;">
                <label>Material URL</label>
                <input type="url" name="material_url" class="form-control">
            </div>
            <div class="form-group" id="material_file_field">
                <label>Material File</label>
                <input type="file" name="material_file" class="form-control">
            </div>
            <div class="form-group">
                <label>Title</label>
                <input type="text" name="title" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Description</label>
                <textarea name="description" class="form-control" required></textarea>
            </div>
            <div class="form-group text-center">
                <input type="submit" class="btn btn-primary" value="Add Material">
                <a class="btn btn-outline-dark" href="view_material.php">View Course Material</a>
            </div>
        </form>
    </div>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>

</html>
