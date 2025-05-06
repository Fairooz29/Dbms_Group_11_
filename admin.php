<?php
// Database connection
$host = '127.0.0.1';
$dbname = 'agritruck';
$username = 'root';
$password = '';

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Sample users data (in a real app, this would come from database)
$users = [
    ['id' => 1, 'name' => 'KANKHITA', 'role' => 'Admin'],
    ['id' => 2, 'name' => 'RAHUL', 'role' => 'Manager'],
    ['id' => 3, 'name' => 'PRIYA', 'role' => 'Analyst']
];

// Handle user switching
session_start();
if (isset($_GET['switch_user'])) {
    $userId = (int)$_GET['switch_user'];
    foreach ($users as $user) {
        if ($user['id'] === $userId) {
            $_SESSION['current_user'] = $user;
            break;
        }
    }
}

// Set default user if not set
if (!isset($_SESSION['current_user'])) {
    $_SESSION['current_user'] = $users[0];
}

// Handle API requests
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action'])) {
    header('Content-Type: application/json');
    
    if ($_GET['action'] === 'get_crops') {
        try {
            $stmt = $conn->prepare("SELECT * FROM crop");
            $stmt->execute();
            $crops = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'data' => $crops]);
            exit;
        } catch(PDOException $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            exit;
        }
    }
    
    if ($_GET['action'] === 'get_chart_data') {
        try {
            // Demand vs Supply data
            $demandSupplyData = [
                'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                'datasets' => [
                    [
                        'label' => 'Demand (tons)',
                        'data' => [120, 190, 170, 220, 250, 210, 200, 230, 240, 260, 280, 300],
                        'borderColor' => '#6a11cb',
                        'backgroundColor' => 'rgba(106, 17, 203, 0.1)',
                        'tension' => 0.4,
                        'fill' => true
                    ],
                    [
                        'label' => 'Supply (tons)',
                        'data' => [100, 170, 150, 200, 230, 200, 190, 210, 220, 240, 260, 280],
                        'borderColor' => '#00c6fb',
                        'backgroundColor' => 'rgba(0, 198, 251, 0.1)',
                        'tension' => 0.4,
                        'fill' => true
                    ]
                ]
            ];
            
            // Price trends data
            $stmt = $conn->prepare("SELECT CropName, price FROM crop LIMIT 6");
            $stmt->execute();
            $crops = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $priceTrendsData = [
                'labels' => array_column($crops, 'CropName'),
                'datasets' => [
                    [
                        'label' => 'Current Price (৳/kg)',
                        'data' => array_column($crops, 'price'),
                        'backgroundColor' => 'rgba(0, 119, 182, 0.7)',
                        'borderColor' => 'rgba(0, 119, 182, 1)',
                        'borderWidth' => 1
                    ],
                    [
                        'label' => 'Historical Avg (৳/kg)',
                        'data' => array_map(function($price) { return $price * 0.9; }, array_column($crops, 'price')),
                        'backgroundColor' => 'rgba(239, 83, 80, 0.7)',
                        'borderColor' => 'rgba(239, 83, 80, 1)',
                        'borderWidth' => 1
                    ]
                ]
            ];
            
            // Seasonal production data
            $seasonalData = [
                'labels' => ['Kharif', 'Rabi', 'Zaid', 'Summer', 'Winter', 'Monsoon'],
                'datasets' => [
                    [
                        'label' => 'Rice',
                        'data' => [90, 70, 40, 30, 20, 60],
                        'backgroundColor' => 'rgba(106, 17, 203, 0.2)',
                        'borderColor' => 'rgba(106, 17, 203, 1)',
                        'borderWidth' => 2
                    ],
                    [
                        'label' => 'Wheat',
                        'data' => [30, 80, 20, 40, 70, 30],
                        'backgroundColor' => 'rgba(0, 198, 251, 0.2)',
                        'borderColor' => 'rgba(0, 198, 251, 1)',
                        'borderWidth' => 2
                    ],
                    [
                        'label' => 'Pulses',
                        'data' => [50, 60, 30, 40, 50, 40],
                        'backgroundColor' => 'rgba(239, 83, 80, 0.2)',
                        'borderColor' => 'rgba(239, 83, 80, 1)',
                        'borderWidth' => 2
                    ]
                ]
            ];
            
            // Crop distribution data
            $cropDistributionData = [
                'labels' => ['Rice', 'Wheat', 'Pulses', 'Vegetables', 'Fruits', 'Others'],
                'datasets' => [[
                    'data' => [35, 25, 15, 10, 10, 5],
                    'backgroundColor' => [
                        '#6a11cb',
                        '#2575fc',
                        '#00c6fb',
                        '#005bea',
                        '#f46b45',
                        '#eea849'
                    ],
                    'borderWidth' => 1
                ]]
            ];
            
            echo json_encode([
                'success' => true,
                'demandSupply' => $demandSupplyData,
                'priceTrends' => $priceTrendsData,
                'seasonal' => $seasonalData,
                'cropDistribution' => $cropDistributionData
            ]);
            exit;
        } catch(PDOException $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            exit;
        }
    }
}

