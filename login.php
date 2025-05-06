<?php
session_start();

$db_host = '127.0.0.1';
$db_username = 'root';
$db_password = '';
$db_name = 'login';

$conn = new mysqli($db_host, $db_username, $db_password, $db_name);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $designation = isset($_POST['designation']) ? $_POST['designation'] : 'customer';
    $isLogin = !isset($_POST['email']);

    if ($isLogin) {
        if (empty($username) || empty($password)) {
            $response = ['success' => false, 'message' => 'Username and password are required'];
        } else {
            $stmt = $conn->prepare("SELECT id, username, password, designation FROM users WHERE username = ?");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 0) {
                $response = ['success' => false, 'message' => 'User not found'];
            } else {
                $user = $result->fetch_assoc();
                if (password_verify($password, $user['password'])) {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['designation'] = $user['designation'];
                    
                    $response = [
                        'success' => true, 
                        'message' => 'Login successful',
                        'redirect' => getRedirectUrl($user['designation'])
                    ];
                } else {
                    $response = ['success' => false, 'message' => 'Incorrect password'];
                }
            }
        }
    } else {
        if (empty($username) || empty($password) || empty($email)) {
            $response = ['success' => false, 'message' => 'All fields are required'];
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $response = ['success' => false, 'message' => 'Invalid email format'];
        } else {
            $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
            $stmt->bind_param("ss", $username, $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $response = ['success' => false, 'message' => 'Username or email already exists'];
            } else {
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("INSERT INTO users (username, email, password, designation) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("ssss", $username, $email, $hashedPassword, $designation);

                if ($stmt->execute()) {
                    $response = ['success' => true, 'message' => 'Registration successful'];
                } else {
                    $response = ['success' => false, 'message' => 'Registration failed'];
                }
            }
        }
    }
    
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }
}

function getRedirectUrl($designation) {
    switch ($designation) {
        case 'admin':
            return 'admin_panel.php';
        case 'crop_researcher':
            return 'researcher_dashboard.php';
        case 'farmer':
            return 'farmer_dashboard.php';
        case 'customer':
            return 'customer_dashboard.php';
        default:
            return 'dashboard.php';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="login.css">
</head>
<body>
<div class="background"></div>

<div class="container">
  <div class="form-box">
    <h1>🌱 AgriTruck</h1>
    <p>Your Smart Agri Companion</p>

    <h2 id="form-title" style="text-align: center; margin-bottom: 20px">Login</h2>

    <?php if (isset($response) && (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest')): ?>
      <div style="color: <?php echo $response['success'] ? '#b8de5f' : '#ff6b6b'; ?>; text-align: center; margin-bottom: 20px;">
        <?php echo $response['message']; ?>
      </div>
    <?php endif; ?>

    <form id="auth-form">
      <div class="input-group">
        <input type="text" id="username" name="username" required placeholder=" " />
        <label for="username">Username</label>
      </div>
      <div class="input-group">
        <div class="password-container">
          <input type="password" id="password" name="password" required placeholder=" " />
          <label for="password">Password</label>
          <span class="toggle-password" onclick="togglePasswordVisibility()">👁️</span>
        </div>
      </div>
      <div class="input-group" id="email-group">
        <input type="email" id="email" name="email" placeholder=" " />
        <label for="email">Email</label>
      </div>
      <div class="input-group" id="designation-group">
        <select id="designation" name="designation">
          <option value="" selected disabled></option>
          <option value="admin">Admin</option>
          <option value="crop_researcher">Crop Researcher</option>
          <option value="customer">Customer</option>
          <option value="farmer">Farmer</option>
        </select>
        <label for="designation">Designation</label>
      </div>

      <div class="remember-me">
        <input type="checkbox" id="remember-me" name="remember-me" />
        <label for="remember-me">Remember Me</label>
      </div>

      <button type="submit" class="btn" id="submit-btn">Login</button>

      <p class="toggle">
        Don't have an account? <a href="#" id="toggle-link">Register</a>
      </p>
    </form>
  </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
      const toggleLink = document.getElementById('toggle-link');
      const formTitle = document.getElementById('form-title');
      const emailGroup = document.getElementById('email-group');
      const designationGroup = document.getElementById('designation-group');
      const submitBtn = document.getElementById('submit-btn');
      const authForm = document.getElementById('auth-form');
      let isLogin = true;

      
      emailGroup.style.display = 'none';
      designationGroup.style.display = 'none';
      
      toggleLink.addEventListener('click', (e) => {
        e.preventDefault();
        isLogin = !isLogin;

        formTitle.textContent = isLogin ? 'Login' : 'Register';
        toggleLink.textContent = isLogin ? 'Register' : 'Login';
        submitBtn.textContent = isLogin ? 'Login' : 'Register';
        emailGroup.style.display = isLogin ? 'none' : 'block';
        designationGroup.style.display = isLogin ? 'none' : 'block';
      });

      authForm.addEventListener('submit', function(e) {
        e.preventDefault();

        const username = document.getElementById('username').value.trim();
        const password = document.getElementById('password').value.trim();
        const email = document.getElementById('email').value.trim();
        const designation = document.getElementById('designation').value;
        const rememberMe = document.getElementById('remember-me').checked;

        if (!username || !password || (!isLogin && (!email || !designation))) {
          alert('Please fill out all required fields.');
          return;
        }

        const formData = new FormData();
        formData.append('username', username);
        formData.append('password', password);
        if (!isLogin) {
          formData.append('email', email);
          formData.append('designation', designation);
        }

        fetch('<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>', {
          method: 'POST',
          body: formData,
          headers: {
            'X-Requested-With': 'XMLHttpRequest'
          }
        })
          .then(res => {
            if (!res.ok) throw new Error('Network response was not ok');
            return res.json();
          })
          .then(data => {
            alert(data.message);
            if (data.success) {
              authForm.reset();
              if (!isLogin) {
                toggleLink.click(); 
              } else if (data.redirect) {
                window.location.href = data.redirect; 
              }
            }
          })
          .catch(err => {
            console.error('Error:', err);
            alert('An error occurred. Please try again.');
          });
      });
    });

    function togglePasswordVisibility() {
      const passwordInput = document.getElementById('password');
      const toggleIcon = document.querySelector('.toggle-password');
      
      if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        toggleIcon.textContent = '👁️';
      } else {
        passwordInput.type = 'password';
        toggleIcon.textContent = '👁️';
      }
    }
  </script>
</body>
</html>