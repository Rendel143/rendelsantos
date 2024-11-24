<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

if (isset($_GET['logout'])) {
    session_unset();
    session_destroy();
    header("Location: dashboard.php");
    exit();
}

$showOtpModal = false;
$message = "";
$login_successful = isset($_SESSION['username']);

$host = 'localhost';
$dbname = 'crop';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        if (isset($_POST['register'])) {
            
            $username = $_POST['username'];
            $email = $_POST['email'];
            $password = $_POST['password'];
            $confirm_password = $_POST['confirm_password'];

            if ($password !== $confirm_password) {
                $message = "Passwords do not match.";
            } else {
               
                $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
                $stmt->execute([$email]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($user) {
                    $message = "Warning: Email is already registered.";
                } else {
                  
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    $otp = mt_rand(100000, 999999);

                    $stmt = $pdo->prepare("INSERT INTO users (username, email, password, otp) VALUES (?, ?, ?, ?)");
                    if ($stmt->execute([$username, $email, $hashed_password, $otp])) {
                        $_SESSION['email'] = $email;
                        $_SESSION['otp'] = $otp;
                        $message = "Registration successful! Please enter the OTP to verify.";
                        $showOtpModal = true;
                    } else {
                        $message = "Error: Registration failed.";
                    }
                }
            }
        } elseif (isset($_POST['verify_otp'])) {
            $input_otp = $_POST['otp'];
            $email = $_SESSION['email'];

            $stmt = $pdo->prepare("SELECT otp FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && $input_otp == $user['otp']) {
                $stmt = $pdo->prepare("UPDATE users SET otp_verified = 1 WHERE email = ?");
                if ($stmt->execute([$email])) {
                    $message = "Registration verified! You can now log in.";
                    unset($_SESSION['otp'], $_SESSION['email']);
                } else {
                    $message = "Error verifying OTP.";
                }
            } else {
                $message = "Invalid OTP. Please try again.";
                $showOtpModal = true;
            }
        } elseif (isset($_POST['signin'])) {
            
            $username = $_POST['username'];
            $password = $_POST['password'];

            $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['username'] = $user['username'];
                header("Location: dashboard.php");
                exit();
            } else {
                $message = "Warning: Invalid username or password.";
            }
        }
    }
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
?>


<!DOCTYPE HTML>
<html>
<head>
    <title>Crop Monitoring</title>
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
        #main {
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
            text-align: center;
            min-height: 60vh;
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

        .user-info {
            display: none;
            position: absolute;
            top: 10px;
            right: 10px;
            background: rgba(255, 255, 255, 0.9);
            border-radius: 5px;
            padding: 10px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.2);
            transition: all 0.3s ease-in-out;
            z-index: 1000;
        }
        .user-info.show {
            display: block;
            transform: translateX(0);
        }
        .user-info.hidden {
            display: block;
            transform: translateX(100%);
        }
        .logout-button, .login-signup-button {
            padding: 10px;
            border-radius: 5px;
            cursor: pointer;
            color: white;
            text-decoration: none;
            display: inline-block;
        }
        .logout-button {
            background-color: #ff4d4d;
        }
        .login-signup-button {
            background-color: #4CAF50;
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

        .form-toggle {
            display: flex;
            justify-content: center;
            margin-bottom: 20px;
        }

        .form-toggle button {
            background-color: #133814;
            padding: 10px 20px;
            margin: 0 5px;
            border-radius: 5px;
        }

        .form-toggle .active {
            background-color: white;
            color: black;
        }

        .message {
            margin-bottom: 20px;
            color: white;
            font-weight: ;
        }

        .error-message {
            margin-bottom: 20px;
            color: white;
            font-weight: ;
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
        <div class="<?php echo strpos($message, 'Error') === false ? 'message' : 'error-message'; ?>">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>

    <div class="form-toggle">
        <button id="login-btn" class="active">Login</button>
        <button id="signup-btn">Sign Up</button>
    </div>

    <form id="login-form" method="POST">
        <h2>Login</h2>
        <input type="text" name="username" placeholder="Username" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit" name="signin">Login</button>
        <p><a href="change_password.php">Forgot Password?</a></p>
    </form>

    <form id="signup-form" method="POST" style="display:none;">
        <h2>Sign Up</h2>
        <input type="text" name="username" placeholder="Username" required>
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Password" required>
        <input type="password" name="confirm_password" placeholder="Confirm Password" required>
        <button type="submit" name="register">Sign Up</button>
    </form>
</div>

<div id="otp-modal" class="modal" style="display:none;">
    <div class="modal-content">
        <span class="close" id="close-modal">&times;</span>
        <h2>Enter OTP</h2>
        <form method="POST">
            <input type="text" name="otp" placeholder="Enter OTP" required>
            <button type="submit" name="verify_otp">Verify OTP</button>
        </form>
    </div>
</div>

<script>
    const loginBtn = document.getElementById('login-btn');
    const signupBtn = document.getElementById('signup-btn');
    const loginForm = document.getElementById('login-form');
    const signupForm = document.getElementById('signup-form');
    const otpModal = document.getElementById('otp-modal');

    loginBtn.addEventListener('click', () => {
        loginForm.style.display = 'block';
        signupForm.style.display = 'none';
        loginBtn.classList.add('active');
        signupBtn.classList.remove('active');
    });

    signupBtn.addEventListener('click', () => {
        loginForm.style.display = 'none';
        signupForm.style.display = 'block';
        loginBtn.classList.remove('active');
        signupBtn.classList.add('active');
    });

    <?php if ($showOtpModal): ?>
        otpModal.style.display = 'block';
    <?php endif; ?>

    document.getElementById('close-modal').onclick = function () {
        otpModal.style.display = 'none';
    };
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
