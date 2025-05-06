<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Handle API requests
if (isset($_GET['entity'])) {
    header("Access-Control-Allow-Origin: *");
    header("Content-Type: application/json; charset=UTF-8");
    header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
    header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "directories";

    try {
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch(PDOException $e) {
        echo json_encode(["error" => "Connection failed: " . $e->getMessage()]);
        exit();
    }

    $input = json_decode(file_get_contents("php://input"), true);
    $entity = $_GET['entity'] ?? '';
    $id = $_GET['id'] ?? '';

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        if ($entity === 'buyers') {
            $stmt = $conn->prepare("SELECT * FROM buyers");
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($result);
        } elseif ($entity === 'sellers') {
            $stmt = $conn->prepare("SELECT * FROM sellers");
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($result);
        }
        exit();
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' || $_SERVER['REQUEST_METHOD'] === 'PUT' || $_SERVER['REQUEST_METHOD'] === 'DELETE') {
        switch ($_SERVER['REQUEST_METHOD']) {
            case 'POST':
                if ($entity === 'buyers') {
                    $stmt = $conn->prepare("INSERT INTO buyers (id, name, email) VALUES (:id, :name, :email)");
                    $stmt->execute([
                        ':id' => $input['id'],
                        ':name' => $input['name'],
                        ':email' => $input['email']
                    ]);
                    echo json_encode(["message" => "Buyer added successfully"]);
                } elseif ($entity === 'sellers') {
                    $stmt = $conn->prepare("INSERT INTO sellers (id, name, thana, zip, city, contact) VALUES (:id, :name, :thana, :zip, :city, :contact)");
                    $stmt->execute([
                        ':id' => $input['id'],
                        ':name' => $input['name'],
                        ':thana' => $input['thana'],
                        ':zip' => $input['zip'],
                        ':city' => $input['city'],
                        ':contact' => $input['contact']
                    ]);
                    echo json_encode(["message" => "Seller added successfully"]);
                }
                exit();
                
            case 'PUT':
                if ($entity === 'buyers') {
                    $stmt = $conn->prepare("UPDATE buyers SET id=:id, name=:name, email=:email WHERE id=:original_id");
                    $stmt->execute([
                        ':id' => $input['id'],
                        ':name' => $input['name'],
                        ':email' => $input['email'],
                        ':original_id' => $id
                    ]);
                    echo json_encode(["message" => "Buyer updated successfully"]);
                } elseif ($entity === 'sellers') {
                    $stmt = $conn->prepare("UPDATE sellers SET id=:id, name=:name, thana=:thana, zip=:zip, city=:city, contact=:contact WHERE id=:original_id");
                    $stmt->execute([
                        ':id' => $input['id'],
                        ':name' => $input['name'],
                        ':thana' => $input['thana'],
                        ':zip' => $input['zip'],
                        ':city' => $input['city'],
                        ':contact' => $input['contact'],
                        ':original_id' => $id
                    ]);
                    echo json_encode(["message" => "Seller updated successfully"]);
                }
                exit();
                
            case 'DELETE':
                if ($entity === 'buyers') {
                    $stmt = $conn->prepare("DELETE FROM buyers WHERE id=:id");
                    $stmt->execute([':id' => $id]);
                    echo json_encode(["message" => "Buyer deleted successfully"]);
                } elseif ($entity === 'sellers') {
                    $stmt = $conn->prepare("DELETE FROM sellers WHERE id=:id");
                    $stmt->execute([':id' => $id]);
                    echo json_encode(["message" => "Seller deleted successfully"]);
                }
                exit();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Buyer and Seller Directories</title>
 <link rel="stylesheet" href="directories.css">
</head>
<body>
  <div class="sidebar">
    <h2>AgriTruck</h2>
    <ul class="nav">
      <li class="dropdown">
        <a href="#">Crop ▾</a>
        <ul class="submenu">
          <li><a href="crop.html">Crop Info</a></li>
          <li><a href="historical_data.html">Data</a></li>
        </ul>
      </li>
      <li><a href="consumer.html">Consumer</a></li>
      <li><a href="warehouse.html">Real Time Supply</a></li>
      <li><a href="trends.html">Trends</a></li>
      <li><a href="#">Analytics</a></li>
      <li><a href="#">Recommendations</a></li>
      <li><a href="directories.html">Directory</a></li>
    </ul>
  </div>

  <div class="container">
    <h1>Buyer and Seller Directories</h1>

    <div class="search-bar">
      <input type="text" id="searchInput" placeholder="Search by ID or Name..." onkeyup="filterTables()" />
      <button onclick="filterTables()">Search</button>
    </div>

    <div class="card">
      <div class="card-header">Buyers List</div>
      <form id="buyerForm">
        <input type="text" id="buyerId" placeholder="ID">
        <input type="text" id="buyerName" placeholder="Name">
        <input type="email" id="buyerEmail" placeholder="Email">
        <button type="button" class="btn btn-add" onclick="addBuyer()">Add Buyer</button>
      </form>
      <table id="buyerTable">
        <thead>
          <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Email</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody></tbody>
      </table>
    </div>

    <div class="card">
      <div class="card-header">Sellers List</div>
      <form id="sellerForm">
        <input type="text" id="sellerId" placeholder="ID">
        <input type="text" id="sellerName" placeholder="Name">
        <input type="text" id="sellerThana" placeholder="Thana">
        <input type="text" id="sellerZip" placeholder="Zip Code">
        <input type="text" id="sellerCity" placeholder="City">
        <input type="text" id="sellerContact" placeholder="Contact">
        <button type="button" class="btn btn-add" onclick="addSeller()">Add Seller</button>
      </form>
      <table id="sellerTable">
        <thead>
          <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Thana</th>
            <th>Zip</th>
            <th>City</th>
            <th>Contact</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody></tbody>
      </table>
    </div>
  </div>

  <script>
    const API_BASE_URL = window.location.href;

    async function fetchData(endpoint) {
        try {
            const response = await fetch(`${API_BASE_URL}?entity=${endpoint}`);
            if (!response.ok) throw new Error('Network response was not ok');
            return await response.json();
        } catch (error) {
            console.error(`Error fetching ${endpoint}:`, error);
            return [];
        }
    }

    async function fetchBuyers() {
        return await fetchData('buyers');
    }

    async function fetchSellers() {
        return await fetchData('sellers');
    }

    async function renderBuyers() {
        const buyers = await fetchBuyers();
        const buyerTable = document.querySelector('#buyerTable tbody');
        buyerTable.innerHTML = '';
        buyers.forEach(buyer => {
            const row = `<tr>
                <td>${buyer.id}</td>
                <td>${buyer.name}</td>
                <td>${buyer.email}</td>
                <td>
                    <button class="btn btn-edit" onclick="editBuyer('${buyer.id}')">Edit</button>
                    <button class="btn btn-delete" onclick="deleteBuyer('${buyer.id}')">Delete</button>
                </td>
            </tr>`;
            buyerTable.innerHTML += row;
        });
    }

    async function renderSellers() {
        const sellers = await fetchSellers();
        const sellerTable = document.querySelector('#sellerTable tbody');
        sellerTable.innerHTML = '';
        sellers.forEach(seller => {
            const row = `<tr>
                <td>${seller.id}</td>
                <td>${seller.name}</td>
                <td>${seller.thana}</td>
                <td>${seller.zip}</td>
                <td>${seller.city}</td>
                <td>${seller.contact}</td>
                <td>
                    <button class="btn btn-edit" onclick="editSeller('${seller.id}')">Edit</button>
                    <button class="btn btn-delete" onclick="deleteSeller('${seller.id}')">Delete</button>
                </td>
            </tr>`;
            sellerTable.innerHTML += row;
        });
    }

    async function sendData(method, endpoint, data, id = '') {
        try {
            const url = id ? `${API_BASE_URL}?entity=${endpoint}&id=${id}` : `${API_BASE_URL}?entity=${endpoint}`;
            const response = await fetch(url, {
                method: method,
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(data)
            });
            
            if (!response.ok) throw new Error('Network response was not ok');
            return await response.json();
        } catch (error) {
            console.error(`Error in ${method} request:`, error);
            throw error;
        }
    }

    async function addBuyer() {
        const id = document.getElementById('buyerId').value;
        const name = document.getElementById('buyerName').value;
        const email = document.getElementById('buyerEmail').value;
        
        if (!id || !name || !email) {
            alert("Please fill all buyer fields");
            return;
        }

        try {
            await sendData('POST', 'buyers', { id, name, email });
            document.getElementById('buyerForm').reset();
            await renderBuyers();
        } catch (error) {
            alert("Error adding buyer: " + error.message);
        }
    }

    async function addSeller() {
        const id = document.getElementById('sellerId').value;
        const name = document.getElementById('sellerName').value;
        const thana = document.getElementById('sellerThana').value;
        const zip = document.getElementById('sellerZip').value;
        const city = document.getElementById('sellerCity').value;
        const contact = document.getElementById('sellerContact').value;
        
        if (!id || !name || !thana || !zip || !city || !contact) {
            alert("Please fill all seller fields");
            return;
        }

        try {
            await sendData('POST', 'sellers', { id, name, thana, zip, city, contact });
            document.getElementById('sellerForm').reset();
            await renderSellers();
        } catch (error) {
            alert("Error adding seller: " + error.message);
        }
    }

    async function editBuyer(buyerId) {
        const buyers = await fetchBuyers();
        const buyer = buyers.find(b => b.id === buyerId);
        
        if (!buyer) return;
        
        const newId = prompt('Edit ID', buyer.id);
        const newName = prompt('Edit Name', buyer.name);
        const newEmail = prompt('Edit Email', buyer.email);
        
        if (newId === null || newName === null || newEmail === null) return;
        
        try {
            await sendData('PUT', 'buyers', { id: newId, name: newName, email: newEmail }, buyerId);
            await renderBuyers();
        } catch (error) {
            alert("Error updating buyer: " + error.message);
        }
    }

    async function deleteBuyer(buyerId) {
        if (!confirm("Are you sure you want to delete this buyer?")) return;
        
        try {
            await sendData('DELETE', 'buyers', {}, buyerId);
            await renderBuyers();
        } catch (error) {
            alert("Error deleting buyer: " + error.message);
        }
    }

    async function editSeller(sellerId) {
        const sellers = await fetchSellers();
        const seller = sellers.find(s => s.id === sellerId);
        
        if (!seller) return;
        
        const newId = prompt('Edit ID', seller.id);
        const newName = prompt('Edit Name', seller.name);
        const newThana = prompt('Edit Thana', seller.thana);
        const newZip = prompt('Edit Zip', seller.zip);
        const newCity = prompt('Edit City', seller.city);
        const newContact = prompt('Edit Contact', seller.contact);
        
        if (newId === null || newName === null || newThana === null || 
            newZip === null || newCity === null || newContact === null) return;
        
        try {
            await sendData('PUT', 'sellers', { 
                id: newId, 
                name: newName, 
                thana: newThana, 
                zip: newZip, 
                city: newCity, 
                contact: newContact 
            }, sellerId);
            await renderSellers();
        } catch (error) {
            alert("Error updating seller: " + error.message);
        }
    }

    async function deleteSeller(sellerId) {
        if (!confirm("Are you sure you want to delete this seller?")) return;
        
        try {
            await sendData('DELETE', 'sellers', {}, sellerId);
            await renderSellers();
        } catch (error) {
            alert("Error deleting seller: " + error.message);
        }
    }

    function filterTables() {
        const searchValue = document.getElementById('searchInput').value.toLowerCase();
        const buyerRows = document.querySelectorAll('#buyerTable tbody tr');
        buyerRows.forEach(row => {
            const id = row.children[0].textContent.toLowerCase();
            const name = row.children[1].textContent.toLowerCase();
            row.style.display = id.includes(searchValue) || name.includes(searchValue) ? '' : 'none';
        });

        const sellerRows = document.querySelectorAll('#sellerTable tbody tr');
        sellerRows.forEach(row => {
            const id = row.children[0].textContent.toLowerCase();
            const name = row.children[1].textContent.toLowerCase();
            row.style.display = id.includes(searchValue) || name.includes(searchValue) ? '' : 'none';
        });
    }

    // Initialize the page
    document.addEventListener('DOMContentLoaded', () => {
        renderBuyers();
        renderSellers();
    });
  </script>
</body>
</html>