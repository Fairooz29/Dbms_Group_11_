<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "agritruck";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch initial data for the main table
$harvests = $conn->query("SELECT h.*, 
                                 c.CropName, 
                                 v.vendorName, 
                                 h.WarehouseId
                          FROM harvest h
                          JOIN crop c ON h.CropId = c.CropId
                          JOIN vendor v ON h.VendorId = v.VendorId
                          ORDER BY h.harvestYear DESC");

// Handle filtering for the main table
$filteredHarvests = null;
if (isset($_POST['filter_option'])) {
    $filterOption = $_POST['filter_option'];
    $sql = "SELECT h.*, c.CropName, v.vendorName, w.WarehouseName
            FROM harvest h
            JOIN crop c ON h.CropId = c.CropId
            JOIN vendor v ON h.VendorId = v.VendorId
            JOIN warehouse w ON h.WarehouseId = w.WarehouseId";
    switch ($filterOption) {
        case 'highest_yield':
            $sql .= " ORDER BY h.yields DESC";
            break;
        case 'largest_acreage':
            $sql .= " ORDER BY h.acreage DESC";
            break;
        case 'lowest_cost':
            $sql .= " ORDER BY h.harvestingCost ASC";
            break;
        case 'highest_price':
            $sql .= " ORDER BY h.harvestPrice DESC";
            break;
        default:
            $sql .= " ORDER BY h.harvestYear DESC";
            break;
    }
    $filteredHarvests = $conn->query($sql);
} else {
    $filteredHarvests = $harvests;
}

// Fetch crops for the analysis filter
$analysisCrops = $conn->query("SELECT CropId, CropName FROM crop ORDER BY CropName");

// Initialize data arrays for the first analysis table (average over years)
$overviewData = [];
$overviewResult = $conn->query("SELECT
                                    c.CropName,
                                    AVG(h.yields) AS average_yield,
                                    AVG(h.acreage) AS average_acreage,
                                    AVG(h.harvestingCost) AS average_cost
                                FROM harvest h
                                JOIN crop c ON h.CropId = c.CropId
                                GROUP BY c.CropName
                                ORDER BY c.CropName ASC");
if ($overviewResult->num_rows > 0) {
    while ($row = $overviewResult->fetch_assoc()) {
        $overviewData[] = $row;
    }
}

// Initialize data arrays for the second analysis table (trend over years for a selected crop)
$historicalData = [];
$selectedCropName = '';
if (isset($_POST['crop_filter']) && $_POST['crop_filter'] != '') {
    $selectedCropId = $_POST['crop_filter'];
    $selectedCropResult = $conn->query("SELECT CropName FROM crop WHERE CropId = $selectedCropId");
    if ($selectedCropResult && $selectedCropResult->num_rows > 0) {
        $row = $selectedCropResult->fetch_assoc();
        $selectedCropName = $row['CropName'];
    }

    $sql = "SELECT
                c.CropName,
                h.harvestYear,
                AVG(h.yields) AS average_yield,
                AVG(h.acreage) AS average_acreage,
                AVG(h.harvestingCost) AS average_cost
            FROM harvest h
            JOIN crop c ON h.CropId = c.CropId
            WHERE h.CropId = ?
            GROUP BY c.CropName, h.harvestYear
            ORDER BY h.harvestYear ASC";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $selectedCropId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $historicalData[] = $row;
        }
    }
    $stmt->close();
}

