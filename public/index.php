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
    <title>Smart Parking System</title>
    <style> 
        body {
            background: linear-gradient(to right, #7694e6ff, #ffffffff);
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 100vh; 
        }

        .container {
            width: 100%;
            max-width: 900px;
        }

        .entryData, .exitData {
            background: white;
            padding: 25px;
            margin: 20px auto;
            width: 100%;
            max-width: 700px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            text-align: left;
        }

        .entryData form, .exitData form {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        input, select {
            padding: 8px;
            width: auto;
            border: 1px solid #ccc;
            border-radius: 4px;
            margin-left: 5px;
        }

        .entryData input[type="text"], .exitData input[type="text"] {
            width: 180px;
        }

        .entryData select {
            width: 150px;
        }

        button {
            padding: 8px 16px;
            border: none;
            border-radius: 6px;
            background: #4a6cf7;
            color: white;
            cursor: pointer;
        }

        button:hover {
            background: #3a54c9;
        }

        a {
            color: #291fbb96;
            font-weight: bold;
            text-decoration: none;
        }

        .report-btn {
            display: block;
            margin-top: 30px;
            text-align: center;
        }
        
        .report-data {
            background: white;
            padding: 25px;
            margin: 20px auto;
            width: 100%;
            max-width: 700px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            text-align: center;
        }

        .report-data table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        .report-data th, .report-data td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: center;
        }

        .report-data th {
            background-color: #f2f2f2;
            color: #333;
        }

        .report-data tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        h1, h3 {
            font-family: serif;
            color: #291fbb96;
        }
</style>

</head>
<body>
    <h1>Smart Parking</h1>
    
    <?php if($msg): ?>
        <p><strong><?= $msg ?></strong></p>
    <?php endif; ?>

    <?php if(!$showReport): ?>
        <div class= "entryData">
            <h3>Entry</h3>
            <form method="post">
                Plate: <input type="text" name="plate" required>
                Type: 
                <select name="type" required>
                    <option></option>
                    <option value="car">Car</option>
                    <option value="motorcycle">Motorcycle</option>
                    <option value="truck">Truck</option>
                </select><br><br>
                <button name="btn_entry">Register Entry</button>
            </form>
        </div>

        <div class = "exitData">
            <h3>Exit</h3>
            <form method="post">
                Plate: <input type="text" name="plate" required><br><br>
                <button name="btn_exit">Register exit</button>
            </form>
        </div>
        
        <a href="?r=1">Show report</a>

    <?php else: ?>
            <div class="report-data">
                <h3>Report</h3>
                <table border="1">
                    <tr>
                        <th>Type</th>
                        <th>Quantity</th>
                        <th>Total</th>
                    </tr>
                    <?php foreach($service->report() as $type => $data): ?>
                    <tr>
                        <td><?= ucfirst($type) ?></td>
                        <td><?= $data['total'] ?></td>
                        <td>R$ <?= number_format($data['billing'], 2, ',', '.') ?></td>
                    </tr>
                    <?php endforeach; ?>
                </table>
                <br>
                <a href="?">Back</a>
            </div>
        <?php endif; ?>

</body>
</html>