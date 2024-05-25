<?php
session_start();
include('config.php');

// Check if the user is logged in and is an instructor, if not then redirect to login page
if (!isset($_SESSION["id"]) || $_SESSION["usertype"] != 'instructor') {
    header("location: login.php");
    exit;
}

// Get material_id from URL
$material_id = isset($_GET['material_id']) ? intval($_GET['material_id']) : 0;

// Fetch the material details
$sql = "SELECT material_type, material_url, title, description FROM Course_Materials WHERE material_id = ?";
if ($stmt = mysqli_prepare($conn, $sql)) {
    mysqli_stmt_bind_param($stmt, "i", $material_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $material = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $material_type = $_POST["material_type"];
    $title = $_POST["title"];
    $description = $_POST["description"];
    $material_url = $_POST["current_material_url"];

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

    $sql = "UPDATE Course_Materials SET material_type = ?, material_url = ?, title = ?, description = ? WHERE material_id = ?";
    if ($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "sssii", $material_type, $material_url, $title, $description, $material_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }

    header("location: view_materials.php");
    exit;
}
mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Edit Course Material</title>
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

        document.addEventListener('DOMContentLoaded', function () {
            toggleFileInput();
        });
    </script>
</head>

<body>
<?php include('navbar.php'); ?>
    <div class="container mt-5">
        <h2 class="text-center">Edit Course Material</h2>
        <p class="text-center">Please edit the details of the course material.</p>

        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . "?material_id=" . $material_id; ?>" method="post" enctype="multipart/form-data">
            <div class="form-group">
                <label>Material Type</label>
                <select name="material_type" id="material_type" class="form-control" onchange="toggleFileInput()" required>
                    <option value="video" <?php echo ($material['material_type'] == 'video') ? 'selected' : ''; ?>>Video</option>
                    <option value="presentation" <?php echo ($material['material_type'] == 'presentation') ? 'selected' : ''; ?>>Presentation</option>
                    <option value="reading material" <?php echo ($material['material_type'] == 'reading material') ? 'selected' : ''; ?>>Reading Material</option>
                </select>
            </div>
            <div class="form-group" id="material_url_field" style="display: none;">
                <label>Material URL</label>
                <input type="url" name="material_url" class="form-control" value="<?php echo htmlspecialchars($material['material_url']); ?>">
            </div>
            <div class="form-group" id="material_file_field">
                <label>Material File</label>
                <input type="file" name="material_file" class="form-control">
                <input type="hidden" name="current_material_url" value="<?php echo htmlspecialchars($material['material_url']); ?>">
            </div>
            <div class="form-group">
                <label>Title</label>
                <input type="text" name="title" class="form-control" value="<?php echo htmlspecialchars($material['title']); ?>" required>
            </div>
            <div class="form-group">
                <label>Description</label>
                <textarea name="description" class="form-control" required><?php echo htmlspecialchars($material['description']); ?></textarea>
            </div>
            <div class="form-group text-center">
                <input type="submit" class="btn btn-primary" value="Save Changes">
            </div>
        </form>
    </div>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>

</html>
