<?php
// PHP Backend Code
header("Content-Type: text/html"); // Set content type for the combined page

// Database configuration and API functions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['api_action'])) {
    handleApiRequest();
    exit;
}

function handleApiRequest() {
    // Database configuration
    define('DB_HOST', 'localhost');
    define('DB_USER', 'root');
    define('DB_PASS', '');
    define('DB_NAME', 'agritruck');
    
    // Create connection
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    // Check connection
    if ($conn->connect_error) {
        die(json_encode(["error" => "Connection failed: " . $conn->connect_error]));
    }
    
    $action = $_POST['api_action'];
    $response = [];
    
    try {
        switch ($action) {
            case 'get_data':
                $response = handleGetRequest($conn);
                break;
            case 'get_crops':
                $response = handleGetCropsRequest($conn);
                break;
            case 'add_data':
                $response = handleAddRequest($conn);
                break;
            case 'update_data':
                $response = handleUpdateRequest($conn);
                break;
            case 'delete_data':
                $response = handleDeleteRequest($conn);
                break;
            default:
                $response = ["error" => "Invalid action"];
                break;
        }
    } catch (Exception $e) {
        $response = ["error" => $e->getMessage()];
    }
    
    $conn->close();
    echo json_encode($response);
    exit;
}

function handleGetRequest($conn) {
    $cropId = isset($_POST['cropId']) ? $conn->real_escape_string($_POST['cropId']) : null;
    
    if ($cropId) {
        $sql = "SELECT pe.ElasticityId, pe.CropId, c.CropName, pe.ElasticityValue, pe.DateRecorded, pe.Source 
                FROM price_elasticity pe
                JOIN crop c ON pe.CropId = c.CropId
                WHERE pe.CropId = $cropId";
    } else {
        $sql = "SELECT pe.ElasticityId, pe.CropId, c.CropName, pe.ElasticityValue, pe.DateRecorded, pe.Source 
                FROM price_elasticity pe
                JOIN crop c ON pe.CropId = c.CropId";
    }
    
    $result = $conn->query($sql);
    $data = [];
    
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    
    return $data;
}

function handleGetCropsRequest($conn) {
    $sql = "SELECT CropId, CropName FROM crop ORDER BY CropName";
    $result = $conn->query($sql);
    $crops = [];
    
    while ($row = $result->fetch_assoc()) {
        $crops[] = $row;
    }
    
    return $crops;
}

function handleAddRequest($conn) {
    if (!isset($_POST['CropId']) || !isset($_POST['ElasticityValue'])) {
        throw new Exception("Missing required fields");
    }
    
    $cropId = $conn->real_escape_string($_POST['CropId']);
    $elasticityValue = $conn->real_escape_string($_POST['ElasticityValue']);
    $source = isset($_POST['Source']) ? $conn->real_escape_string($_POST['Source']) : 'System';
    
    // Check if crop exists
    $checkSql = "SELECT CropId FROM crop WHERE CropId = $cropId";
    $checkResult = $conn->query($checkSql);
    
    if ($checkResult->num_rows == 0) {
        throw new Exception("Crop not found");
    }
    
    // Insert new elasticity data
    $sql = "INSERT INTO price_elasticity (CropId, ElasticityValue, DateRecorded, Source) 
            VALUES ($cropId, $elasticityValue, NOW(), '$source')";
    
    if ($conn->query($sql)) {
        return [
            "message" => "Elasticity data added successfully",
            "ElasticityId" => $conn->insert_id
        ];
    } else {
        throw new Exception("Error adding elasticity data: " . $conn->error);
    }
}

function handleUpdateRequest($conn) {
    if (!isset($_POST['ElasticityId']) || !isset($_POST['ElasticityValue'])) {
        throw new Exception("Missing required fields");
    }
    
    $elasticityId = $conn->real_escape_string($_POST['ElasticityId']);
    $elasticityValue = $conn->real_escape_string($_POST['ElasticityValue']);
    $source = isset($_POST['Source']) ? $conn->real_escape_string($_POST['Source']) : null;
    
    $sql = "UPDATE price_elasticity SET ElasticityValue = $elasticityValue";
    if ($source) {
        $sql .= ", Source = '$source'";
    }
    $sql .= " WHERE ElasticityId = $elasticityId";
    
    if ($conn->query($sql)) {
        if ($conn->affected_rows > 0) {
            return ["message" => "Elasticity data updated successfully"];
        } else {
            throw new Exception("No elasticity data found with this ID");
        }
    } else {
        throw new Exception("Error updating elasticity data: " . $conn->error);
    }
}

