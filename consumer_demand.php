<?php
// Database connection
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'agritruck';

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_data'])) {
        // Add new data
        $cropId = $_POST['CropId'];
        $rate = $_POST['rate'];
        $demandValue = $rate === "High" ? 3 : ($rate === "Medium" ? 2 : 1); // Changed Low to 1 for better chart visibility
        
        $stmt = $conn->prepare("INSERT INTO consumer_demand (CropId, ConsumptionRate, DemandValue) VALUES (?, ?, ?)");
        $stmt->bind_param("isd", $cropId, $rate, $demandValue);
        $stmt->execute();
    } elseif (isset($_POST['delete_id'])) {
        // Delete data
        $id = $_POST['delete_id'];
        $stmt = $conn->prepare("DELETE FROM consumer_demand WHERE id=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
    } elseif (isset($_POST['edit_id'])) {
        // Edit data
        $id = $_POST['edit_id'];
        $cropId = $_POST['edit_crop_id'];
        $rate = $_POST['edit_rate'];
        $demandValue = $rate === "High" ? 3 : ($rate === "Medium" ? 2 : 1);
        
        $stmt = $conn->prepare("UPDATE consumer_demand SET CropId=?, ConsumptionRate=?, DemandValue=? WHERE id=?");
        $stmt->bind_param("isdi", $cropId, $rate, $demandValue, $id);
        $stmt->execute();
    }
}

// Fetch crop options
$crops = $conn->query("SELECT CropId, CropName FROM crop");

