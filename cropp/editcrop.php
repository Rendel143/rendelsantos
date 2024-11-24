<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: dashboard.php");
    exit();
}

$host = 'localhost';
$dbname = 'crop';
$db_username = 'root';
$db_password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $db_username, $db_password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $cropId = $_POST['crop_id'];
        $cropName = $_POST['cropName'];
        $field = $_POST['field'];
        $status = $_POST['status'];
        $description = $_POST['description'];
        $stmt = $pdo->prepare("UPDATE crops SET crop_name=?, field=?, status=?, description=? WHERE crop_id=?");

        if ($stmt->execute([$cropName, $field, $status, $description, $cropId])) {
            $_SESSION['message'] = "Crop updated successfully."; 
            header("Location: dashboard.php");
            exit();
        } else {
            echo "Error updating crop.";
        }
    }
    $crop_id = isset($_GET['id']) ? (int)$_GET['id'] : 0; 
    $stmt = $pdo->prepare("SELECT crop_id, crop_name, field, status, description FROM crops WHERE crop_id = ?");
    $stmt->execute([$crop_id]);
    $crop = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($crop) {
        $cropId = htmlspecialchars($crop['crop_id']);
        $crop_name = htmlspecialchars($crop['crop_name']);
        $field = htmlspecialchars($crop['field']);
        $status = htmlspecialchars($crop['status']);
        $description = htmlspecialchars($crop['description']);
    } else {
        echo "Crop not found.";
        exit();
    }

} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}

$welcome_message = "Mabuhay! " . htmlspecialchars($_SESSION['username']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Crop</title>
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
            transition: 0.3s;
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
            background-color: #1abc9c;
            color: white;
            padding: 15px 20px;
            border: none;
            border-radius: 5px;
            font-size: 18px;
            cursor: pointer;
            transition: 0.3s;
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
            <img src="images/profile2.png" alt="Profile Picture">
                <p><?php echo htmlspecialchars($_SESSION['username']); ?></p>
        </div>
            <a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
            <a class="active" href="editcrop.php?id=<?php echo $crop_id; ?>"><i class="fas fa-edit"></i> Edit Crop</a>
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
            
    <h2>Edit Crop</h2>
    <form action="editcrop.php?id=<?php echo $crop_id; ?>" method="POST">
        <input type="hidden" name="crop_id" value="<?php echo $crop_id; ?>">
            <label for="cropName">Crop Name</label>
            <input type="text" id="cropName" name="cropName" value="<?php echo htmlspecialchars($crop_name); ?>" required>
            <label for="field">Field</label>
                <select id="field" name="field" required>
                    <option value="Field 1" <?php if ($field == "Field 1") echo 'selected'; ?>>Field 1</option>
                    <option value="Field 2" <?php if ($field == "Field 2") echo 'selected'; ?>>Field 2</option>
                    <option value="Field 3" <?php if ($field == "Field 3") echo 'selected'; ?>>Field 3</option>
                    <option value="Field 4" <?php if ($field == "Field 4") echo 'selected'; ?>>Field 4</option>
                </select>
            <label for="status">Status</label>
                <select id="status" name="status" required>
                    <option value="Good" <?php if ($status == "Good") echo 'selected'; ?>>Good</option>
                    <option value="Needs Attention" <?php if ($status == "Needs Attention") echo 'selected'; ?>>Needs Attention</option>
                </select>
            <label for="description">Description</label>
                <textarea id="description" name="description" rows="5" required><?php echo htmlspecialchars($description); ?></textarea>
        <input type="submit" value="Update Crop">
    </form>
    </div>
    </div>
</body>
</html>