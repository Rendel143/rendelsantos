<?php
session_start();

$host = 'localhost';
$dbname = 'crop';
$db_username = 'root';
$db_password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $db_username, $db_password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $user = $_SESSION['username'];
    $stmt = $pdo->prepare("SELECT email, age, weight, height, emergency_contact, address, bio FROM users WHERE username = ?");
    $stmt->execute([$user]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row) {
        $email = htmlspecialchars($row['email']);
        $age = htmlspecialchars($row['age']);
        $weight = htmlspecialchars($row['weight']);
        $height = htmlspecialchars($row['height']);
        $emergency_contact = htmlspecialchars($row['emergency_contact']);
        $address = htmlspecialchars($row['address']);
        $bio = htmlspecialchars($row['bio']);
    } else {
        echo "User data not found.";
        exit();
    }

    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update'])) {
        $new_email = $_POST['email'];
        $new_age = $_POST['age'];
        $new_weight = $_POST['weight'];
        $new_height = $_POST['height'];
        $new_emergency_contact = $_POST['emergency_contact'];
        $new_address = $_POST['address'];
        $new_bio = $_POST['bio'];

        $stmt = $pdo->prepare("UPDATE users SET email=?, age=?, weight=?, height=?, emergency_contact=?, address=?, bio=? WHERE username=?");
        
        if ($stmt->execute([$new_email, $new_age, $new_weight, $new_height, $new_emergency_contact, $new_address, $new_bio, $user])) {
           
            header("Location: profile.php");
            exit();
        } else {
            $message = "Error updating profile.";
        }
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
    <title>Edit Profile - Crop Monitoring</title>
    <link rel="stylesheet" href="assets/css/main.css" />
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #3e713e;
        }

        .form-container {
            max-width: 500px;
            margin: 0 auto;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
            background-color: #f9f9f9;
        }

        .form-container h2 {
            text-align: center;
            margin-bottom: 20px;
        }

        .form-container input {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        .form-container button {
            width: 100%;
            padding: 10px;
            background-color: lightcyan;
            border: none;
            border-radius: 10px;
            color: black;
            font-size: 16px;
            cursor: pointer;
        }

        .form-container button:hover {
            background-color: #3e713e;
        }

        .message {
            margin-bottom: 20px;
            color: green;
            font-weight: bold;
            text-align: center;
        }

        .error-message {
            margin-bottom: 20px;
            color: red;
            font-weight: bold;
            text-align: center;
        }
    </style>
</head>
<body>

<div class="form-container">
    <h2>Edit Profile</h2>

    <?php if (!empty($message)): ?>
        <div class="message"><?php echo $message; ?></div>
    <?php endif; ?>

    <?php if (isset($error)): ?>
        <div class="error-message"><?php echo $error; ?></div>
    <?php endif; ?>

    <form method="POST">
        <input type="email" name="email" placeholder="Email" value="<?php echo htmlspecialchars($email); ?>" required>
        <input type="number" name="age" placeholder="Age" value="<?php echo htmlspecialchars($age); ?>" required>
        <input type="number" name="weight" placeholder="Weight (kg)" value="<?php echo htmlspecialchars($weight); ?>" required>
        <input type="text" name="height" placeholder="Height (e.g. 5'7\")" value="<?php echo htmlspecialchars($height); ?>" required>
        <input type="text" name="emergency_contact" placeholder="Emergency Contact" value="<?php echo htmlspecialchars($emergency_contact); ?>" required>
        <input type="text" name="address" placeholder="Address" value="<?php echo htmlspecialchars($address); ?>" required>
        <textarea name="bio" placeholder="Bio" rows="4" required><?php echo htmlspecialchars($bio); ?></textarea>

        <button type="submit" name="update">Update Profile</button>
    </form>
</div>

</body>
</html>
