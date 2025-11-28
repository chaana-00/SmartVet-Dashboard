<?php
// -------------------------------------------------------
// Fetch filter values safely
// -------------------------------------------------------
$farm_id = isset($_GET['farm_id']) ? $_GET['farm_id'] : '';
$farm_location = isset($_GET['farm_location']) ? $_GET['farm_location'] : '';
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>View Batch Details | VetSmart</title>

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

  <!-- Sidebar -->
  <div class="sidebar" id="sidebar">
    <div class="toggle-btn" id="toggle-btn">
      <i class='bx bx-chevron-left'></i>
    </div>

    <div class="logo">
      <img src="images/FCL.gif" alt="VetSmart Logo" style="width: 100px; height: auto;">
      <h2 class="logo">VetSmart</h2>
    </div>

    <ul>
      <li><a href="add-farm-records.php" class="highlight">
                    <i class='bx bx-file'></i><span class="link-text">Create Farm Records</span>
                </a></li>
            <li><a href="create-prescription.html" class="button">
                    <i class='bx bx-file'></i><span class="link-text">Create Prescription</span>
                </a></li>
            <li><a href="add-batch-details.php"><i class='bx bx-card'></i><span class="link-text">Add Batch
                        Details</span></a></li>
            <li><a href="view-batch-details.php" class="active"><i class='bx bx-folder-open'></i><span
                        class="link-text">View Batch Details</span></a></li>
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

  <!-- Main Section -->
  <div class="main container mt-4">

    <!-- Page Title -->
    <h3 class="text-success mb-4">View Batch Details</h3>

    <!-- Filters -->
    <div class="card shadow-sm p-4 mb-4">
      <form method="GET" action="">
        <div class="row g-3">

          <!-- Farm Name -->
          <div class="col-md-6">
            <label class="form-label">Farm Name</label>
            <select name="farm_id" class="form-select">
              <option value="">Select Farm</option>

              <?php
              $conn = new mysqli("localhost", "root", "1234", "vetsmartdb");
              $result = $conn->query("SELECT id, farm_name FROM farms ORDER BY farm_name DESC");

              while ($row = $result->fetch_assoc()) {
                $selected = ($farm_id == $row['id']) ? "selected" : "";
                echo "<option value='{$row['id']}' $selected>{$row['farm_name']}</option>";
              }
              ?>
            </select>
          </div>

          <!-- Farm Location -->
          <div class="col-md-6">
            <label class="form-label">Farm Location</label>
            <select name="farm_location" class="form-select">
              <option value="">Select Farm Location</option>

              <?php
              $result2 = $conn->query("SELECT DISTINCT farm_location FROM farms ORDER BY farm_location DESC");

              while ($row = $result2->fetch_assoc()) {
                $selected = ($farm_location == $row['farm_location']) ? "selected" : "";
                echo "<option value='{$row['farm_location']}' $selected>{$row['farm_location']}</option>";
              }

              $conn->close();
              ?>
            </select>
          </div>

          <div class="col-md-2 d-grid mt-3">
            <button type="submit" class="btn btn-success">Search</button>
          </div>
        </div>
      </form>
    </div>

    <!-- Table Display -->
    <div class="card shadow-sm p-4 mb-4">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="text-success">Batch Records</h5>

        <button class="btn btn-primary"
          onclick="window.location.href='download-selected-batch.php?farm_id=<?= $farm_id ?>&farm_location=<?= $farm_location ?>'">
          <i class='bx bx-download me-2'></i>Download Selected Data
        </button>

      </div>

      <div class="table-responsive" id="batchTable">
        <table class="table table-bordered align-middle">
          <thead class="table-success">
            <tr>
              <th>Farm Name</th>
              <th>Location</th>
              <th>Cage No</th>
              <th>Breed</th>
              <th>Hatchery</th>
              <th>Total Input</th>
              <th>Vaccination Details</th>
              <th>Treatment History</th>
            </tr>
          </thead>

          <tbody>
            <?php
            // Fetch batch details with JOIN to farms table
            $conn = new mysqli("localhost", "root", "1234", "vetsmartdb");

            $query = "
              SELECT 
                b.*, 
                f.farm_name,
                f.farm_location
              FROM batch_details b
              LEFT JOIN farms f ON b.farm_id = f.id
              WHERE 1=1
            ";

            if ($farm_id != "") {
              $query .= " AND b.farm_id = '$farm_id'";
            }

            if ($farm_location != "") {
              $query .= " AND f.farm_location = '$farm_location'";
            }
            // Sort by farm_name and farm_id in descending order
            $query .= " ORDER BY f.farm_name DESC, b.farm_id DESC";
            $result = $conn->query($query);

            if ($result->num_rows > 0) {
              while ($row = $result->fetch_assoc()) {

                echo "
                  <tr>
                    <td>{$row['farm_name']}</td>
                    <td>{$row['farm_location']}</td>
                    <td>{$row['cage_no']}</td>
                    <td>{$row['breed']}</td>
                    <td>{$row['hatchery']}</td>
                    <td>{$row['total_input']}</td>
                    <td>{$row['vaccination_details']}</td>
                    <td>{$row['treatment_history']}</td>
                  </tr>";
              }
            } else {
              echo "<tr><td colspan='8' class='text-center text-muted'>No batch details found.</td></tr>";
            }

            $conn->close();
            ?>
          </tbody>
        </table>
      </div>
    </div>

  </div>

  <!-- Word Export Dependencies -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/FileSaver.js/2.0.5/FileSaver.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/html-docx-js/0.4.1/html-docx.js"></script>

  <script>
    function downloadWord(elementId, filename) {
      const content = "<h2>Batch Details Report</h2>" +
        document.getElementById(elementId).innerHTML;

      const converted = htmlDocx.asBlob(content);
      saveAs(converted, filename + ".docx");
    }
  </script>

</body>
</html>
