<?php
// -------------------- DATABASE CONNECTION --------------------
$conn = new mysqli("localhost", "root", "1234", "vetsmartdb");
if ($conn->connect_error) {
    die("DB Connection Failed: " . $conn->connect_error);
}

// ---------- LOAD FARMS ----------
$farmList = $conn->query("SELECT id, farm_name FROM farms ORDER BY farm_name ASC");

// ---------- LOAD BATCHES ----------
$batchList = $conn->query("SELECT id, cage_no FROM batch_details ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Add Farm Records | VetSmart</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="style.css">
</head>

<body>

<div class="main container mt-4">

    <!-- ---------------- BROILERS SECTION ---------------- -->
    <div class="card shadow-sm mb-4 p-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3 class="text-primary mb-0">Add Records for Broilers</h3>
            <button class="btn btn-outline-primary" data-bs-toggle="collapse" data-bs-target="#broilerCollapse">
                <i class='bx bx-chevron-down'></i>
            </button>
        </div>

        <div class="collapse" id="broilerCollapse">
            <form action="save-broilers.php" method="POST" enctype="multipart/form-data">

                <!-- FARM NAME -->
                <div class="mb-3">
                    <label class="form-label">Farm Name</label>
                    <select name="farm_name" class="form-select" required>
                        <option value="">Select Farm</option>
                        <?php foreach ($farmList as $row): ?>
                            <option value="<?= $row['id'] ?>"><?= $row['farm_name'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- FIELDS -->
                <div class="mb-3"><label>Registration Number</label><input type="text" name="reg_number" class="form-control" required></div>
                <div class="mb-3"><label>Visit Date</label><input type="datetime-local" name="visit_datetime" class="form-control" required></div>
                <div class="mb-3"><label>Flock Size</label><input type="number" name="flock_size" class="form-control" required></div>
                <div class="mb-3"><label>Age</label><input type="number" name="age" class="form-control" required></div>
                <div class="mb-3"><label>Average Weight</label><input type="number" step="0.01" name="avg_weight" class="form-control"></div>
                <div class="mb-3"><label>Weight Gain</label><input type="number" step="0.01" name="weight_gain" class="form-control"></div>
                <div class="mb-3"><label>Feed Intake</label><input type="text" name="feed_intake" class="form-control"></div>
                <div class="mb-3"><label>Mortality</label><input type="number" name="mortality" class="form-control"></div>
                <div class="mb-3"><label>Mortality %</label><input type="number" step="0.01" name="mortality_percent" class="form-control"></div>
                <div class="mb-3"><label>Complain</label><textarea name="complain" class="form-control"></textarea></div>
                <div class="mb-3"><label>Clinical Signs</label><textarea name="clinical_signs" class="form-control"></textarea></div>
                <div class="mb-3"><label>Post Mortem Images</label><input type="file" name="post_mortem_images[]" multiple class="form-control"></div>
                <div class="mb-3"><label>DD</label><input type="text" name="dd" class="form-control"></div>
                <div class="mb-3"><label>Test Request</label><textarea name="test_request" class="form-control"></textarea></div>
                <div class="mb-3"><label>Test Report</label><textarea name="test_report" class="form-control"></textarea></div>
                <div class="mb-3"><label>Recommendation</label><textarea name="recommendation" class="form-control"></textarea></div>
                <div class="mb-3"><label>Treatment</label><textarea name="treatment" class="form-control"></textarea></div>
                <div class="mb-3"><label>Follow Ups</label><textarea name="follow_ups" class="form-control"></textarea></div>

                <button class="btn btn-success">Save Broiler Record</button>

            </form>
        </div>
    </div>


    <!-- ---------------- LAYERS SECTION ---------------- -->
    <div class="card shadow-sm mb-4 p-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3 class="text-success mb-0">Add Records for Layers</h3>
            <button class="btn btn-outline-success" data-bs-toggle="collapse" data-bs-target="#layersCollapse">
                <i class='bx bx-chevron-down'></i>
            </button>
        </div>

        <div class="collapse" id="layersCollapse">
            <form action="save-layers.php" method="POST" enctype="multipart/form-data">

                <!-- FARM NAME -->
                <div class="mb-3">
                    <label class="form-label">Farm Name</label>
                    <select name="farm_name" class="form-select" required>
                        <option value="">Select Farm</option>
                        <?php foreach ($farmList as $row): ?>
                            <option value="<?= $row['id'] ?>"><?= $row['farm_name'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3"><label class="form-label">Registration Number</label>
                    <input type="text" name="reg_number" class="form-control" required></div>

                <div class="mb-3"><label class="form-label">Visit Date & Time</label>
                    <input type="datetime-local" name="visit_datetime" class="form-control" required></div>

                <div class="mb-3"><label class="form-label">Flock Size</label>
                    <input type="number" name="flock_size" class="form-control" required></div>

                <div class="mb-3"><label class="form-label">Age</label>
                    <input type="number" name="age" class="form-control" required></div>

                <div class="mb-3"><label class="form-label">Average Age</label>
                    <input type="number" name="avg_age" class="form-control"></div>

                <div class="mb-3"><label class="form-label">Egg Production</label>
                    <input type="number" name="egg_production" class="form-control"></div>

                <div class="mb-3"><label class="form-label">Egg Production (%)</label>
                    <input type="number" name="egg_percent" step="0.01" class="form-control"></div>

                <div class="mb-3"><label class="form-label">Feed Intake</label>
                    <input type="text" name="feed_intake" class="form-control" required></div>

                <div class="mb-3"><label class="form-label">Mortality</label>
                    <input type="number" name="mortality" class="form-control" required></div>

                <div class="mb-3"><label class="form-label">Mortality (%)</label>
                    <input type="number" step="0.01" name="mortality_percent" class="form-control" required></div>

                <div class="mb-3"><label class="form-label">Complain / Visit Purpose</label>
                    <textarea name="complain" class="form-control"></textarea></div>

                <div class="mb-3"><label class="form-label">Clinical Signs</label>
                    <textarea name="clinical_signs" class="form-control"></textarea></div>

                <div class="mb-3"><label class="form-label">Post Mortem Changes</label>
                    <textarea name="post_mortem_changes" class="form-control"></textarea></div>

                <div class="mb-3"><label class="form-label">Post Mortem Images</label>
                    <input type="file" name="post_mortem_images[]" multiple accept="image/*" class="form-control">
                </div>

                <div class="mb-3"><label class="form-label">DD</label>
                    <input type="text" name="dd" class="form-control"></div>

                <div class="mb-3"><label class="form-label">Test Request</label>
                    <textarea name="test_request" class="form-control"></textarea></div>

                <div class="mb-3"><label class="form-label">Test Report</label>
                    <textarea name="test_report" class="form-control"></textarea></div>

                <div class="mb-3"><label class="form-label">Recommendation / Advices</label>
                    <textarea name="recommendation" class="form-control"></textarea></div>

                <div class="mb-3"><label class="form-label">Treatment</label>
                    <textarea name="treatment" class="form-control"></textarea></div>

                <div class="mb-3"><label class="form-label">Follow Ups</label>
                    <textarea name="follow_ups" class="form-control"></textarea></div>

                <button class="btn btn-success">Save Layers Record</button>
            </form>
        </div>
    </div>


</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
