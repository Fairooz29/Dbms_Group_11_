<?php
header('Content-Type: application/json');

$conn = new mysqli("localhost", "root", "", "agritruck");

if ($conn->connect_error) {
    die(json_encode(["error" => "Connection failed: " . $conn->connect_error]));
}

$sql = "SELECT CropId, CropName FROM crop ORDER BY CropName";
$result = $conn->query($sql);

if (!$result) {
    die(json_encode(["error" => "Query failed: " . $conn->error]));
}

$crops = [];
while ($row = $result->fetch_assoc()) {
    $crops[] = $row;
}

echo json_encode($crops);
$conn->close();
?>