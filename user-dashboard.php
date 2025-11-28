<?php
// dashboard.php
// Adjust DB credentials if required
$servername = "localhost";
$username   = "root";
$password   = "1234";
$dbname     = "vetsmartdb";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("DB connection failed: " . $conn->connect_error);
}

// Totals
$farmCount = 0;
$batchCount = 0;
$totalBirds = 0;

$q = $conn->query("SELECT COUNT(*) AS total FROM farms");
if ($q) { $farmCount = (int)$q->fetch_assoc()['total']; }

$q = $conn->query("SELECT COUNT(*) AS total FROM batch_details");
if ($q) { $batchCount = (int)$q->fetch_assoc()['total']; }

$q = $conn->query("SELECT SUM(total_input) AS total FROM batch_details");
if ($q) { $totalBirds = (int)$q->fetch_assoc()['total']; }

// Data for charts (group by farm)
$labels = [];
$values = [];

$q = $conn->query("
    SELECT f.farm_name, SUM(b.total_input) AS total_input
    FROM batch_details b
    LEFT JOIN farms f ON b.farm_id = f.id
    GROUP BY f.id
    ORDER BY SUM(b.total_input) DESC
");
if ($q) {
    while ($r = $q->fetch_assoc()) {
        $labels[] = $r['farm_name'] ?? 'Unknown';
        $values[] = (int)$r['total_input'];
    }
}

// Table rows (detailed)
$tableQuery = "
  SELECT b.*, f.farm_name, f.farm_location
  FROM batch_details b
  LEFT JOIN farms f ON b.farm_id = f.id
  ORDER BY f.farm_name DESC, b.id DESC
";
$tableResult = $conn->query($tableQuery);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>VetSmart — Dashboard</title>

  <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Boxicons -->
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">

    <link rel="stylesheet" href="style.css">

    <!-- Google Maps -->
    <script src="https://maps.googleapis.com/maps/api/js?key=YOUR_API_KEY&libraries=places"></script>

  <style>
    body { font-family: "Outfit", sans-serif; background:#f6f7fb; }
    .sidebar {
      position: fixed;
      left: 0; top: 0; bottom: 0;
      width: 220px;
      background: #0f1e36; /* blue */
      color:#fff;
      padding: 20px 10px;
      overflow:auto;
    }
    .sidebar .logo { text-align:center; margin-bottom:10px; }
    .sidebar .logo img { max-width:120px; height:auto; display:block; margin:0 auto 6px; }
    .sidebar h2.logo { font-size:18px; margin:0 0 8px; font-weight:700; color:#fff;}
    .sidebar ul { list-style:none; padding:0; margin:0; }
    .sidebar li { margin:8px 0; }
    .sidebar a { color:#fff; text-decoration:none; display:flex; align-items:center; gap:10px; padding:8px; border-radius:6px; }
    .sidebar a.active, .sidebar a:hover { background: rgba(255,255,255,0.08); }

    .main {
      margin-left: 240px;
      padding: 22px;
    }

    .card-compact { padding:18px; border-radius:10px; }
    .stat-number { font-size:28px; font-weight:700; margin-top:6px; }

    /* limit pie size and center inside its card */
    #pieCard { display:flex; align-items:center; justify-content:center; }
    #pieChart { max-width:220px; max-height:220px; }

    /* small screens adjust */
    @media (max-width: 767px) {
      .sidebar { position:relative; width:100%; height:auto; display:block; }
      .main { margin-left:0; padding:12px; }
      #pieChart { max-width:180px; max-height:180px; }
    }
  </style>

  <!-- Chart.js -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <!-- html-docx-js + FileSaver for client docx export if needed -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/FileSaver.js/2.0.5/FileSaver.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/html-docx-js/0.4.1/html-docx.js"></script>
</head>
<body>

  <!-- SIDEBAR (theme from your farm-register) -->
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
                    <i class='bx bx-file'></i><span class="link-text">Create Farm Records</span>
                </a></li>
            <li><a href="create-prescription.php" class="button">
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

  <!-- MAIN -->
  <div class="main">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h3 class="mb-0">User Dashboard</h3>
      <div>
        <!-- profile icon -->
        <button class="btn btn-light"><i class="bx bx-user-circle"></i></button>
      </div>
    </div>

    <!-- Stats -->
    <div class="row g-3 mb-4">
      <div class="col-md-4">
        <div class="card card-compact shadow-sm">
          <small class="text-muted">Total Registered Farms</small>
          <div class="stat-number"><?php echo (int)$farmCount; ?></div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card card-compact shadow-sm">
          <small class="text-muted">Total Batches</small>
          <div class="stat-number"><?php echo (int)$batchCount; ?></div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card card-compact shadow-sm">
          <small class="text-muted">Total Birds Input</small>
          <div class="stat-number"><?php echo (int)$totalBirds; ?></div>
        </div>
      </div>
    </div>

    <!-- Charts -->
    <div class="row g-3 mb-4">
      <div class="col-lg-8">
        <div class="card p-3 shadow-sm">
          <h6 class="text-success">Birds Input by Farm</h6>
          <canvas id="barChart" style="width:100%; height:320px;"></canvas>
        </div>
      </div>

      <div class="col-lg-4">
        <div class="card p-3 shadow-sm" id="pieCard">
          <div>
            <h6 class="text-success text-center">Farm Input Distribution</h6>
            <canvas id="pieChart"></canvas>
            <div class="text-center mt-2">
              <!-- <button class="btn btn-outline-primary btn-sm" id="downloadTableBtn">Download Table (.docx)</button> -->
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Table -->
    <div class="card p-3 shadow-sm">
      <h6 class="text-success mb-3">Batch Details Summary</h6>
      <div class="table-responsive" id="batchTable">
        <table class="table table-striped table-bordered">
          <thead class="table-success">
            <tr>
              <th>Farm Name</th>
              <th>Location</th>
              <th>Cage No</th>
              <th>Breed</th>
              <th>Hatchery</th>
              <th>Total Input</th>
            </tr>
          </thead>
          <tbody>
            <?php
            if ($tableResult && $tableResult->num_rows > 0) {
                while ($r = $tableResult->fetch_assoc()) {
                    $farmName = htmlspecialchars($r['farm_name'] ?? '—');
                    $loc = htmlspecialchars($r['farm_location'] ?? '—');
                    $cage = htmlspecialchars($r['cage_no'] ?? '—');
                    $breed = htmlspecialchars($r['breed'] ?? '—');
                    $hatchery = htmlspecialchars($r['hatchery'] ?? '—');
                    $input = htmlspecialchars($r['total_input'] ?? '0');

                    echo "<tr>
                      <td>{$farmName}</td>
                      <td>{$loc}</td>
                      <td>{$cage}</td>
                      <td>{$breed}</td>
                      <td>{$hatchery}</td>
                      <td>{$input}</td>
                    </tr>";
                }
            } else {
                echo "<tr><td colspan='6' class='text-center text-muted'>No batch data</td></tr>";
            }
            ?>
          </tbody>
        </table>
      </div>
    </div>

  </div> <!-- /main -->

<script>
  // chart data from PHP
  const labels = <?php echo json_encode($labels, JSON_HEX_TAG); ?>;
  const values = <?php echo json_encode($values, JSON_HEX_TAG); ?>;

  // Bar chart
  const barCtx = document.getElementById('barChart').getContext('2d');
  new Chart(barCtx, {
    type: 'bar',
    data: {
      labels: labels,
      datasets: [{
        label: 'Total Birds Input',
        data: values,
        backgroundColor: 'rgba(13,110,253,0.7)',
        borderColor: 'rgba(13,110,253,1)',
        borderWidth: 1
      }]
    },
    options: {
      responsive: true,
      scales: {
        y: { beginAtZero: true, ticks: { precision:0 } }
      }
    }
  });

  // Pie chart (reduced size)
  const pieCtx = document.getElementById('pieChart').getContext('2d');
  new Chart(pieCtx, {
    type: 'pie',
    data: {
      labels: labels,
      datasets: [{
        data: values,
        backgroundColor: [
          '#FF6384','#36A2EB','#FFCD56','#4BC0C0','#9966FF','#FF9F40','#8DD3C7','#BEBADA'
        ]
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: true,
      plugins: {
        legend: { display: true, position: 'bottom' }
      }
    }
  });

  // Download visible table to .docx (client-side)
  // document.getElementById('downloadTableBtn').addEventListener('click', function () {
  //   const el = document.getElementById('batchTable');
  //   const content = `
  //     <h2 style="text-align:center;">Batch Details Report</h2>
  //     ${el.innerHTML}
  //     <p style="font-size:11px; color:#666;">This record is system generated. Copyright © Farmchemie Private Limited.</p>
  //   `;
  //   const converted = htmlDocx.asBlob(content, {orientation: 'portrait'});
  //   saveAs(converted, 'BatchDetails_Report.docx');
  // });
</script>

</body>
</html>
