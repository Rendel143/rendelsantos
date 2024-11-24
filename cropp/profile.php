<?php 
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: register.php");
    exit();
}

$host = 'localhost';
$dbname = 'crop';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    if (isset($_SESSION['username'])) {
        $user = $_SESSION['username'];
        $stmt = $pdo->prepare("SELECT name, age, weight, height, emergency_contact, address, bio FROM users WHERE username = ?");
        $stmt->execute([$user]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($row) {
            $name = htmlspecialchars($row['name']);
            $age = htmlspecialchars($row['age']);
            $weight = htmlspecialchars($row['weight']);
            $height = htmlspecialchars($row['height']);
            $emergency_contact = htmlspecialchars($row['emergency_contact']);
            $address = htmlspecialchars($row['address']);
            $bio = htmlspecialchars($row['bio']);
        } else {
            echo "No user data found.";
            exit();
        }
    } else {
        header("Location: dashboard.php"); 
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
    <title>User Profile</title>
    <style>
    *   {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-color: #3e713e;
        }
        .card {
            display: flex;
            width: 900px;
            height: 600px;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }
        .card-left, .card-right {
            width: 50%;
            padding: 20px;
        }
        .card-left {
            background-color: #333;
            color: #fff;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }
        .card-left img {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            margin-bottom: 10px;
        }
        .card-right {
            background-color: #fff;
            color: #333;
        }
        h2, h3 {
            margin-bottom: 10px;
        }
        .info-section {
            margin-bottom: 15px;
        }
        .label {
            font-size: 12px;
            color: #777;
            margin-bottom: 5px;
        }
        .value {
            font-size: 16px;
            color: #333;
        }
        .emergency-contact {
            background-color: #00bfa5;
            color: #fff;
            padding: 10px;
            text-align: center;
            border-radius: 5px;
            cursor: pointer;
        }
        .nav-button {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 15px;
            background-color: #00bfa5;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }
        .nav-button:hover {
            background-color: #009a8a;
        }
    </style>
</head>
<body>
    <div class="card">
    <div class="card-left">
        <img src="images/profile2.png" alt="Profile Picture">
            <h2><?php echo htmlspecialchars($_SESSION['username']); ?></h2> 
            <p><?php echo $bio; ?></p> 
                <a href="editprofile.php" class="nav-button">Edit Profile</a> 
    </div>

    <div class="card-right">
            <h3><?php echo $name; ?></h3> 
            <p style="margin-bottom: 20px;">Profile Information</p>
        
    <div class="info-section">
        <div class="label">AGE</div>
        <div class="value"><?php echo $age; ?> Years old</div>
    </div>
        
    <div class="info-section">
        <div class="label">WEIGHT</div>
        <div class="value"><?php echo $weight; ?> kg</div>
    </div>
        
    <div class="info-section">
        <div class="label">HEIGHT</div>
        <div class="value"><?php echo $height; ?></div>
    </div>
        
    <div class="info-section">
        <div class="label">EMERGENCY CONTACT</div>
        <div class="value"><?php echo $emergency_contact; ?></div>
    </div>
        
    <div class="info-section">
        <div class="label">ADDRESS</div>
        <div class="value"><?php echo $address; ?></div>
    </div>
        
    <div class="info-section">
        <a href="dashboard.php" class="nav-button">Return</a>
        <a href="register.php" class="nav-button">Logout</a>
    </div>

</body>
</html>
