<?php
session_start();



// Database connection
require_once 'config.php';



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

// Fetch feedback from the database
$sql2 = "SELECT feedback.*, users.username 
        FROM feedback 
        JOIN users ON feedback.user_id = users.id";
$feedbacks = [];
if ($result = $conn->query($sql2)) {
    while ($row = $result->fetch_assoc()) {
        $feedbacks[] = $row;
    }
    $result->free();
}

// Function to generate star ratings
function generateStarRating($rating) {
    $stars = "";
    for ($i = 1; $i <= 5; $i++) {
        if ($i <= $rating) {
            $stars .= '<span class="star">&#9733;</span>'; // Filled star
        } else {
            $stars .= '<span class="star">&#9734;</span>'; // Empty star
        }
    }
    return $stars;
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>SkillsHub</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="./css/style.css">
 <style>
.jumbotron {
            height: 500px;
            background-image: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), url('./images/hotel.jpg');
            background-size: cover;
            color: white;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }

        .jumbotron h1 {
            font-size: 3rem;
            margin-bottom: 10px;
        }

        .jumbotron p {
            font-size: 1.5rem;
        }
        .star{
            color:gold;
        }
    </style>
</head>
<body>

<?php
include('navbar.php');
?>

<div class="jumbotron text-center">
    <h1>Welcome to SkillsHub</h1>
    <p>Empowering Your Learning Journey</p>
    <a href="login.php" class="btn btn-primary btn-lg">Explore Now</a>
</div>


<div class="container mt-5">
        <h2 class="text-center">Our Courses</h2>

        <form class="form-inline my-2 my-lg-0 justify-content-center" method="GET">
            <input class="form-control mr-sm-2" type="search" placeholder="Search courses" aria-label="Search" name="search" value="<?php echo htmlspecialchars($search); ?>">
            <button class="btn btn-outline-success my-2 my-sm-0" type="submit">Search</button>
        </form>

        <div class="row mt-4">
            <?php foreach ($courses as $course): ?>
                <div class="col-md-4 mb-4">
                    <div class="card">
                        <img src="instructor/<?php echo htmlspecialchars($course['picture']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($course['title']); ?>">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($course['title']); ?></h5>
                            <p class="card-text"><?php echo htmlspecialchars($course['description']); ?></p>
                            <a href="login.php" class="btn btn-primary">Enroll Now</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="container mt-3">
        <h2 class="mb-4">Feedback</h2>
        <div class="row">
            <?php foreach ($feedbacks as $feedback): ?>
                <div class="col-md-4 mb-4">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($feedback['username']); ?></h5>
                            <p class="card-text"><?php echo generateStarRating($feedback['rating']); ?></p>
                            <p class="card-text"><?php echo htmlspecialchars($feedback['comment']); ?></p>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
<footer class="mt-5 py-3 bg-light">
    <div class="container text-center">
        <p>&copy; 2024 SkillsHub. All rights reserved.</p>
    </div>
</footer>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
