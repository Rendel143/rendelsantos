<?php
session_start();

$host = "localhost";
$dbname = "crop";
$username = "root";
$password = "";

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = isset($_POST['email']) ? $_POST['email'] : '';
    $current_password = isset($_POST['current_password']) ? $_POST['current_password'] : '';
    $new_password = isset($_POST['new_password']) ? $_POST['new_password'] : '';
    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        if (isset($_POST['send_otp'])) {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
            $stmt->bindParam(':email', $email, PDO::PARAM_STR);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                $forget_otp = rand(100000, 999999); 
                $expiry_time = date("Y-m-d H:i:s", strtotime("+10 minutes"));
                $updateStmt = $pdo->prepare("UPDATE users SET forget_otp = :forget_otp, otp_expiry = :expiry_time WHERE email = :email");
                $updateStmt->bindParam(':forget_otp', $forget_otp, PDO::PARAM_INT);
                $updateStmt->bindParam(':expiry_time', $expiry_time, PDO::PARAM_STR);
                $updateStmt->bindParam(':email', $email, PDO::PARAM_STR);
                $updateStmt->execute();
                $_SESSION['email'] = $email;
                $message = "OTP generated successfully. Please enter the OTP to change your password.";
                header("Location: otp.php");
                exit();
            } else {
                $message = "Email not found. Please check your email address.";
            }
        }
        if (isset($_POST['verify_otp'])) {
            $input_otp = isset($_POST['otp']) ? $_POST['otp'] : '';
            $new_password = isset($_POST['new_password']) ? $_POST['new_password'] : '';
            if (!empty($input_otp) && !empty($new_password)) {
                $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
                $stmt->bindParam(':email', $_SESSION['email'], PDO::PARAM_STR);
                $stmt->execute();
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($user) {
                    $stored_otp = isset($user['forget_otp']) ? $user['forget_otp'] : null;
                    $otp_expiry = isset($user['otp_expiry']) ? $user['otp_expiry'] : null;
                    if ($input_otp == $stored_otp && strtotime($otp_expiry) > time()) {
                        $new_password_hashed = password_hash($new_password, PASSWORD_DEFAULT);
                        $updateStmt = $pdo->prepare("UPDATE users SET password = :new_password WHERE email = :email");
                        $updateStmt->bindParam(':new_password', $new_password_hashed, PDO::PARAM_STR);
                        $updateStmt->bindParam(':email', $_SESSION['email'], PDO::PARAM_STR);
                        $updateStmt->execute();
                        unset($_SESSION['email']);
                        $message = "Password updated successfully.";
                        header("Location: register.php");
                        exit();
                    } else {
                        $message = "Invalid OTP or OTP has expired. Please request a new OTP.";
                    }
                }
            } else {
                $message = "Please fill in both the OTP and new password.";
            }
        }
        if (isset($_POST['request_new_otp'])) {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
            $stmt->bindParam(':email', $_SESSION['email'], PDO::PARAM_STR);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($user) {
                $forget_otp = rand(100000, 999999);
                $expiry_time = date("Y-m-d H:i:s", strtotime("+10 minutes"));
                $updateStmt = $pdo->prepare("UPDATE users SET forget_otp = :forget_otp, otp_expiry = :expiry_time WHERE email = :email");
                $updateStmt->bindParam(':forget_otp', $forget_otp, PDO::PARAM_INT);
                $updateStmt->bindParam(':expiry_time', $expiry_time, PDO::PARAM_STR);
                $updateStmt->bindParam(':email', $_SESSION['email'], PDO::PARAM_STR);
                $updateStmt->execute();
                $message = "New OTP generated. Please enter the new OTP to change your password.";
                header("Location: change_password.php");
                exit();
            }
        }
    } catch (PDOException $e) {
        $message = "Connection failed: " . $e->getMessage();
    }
}
?>
<!DOCTYPE HTML>
<html>
<head>
    <title>Change Password - Crop Monitoring</title>
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
        .form-box {
            width: 400px;
            padding: 40px;
            background-color: rgba(30, 80, 54, 0.7);
            border-radius: 10px;
            text-align: center;
            box-shadow: 0px 0px 20px rgba(0, 0, 0, 0.1);
            position: relative;
            z-index: 1;
        }
        .cover {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            border-radius: 10px;
            z-index: -1;
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
        }
        .form-box button {
            width: 100%;
            padding: 10px;
            background-color: #133814;;
            border: none;
            border-radius: 5px;
            color: black;
            font-size: 16px;
            cursor: pointer;
            gap: 5px;
        }
        .form-box button:hover {
            background-color: white;
        }
        .message {
            margin-bottom: 10px;
            color: white;  
        }
        .error-message {
            margin-bottom: 10px;
            color: red;  
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
        .modal {
            display: none;  
            position: fixed;  
            z-index: 9999;  
            left: 50%;  
            top: 50%;  
            transform: translate(-50%, -50%);
            width: 300px;  
            padding: 20px;
            box-sizing: border-box;
            border-radius: 8px;
            overflow: auto;
        }

        .modal-content {
            background-color: #fefefe;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2); 
        }

        .close {
            color: #aaa;
            font-size: 20px;
            position: absolute;
            right: 10px;
            top: 10px;
            font-weight: bold;
            cursor: pointer;
        }

        .close:hover {
            color: red;
        }

        .modal-content h2 {
            font-size: 18px;  
            margin-bottom: 15px;
        }

        .modal-content input {
            width: 90%;  
            padding: 8px;
            margin-bottom: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        .modal-content button {
            padding: 8px 12px;  
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .modal-content button:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
<header id="header">
    <h1>Crop Monitoring System</h1>
</header>

<div class="form-box">
    <?php if (!empty($message)): ?>
        <div class="message">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>

    <form method="POST">
        <h2>Change Password</h2>

        <?php if (!isset($_SESSION['otp'])): ?>
            <input type="email" name="email" placeholder="Enter your email" required><br>
            <button type="submit" name="send_otp">Send OTP</button>
        <?php else: ?>
            <input type="text" name="otp" placeholder="Enter OTP" required><br>
            <input type="password" name="new_password" placeholder="Enter new password" required><br>
            <button type="submit" name="verify_otp">Verify OTP and Update Password</button><br>
            <div style="margin-top: 10px;"></div>
            <button type="submit" name="request_new_otp">Request New OTP</button>
        <?php endif; ?>

        <p><a href="register.php">Return</a></p>
    </form>
</div>



<script>
    const modal = document.getElementById('otp-modal');
    const closeModal = document.getElementById('close-modal');
    closeModal.onclick = function() {
        modal.style.display = 'none';
    }
</script>
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