// Handle POST requests (add, update, delete)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    
    if (isset($_POST['action']) && $_POST['action'] === 'add_crop') {
        try {
            $stmt = $conn->prepare("INSERT INTO crop (CropName, price) VALUES (:name, :price)");
            $stmt->bindParam(':name', $_POST['name']);
            $stmt->bindParam(':price', $_POST['price']);
            $stmt->execute();
            echo json_encode(['success' => true, 'message' => 'Crop added successfully']);
            exit;
        } catch(PDOException $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            exit;
        }
    }
    
    if (isset($_POST['action']) && $_POST['action'] === 'update_crop') {
        try {
            $stmt = $conn->prepare("UPDATE crop SET CropName = :name, price = :price WHERE CropId = :id");
            $stmt->bindParam(':name', $_POST['name']);
            $stmt->bindParam(':price', $_POST['price']);
            $stmt->bindParam(':id', $_POST['id']);
            $stmt->execute();
            echo json_encode(['success' => true, 'message' => 'Crop updated successfully']);
            exit;
        } catch(PDOException $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            exit;
        }
    }
    
    if (isset($_POST['action']) && $_POST['action'] === 'delete_crop') {
        try {
            $stmt = $conn->prepare("DELETE FROM crop WHERE CropId = :id");
            $stmt->bindParam(':id', $_POST['id']);
            $stmt->execute();
            echo json_encode(['success' => true, 'message' => 'Crop deleted successfully']);
            exit;
        } catch(PDOException $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Admin Panel - Agricultural App</title>
  <link rel="stylesheet" href="admin.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <style>
    /* Minimal CSS for functionality */
    .dropdown-menu {
      display: none;
      position: absolute;
      background: white;
      box-shadow: 0 2px 5px rgba(0,0,0,0.2);
      z-index: 1000;
    }
    .user-dropdown:hover .dropdown-menu {
      display: block;
    }
    .user-dropdown {
      position: relative;
      cursor: pointer;
    }
  </style>
</head>
<body>
  <div class="container">
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
    
    <div class="main-content">
      <header class="top-nav">
        <div class="search-container">
          <i class="fas fa-search"></i>
          <input type="text" placeholder="Search..." />
        </div>
        <div class="top-actions">
          <span class="notification-icon"><i class="fas fa-envelope"></i></span>
          <span class="notification-icon"><i class="fas fa-bell"></i></span>
          <div class="user-dropdown">
            <span class="user"><i class="fas fa-user-circle"></i> <?php echo $_SESSION['current_user']['name']; ?> (<?php echo $_SESSION['current_user']['role']; ?>) ▾</span>
            <div class="dropdown-menu">
              <?php foreach ($users as $user): ?>
                <a href="?switch_user=<?php echo $user['id']; ?>" style="display: block; padding: 8px 12px; color: #333; text-decoration: none;">
                  <i class="fas fa-user"></i> <?php echo $user['name']; ?> (<?php echo $user['role']; ?>)
                </a>
              <?php endforeach; ?>
            </div>
          </div>
        </div>
      </header>

      <div class="dashboard-overview">
        <h1>Agricultural Demand-Supply Dashboard</h1>
        <div class="stats-cards">
          <div class="card card-purple">
            <div class="card-content">
              <h3>Weekly Sales</h3>
              <h2>৳ 15,0000</h2>
              <p class="positive"><i class="fas fa-arrow-up"></i> Increased by 40%</p>
            </div>
            <div class="card-icon">
              <i class="fas fa-shopping-cart"></i>
            </div>
          </div>
          
          <div class="card card-blue">
            <div class="card-content">
              <h3>Weekly Orders</h3>
              <h2>45,6334</h2>
              <p class="negative"><i class="fas fa-arrow-down"></i> Decreased by 19%</p>
            </div>
            <div class="card-icon">
              <i class="fas fa-clipboard-list"></i>
            </div>
          </div>
          
          <div class="card card-orange">
            <div class="card-content">
              <h3>Active Farmers</h3>
              <h2>95,5741</h2>
              <p class="positive"><i class="fas fa-arrow-up"></i> Increased by 5%</p>
            </div>
            <div class="card-icon">
              <i class="fas fa-users"></i>
            </div>
          </div>
        </div>
        
        <div class="chart-row">
          <div class="chart-container">
            <h3>Demand vs Supply Analysis</h3>
            <canvas id="demandSupplyChart"></canvas>
          </div>
          <div class="chart-container">
            <h3>Price Trends</h3>
            <canvas id="priceTrendChart"></canvas>
          </div>
        </div>
        
        <div class="chart-row">
          <div class="chart-container">
            <h3>Seasonal Production</h3>
            <canvas id="seasonalChart"></canvas>
          </div>
          <div class="pie-chart-container">
            <h3>Crop Distribution</h3>
            <canvas id="cropDistributionChart"></canvas>
          </div>
        </div>
        
        <div class="product-list">
          <div class="section-header">
            <h3>CHASHBASH : CROP LIST</h3>
            <button class="add-btn">➕ ADD YOUR PRODUCTS</button>
          </div>
          <table>
            <thead>
              <tr>
                <th>Crop ID</th>
                <th>Crop Name</th>
                <th>Type</th>
                <th>Variety</th>
                <th>Seasonality</th>
                <th>Historical Price</th>
                <th>Current Price</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              <?php
              try {
                  $stmt = $conn->prepare("SELECT * FROM crop");
                  $stmt->execute();
                  $crops = $stmt->fetchAll(PDO::FETCH_ASSOC);
                  
                  foreach ($crops as $crop) {
                      echo '<tr>
                          <td>'.$crop['CropId'].'</td>
                          <td>'.$crop['CropName'].'</td>
                          <td>N/A</td>
                          <td>N/A</td>
                          <td>All</td>
                          <td>'.($crop['price'] * 0.9).' ৳</td>
                          <td>'.$crop['price'].' ৳</td>
                          <td>
                              <button class="btn btn_edit" data-id="'.$crop['CropId'].'">Edit</button>
                              <button class="btn btn_delete" data-id="'.$crop['CropId'].'">Delete</button>
                          </td>
                      </tr>';
                  }
              } catch(PDOException $e) {
                  echo '<tr><td colspan="8">Error loading products: '.$e->getMessage().'</td></tr>';
              }
              ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
  
  <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize charts with empty data
        let demandSupplyChart, priceTrendChart, seasonalChart, cropDistributionChart;
        
        // Function to fetch and update all chart data
        function fetchChartData() {
            fetch(window.location.href.split('?')[0] + '?action=get_chart_data')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        updateCharts(data);
                    } else {
                        console.error('Error fetching chart data:', data.message);
                    }
                })
                .catch(error => console.error('Error:', error));
        }
        
        // Function to update all charts with new data
        function updateCharts(data) {
            // Update Demand vs Supply Chart
            if (demandSupplyChart) {
                demandSupplyChart.data = data.demandSupply;
                demandSupplyChart.update();
            } else {
                initDemandSupplyChart(data.demandSupply);
            }
            
            // Update Price Trends Chart
            if (priceTrendChart) {
                priceTrendChart.data = data.priceTrends;
                priceTrendChart.update();
            } else {
                initPriceTrendChart(data.priceTrends);
            }
            
            // Update Seasonal Production Chart
            if (seasonalChart) {
                seasonalChart.data = data.seasonal;
                seasonalChart.update();
            } else {
                initSeasonalChart(data.seasonal);
            }
            
            // Update Crop Distribution Chart
            if (cropDistributionChart) {
                cropDistributionChart.data = data.cropDistribution;
                cropDistributionChart.update();
            } else {
                initCropDistributionChart(data.cropDistribution);
            }
        }
        
        // Initialize charts
        function initDemandSupplyChart(data) {
            const ctx = document.getElementById('demandSupplyChart').getContext('2d');
            demandSupplyChart = new Chart(ctx, {
                type: 'line',
                data: data,
                options: {
                    responsive: true,
                    plugins: {
                        legend: { position: 'top' },
                        tooltip: { mode: 'index', intersect: false }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: { display: true, text: 'Quantity (tons)' }
                        }
                    }
                }
            });
        }
        
        function initPriceTrendChart(data) {
            const ctx = document.getElementById('priceTrendChart').getContext('2d');
            priceTrendChart = new Chart(ctx, {
                type: 'bar',
                data: data,
                options: {
                    responsive: true,
                    plugins: {
                        legend: { position: 'top' },
                        tooltip: { mode: 'index', intersect: false }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: { display: true, text: 'Price (৳/kg)' }
                        }
                    }
                }
            });
        }
        
        function initSeasonalChart(data) {
            const ctx = document.getElementById('seasonalChart').getContext('2d');
            seasonalChart = new Chart(ctx, {
                type: 'radar',
                data: data,
                options: {
                    responsive: true,
                    plugins: { legend: { position: 'top' } },
                    scales: {
                        r: {
                            angleLines: { display: true },
                            suggestedMin: 0,
                            suggestedMax: 100
                        }
                    }
                }
            });
        }
        
        function initCropDistributionChart(data) {
            const ctx = document.getElementById('cropDistributionChart').getContext('2d');
            cropDistributionChart = new Chart(ctx, {
                type: 'doughnut',
                data: data,
                options: {
                    responsive: true,
                    plugins: {
                        legend: { position: 'right' },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return `${context.label}: ${context.raw}%`;
                                }
                            }
                        }
                    }
                }
            });
        }
        
        // Initial fetch of chart data
        fetchChartData();
        
        // Handle delete button click
        document.querySelectorAll('.btn_delete').forEach(btn => {
            btn.addEventListener('click', function(e) {
                const cropId = e.target.getAttribute('data-id');
                if (confirm('Are you sure you want to delete this crop?')) {
                    const formData = new FormData();
                    formData.append('action', 'delete_crop');
                    formData.append('id', cropId);
                    
                    fetch(window.location.href.split('?')[0], {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert('Crop deleted successfully');
                            window.location.reload();
                        } else {
                            alert('Error deleting crop: ' + data.message);
                        }
                    })
                    .catch(error => console.error('Error:', error));
                }
            });
        });
        
        // Add product button handler
        document.querySelector('.add-btn').addEventListener('click', function() {
            const cropName = prompt('Enter crop name:');
            if (cropName) {
                const price = parseFloat(prompt('Enter price:'));
                if (!isNaN(price)) {
                    const formData = new FormData();
                    formData.append('action', 'add_crop');
                    formData.append('name', cropName);
                    formData.append('price', price);
                    
                    fetch(window.location.href.split('?')[0], {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert('Crop added successfully');
                            window.location.reload();
                        } else {
                            alert('Error adding crop: ' + data.message);
                        }
                    })
                    .catch(error => console.error('Error:', error));
                }
            }
        });
        
        // Dropdown functionality
        const dropdowns = document.querySelectorAll('.dropdown');
        dropdowns.forEach(dropdown => {
            dropdown.addEventListener('click', function(e) {
                e.preventDefault();
                const submenu = this.querySelector('.submenu');
                submenu.style.display = submenu.style.display === 'block' ? 'none' : 'block';
            });
        });
    });
  </script>
</body>
</html>