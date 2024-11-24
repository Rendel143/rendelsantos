<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: register.php");
    exit();
}

if (isset($_GET['crop_id'])) {
    $crop_id = $_GET['crop_id'];

    $host = 'localhost';
    $dbname = 'crop';
    $db_username = 'root';
    $db_password = '';

    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname", $db_username, $db_password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $stmt = $pdo->prepare("DELETE FROM crops WHERE crop_id = ?");
        if ($stmt->execute([$crop_id])) {
            $_SESSION['message'] = "Crop deleted successfully.";
            header("Location: dashboard.php");
            exit();
        } else {
            echo "Error deleting crop.";
        }
    } catch (PDOException $e) {
        echo "Connection failed: " . $e->getMessage();
    }
} else {
    echo "Invalid crop ID.";
}
?>
