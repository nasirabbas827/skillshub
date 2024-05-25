<?php
session_start();
include('config.php');

// Check if the user is logged in and is an instructor, if not then redirect to login page
if (!isset($_SESSION["id"]) || $_SESSION["usertype"] != 'instructor') {
    header("location: login.php");
    exit;
}

// define variables and initialize with empty values
$picture = $title = $description = "";
$picture_err = $title_err = $description_err = "";

// check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // validate title
    if (empty(trim($_POST["title"]))) {
        $title_err = "Please enter a title.";
    } else {
        $title = trim($_POST["title"]);
    }

    // validate description
    if (empty(trim($_POST["description"]))) {
        $description_err = "Please enter a description.";
    } else {
        $description = trim($_POST["description"]);
    }

    // validate picture
    if (!empty($_FILES["picture"]["name"])) {
        $target_dir = "uploads/";
        $target_file = $target_dir . basename($_FILES["picture"]["name"]);
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Check if file is an actual image
        $check = getimagesize($_FILES["picture"]["tmp_name"]);
        if ($check === false) {
            $picture_err = "File is not an image.";
        } else {
            if (move_uploaded_file($_FILES["picture"]["tmp_name"], $target_file)) {
                $picture = $target_file;
            } else {
                $picture_err = "Sorry, there was an error uploading your file.";
            }
        }
    }

    // if no errors, insert course into database
    if (empty($title_err) && empty($description_err) && empty($picture_err)) {
        $sql = "INSERT INTO courses (picture, title, description, instructor_id) VALUES (?, ?, ?, ?)";
        if ($stmt = mysqli_prepare($conn, $sql)) {
            mysqli_stmt_bind_param($stmt, "sssi", $param_picture, $param_title, $param_description, $param_instructor_id);
            $param_picture = $picture;
            $param_title = $title;
            $param_description = $description;
            $param_instructor_id = $_SESSION["id"];

            if (mysqli_stmt_execute($stmt)) {
                echo '<div class="alert alert-success" role="alert">Course added successfully.</div>';
            } else {
                echo '<div class="alert alert-danger" role="alert">Something went wrong. Please try again later.</div>';
            }
            mysqli_stmt_close($stmt);
        }
    }
    mysqli_close($conn);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Add Course</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="./css/style.css">
</head>

<body>
<?php include('navbar.php'); ?>
    <div class="container mt-5">
        <h2 class="text-center">Add Course</h2>
        <p class="text-center">Please fill in the details to add a new course.</p>

        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" enctype="multipart/form-data">
            <div class="form-group">
                <label>Course Picture</label>
                <input type="file" name="picture" class="form-control <?php echo (!empty($picture_err)) ? 'is-invalid' : ''; ?>">
                <span class="invalid-feedback"><?php echo $picture_err; ?></span>
            </div>
            <div class="form-group">
                <label>Title</label>
                <input type="text" name="title" class="form-control <?php echo (!empty($title_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $title; ?>">
                <span class="invalid-feedback"><?php echo $title_err; ?></span>
            </div>
            <div class="form-group">
                <label>Description</label>
                <textarea name="description" class="form-control <?php echo (!empty($description_err)) ? 'is-invalid' : ''; ?>"><?php echo $description; ?></textarea>
                <span class="invalid-feedback"><?php echo $description_err; ?></span>
            </div>
            <div class="form-group text-center">
                <input type="submit" class="btn btn-primary" value="Add Course">
                <a class="btn btn-outline-dark" href="view_courses.php">View Courses</a>
            </div>
        </form>
    </div>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>

</html>
