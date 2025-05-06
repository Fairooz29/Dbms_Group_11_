<?php
require_once 'db_connect.php';

// Fetch vendor details from database
$vendors = [];
try {
    $stmt = $pdo->query("
        SELECT v.VendorId, v.vendorName, v.thana, v.city, 
               GROUP_CONCAT(vc.contactNo) AS contactNumbers
        FROM vendor v
        LEFT JOIN vendor_contact vc ON v.VendorId = vc.VendorId
        GROUP BY v.VendorId
    ");
    $vendors = $stmt->fetchAll();
} catch (PDOException $e) {
    // Fallback data if query fails
    $vendors = [
        ['vendorName' => 'Rahim Traders', 'contactNumbers' => '017xxxxxxxx'],
        ['vendorName' => 'Green Agro', 'contactNumbers' => '018xxxxxxxx']
    ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Vendor Directory</title>
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
    <h2>Vendor Directory</h2>
    <table>
      <tr>
        <th>Vendor Name</th>
        <th>Location</th>
        <th>Contact Number</th>
      </tr>
      <?php foreach ($vendors as $vendor): ?>
      <tr>
        <td><?= htmlspecialchars($vendor['vendorName']) ?></td>
        <td><?= htmlspecialchars($vendor['thana'] ?? '') ?>, <?= htmlspecialchars($vendor['city'] ?? '') ?></td>
        <td><?= htmlspecialchars($vendor['contactNumbers'] ?? 'N/A') ?></td>
      </tr>
      <?php endforeach; ?>
    </table>
  </div>
</body>
</html>