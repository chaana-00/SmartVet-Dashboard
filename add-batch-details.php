<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Add Batch Details | VetSmart</title>

  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

  <!-- Google Font - Outfit -->
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">

  <!-- Boxicons -->
  <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">

  <!-- Custom CSS -->
  <link rel="stylesheet" href="style.css">
</head>

<body>
  <div class="sidebar" id="sidebar">
    <div class="toggle-btn" id="toggle-btn">
      <i class='bx bx-chevron-right'></i>
    </div>

    <!-- Company Logo -->
    <div class="logo">
      <img src="images/FCL.gif" alt="VetSmart Logo" style="width: 100px; height: auto;">
    </div>
    <h2 class="logo">VetSmart</h2>
    <ul>
      <li><a href="add-farm-records.php" class="highlight">
          <i class='bx bx-file'></i><span class="link-text">Add Farm Records</span>
        </a></li>
      <li><a href="create-prescription.html" class="button">
          <i class='bx bx-file'></i><span class="link-text">Create Prescription</span>
        </a></li>
      <li><a href="add-batch-details.php"><i class='bx bx-card'></i><span class="link-text">Add Batch
            Details</span></a></li>
      <li><a href="view-batch-details.php" class="active"><i class='bx bx-folder-open'></i><span class="link-text">View
            Batch Details</span></a></li>
      <!-- <li><a href="view-batch-details.html" class="active"><i class='bx bx-folder-open'></i><span class="link-text">View
            Batch Details</span></a></li> -->
      <li><a href="farm-register.html"><i class='bx bx-user'></i><span class="link-text">Farm Register</span></a>
      </li>
      <!-- <li><a href="add-farm-records.html"><i class='bx bx-file'></i><span class="link-text">Add Farm
                        Records</span></a></li> -->
      <li><a href="view-history.html"><i class='bx bx-folder-open'></i><span class="link-text">View
            History</span></a></li>
      <!-- <li><a href="notifications.html" class="active"><i class='bx bx-bell'></i><span
            class="link-text">Notifications</span></a></li> -->
      <!-- <li><a href="#"><i class='bx bx-bell'></i><span class="link-text">Notification</span></a></li>
            <li><a href="#"><i class='bx bx-cog'></i><span class="link-text">Settings</span></a></li> -->
      <li><a href="user-dashboard.php"><i class='bx bx-layer'></i><span class="link-text">Dashboard</span></a>
      </li>
    </ul>
  </div>
  <div class="main">
    <div class="container mt-4">
      <h3 class="text-success mb-4">Add Batch Details</h3>

      <form action="save-batch-details.php" method="POST">

        <div class="card shadow-sm p-4">
          <div class="row g-3">

            <!-- Farm Name -->
            <div class="col-md-6">
              <label class="form-label">Farm Name</label>
              <select name="farm_id" class="form-select" required>
                <option value="">Select Farm</option>
                <?php
              $conn = new mysqli("localhost", "root", "1234", "vetsmartdb");
              $result = $conn->query("SELECT id, farm_name FROM farms ORDER BY farm_name ASC");
              while($row = $result->fetch_assoc()){
                echo "<option value='".$row['id']."'>".$row['farm_name']."</option>";
              }
              $conn->close();
            ?>
              </select>
            </div>

            <!-- Farm Location -->
            <div class="col-md-6">
              <label class="form-label">Farm Location</label>
              <select name="farm_location" class="form-select" required>
                <option value="">Select Farm Location</option>
                <?php
              $conn = new mysqli("localhost", "root", "1234", "vetsmartdb");
              $result = $conn->query("SELECT farm_location FROM farms GROUP BY farm_location ORDER BY farm_location ASC");
              while($row = $result->fetch_assoc()){
                echo "<option value='".$row['farm_location']."'>".$row['farm_location']."</option>";
              }
              $conn->close();
            ?>
              </select>
            </div>

            <div class="col-md-4"><label>Cage No</label><input type="text" name="cage_no" class="form-control" required>
            </div>
            <div class="col-md-4"><label>Breed</label><input type="text" name="breed" class="form-control" required>
            </div>
            <div class="col-md-4"><label>Hatchery</label><input type="text" name="hatchery" class="form-control"
                required></div>
            <div class="col-md-6"><label>Total Input</label><input type="number" name="total_input" class="form-control"
                required></div>
            <div class="col-md-6"><label>Vaccination Details</label><textarea name="vaccination_details"
                class="form-control"></textarea></div>
            <div class="col-md-12"><label>Treatment History</label><textarea name="treatment_history"
                class="form-control"></textarea></div>

          </div>

          <button class="btn btn-success mt-3">Save Batch Details</button>
        </div>
      </form>
    </div>
  </div>

</body>

</html>