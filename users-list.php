<?php
session_start();

// Database connection
$servername = "localhost";
$username   = "root";
$password   = "1234";
$dbname     = "vetsmartdb";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

// Fetch users
$sql = "SELECT * FROM users ORDER BY id DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>User List</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
  <link rel="stylesheet" href="style.css">
</head>

<body>
  <!-- Sidebar -->
   <div class="sidebar" id="sidebar">
    <div class="toggle-btn" id="toggle-btn">
      <i class='bx bx-chevron-right'></i>
    </div>

    <!-- Company Logo -->
    <div class="logo">
      <img src="images/favicon.png" alt="VetSmart Logo" style="width: 100px; height: auto;">
    </div>

    <h2 class="logo">VetSmart</h2>
    <ul>
      <li class="nav-item">
        <a class="nav-link d-flex align-items-center" data-bs-toggle="collapse" href="#userMenu" role="button"
          aria-expanded="false" aria-controls="userMenu">
          <i class='bx bx-user'></i><span class="link-text">User Management</span>
          <i class='bx bx-chevron-down ms-auto'></i>
        </a>
        <div class="collapse" id="userMenu">
          <ul class="list-unstyled ps-4">
            <li><a href="manage-users.html" class="d-block py-1"><i class='bx bx-plus me-2'></i>Create User</a></li>
            <li><a href="users-list.html" class="d-block py-1"><i class='bx bx-list-ul me-2'></i>Manage Users</a></li>
          </ul>
        </div>
      </li>
      <li><a href="index.html"><i class='bx bx-layer'></i><span class="link-text">Dashboard</span></a></li>
    </ul>
  </div>

  <div class="main container mt-4">
    <!-- Topbar -->
    <div class="d-flex justify-content-end p-3">
      <div>
        <button class="btn btn-light d-flex align-items-center" type="button" id="profileMenu"
                data-bs-toggle="dropdown" aria-expanded="false">
          <i class='bx bx-user-circle fs-3'></i>
        </button>
        <ul class="dropdown-menu dropdown-menu-end shadow" aria-labelledby="profileMenu">
          <li><a class="dropdown-item" href="#"><i class='bx bx-log-out me-2'></i>Logout</a></li>
        </ul>
      </div>
    </div>

    <h3 class="mb-3">All Users</h3>

    <table class="table table-bordered table-striped">
      <thead class="table-dark">
        <tr>
          <th>#</th>
          <th>Full Name</th>
          <th>Designation</th>
          <th>Company</th>
          <th>Telephone</th>
          <th>Username</th>
          <th>Role</th>
        </tr>
      </thead>
      <tbody>
        <?php
        if ($result->num_rows > 0) {
          $i = 1;
          while ($row = $result->fetch_assoc()) {
            echo "<tr>
                    <td>".$i++."</td>
                    <td>".$row['fullname']."</td>
                    <td>".$row['designation']."</td>
                    <td>".$row['company']."</td>
                    <td>".$row['telephone']."</td>
                    <td>".$row['username']."</td>
                    <td>".$row['role']."</td>
                  </tr>";
          }
        } else {
          echo "<tr><td colspan='7' class='text-center'>No users found</td></tr>";
        }
        ?>
      </tbody>
    </table>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php $conn->close(); ?>
