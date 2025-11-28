<?php
// DB Connection
$conn = new mysqli("localhost", "root", "1234", "vetsmartdb");
if ($conn->connect_error) { die("DB Connection failed"); }

// If editing existing prescription (optional)
$prescription_id = $_GET['id'] ?? null;
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Create Prescription | VetSmart</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
  <link rel="stylesheet" href="style.css">
</head>

<body>

  <!-- Sidebar -->
  <div class="sidebar" id="sidebar">
    <div class="toggle-btn" id="toggle-btn">
      <i class='bx bx-chevron-right'></i>
    </div>

    <div class="logo">
      <img src="images/FCL.gif" style="width: 100px;">
    </div>
    <h2 class="logo">VetSmart</h2>

    <ul>
      <li><a href="add-farm-records.php" class="highlight">
          <i class='bx bx-file'></i><span class="link-text">Add Farm Records</span>
        </a></li>
      <li><a href="create-prescription.php" class="button">
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

      <!-- ===========================
           STEP 1 – DOWNLOAD BUTTON
      ============================ -->
      <?php if ($prescription_id): ?>
        <div class="d-flex justify-content-end mb-3">
          <a href="download-prescription.php?id=<?php echo $prescription_id; ?>" 
             class="btn btn-primary">
             <i class='bx bxs-file-doc'></i> Download Prescription
          </a>
        </div>
      <?php endif; ?>

      <h3 class="text-success mb-4">Create Prescription</h3>

      <form action="save-prescription.php" method="POST">

        <div class="card shadow-sm p-4">
          <div class="row g-3">

            <!-- Farm Name Dropdown -->
            <div class="col-md-6">
              <label class="form-label">Farm Name</label>
              <select name="farm_id" class="form-select" required>
                <option value="">Select Farm</option>

                <?php
                $result = $conn->query("SELECT id, farm_name FROM farms ORDER BY farm_name ASC");
                while ($row = $result->fetch_assoc()) {
                  echo "<option value='".$row['id']."'>".$row['farm_name']."</option>";
                }
                ?>
              </select>
            </div>

            <!-- Number of Birds -->
            <div class="col-md-6">
              <label class="form-label">Number of Birds</label>
              <input type="number" class="form-control" name="no_of_birds" required>
            </div>

          </div>

          <hr class="my-4">

          <!-- Prescription Table -->
          <h5 class="mb-3">Prescription Details</h5>

          <table class="table table-bordered">
            <thead class="table-light">
              <tr>
                <th>Drugs</th>
                <th>Indication</th>
                <th>Duration</th>
                <th>Total Volume / Drugs / Weight</th>
                <th>Action</th>
              </tr>
            </thead>

            <tbody id="drugTable">
              <tr>
                <td><input type="text" name="drug[]" class="form-control" required></td>
                <td><input type="text" name="indication[]" class="form-control" required></td>
                <td><input type="text" name="duration[]" class="form-control" required></td>
                <td><input type="text" name="total_volume[]" class="form-control" required></td>
                <td><button type="button" class="btn btn-danger btn-sm" onclick="removeRow(this)">X</button></td>
              </tr>
            </tbody>
          </table>

          <button type="button" class="btn btn-primary btn-sm" onclick="addRow()">+ Add Row</button>

          <hr class="my-4">

          <!-- Date & Time -->
          <div class="col-md-6">
            <label class="form-label">Date & Time</label>
            <input type="datetime-local" name="date_time" class="form-control" required>
          </div>

          <button class="btn btn-success mt-4">Save Prescription</button>
        </div>

      </form>
    </div>
  </div>


  <!-- JS for Add / Remove Table Rows -->
  <script>
    function addRow() {
      let table = document.getElementById("drugTable");
      let row = `
        <tr>
          <td><input type="text" name="drug[]" class="form-control" required></td>
          <td><input type="text" name="indication[]" class="form-control" required></td>
          <td><input type="text" name="duration[]" class="form-control" required></td>
          <td><input type="text" name="total_volume[]" class="form-control" required></td>
          <td><button type="button" class="btn btn-danger btn-sm" onclick="removeRow(this)">X</button></td>
        </tr>`;
      table.insertAdjacentHTML("beforeend", row);
    }

    function removeRow(btn) {
      btn.closest("tr").remove();
    }
  </script>

</body>
</html>