// Fetch consumer demand data for table
$demandData = $conn->query("
    SELECT cd.id, cd.CropId, c.CropName, cd.ConsumptionRate, cd.DemandValue 
    FROM consumer_demand cd
    JOIN crop c ON cd.CropId = c.CropId
");

// Fetch consumer demand data for chart (separate query)
$chartDataQuery = $conn->query("
    SELECT c.CropName, cd.DemandValue 
    FROM consumer_demand cd
    JOIN crop c ON cd.CropId = c.CropId
");
$chartData = $chartDataQuery->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Consumer Demand Data</title>
  <link rel="stylesheet" href="consumer.css" />
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <style>
    .edit-form {
      display: none;
      background: #f9f9f9;
      padding: 10px;
      margin: 10px 0;
      border: 1px solid #ddd;
    }
  </style>
</head>
<body>
  <div class="container">
    <!-- Sidebar -->
    <div class="sidebar">
      <h2>AgriTruck</h2>
      <ul class="nav">
        <li class="dropdown">
          <a href="#">Crop ▾</a>
          <ul class="submenu">
            <li><a href="crops.php">Crop Info</a></li>
            <li><a href="historical_data.php">Data</a></li>
          </ul>
        </li>
        <li class="dropdown">
          <a href="#">Consumer ▾</a>
          <ul class="submenu">
            <li><a href="consumer_demand.php">Consumer Demand</a></li>
            <li><a href="price_elasticity.php">Price Elasticity</a></li>
          </ul>
        </li>
        <li><a href="warehouse.php">Real Time Supply</a></li>
        <li><a href="trends.php">Trends</a></li>
        <li><a href="recommendation.php">Recommendations</a></li>
        <li><a href="directories.php">Directory</a></li>
      </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
      <h1>Consumption Rate</h1>
      <canvas id="consumptionChart" width="800" height="400"></canvas>

      <form method="POST" class="form-group">
        <select name="CropId" id="CropId" required>
          <?php while ($crop = $crops->fetch_assoc()): ?>
            <option value="<?= $crop['CropId'] ?>"><?= $crop['CropName'] ?></option>
          <?php endwhile; ?>
        </select>
        <select name="rate" id="rate" required>
          <option value="Low">Low</option>
          <option value="Medium">Medium</option>
          <option value="High">High</option>
        </select>
        <button type="submit" name="add_data">Add Data</button>
      </form>

      <table>
        <thead>
          <tr>
            <th>Crop ID</th>
            <th>Crop Name</th>
            <th>Consumption Rate</th>
            <th>Demand</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody id="dataTable">
          <?php 
          // Reset pointer for demand data
          $demandData->data_seek(0);
          while ($row = $demandData->fetch_assoc()): 
          ?>
            <tr data-id="<?= $row['id'] ?>">
              <td class="crop-id"><?= $row['CropId'] ?></td>
              <td class="crop-name"><?= $row['CropName'] ?></td>
              <td class="consumption-rate"><?= $row['ConsumptionRate'] ?></td>
              <td class="demand-value"><?= $row['DemandValue'] ?></td>
              <td>
                <button class="edit-btn" data-id="<?= $row['id'] ?>">Edit</button>
                <form method="POST" style="display: inline;">
                  <input type="hidden" name="delete_id" value="<?= $row['id'] ?>">
                  <button class="delete" type="submit">Delete</button>
                </form>
              </td>
            </tr>
            <tr class="edit-form" id="edit-form-<?= $row['id'] ?>">
              <td colspan="5">
                <form method="POST">
                  <input type="hidden" name="edit_id" value="<?= $row['id'] ?>">
                  <select name="edit_crop_id" required>
                    <?php 
                    $crops->data_seek(0);
                    while ($crop = $crops->fetch_assoc()): 
                    ?>
                      <option value="<?= $crop['CropId'] ?>" <?= $crop['CropId'] == $row['CropId'] ? 'selected' : '' ?>>
                        <?= $crop['CropName'] ?>
                      </option>
                    <?php endwhile; ?>
                  </select>
                  <select name="edit_rate" required>
                    <option value="Low" <?= $row['ConsumptionRate'] == 'Low' ? 'selected' : '' ?>>Low</option>
                    <option value="Medium" <?= $row['ConsumptionRate'] == 'Medium' ? 'selected' : '' ?>>Medium</option>
                    <option value="High" <?= $row['ConsumptionRate'] == 'High' ? 'selected' : '' ?>>High</option>
                  </select>
                  <button type="submit">Save</button>
                  <button type="button" class="cancel-edit">Cancel</button>
                </form>
              </td>
            </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </div>

  <script>
    // Initialize chart with data from PHP
    const chartData = {
      labels: <?= json_encode(array_column($chartData, 'CropName')) ?>,
      datasets: [{
        label: "Demand Value",
        data: <?= json_encode(array_column($chartData, 'DemandValue')) ?>,
        backgroundColor: [
          "#FF6B6B", "#4D96FF", "#6B6BFF", "#FFA500", "#33CC99",
          "#96FF4D", "#CC9933", "#A500FF", "#9933CC", "#FF4D96"
        ],
        borderColor: [
          "#FF0000", "#0066FF", "#0000FF", "#FF8C00", "#009966",
          "#66FF00", "#996600", "#9900FF", "#660099", "#FF0066"
        ],
        borderWidth: 1
      }]
    };

    const chartCtx = document.getElementById("consumptionChart").getContext("2d");
    const chart = new Chart(chartCtx, {
      type: "bar",
      data: chartData,
      options: {
        responsive: true,
        scales: {
          y: {
            beginAtZero: true,
            title: { display: true, text: 'Demand Value' },
            ticks: {
              stepSize: 1
            }
          },
          x: {
            title: { display: true, text: 'Crop Name' }
          }
        }
      }
    });

    // Edit button functionality
    document.querySelectorAll('.edit-btn').forEach(btn => {
      btn.addEventListener('click', function() {
        const id = this.getAttribute('data-id');
        document.getElementById(`edit-form-${id}`).style.display = 'table-row';
      });
    });

    // Cancel edit button functionality
    document.querySelectorAll('.cancel-edit').forEach(btn => {
      btn.addEventListener('click', function() {
        this.closest('.edit-form').style.display = 'none';
      });
    });

    // Refresh page after form submissions to update chart
    document.querySelectorAll('form').forEach(form => {
      form.addEventListener('submit', function(e) {
        if (!this.querySelector('[name="delete_id"]')) { // Skip for delete forms
          setTimeout(() => {
            window.location.reload();
          }, 100);
        }
      });
    });
  </script>
</body>
</html>