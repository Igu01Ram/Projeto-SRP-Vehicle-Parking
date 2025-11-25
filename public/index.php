<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Infra\Database\Connection;
use App\Infra\Repository\SQLiteVehicleRepository;
use App\Infra\Repository\SQLiteParkingRepository;
use App\Application\ParkingService;

$pdo = Connection::getInstance(__DIR__ . '/../database.sqlite');
$service = new ParkingService(
    new SQLiteVehicleRepository($pdo),
    new SQLiteParkingRepository($pdo)
);

$msg = '';

if ($_POST) {
    try {
        if (isset($_POST['btn_entry'])) {
            $service->entry($_POST['plate'], $_POST['type']);
            $msg = "Registered entry!";
        }
        if (isset($_POST['btn_exit'])) {
            $value = $service->exit($_POST['plate']);
            $msg = "Pay: R$ " . number_format($value, 2, ',', '.');
        }
    } catch (Exception $e) {
        $msg = $e->getMessage();
    }
}

$showReport = isset($_GET['r']);
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Smart Park Project</title>
    <style> 
        
        body {
            background: linear-gradient(to left, #506dccff, #c3dafe); 
            font-family: serif;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }

        .parking-card, .report-data {
            background: white;
            padding: 40px;
            width: 100%;
            max-width: 650px; 
            border-radius: 20px; 
            text-align: left;
        }

        h1, h3 {
            font-family: Serif;
            color: #483d8b; 

        }
        
        .input-row {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
            gap: 15px; 
        }
        
        input[type="text"], select {
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 8px; 
            flex-grow: 1; 
            max-width: 200px;
            transition: border-color 0.3s;
        }

        button.main-button {
            padding: 10px 20px;
            border-radius: 8px;
            background: #4a6cf7; 
            color: white;
            font-weight: bold;
            cursor: pointer;
        }

        button.main-button:hover {
            background: #3a54c9;
        }

        .report-link {
            display: block;
            margin-top: 30px;
            text-align: center;
            color: #483d8b;
            font-weight: bold;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 8px;
        }
        
        .report-data {
            text-align: center;
        }
        
        .report-data table {
            width: 100%;
            margin-top: 15px;
            border: 1px solid #e0e7ff;
            border-radius: 8px;
        }

        .report-data th, .report-data td {
            border: 1px solid #e0e7ff;
            padding: 12px;
            text-align: center;
        }
    </style>

</head>
<body>
    <h1>Smart Parking</h1>
    
    <?php if($msg): ?>
        <p class="message-box"><strong><?= $msg ?></strong></p>
    <?php endif; ?>

    <?php if(!$showReport): ?>
        <div class="parking-card"> 
            
            <div class="form-section entry-section">
                <h3>Entry</h3>
                <form method="post">
                    <div class="input-row plate-row"> 
                        <label for="plate-entry">Plate:</label>
                        <input type="text" id="plate-entry" name="plate" required>
                    </div>
                    <div class="input-row type-row"> 
                        <label for="type-entry">Type:</label>
                        <select id="type-entry" name="type" required>
                            <option value=""></option>
                            <option value="car">Car</option>
                            <option value="motorcycle">Motorcycle</option>
                            <option value="truck">Truck</option>
                        </select>
                        <button name="btn_entry" class="main-button">Register Entry</button>
                    </div>
                </form>
            </div>

            <div class="form-section exit-section">
                <h3>Exit</h3>
                <form method="post">
                    <div class="input-row exit-row"> 
                        <label for="plate-exit">Plate:</label>
                        <input type="text" id="plate-exit" name="plate" required>
                        <button name="btn_exit" class="main-button">Register exit</button>
                    </div>
                </form>
            </div>
        </div>
        
        <a href="?r=1" class="report-link">Show report</a>

    <?php else: ?>
        <div class="report-data">
            <h3>Report</h3>
            <table border="0">
                <thead>
                    <tr>
                        <th>Type</th>
                        <th>Quantity</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($service->report() as $type => $data): ?>
                    <tr>
                        <td><?= ucfirst($type) ?></td>
                        <td><?= $data['total'] ?></td>
                        <td>R$ <?= number_format($data['billing'], 2, ',', '.') ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <br>
            <a href="?">Back</a>
        </div>
    <?php endif; ?>

</body>
</html>