function handleDeleteRequest($conn) {
    if (!isset($_POST['ElasticityId'])) {
        throw new Exception("Missing ElasticityId");
    }
    
    $elasticityId = $conn->real_escape_string($_POST['ElasticityId']);
    $sql = "DELETE FROM price_elasticity WHERE ElasticityId = $elasticityId";
    
    if ($conn->query($sql)) {
        if ($conn->affected_rows > 0) {
            return ["message" => "Elasticity data deleted successfully"];
        } else {
            throw new Exception("No elasticity data found with this ID");
        }
    } else {
        throw new Exception("Error deleting elasticity data: " . $conn->error);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Price Elasticity Data</title>
  <link rel="stylesheet" href="consumer.css"/>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
      <h1>Price Elasticity</h1>
      <canvas id="elasticityChart" width="800" height="400"></canvas>

      <div class="form-group">
        <select id="cropId">
          <!-- Options will be loaded dynamically -->
        </select>
        <input type="number" id="elasticity" step="0.1" placeholder="Elasticity Value">
        <input type="hidden" id="elasticityId">
        <button onclick="addData()" id="addButton">Add Data</button>
        <button onclick="updateData()" id="updateButton" style="display:none;">Update Data</button>
      </div>

      <table>
        <thead>
          <tr>
            <th>Elasticity ID</th>
            <th>Crop ID</th>
            <th>Crop Name</th>
            <th>Price Elasticity</th>
            <th>Elasticity Type</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody id="dataTable">
          <!-- Data will be loaded dynamically -->
        </tbody>
      </table>
    </div>
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
        const elasticityCtx = document.getElementById("elasticityChart").getContext("2d");
        let elasticityChart;
        
        // Initialize chart with empty data
        function initChart() {
            elasticityChart = new Chart(elasticityCtx, {
                type: "bar",
                data: {
                    labels: [],
                    datasets: [{
                        label: "Price Elasticity",
                        data: [],
                        backgroundColor: ["#FF6B6B", "#4D96FF", "#33CC99", "#FFA500", "#9966FF"],
                        borderColor: ["#FF6B6B", "#4D96FF", "#33CC99", "#FFA500", "#9966FF"],
                        borderWidth: 1
                    }]
                },
                options: {
                    scales: {
                        y: {
                            beginAtZero: false,
                            title: { display: true, text: 'Elasticity Value' },
                            min: -2,
                            max: 0
                        },
                        x: {
                            title: { display: true, text: 'Crop Name' }
                        }
                    },
                    plugins: {
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    let label = context.dataset.label || '';
                                    if (label) {
                                        label += ': ';
                                    }
                                    label += context.parsed.y;
                                    const elasticityType = getElasticityType(context.parsed.y);
                                    label += ` (${elasticityType})`;
                                    return label;
                                }
                            }
                        }
                    }
                }
            });
        }
        
        // Load crops for dropdown
        function loadCrops() {
            const formData = new FormData();
            formData.append('api_action', 'get_crops');
            
            fetch(window.location.href, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                const cropSelect = document.getElementById("cropId");
                cropSelect.innerHTML = '';
                
                data.forEach(crop => {
                    const option = document.createElement("option");
                    option.value = crop.CropId;
                    option.textContent = `${crop.CropId} - ${crop.CropName}`;
                    cropSelect.appendChild(option);
                });
            })
            .catch(error => console.error('Error loading crops:', error));
        }
        
        // Load initial data
        function loadData() {
            const formData = new FormData();
            formData.append('api_action', 'get_data');
            
            fetch(window.location.href, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                updateChart(data);
                updateTable(data);
            })
            .catch(error => console.error('Error loading data:', error));
        }
        
        // Update chart with data
        function updateChart(data) {
            const labels = [];
            const values = [];
            
            data.forEach(item => {
                labels.push(item.CropName);
                values.push(parseFloat(item.ElasticityValue));
            });
            
            elasticityChart.data.labels = labels;
            elasticityChart.data.datasets[0].data = values;
            elasticityChart.update();
        }
        
        // Update table with data
        function updateTable(data) {
            const tableBody = document.getElementById("dataTable");
            tableBody.innerHTML = '';
            
            data.forEach(item => {
                const row = document.createElement("tr");
                const elasticityType = getElasticityType(item.ElasticityValue);
                const typeClass = getElasticityClass(elasticityType);
                
                row.innerHTML = `
                    <td>${item.ElasticityId}</td>
                    <td>${item.CropId}</td>
                    <td>${item.CropName}</td>
                    <td>${item.ElasticityValue}</td>
                    <td class="${typeClass}">${elasticityType}</td>
                    <td>
                        <button class="btn edit" onclick="editRow(${item.ElasticityId}, ${item.CropId}, '${item.CropName}', ${item.ElasticityValue})">Edit</button>
                        <button class="btn delete" onclick="deleteRow(${item.ElasticityId})">Delete</button>
                    </td>`;
                tableBody.appendChild(row);
            });
        }
        
        function getElasticityType(value) {
            value = parseFloat(value);
            if (value <= -1.5) return "Highly Elastic";
            if (value <= -1) return "Elastic";
            if (value <= -0.5) return "Moderately Elastic";
            if (value < 0) return "Inelastic";
            return "Unknown";
        }
        
        function getElasticityClass(type) {
            switch(type) {
                case "Highly Elastic": return "highly-elastic";
                case "Elastic": return "elastic";
                case "Moderately Elastic": return "moderately-elastic";
                case "Inelastic": return "inelastic";
                default: return "";
            }
        }
        
        // Initialize the page
        initChart();
        loadCrops();
        loadData();
    });

    // Global functions for button actions
    function addData() {
        const cropSelect = document.getElementById("cropId");
        const cropId = cropSelect.value;
        const elasticity = parseFloat(document.getElementById("elasticity").value);
        
        if (isNaN(elasticity)) {
            alert("Please enter a valid elasticity value.");
            return;
        }
        
        const formData = new FormData();
        formData.append('api_action', 'add_data');
        formData.append('CropId', cropId);
        formData.append('ElasticityValue', elasticity);
        formData.append('Source', 'Manual Entry');
        
        fetch(window.location.href, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                alert(data.error);
            } else {
                // Reload data to refresh chart and table
                location.reload();
            }
        })
        .catch(error => console.error('Error:', error));
    };

    function updateData() {
        const elasticityId = document.getElementById("elasticityId").value;
        const elasticity = parseFloat(document.getElementById("elasticity").value);
        
        if (isNaN(elasticity)) {
            alert("Please enter a valid elasticity value.");
            return;
        }
        
        const formData = new FormData();
        formData.append('api_action', 'update_data');
        formData.append('ElasticityId', elasticityId);
        formData.append('ElasticityValue', elasticity);
        formData.append('Source', 'Manual Update');
        
        fetch(window.location.href, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                alert(data.error);
            } else {
                // Reset form and reload data
                document.getElementById("elasticityId").value = '';
                document.getElementById("elasticity").value = '';
                document.getElementById("addButton").style.display = 'inline-block';
                document.getElementById("updateButton").style.display = 'none';
                location.reload();
            }
        })
        .catch(error => console.error('Error:', error));
    }

    function deleteRow(elasticityId) {
        if (!confirm("Are you sure you want to delete this record?")) return;
        
        const formData = new FormData();
        formData.append('api_action', 'delete_data');
        formData.append('ElasticityId', elasticityId);
        
        fetch(window.location.href, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                alert(data.error);
            } else {
                // Reload data to refresh chart and table
                location.reload();
            }
        })
        .catch(error => console.error('Error:', error));
    };

    function editRow(elasticityId, cropId, cropName, elasticityValue) {
        document.getElementById("elasticityId").value = elasticityId;
        document.getElementById("cropId").value = cropId;
        document.getElementById("elasticity").value = elasticityValue;
        
        // Show update button and hide add button
        document.getElementById("addButton").style.display = 'none';
        document.getElementById("updateButton").style.display = 'inline-block';
        
        // Scroll to form
        document.querySelector('.form-group').scrollIntoView({ behavior: 'smooth' });
    };
  </script>
</body>
</html>