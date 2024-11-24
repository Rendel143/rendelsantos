<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: index.php");
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
        $cropName = htmlspecialchars($_POST['cropName']);
        $field = htmlspecialchars($_POST['field']);
        $status = htmlspecialchars($_POST['status']);
        $description = htmlspecialchars($_POST['description']);

        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $image = $_FILES['image'];
            $imageName = time() . '_' . basename($image['name']); 
            $targetDirectory = "uploads/"; 
            $targetFilePath = $targetDirectory . $imageName;

            $check = getimagesize($image['tmp_name']);
            if ($check !== false) {
                if (move_uploaded_file($image['tmp_name'], $targetFilePath)) {
                    $stmt = $pdo->prepare("INSERT INTO crops (crop_name, field, status, description, image) VALUES (?, ?, ?, ?, ?)");

                    if ($stmt->execute([$cropName, $field, $status, $description, $imageName])) {
                        $_SESSION['message'] = "Crop added successfully!";
                    } else {
                        $_SESSION['message'] = "Failed to add crop. Please try again.";
                    }
                } else {
                    $_SESSION['message'] = "Failed to upload image.";
                }
            } else {
                $_SESSION['message'] = "File is not a valid image.";
            }
        } else {
            $_SESSION['message'] = "No image uploaded or there was an error.";
        }

        header("Location: add-crop.php");
        exit();
    }

} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
?>
