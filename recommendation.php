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
    if (isset($_POST['add_recommendation'])) {
        // Add new recommendation
        $farmerId = $_POST['farmerId'];
        $details = $_POST['details'];
        
        $stmt = $conn->prepare("INSERT INTO farmer_recommendation (FarmerId, details, date) VALUES (?, ?, CURDATE())");
        $stmt->bind_param("is", $farmerId, $details);
        if (!$stmt->execute()) {
            die("Error adding recommendation: " . $stmt->error);
        }
        $stmt->close();
        header("Location: recommendation.php");
        exit();
    } elseif (isset($_POST['delete_id'])) {
        // Delete recommendation
        $id = $_POST['delete_id'];
        $stmt = $conn->prepare("DELETE FROM farmer_recommendation WHERE recommendationId=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
        header("Location: recommendation.php");
        exit();
    } elseif (isset($_POST['update_recommendation'])) {
        // Edit recommendation
        $id = $_POST['recommendationId'];
        $farmerId = $_POST['edit_farmer_id'];
        $details = $_POST['edit_details'];
        
        $stmt = $conn->prepare("UPDATE farmer_recommendation SET FarmerId=?, details=? WHERE recommendationId=?");
        $stmt->bind_param("isi", $farmerId, $details, $id);
        if (!$stmt->execute()) {
            die("Error updating recommendation: " . $stmt->error);
        }
        $stmt->close();
        header("Location: recommendation.php");
        exit();
    }
}

// Fetch farmer options
$farmers = $conn->query("SELECT FarmerId, FarmerName FROM farmer");

// Fetch recommendation data for table
$recommendations = $conn->query("
    SELECT fr.recommendationId, fr.FarmerId, f.FarmerName, fr.details, fr.date 
    FROM farmer_recommendation fr
    JOIN farmer f ON fr.FarmerId = f.FarmerId
    ORDER BY fr.date DESC
");

// Fetch data for chart
$chartData = $conn->query("
    SELECT f.FarmerName, COUNT(fr.recommendationId) as recommendation_count
    FROM farmer_recommendation fr
    JOIN farmer f ON fr.FarmerId = f.FarmerId
    GROUP BY f.FarmerName
")->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Farmer Recommendations</title>
  <link rel="stylesheet" href="consumer.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <style>
    .modal {
      display: none;
      position: fixed;
      z-index: 1;
      left: 0;
      top: 0;
      width: 100%;
      height: 100%;
      overflow: auto;
      background-color: rgba(0,0,0,0.4);
    }
    .modal-content {
      background-color: #fefefe;
      margin: 15% auto;
      padding: 20px;
      border: 1px solid #888;
      width: 80%;
      max-width: 600px;
    }
    .close {
      color: #aaa;
      float: right;
      font-size: 28px;
      font-weight: bold;
      cursor: pointer;
    }
    .close:hover {
      color: black;
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
      <h1>Farmer Recommendations</h1>
      
      <!-- Chart showing recommendations per farmer -->
      <canvas id="recommendationChart" width="800" height="400"></canvas>

      <!-- Add Recommendation Form -->
      <form method="POST" class="form-group">
        <select name="farmerId" id="farmerId" required>
          <?php while ($farmer = $farmers->fetch_assoc()): ?>
            <option value="<?= htmlspecialchars($farmer['FarmerId']) ?>"><?= htmlspecialchars($farmer['FarmerName']) ?></option>
          <?php endwhile; ?>
        </select>
        <textarea name="details" placeholder="Enter recommendation details" required></textarea>
        <button type="submit" name="add_recommendation">Add Recommendation</button>
      </form>

      <!-- Recommendations Table -->
      <table>
        <thead>
          <tr>
            <th>Farmer ID</th>
            <th>Farmer Name</th>
            <th>Recommendation</th>
            <th>Date</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody id="dataTable">
          <?php 
          $recommendations->data_seek(0);
          while ($row = $recommendations->fetch_assoc()): 
          ?>
            <tr>
              <td><?= htmlspecialchars($row['FarmerId']) ?></td>
              <td><?= htmlspecialchars($row['FarmerName']) ?></td>
              <td><?= htmlspecialchars($row['details']) ?></td>
              <td><?= htmlspecialchars($row['date']) ?></td>
              <td>
                <button onclick="openEditModal(
                  <?= htmlspecialchars($row['recommendationId']) ?>,
                  <?= htmlspecialchars($row['FarmerId']) ?>,
                  '<?= htmlspecialchars(addslashes($row['details'])) ?>'
                )">Edit</button>
                <form method="POST" style="display: inline;">
                  <input type="hidden" name="delete_id" value="<?= htmlspecialchars($row['recommendationId']) ?>">
                  <button type="submit">Delete</button>
                </form>
              </td>
            </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Edit Modal -->
  <div id="editModal" class="modal">
    <div class="modal-content">
      <span class="close" onclick="closeEditModal()">&times;</span>
      <h2>Edit Recommendation</h2>
      <form method="POST">
        <input type="hidden" name="recommendationId" id="modalRecommendationId">
        <select name="edit_farmer_id" id="modalFarmerId" required>
          <?php 
          $farmers->data_seek(0);
          while ($farmer = $farmers->fetch_assoc()): 
          ?>
            <option value="<?= htmlspecialchars($farmer['FarmerId']) ?>"><?= htmlspecialchars($farmer['FarmerName']) ?></option>
          <?php endwhile; ?>
        </select>
        <textarea name="edit_details" id="modalDetails" required></textarea>
        <button type="submit" name="update_recommendation">Save Changes</button>
      </form>
    </div>
  </div>

  <script>
    // Initialize chart with data from PHP
    const chartData = {
      labels: <?= json_encode(array_column($chartData, 'FarmerName')) ?>,
      datasets: [{
        label: "Recommendations Count",
        data: <?= json_encode(array_column($chartData, 'recommendation_count')) ?>,
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

    const chartCtx = document.getElementById("recommendationChart").getContext("2d");
    const chart = new Chart(chartCtx, {
      type: "bar",
      data: chartData,
      options: {
        responsive: true,
        scales: {
          y: {
            beginAtZero: true,
            title: { display: true, text: 'Number of Recommendations' },
            ticks: {
              stepSize: 1
            }
          },
          x: {
            title: { display: true, text: 'Farmer Name' }
          }
        }
      }
    });

    // Modal functions
    function openEditModal(id, farmerId, details) {
      document.getElementById('modalRecommendationId').value = id;
      document.getElementById('modalFarmerId').value = farmerId;
      document.getElementById('modalDetails').value = details;
      document.getElementById('editModal').style.display = 'block';
    }

    function closeEditModal() {
      document.getElementById('editModal').style.display = 'none';
    }

    // Close modal when clicking outside of it
    window.onclick = function(event) {
      const modal = document.getElementById('editModal');
      if (event.target === modal) {
        closeEditModal();
      }
    };
  </script>
</body>
</html>