// New Analysis Table 3: Total yield per vendor
$vendorYieldData = [];
$vendorYieldResult = $conn->query("SELECT
                                        v.vendorName,
                                        SUM(h.yields) AS total_yield
                                    FROM harvest h
                                    JOIN vendor v ON h.VendorId = v.VendorId
                                    GROUP BY v.vendorName
                                    ORDER BY total_yield DESC");
if ($vendorYieldResult->num_rows > 0) {
    while ($row = $vendorYieldResult->fetch_assoc()) {
        $vendorYieldData[] = $row;
    }
}

// New Analysis Table 4: Average price per crop
$cropPriceData = [];
$cropPriceResult = $conn->query("SELECT
                                    c.CropName,
                                    AVG(h.harvestPrice) AS average_price
                                FROM harvest h
                                JOIN crop c ON h.CropId = c.CropId
                                GROUP BY c.CropName
                                ORDER BY average_price DESC");
if ($cropPriceResult->num_rows > 0) {
    while ($row = $cropPriceResult->fetch_assoc()) {
        $cropPriceData[] = $row;
    }
}

// Query for average yield, cost, and acreage by year
$averageDataByYear = $conn->query("SELECT harvestYear, 
                                          AVG(yields) AS avg_yield, 
                                          AVG(harvestingCost) AS avg_cost, 
                                          AVG(acreage) AS avg_acreage
                                   FROM harvest
                                   GROUP BY harvestYear
                                   ORDER BY harvestYear DESC");

// Query for top 5 years with the highest yield
$topYieldYears = $conn->query("SELECT harvestYear, 
                                      SUM(yields) AS total_yield
                               FROM harvest
                               GROUP BY harvestYear
                               ORDER BY total_yield DESC
                               LIMIT 5");

// Query for top 5 crops with the largest acreage
$topCropsByAcreage = $conn->query("SELECT c.CropName, 
                                          SUM(h.acreage) AS total_acreage
                                   FROM harvest h
                                   JOIN crop c ON h.CropId = c.CropId
                                   GROUP BY c.CropName
                                   ORDER BY total_acreage DESC
                                   LIMIT 5");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crop History</title>
    <style>
        /* Your existing CSS here */
        body {
            margin: 0;
            font-family: 'Segoe UI', sans-serif;
            background-color: #f4f4f4;
            color: #333;
            display: flex;
        }

    .sidebar {
    width: 220px;
    height: 100vh;
    background-color: #0077b6;
    padding: 20px;
    color: white;
    position: fixed;
    top: 0;
    left: 0;
    overflow-y: auto;
  }
  
  .sidebar h2 {
    font-size: 24px;
    margin-bottom: 30px;
  }
  
  .nav {
    list-style: none;
    padding: 0;
  }
  
  .nav > li {
    margin-bottom: 10px;
    position: relative;
  }
  
  .nav > li > a {
    color: white;
    text-decoration: none;
    padding: 8px 10px;
    display: block;
    border-radius: 5px;
  }
  
  .nav > li > a:hover {
    background-color: #005f87;
  }
  
  .submenu {
    list-style: none;
    padding-left: 15px;
    display: none;
    margin-top: 5px;
  }
  
  .submenu li a {
    font-size: 14px;
    color: white;
    padding: 5px 10px;
    display: block;
    border-radius: 4px;
  }
  
  .submenu li a:hover {
    background-color: #003f5c;
  }
  
  .dropdown:hover .submenu {
    display: block;
  }
  .nav li a:hover {
    background-color: #2c3e50;
}

        main {
            flex-grow: 1;
            padding: 20px;
            margin-left: 250px; /* Adjust based on sidebar width */
        }

        h2 {
            margin-bottom: 20px;
        }

        .filter-options {
            margin-bottom: 20px;
            padding: 15px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .filter-options label {
            font-weight: bold;
            margin-right: 10px;
        }

        .filter-options select {
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        .table-container {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            overflow-x: auto;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .analysis-filter-options {
            margin-top: 30px;
            margin-bottom: 20px;
            padding: 15px;
            background-color: #f9f9f9;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            border: 1px solid #eee;
        }

        .analysis-filter-options label {
            font-weight: bold;
            margin-right: 10px;
        }

        .analysis-filter-options select {
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        .analysis-table-container {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            overflow-x: auto;
            margin-bottom: 20px;
        }

        .analysis-table-container h3 {
            margin-top: 0;
            margin-bottom: 15px;
            color: #555;
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

        <main>
            <section id="crop-history">
                <h2>Crop History</h2>

                <div class="filter-options">
                    <form method="POST">
                        <label for="filter_option">Filter by:</label>
                        <select name="filter_option" id="filter_option" onchange="this.form.submit()">
                            <option value="">Show All</option>
                            <option value="highest_yield">Highest Yield</option>
                            <option value="largest_acreage">Largest Acreage</option>
                            <option value="lowest_cost">Lowest Harvesting Cost</option>
                            <option value="highest_price">Highest Harvest Price</option>
                        </select>
                    </form>
                </div>

                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Harvest Year</th>
                                <th>Crop Name</th>
                                <th>Vendor Name</th>
                                <th>Warehouse ID</th> <!-- Update column header -->
                                <th>Yield</th>
                                <th>Acreage</th>
                                <th>Harvesting Cost</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $harvests->fetch_assoc()): ?>
                                <tr>
                                    <td><?= $row['harvestYear'] ?></td>
                                    <td><?= $row['CropName'] ?></td>
                                    <td><?= $row['vendorName'] ?></td>
                                    <td><?= $row['WarehouseId'] ?></td> <!-- Display Warehouse ID -->
                                    <td><?= number_format($row['yields'], 2) ?></td>
                                    <td><?= number_format($row['acreage'], 2) ?></td>
                                    <td><?= number_format($row['harvestingCost'], 2) ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </section>

            <section id="crop-analysis">
                <h2>Historical Crop Analysis</h2>

                <div class="analysis-filter-options">
                    <form method="POST">
                        <label for="crop_filter">Analyze Crop Trends:</label>
                        <select name="crop_filter" id="crop_filter" onchange="this.form.submit()">
                            <option value="">Show Overview</option>
                            <?php if ($analysisCrops && $analysisCrops->num_rows > 0): ?>
                                <?php while($crop = $analysisCrops->fetch_assoc()): ?>
                                    <option value="<?= $crop['CropId'] ?>" <?= (isset($_POST['crop_filter']) && $_POST['crop_filter'] == $crop['CropId']) ? 'selected' : '' ?>><?= $crop['CropName'] ?></option>
                                <?php endwhile; ?>
                            <?php endif; ?>
                        </select>
                    </form>
                </div>

                <div class="analysis-table-container">
                    <h3>Average Crop Metrics Across All Years</h3>
                    <?php if (!empty($overviewData)): ?>
                        <table>
                            <thead>
                                <tr>
                                    <th>Crop Name</th>
                                    <th>Average Yield (tonnes)</th>
                                    <th>Average Acreage</th>
                                    <th>Average Harvesting Cost</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($overviewData as $data): ?>
                                    <tr>
                                        <td><?= $data['CropName'] ?></td>
                                        <td><?= number_format($data['average_yield'], 2) ?></td>
                                        <td><?= number_format($data['average_acreage'], 2) ?></td>
                                        <td><?= number_format($data['average_cost'], 2) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p>No historical data available for overview.</p>
                    <?php endif; ?>
                </div>

                <?php if ($selectedCropName): ?>
                    <div class="analysis-table-container">
                        <h3>Historical Trends for <?= $selectedCropName ?></h3>
                        <?php if (!empty($historicalData)): ?>
                            <table>
                                <thead>
                                    <tr>
                                        <th>Year</th>
                                        <th>Average Yield (tonnes)</th>
                                        <th>Average Acreage</th>
                                        <th>Average Harvesting Cost</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($historicalData as $data): ?>
                                        <tr>
                                            <td><?= $data['harvestYear'] ?></td>
                                            <td><?= number_format($data['average_yield'], 2) ?></td>
                                            <td><?= number_format($data['average_acreage'], 2) ?></td>
                                            <td><?= number_format($data['average_cost'], 2) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php else: ?>
                            <p>No historical data found for <?= $selectedCropName ?>.</p>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <div class="analysis-table-container">
                    <h3>Total Yield per Vendor</h3>
                    <?php if (!empty($vendorYieldData)): ?>
                        <table>
                            <thead>
                                <tr>
                                    <th>Vendor Name</th>
                                    <th>Total Yield (tonnes)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($vendorYieldData as $data): ?>
                                    <tr>
                                        <td><?= $data['vendorName'] ?></td>
                                        <td><?= number_format($data['total_yield'], 2) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p>No data available on total yield per vendor.</p>
                    <?php endif; ?>
                </div>

                <div class="analysis-table-container">
                    <h3>Average Yield, Cost, and Acreage by Year</h3>
                    <?php if ($averageDataByYear->num_rows > 0): ?>
                        <table>
                            <thead>
                                <tr>
                                    <th>Year</th>
                                    <th>Average Yield (tonnes)</th>
                                    <th>Average Harvesting Cost</th>
                                    <th>Average Acreage</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = $averageDataByYear->fetch_assoc()): ?>
                                    <tr>
                                        <td><?= $row['harvestYear'] ?></td>
                                        <td><?= number_format($row['avg_yield'], 2) ?></td>
                                        <td><?= number_format($row['avg_cost'], 2) ?></td>
                                        <td><?= number_format($row['avg_acreage'], 2) ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p>No data available.</p>
                    <?php endif; ?>
                </div>

                <div class="analysis-table-container">
                    <h3>Top 5 Years with Highest Yield</h3>
                    <?php if ($topYieldYears->num_rows > 0): ?>
                        <table>
                            <thead>
                                <tr>
                                    <th>Year</th>
                                    <th>Total Yield (tonnes)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = $topYieldYears->fetch_assoc()): ?>
                                    <tr>
                                        <td><?= $row['harvestYear'] ?></td>
                                        <td><?= number_format($row['total_yield'], 2) ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p>No data available.</p>
                    <?php endif; ?>
                </div>

                <div class="analysis-table-container">
                    <h3>Top 5 Crops with Largest Acreage</h3>
                    <?php if ($topCropsByAcreage->num_rows > 0): ?>
                        <table>
                            <thead>
                                <tr>
                                    <th>Crop Name</th>
                                    <th>Total Acreage</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = $topCropsByAcreage->fetch_assoc()): ?>
                                    <tr>
                                        <td><?= $row['CropName'] ?></td>
                                        <td><?= number_format($row['total_acreage'], 2) ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p>No data available.</p>
                    <?php endif; ?>
                </div>

            </section>
        </main>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var dropdowns = document.querySelectorAll('.dropdown-btn');
            dropdowns.forEach(function(dropdown) {
                dropdown.addEventListener('click', function() {
                    this.parentElement.classList.toggle('active');
                });
            });
        });
    </script>
</body>
</html>