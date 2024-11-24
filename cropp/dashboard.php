<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: register.php");
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

    $filterStatus = isset($_GET['status']) ? str_replace('-', ' ', strtolower($_GET['status'])) : 'all';

    $searchTerm = isset($_GET['search']) ? $_GET['search'] : '';

    $query = "SELECT crop_id, crop_name, field, status, description, image FROM crops";

    if (!empty($searchTerm)) {
        $query .= " WHERE crop_name LIKE :searchTerm";
    }

    if ($filterStatus !== 'all') {
        $query .= $searchTerm ? " AND status = :status" : " WHERE status = :status";
    }

    $query .= " ORDER BY CASE WHEN status = 'needs attention' THEN 1 ELSE 2 END, crop_name";

    $stmt = $pdo->prepare($query);

    if (!empty($searchTerm)) {
        $stmt->bindValue(':searchTerm', '%' . $searchTerm . '%');
    }

    if ($filterStatus !== 'all') {
        $stmt->bindValue(':status', $filterStatus);
    }

    $stmt->execute();
    $cropDetails = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $cropStats = [
        'Total Crops Monitored' => count($cropDetails),
        'Crops Needing Attention' => count(array_filter($cropDetails, function($crop) {
            $status = strtolower(trim($crop['status'])); // Normalize and check status
            return $status === 'needs attention'; // Match exact 'needs attention'
        })),
        'Crops in Good Condition' => count(array_filter($cropDetails, fn($crop) => strtolower(trim($crop['status'])) === 'good')),
        'Total Fields Monitored' => count(array_unique(array_column($cropDetails, 'field')))
    ];

} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crop Monitoring Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/normalize/8.0.1/normalize.min.css">
    <style>
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            display: absolute;
            flex-direction: column; 
            min-height: 100vh;
            background-color: #3e713e;
        }
        .header .welcome {
            display: 100px;
            font-size: 20px;
            top: auto;
            justify-content: space-between;
            align-items: center;
            background-color: #4C6444;
            color: white;
            padding: 15px;
            position: flex;
            width: 100%;
            z-index: 10;
        }
        .sidebar {
            height: 100%;
            width: 250px;
            background-color: #1a261a;
            padding-top: 20px;
            color: white;
            position: absolute;
            top: 52px;
            z-index: 1;
        }
        .sidebar a {
            padding: 15px 20px;
            text-decoration: none;
            font-size: 18px;
            color: white;
            display: block;
            transition: transform 0.3s;
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
            margin-left: 275px;
            margin-right: 0px;
            margin-top: 21px;
            padding: 20px;
            background-color: #fff;
            flex-grow: 1;
            border-radius: 15px;
        }
        .stats-container {
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 20px;
        }
        .stats-card {
            background-color: #3e713e;;
            border-radius: 15px;
            padding: 20px;
            width: 20%;
            text-align: center;
            box-shadow: 0px 4px 20px rgba(0, 0, 0, 0.1);
            transition: 0.3s;
        }
        .stats-card:hover {
            transform: translateY(-5px);
        }
        .stats-card h2 {
            color: white;
            font-size: 2em;
            margin: 0;
            
        }
        .stats-card p {
            margin: 10px 0 0;
            color: white;
        }
        .main-content h2 {
            font-size: 24px;
            margin-bottom: 20px;
        }
        .crop-overview {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            }

            .crop-card {
                background-color: white;
                border-radius: 10px;
                box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
                width: 100%;
                padding: 15px;
                display: flex;
                align-items: center; 
                justify-content: flex-start;
                transition: transform 0.3s;
                margin-top: 15px;
            }

            .crop-card img {
                width: 150px;
                height: 150px;
                border-radius: 5px;
                margin-left: 15px; 
                object-fit: cover; 
            }

            .crop-card .details {
                display: flex;
                flex-direction: column;
                justify-content: flex-start;
                align-items: flex-start; 
                flex: 1; 
            }

            .crop-card h3 {
                margin: 0;
                font-size: 20px;
                color: #333;
            }

            .crop-card p {
                margin: 5px 0;
                color: #777;
            }

            .crop-card .status {
                font-size: 15px;
                font-weight: bold;
                margin-bottom: 10px; 
            }

            div.crop-card p.status.good {
                color: blue;
            }

            div.crop-card p.status.needs-attention {
                color: red;
            }

            .crop-card .details p {
                margin: 1px 0;
                color: #555;
                
            }

            .action-buttons a {
                text-decoration: none;
                color: blue;
                padding: 5px;
                margin-right: 5px;
                transition: color 0.3s;
            }

            .action-buttons a:hover {
                color: #c82333;
            }

            .divider {
                padding: 0 5px;
            }

                    
        .search-bar-container {
            margin: 20px auto;
            text-align: left;
        }

        .search-bar-container input[type="text"] {
            width: 300px;
            padding: 12px;
            font-size: 16px;
            border: 2px solid #ddd; 
            border-radius: 25px; 
            outline: none
            transition: border-color 0.3s ease; 
        }

        .search-bar-container input[type="text"]:focus {
            border-color: #5b9bd5;
        }
        .search-bar-container button {
            padding: 12px 20px;
            font-size: 16px;
            background-color: #5b9bd5; 
            color: white;
            border: none;
            border-radius: 25px;
            cursor: pointer;
            margin-left: 10px;
            transition: background-color 0.3s ease;
        }

        .search-bar-container button:hover {
            background-color: #4a8ab6;
        }

        .search-bar-container button i {
            margin-right: 8px;
        }
