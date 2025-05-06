<?php
require_once 'db_connect.php';

// Fetch crop details from database
$crops = [];
try {
    $stmt = $pdo->query("
        SELECT c.CropId, c.CropName, c.price, 
               GROUP_CONCAT(DISTINCT ct.Type) AS types,
               GROUP_CONCAT(DISTINCT cs.seasonality) AS seasons,
               GROUP_CONCAT(DISTINCT cv.variety) AS varieties
        FROM crop c
        LEFT JOIN crop_type ct ON c.CropId = ct.CropId
        LEFT JOIN crop_seasonality cs ON c.CropId = cs.CropId
        LEFT JOIN crop_variety cv ON c.CropId = cv.CropId
        GROUP BY c.CropId
    ");
    $crops = $stmt->fetchAll();
} catch (PDOException $e) {
    // Fallback data if query fails
    $crops = [
        ['CropName' => 'Rice', 'varieties' => 'BRRI-28', 'seasons' => 'Winter'],
        ['CropName' => 'Wheat', 'varieties' => 'Shatabdi', 'seasons' => 'Summer']
    ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Crop Details</title>
  <link rel="stylesheet" href="customer.css">
</head>
<body>
  <div class="sidebar">
    <h2>AgriTrack</h2>
    <nav>
      <ul>
        <li><a href="consumer.php">Home</a></li>
        <li><a href="crop-details.php">Crop Details</a></li>
        <li><a href="vendor-directory.php">Vendor Directory</a></li>
      </ul>
    </nav>
  </div>

  <div class="main">
    <h2>Crop Details</h2>
    <table>
      <tr>
        <th>Name</th>
        <th>Type</th>
        <th>Variety</th>
        <th>Seasonality</th>
        <th>Price (৳)</th>
      </tr>
      <?php foreach ($crops as $crop): ?>
      <tr>
        <td><?= htmlspecialchars($crop['CropName']) ?></td>
        <td><?= htmlspecialchars($crop['types'] ?? 'N/A') ?></td>
        <td><?= htmlspecialchars($crop['varieties'] ?? 'N/A') ?></td>
        <td><?= htmlspecialchars($crop['seasons'] ?? 'N/A') ?></td>
        <td><?= htmlspecialchars($crop['price'] ?? 'N/A') ?></td>
      </tr>
      <?php endforeach; ?>
    </table>
  </div>
</body>
</html>