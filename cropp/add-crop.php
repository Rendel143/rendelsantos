<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: dashboard.php");
    exit();
}

$welcome_message = "Mabuhay! " . htmlspecialchars($_SESSION['username']);

$host = 'localhost';
$dbname = 'crop';
$db_username = 'root';
$db_password = '';

try {

    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $db_username, $db_password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $cropName = htmlspecialchars($_POST['cropName']);
        $field = htmlspecialchars($_POST['field']);
        $status = htmlspecialchars($_POST['status']);
        $description = htmlspecialchars($_POST['description']);

        $image = $_FILES['image'];
        $imageName = time() . '_' . basename($image['name']); 
        $targetDirectory = "uploads/"; 
        $targetFile = $targetDirectory . $imageName;

        $check = getimagesize($image['tmp_name']);
        if ($check !== false) {
           
            if (move_uploaded_file($image['tmp_name'], $targetFile)) {
               
                $stmt = $pdo->prepare("INSERT INTO crops (crop_name, field, status, description, image) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$cropName, $field, $status, $description, $imageName]);

                $_SESSION['message'] = "Crop added successfully!";
            } else {
                $_SESSION['message'] = "Failed to upload image. Please try again.";
            }
        } else {
            $_SESSION['message'] = "File is not a valid image.";
        }

        header("Location: add-crop.php");
        exit();
    }

} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Crop</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/normalize/8.0.1/normalize.min.css">
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #3e713e;
            margin: 0;
            padding: 0;
            display: flex;
        }

        .sidebar {
            height: 100vh;
            width: 250px;
            background-color: #1a261a;
            padding-top: 20px;
            color: white;
            position: fixed;
            left: 0;
            top: 0;
        }

        .sidebar a {
            padding: 15px 20px;
            text-decoration: none;
            font-size: 18px;
            color: white;
            display: block;
            transition: background-color 0.3s;

        }

        .sidebar a:hover {
            background-color: #34495e;
        }

        .sidebar .active {
            background-color: #1abc9c;
        }

        .sidebar .profile {
            text-align: center;
            margin-bottom: 20px;
        }

        .sidebar .profile img {
            border-radius: 50%;
            width: 100px;
            height: 100px;
            object-fit: cover;
        }

        .main-content {
            margin-left: 250px;
            padding: 20px;
            flex-grow: 1;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-color: white;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
            margin-bottom: 20px;
        }

        .header .welcome {
            font-size: 24px;
            color: #333;
        }

        .form-container {
            background-color: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0px 4px 20px rgba(0, 0, 0, 0.1);
        }

        .form-container h2 {
            margin-top: 0;
            font-size: 24px;
            margin-bottom: 20px;
        }

        .form-container label {
            display: block;
            margin-bottom: 10px;
            color: #333;
        }
        .form-container input[type="text"],
        .form-container input[type="number"],
        .form-container select,
        .form-container textarea {
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
        }
        .form-container input[type="submit"] {
            background-color: #1a261a;
            color: white;
            padding: 15px 20px;
            border: none;
            border-radius: 5px;
            font-size: 18px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .form-container input[type="submit"]:hover {
            background-color: #16a085;
        }
        .message {
            margin-bottom: 20px;
            padding: 15px;
            background-color: #f1f1f1;
            border-left: 5px solid #1abc9c;
            color: #333;
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="profile">
            <img src="images/profile.jpg" alt="Profile Picture">
            <p><?php echo htmlspecialchars($_SESSION['username']); ?></p>
        </div>
        <a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
        <a class="active" href="add-crop.php"><i class="fas fa-plus-circle"></i> Add Crop</a>
        <a href="register.php?logout=true"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>

    <div class="main-content">
        <div class="header">
            <div class="welcome"><?php echo $welcome_message; ?></div>
        </div>

        <div class="form-container">
            <?php if (isset($_SESSION['message'])): ?>
                <div class="message">
                    <p><?php echo $_SESSION['message']; ?></p>
                    <?php unset($_SESSION['message']); ?>
                </div>
            <?php endif; ?>
            <h2>Add New Crop</h2>
            <form action="add-crop.php" method="POST" enctype="multipart/form-data">
                <label for="cropName">Crop Name</label>
                <input type="text" id="cropName" name="cropName" required>

                <label for="field">Field</label>
                <select id="field" name="field" required>
                    <option value="Field 1">Field 1</option>
                    <option value="Field 2">Field 2</option>
                    <option value="Field 3">Field 3</option>
                    <option value="Field 3">Field 4</option>
                </select>

                <label for="status">Status</label>
                <select id="status" name="status" required>
                    <option value="Good">Good</option>
                    <option value="Needs Attention">Needs Attention</option>
                </select>

                <label for="description">Description</label>
                <textarea id="description" name="description" rows="5" required></textarea>

                <label for="image">Upload Image</label>
                <input type="file" id="image" name="image" accept="image/*" required>

                <input type="submit" value="Add Crop">
            </form>
        </div>
    </div>
</body>
</html>