</style>
    </style>
</head>
<body>
    <header class="header">
        <div class="welcome"><?php echo $welcome_message; ?></div>
    </header>

    <div class="sidebar">
        <div class="profile">
            <img src="images/profile2.png" alt="Profile Picture">
            <p><?php echo htmlspecialchars($_SESSION['username']); ?></p>
        </div>
        <a href="dashboard.php" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
        <a href="add-crop.php"><i class="fas fa-plus-circle"></i> Add Crop</a>
        <a href="profile.php"><i class="fas fa-user"></i> Profile</a>
        <a href="register.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>

    <div class="main-content">
    <div class="search-bar-container">
    <form method="GET" action="">
        <input type="text" name="search" placeholder="Search for a crop..." value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
        <button type="submit"><i class="fas fa-search"></i> Search</button>
    </form>
</div>
        <div class="stats-container">
            <?php foreach ($cropStats as $label => $value): ?>
                <?php
                $statusParam = match ($label) {
                    'Crops Needing Attention' => 'needs attention',
                    'Crops in Good Condition' => 'good',
                    default => 'all'
                };
                ?>
                <div class="stats-card">
                    <a href="?status=<?php echo urlencode($statusParam); ?>">
                        <h2><?php echo $value; ?></h2>
                        <p><?php echo $label; ?></p>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="crop-overview">
    <h2><?php echo $filterStatus === 'all' ? 'All Crops' : ucfirst($filterStatus) . ' Crops'; ?></h2>
    <?php if ($cropDetails): ?>
        <?php foreach ($cropDetails as $crop): ?>
            <div class="crop-card">
                <div class="details">
                <h3><?php echo htmlspecialchars($crop['crop_name'] ?? 'Unknown'); ?></h3>
                    
                    <p class="status <?php echo strtolower($crop['status'] ?? '') === 'good' ? 'good' : 'needs-attention'; ?>">
                        <?php echo ucfirst(strtolower($crop['status'] ?? 'Needs Attention')); ?>
                    </p>
                    
                    <p>Field: <?php echo htmlspecialchars($crop['field'] ?? 'Unknown'); ?></p>

                    <p class="description">Description: <?php echo htmlspecialchars($crop['description'] ?? 'None'); ?></p>

                    <div class="action-buttons">
                        <a href="editcrop.php?id=<?php echo $crop['crop_id']; ?>">Edit</a>
                        <span class="divider">|</span>
                        <a href="javascript:void(0);" onclick="confirmDelete(<?php echo $crop['crop_id']; ?>)">Delete</a>
                    </div>
                </div>
                
                <img src="uploads/<?php echo htmlspecialchars($crop['image'] ?? 'placeholder.jpg'); ?>" 
                     alt="<?php echo htmlspecialchars($crop['crop_name'] ?? 'Unknown Crop'); ?>">
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>No crops available.</p>
    <?php endif; ?>
</div>

    <script>
        function confirmDelete(cropId) {
            if (confirm("Are you sure you want to delete this crop?")) {
                window.location.href = "delete-crop.php?crop_id=" + cropId;
            }
        }
    </script>
    
</body>
</html>