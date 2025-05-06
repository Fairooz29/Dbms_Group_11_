<?php
require_once 'config/db_connect.php';

if (isset($_GET['action'])) {
    header('Content-Type: application/json');
    $action = $_GET['action'];
    if ($action === 'get_crops') {
        try {
            $stmt = $pdo->query("SELECT CropId, CropName FROM crop ORDER BY CropName");
            $crops = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'data' => $crops]);
        } catch(PDOException $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit;
    } elseif ($action === 'get_months_for_crop') {
        if (!isset($_GET['cropId'])) {
            echo json_encode(['success' => false, 'error' => 'Missing cropId parameter']);
            exit;
        }
        $cropId = $_GET['cropId'];
        try {
            $stmt = $pdo->prepare("SELECT DISTINCT DATE_FORMAT(ot.orderDate, '%Y-%m') as month
                                   FROM order_line ol
                                   JOIN order_t ot ON ol.OrderId = ot.OrderId
                                   WHERE ol.CropId = ?
                                   ORDER BY ot.orderDate DESC");
            $stmt->execute([$cropId]);
            $months = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'data' => $months]);
        } catch(PDOException $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit;
    } elseif ($action === 'get_price_data') {
        if (!isset($_GET['cropId']) || !isset($_GET['month'])) {
            echo json_encode(['success' => false, 'error' => 'Missing required parameters']);
            exit;
        }
        $cropId = $_GET['cropId'];
        $month = $_GET['month'];
        try {
            // Get average price for the selected month (current price)
            $currentPriceStmt = $pdo->prepare("
                SELECT AVG(ol.price) as avgPrice
                FROM order_line ol
                JOIN order_t ot ON ol.OrderId = ot.OrderId
                WHERE ol.CropId = ? 
                AND DATE_FORMAT(ot.orderDate, '%Y-%m') = ?
            ");
            $currentPriceStmt->execute([$cropId, $month]);
            $currentPrice = $currentPriceStmt->fetch(PDO::FETCH_ASSOC);

            // Get historical average price (all prices before the selected month)
            $historicalStmt = $pdo->prepare("
                SELECT AVG(ol.price) as avgPrice
                FROM order_line ol
                JOIN order_t ot ON ol.OrderId = ot.OrderId
                WHERE ol.CropId = ? 
                AND DATE_FORMAT(ot.orderDate, '%Y-%m') < ?
            ");
            $historicalStmt->execute([$cropId, $month]);
            $historicalPrice = $historicalStmt->fetch(PDO::FETCH_ASSOC);

            // Calculate price change percentage
            $currentPriceValue = $currentPrice ? $currentPrice['avgPrice'] : 0;
            $historicalPriceValue = $historicalPrice ? $historicalPrice['avgPrice'] : 0;
            $priceChange = $historicalPriceValue > 0 
                ? (($currentPriceValue - $historicalPriceValue) / $historicalPriceValue) * 100 
                : 0;

            echo json_encode([
                'success' => true,
                'data' => [
                    'currentPrice' => $currentPriceValue,
                    'historicalPrice' => $historicalPriceValue,
                    'priceChange' => round($priceChange, 2)
                ]
            ]);
        } catch(PDOException $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Price Trends</title>
  <link rel="stylesheet" href="trends.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-zoom"></script>
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

    <!-- Main Dashboard -->
    <main class="dashboard">
      <section class="control-panel">
        <h2>Controls</h2>
        <div class="form-group">
          <label for="crop">Crop</label>
          <select id="crop">
            <option value="">Loading crops...</option>
          </select>
        </div>
        <div class="form-group">
          <label for="month">Month</label>
          <select id="month">
            <option value="">Loading months...</option>
          </select>
        </div>
        <div class="button-group">
          <button id="apply-filters" class="btn primary">Apply Filters</button>
          <button id="reset-filters" class="btn secondary">Reset</button>
        </div>
      </section>

      <section class="visualization-container">
        <!-- Chart -->
        <div class="chart-header">
          <h2>Price Trends</h2>
        </div>
        <div class="chart-container">
          <canvas id="trendChart"></canvas>
        </div>

        <!-- Summary Box -->
        <div class="summary-box">
          <p><strong>Current Price (<span id="selected-crop">-</span>):</strong> <span id="current-price">-</span></p>
          <p><strong>Average Historical Price:</strong> <span id="avg-price">-</span></p>
          <p><strong>Change:</strong> <span id="price-change">-</span></p>
        </div>

        <!-- Data Table -->
        <div class="data-grid">
          <h3>Data Table</h3>
          <table>
            <thead>
              <tr>
                <th>Date</th>
                <th>Crop</th>
                <th>Current Price (৳/kg)</th>
                <th>Historical Avg (৳/kg)</th>
                <th>Change</th>
              </tr>
            </thead>
            <tbody id="data-table-body">
            </tbody>
          </table>
        </div>
      </section>
    </main>
  </div>

  <!-- Chart Script -->
  <script>
    // Global reference to the chart
    let trendChart;
    
    // Load crops from the database
    async function loadCrops() {
      try {
        const response = await fetch('trends.php?action=get_crops');
        const result = await response.json();
        
        if (result.success) {
          const cropSelect = document.getElementById('crop');
          cropSelect.innerHTML = '';
          result.data.forEach(crop => {
            const option = document.createElement('option');
            option.value = crop.CropId;
            option.textContent = `${crop.CropName} (ID: ${crop.CropId})`;
            cropSelect.appendChild(option);
          });
        } else {
          console.error('Failed to load crops:', result.error);
        }
      } catch (error) {
        console.error('Error loading crops:', error);
      }
    }

    // Load months for a crop
    async function loadMonthsForCrop(cropId) {
      try {
        const response = await fetch(`trends.php?action=get_months_for_crop&cropId=${cropId}`);
        const result = await response.json();
        const monthSelect = document.getElementById('month');
        monthSelect.innerHTML = '';
        if (result.success && result.data.length > 0) {
          result.data.forEach(month => {
            const option = document.createElement('option');
            option.value = month.month;
            const date = new Date(month.month + '-01');
            option.textContent = date.toLocaleDateString('en-US', { month: 'long', year: 'numeric' });
            monthSelect.appendChild(option);
          });
        } else {
          const option = document.createElement('option');
          option.value = '';
          option.textContent = 'No months available';
          monthSelect.appendChild(option);
        }
      } catch (error) {
        console.error('Error loading months for crop:', error);
      }
    }

    // Listen for crop selection changes
    document.addEventListener('DOMContentLoaded', function() {
      document.getElementById('crop').addEventListener('change', function() {
        const cropId = this.value;
        if (cropId) {
          loadMonthsForCrop(cropId);
        } else {
          document.getElementById('month').innerHTML = '<option value="">Select a crop first</option>';
        }
      });
    });

    // Initialize the chart with default data
    function initializeChart() {
      const ctx = document.getElementById('trendChart').getContext('2d');
      trendChart = new Chart(ctx, {
        type: 'bar',
        data: {
          labels: ['Current Price', 'Historical Average'],
          datasets: [{
            data: [0, 0],
            backgroundColor: ['#0077b6', '#f28482'],
            hoverBackgroundColor: ['#005f87', '#c96a6a'],
            borderRadius: 4,
            barThickness: 40
          }]
        },
        options: {
          responsive: true,
          plugins: {
            legend: {
              display: false
            },
            tooltip: {
              callbacks: {
                label: (context) => `৳${context.parsed.y} per kg`
              }
            }
          },
          scales: {
            y: {
              beginAtZero: true,
              title: {
                display: true,
                text: 'Price (৳/kg)'
              }
            }
          }
        }
      });
    }
    
    // Apply filters when the button is clicked
    async function applyFilters() {
      const cropSelect = document.getElementById('crop');
      const monthSelect = document.getElementById('month');
      const cropId = cropSelect.value;
      const cropName = cropSelect.options[cropSelect.selectedIndex].text;
      const month = monthSelect.value;

      if (!cropId || !month) {
        alert('Please select both a crop and a month');
        return;
      }

      try {
        const response = await fetch(`trends.php?action=get_price_data&cropId=${cropId}&month=${month}`);
        const result = await response.json();

        if (result.success) {
          updateSummary(cropName, result.data);
          updateChart(result.data);
          updateDataTable(cropName, month, result.data);
        } else {
          alert('No data found for the selected crop and month.');
          console.error('Failed to load price data:', result.error);
        }
      } catch (error) {
        alert('Error loading price data.');
        console.error('Error loading price data:', error);
      }
    }

    // Reset filters to default
    function resetFilters() {
      document.getElementById('crop').selectedIndex = 0;
      document.getElementById('month').innerHTML = '<option value="">Select a crop first</option>';
      // Reset summary
      document.getElementById('selected-crop').textContent = '-';
      document.getElementById('current-price').textContent = '-';
      document.getElementById('avg-price').textContent = '-';
      document.getElementById('price-change').textContent = '-';
      // Reset chart
      trendChart.data.datasets[0].data = [0, 0];
      trendChart.update();
      // Clear data table
      document.getElementById('data-table-body').innerHTML = '';
    }

    // Update the summary box with information for the selected crop
    function updateSummary(cropName, data) {
      document.getElementById('selected-crop').textContent = cropName;
      document.getElementById('current-price').textContent = `৳${data.currentPrice}/kg`;
      document.getElementById('avg-price').textContent = `৳${data.historicalPrice}/kg`;
      const changeText = data.priceChange > 0 ? `+${data.priceChange}%` : `${data.priceChange}%`;
      document.getElementById('price-change').textContent = changeText;
    }

    // Update the chart with new data
    function updateChart(data) {
      trendChart.data.labels = ['Current Price', 'Historical Average'];
      trendChart.data.datasets[0].data = [data.currentPrice, data.historicalPrice];
      trendChart.update();
    }

    // Update the data table with information for the selected crop
    function updateDataTable(cropName, month, data) {
      const tableBody = document.getElementById('data-table-body');
      tableBody.innerHTML = '';
      const date = new Date(month + '-01');
      const formattedMonth = date.toLocaleDateString('en-US', { month: 'long', year: 'numeric' });
      const row = document.createElement('tr');
      row.innerHTML = `
        <td>${formattedMonth}</td>
        <td>${cropName}</td>
        <td>৳${data.currentPrice}</td>
        <td>৳${data.historicalPrice}</td>
        <td>${data.priceChange > 0 ? '+' : ''}${data.priceChange}%</td>
      `;
      tableBody.appendChild(row);
    }

    // On page load
    window.onload = async function () {
      await loadCrops();
      initializeChart();
      // No months loaded until crop is selected
      // Add event listeners
      document.getElementById('apply-filters').addEventListener('click', applyFilters);
      document.getElementById('reset-filters').addEventListener('click', resetFilters);
    };
  </script>
</body>
</html>
