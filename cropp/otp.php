<?php
session_start();

$host = "localhost";
$dbname = "crop";
$username = "root";
$password = "";

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input_otp = isset($_POST['otp']) ? $_POST['otp'] : '';
    $new_password = isset($_POST['new_password']) ? $_POST['new_password'] : '';

    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        if (!empty($input_otp) && !empty($new_password)) {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
            $stmt->bindParam(':email', $_SESSION['email'], PDO::PARAM_STR);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user) {
                $stored_otp = $user['forget_otp'];
                $otp_expiry = $user['otp_expiry'];

                if ($input_otp == $stored_otp && strtotime($otp_expiry) > time()) {
                    $new_password_hashed = password_hash($new_password, PASSWORD_DEFAULT);

                    $updateStmt = $pdo->prepare("UPDATE users SET password = :new_password WHERE email = :email");
                    $updateStmt->bindParam(':new_password', $new_password_hashed, PDO::PARAM_STR);
                    $updateStmt->bindParam(':email', $_SESSION['email'], PDO::PARAM_STR);
                    $updateStmt->execute();

                    unset($_SESSION['email']);
                    $message = "Password updated successfully!";
                    header("Location: register.php"); 
                    exit();
                } else {
                    $message = "Invalid OTP or OTP has expired. Please request a new OTP.";
                }
            }
        } else {
            $message = "Please fill in both the OTP and new password.";
        }
    } catch (PDOException $e) {
        $message = "Connection failed: " . $e->getMessage();
    }
}
?>

<!DOCTYPE HTML>
<html>
<head>
    <title>OTP Verification - Crop Monitoring</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no" />
    <link rel="stylesheet" href="assets/css/main.css" />
    <style>
        body {
            background-image: url('images/body.jpg');
            background-repeat: no-repeat;
            background-size: cover;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        #header {
            background-color: rgba(30, 80, 54, 0.7);
            color: white; 
            position: absolute; 
            top: 0; 
            width: 100%;   
            padding: 20px; 
            font-family: 'Times New Roman', Times, serif; 
        }

        #footer {
            background-color: rgba(30, 80, 54, 0.7); 
            color: white;
            padding: 10px;
            text-align: center;
            position: absolute;
            bottom: 0;
            width: 100%;
        }

        #footer .icons {
            list-style: none;
            padding: 0;
            margin: 0;
            display: flex;
            justify-content: center;
            align-items: center; 
            height: 100%; 
        }

        #footer .icons li {
            margin: 0 5px;
        }

        #footer .icons li a {
            color: white;
            font-size: 10px;
            text-decoration: none;
        }

        #main {
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
            text-align: center;
            min-height: 60vh;
        }

        .form-box {
            width: 400px;
            padding: 40px;
            background-color: rgba(30, 80, 54, 0.7);
            border-radius: 10px;
            text-align: center;
            box-shadow: 0px 0px 20px rgba(0, 0, 0, 0.1);
            position: relative;
            z-index: 1;
            color: white;
        }

        .form-box h2 {
            margin-bottom: 30px;
            color: #333;
        }

        .form-box input {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
            color: #333; 
        }

        .form-box input:focus {
            color: black; 
            border-color: #28a745;
            outline: none;
        }

        .form-box button {
            width: 100%;
            padding: 10px;
            background-color: #133814;
            border: none;
            border-radius: 5px;
            color: black;
            font-size: 16px;
            cursor: pointer;
        }

        .form-box button:hover {
            background-color: white;
        }

    </style>
</head>
<body>
<header id="header">
    <h1>Crop Monitoring System</h1>
</header>

<div class="form-box">
    <h2>Verify OTP and Change Password</h2>
    
    <?php if (!empty($message)): ?>
        <div class="error-message"><?php echo $message; ?></div>
    <?php endif; ?>

    <form method="POST">
        <input type="text" name="otp" placeholder="Enter OTP" required><br>
        <input type="password" name="new_password" placeholder="Enter new password" required><br>
        <button type="submit">Verify OTP and Update Password</button>
    </form>
</div>
<footer id="footer">
        <ul class="icons">
            <li><a href="#" class="icon brands fa-twitter"><span class="label">Twitter</span></a></li>
            <li><a href="#" class="icon brands fa-facebook-f"><span class="label">Facebook</span></a></li>
            <li><a href="#" class="icon brands fa-instagram"><span class="label">Instagram</span></a></li>
            <li><a href="#" class="icon brands fa-github"><span class="label">Github</span></a></li>
            <li><a href="#" class="icon brands fa-dribbble"><span class="label">Dribbble</span></a></li>
            <li><a href="#" class="icon brands fa-google-plus"><span class="label">Google+</span></a></li>
        </ul>
    </footer>
</body>
</html>
