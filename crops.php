<?php
$host = 'localhost';
$db   = 'agritruck';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Handle API requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    $action = $_GET['action'] ?? '';
    $data = json_decode(file_get_contents('php://input'), true);

    switch ($action) {
        case 'add':
            handleAddCrop($pdo, $data);
            break;
        case 'update':
            handleUpdateCrop($pdo, $data);
            break;
        case 'delete':
            handleDeleteCrop($pdo, $data);
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
    exit;
}

// API Handler Functions
function handleAddCrop($pdo, $data) {
    try {
        $cropId = $data['cropId'] ?? null;
        $type = $data['type'] ?? null;
        $variety = $data['variety'] ?? null;
        $season = $data['season'] ?? null;
        
        if (!$cropId || !$type || !$variety || !$season) {
            throw new Exception('Missing required fields');
        }

        $pdo->beginTransaction();
        
        $typeStmt = $pdo->prepare("INSERT INTO crop_type (CropId, Type) VALUES (?, ?) 
                                  ON DUPLICATE KEY UPDATE Type = VALUES(Type)");
        $typeStmt->execute([$cropId, $type]);
        
        $varietyStmt = $pdo->prepare("INSERT INTO crop_variety (CropId, variety, seasonality) VALUES (?, ?, ?)");
        $varietyStmt->execute([$cropId, $variety, $season]);
        
        $pdo->commit();
        
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

function handleUpdateCrop($pdo, $data) {
    try {
        $cropId = $data['cropId'] ?? null;
        $variety = $data['variety'] ?? null;
        
        if (!$cropId || !$variety) {
            throw new Exception('Missing required fields');
        }
        
        $stmt = $pdo->prepare("UPDATE crop_variety SET variety = ? WHERE CropId = ?");
        $stmt->execute([$variety, $cropId]);
        
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

function handleDeleteCrop($pdo, $data) {
    try {
        $cropId = $data['cropId'] ?? null;
        
        if (!$cropId) {
            throw new Exception('Missing crop ID');
        }
        
        $stmt = $pdo->prepare("DELETE FROM crop_variety WHERE CropId = ?");
        $stmt->execute([$cropId]);
        
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

// Main page data
$allCrops = $pdo->query("SELECT CropId, CropName FROM crop")->fetchAll();
$cropTypes = $pdo->query("SELECT DISTINCT Type FROM crop_type WHERE Type IS NOT NULL")->fetchAll(PDO::FETCH_COLUMN);
$seasons = $pdo->query("SELECT DISTINCT seasonality FROM crop_seasonality WHERE seasonality IS NOT NULL")->fetchAll(PDO::FETCH_COLUMN);

$selectedCrop = null;
$cropDetails = [];
$cropMeta = ['type' => '--', 'season' => '--'];

if (isset($_GET['crop_filter'])) {
    $cropId = $_GET['crop_filter'];

    $stmt = $pdo->prepare("
        SELECT c.CropName, ct.Type, cs.seasonality, cv.variety, c.CropId
        FROM crop c
        LEFT JOIN crop_type ct ON c.CropId = ct.CropId
        LEFT JOIN crop_seasonality cs ON c.CropId = cs.CropId
        LEFT JOIN crop_variety cv ON c.CropId = cv.CropId
        WHERE c.CropId = ?
    ");
    $stmt->execute([$cropId]);
    $cropDetails = $stmt->fetchAll();

    $metaStmt = $pdo->prepare("
        SELECT ct.Type, cs.seasonality
        FROM crop
        LEFT JOIN crop_type ct ON crop.CropId = ct.CropId
        LEFT JOIN crop_seasonality cs ON crop.CropId = cs.CropId
        WHERE crop.CropId = ?
        LIMIT 1
    ");
    $metaStmt->execute([$cropId]);
    $metaResult = $metaStmt->fetch();
    if ($metaResult) {
        $cropMeta['type'] = $metaResult['Type'] ?? '--';
        $cropMeta['season'] = $metaResult['seasonality'] ?? '--';
    }

    $selectedCrop = $cropId;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Crop Info - AgriTruck</title>
    <link rel="stylesheet" href="crop.css"/>
    <style>
        /* Modal styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }

        .modal-content {
            background-color: #fefefe;
            margin: 15% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 500px;
            border-radius: 5px;
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

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }

        .form-group select, .form-group input {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        .form-buttons {
            text-align: right;
            margin-top: 20px;
        }

        .form-buttons button {
            margin-left: 10px;
            padding: 8px 15px;
            border-radius: 4px;
            cursor: pointer;
        }

        .btn-cancel {
            background-color: #f8f9fa;
            border: 1px solid #ddd;
        }

        .btn-submit {
            background-color: #28a745;
            color: white;
            border: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <aside class="sidebar">
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
        </aside>

        <main class="main-content">
            <section class="filter-panel">
                <h2>Select a Crop</h2>
                <form method="get" action="crops.php">
                    <select id="cropSelect" name="crop_filter">
                        <option value="">-- Select --</option>
                        <?php foreach ($allCrops as $c): ?>
                            <option value="<?= $c['CropId'] ?>" <?= $selectedCrop == $c['CropId'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($c['CropName']) ?> (<?= $c['CropId'] ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit">Show Details</button>
                </form>
            </section>

            <section class="info-section" id="infoSection">
                <div class="info-header">
                    <h2>Comprehensive Product Information</h2>
                </div>

                <div class="info-summary">
                    <p><strong>Type:</strong> <span id="cropType"><?= htmlspecialchars($cropMeta['type']) ?></span></p>
                    <p><strong>Typical Season:</strong> <span id="cropSeason"><?= htmlspecialchars($cropMeta['season']) ?></span></p>
                </div>

                <div class="info-table">
                    <h3>Variety & Seasonality</h3>
                    <table>
                        <thead>
                            <tr>
                                <th>Crop Name</th>
                                <th>Crop Type</th>
                                <th>Variety</th>
                                <th>Season</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="varietyTableBody">
                            <?php if (!$cropDetails): ?>
                                <tr>
                                    <td colspan="5" style="text-align:center; color: gray;">No crop selected</td>
                                </tr>
                            <?php else: ?>
                                <?php
                                    $rowCount = count($cropDetails);
                                    foreach ($cropDetails as $index => $row):
                                ?>
                                    <tr>
                                        <?php if ($index == 0): ?>
                                            <td rowspan="<?= $rowCount ?>"><?= htmlspecialchars($row['CropName']) ?></td>
                                            <td rowspan="<?= $rowCount ?>"><?= htmlspecialchars($row['Type']) ?></td>
                                        <?php endif; ?>
                                        <td><?= htmlspecialchars($row['variety']) ?></td>
                                        <td><?= htmlspecialchars($row['seasonality']) ?></td>
                                        <td>
                                            <button class="btn green" onclick="handleEdit(<?= $row['CropId'] ?>)">Edit</button>
                                            <button class="btn red" onclick="handleDelete(<?= $row['CropId'] ?>)">Delete</button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                    <div class="action-button-group">
                        <button class="btn green" onclick="openAddModal()">Add</button>
                    </div>
                </div>
            </section>
        </main>
    </div>

    <!-- Add Crop Modal -->
    <div id="addCropModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeAddModal()">&times;</span>
            <h2>Add New Crop Variety</h2>
            <form id="addCropForm" onsubmit="handleAddFormSubmit(event)">
                <div class="form-group">
                    <label for="modalCropSelect">Select Crop:</label>
                    <select id="modalCropSelect" required>
                        <option value="">-- Select Crop --</option>
                        <?php foreach ($allCrops as $crop): ?>
                            <option value="<?= $crop['CropId'] ?>"><?= htmlspecialchars($crop['CropName']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="cropType">Crop Type:</label>
                    <select id="cropType" required>
                        <option value="">-- Select Type --</option>
                        <?php foreach ($cropTypes as $type): ?>
                            <option value="<?= htmlspecialchars($type) ?>"><?= htmlspecialchars($type) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="cropVariety">Variety:</label>
                    <input type="text" id="cropVariety" required>
                </div>
                <div class="form-group">
                    <label for="cropSeason">Season:</label>
                    <select id="cropSeason" required>
                        <option value="">-- Select Season --</option>
                        <?php foreach ($seasons as $season): ?>
                            <option value="<?= htmlspecialchars($season) ?>"><?= htmlspecialchars($season) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-buttons">
                    <button type="button" class="btn-cancel" onclick="closeAddModal()">Cancel</button>
                    <button type="submit" class="btn-submit">Add Crop</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Modal functions
        function openAddModal() {
            document.getElementById('addCropModal').style.display = 'block';
        }

        function closeAddModal() {
            document.getElementById('addCropModal').style.display = 'none';
        }

        // Form submission handlers
        async function handleAddFormSubmit(event) {
            event.preventDefault();
            
            const data = {
                cropId: document.getElementById('modalCropSelect').value,
                type: document.getElementById('cropType').value,
                variety: document.getElementById('cropVariety').value,
                season: document.getElementById('cropSeason').value
            };

            try {
                const response = await fetch('crops.php?action=add', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(data)
                });

                const result = await response.json();
                if (result.success) {
                    window.location.reload();
                } else {
                    alert('Error: ' + result.message);
                }
            } catch (error) {
                alert('Error: ' + error.message);
            }
        }

        async function handleEdit(cropId) {
            const variety = prompt('Enter new variety name:');
            if (variety) {
                try {
                    const response = await fetch('crops.php?action=update', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({ cropId, variety })
                    });

                    const result = await response.json();
                    if (result.success) {
                        window.location.reload();
                    } else {
                        alert('Error: ' + result.message);
                    }
                } catch (error) {
                    alert('Error: ' + error.message);
                }
            }
        }

        async function handleDelete(cropId) {
            if (confirm('Are you sure you want to delete this crop variety?')) {
                try {
                    const response = await fetch('crops.php?action=delete', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({ cropId })
                    });

                    const result = await response.json();
                    if (result.success) {
                        window.location.reload();
                    } else {
                        alert('Error: ' + result.message);
                    }
                } catch (error) {
                    alert('Error: ' + error.message);
                }
            }
        }
    </script>
</body>
</html> 