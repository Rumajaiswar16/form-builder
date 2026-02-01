<?php
require_once '../config/db.php';

$form_id = isset($_GET['form_id']) ? (int)$_GET['form_id'] : 0;

// Delete submission
if (isset($_GET['delete'])) {
    $sub_id = (int)$_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM form_submissions WHERE id = ?");
    $stmt->execute([$sub_id]);
    header("Location: submissions.php?form_id=$form_id");
    exit;
}

// Fetch form
$stmt = $pdo->prepare("SELECT * FROM forms WHERE id = ?");
$stmt->execute([$form_id]);
$form = $stmt->fetch();

if (!$form) {
    die("Form not found!");
}

$fields = json_decode($form['structure_json'], true);

// Fetch submissions
$stmt = $pdo->prepare("SELECT * FROM form_submissions WHERE form_id = ? ORDER BY submitted_at DESC");
$stmt->execute([$form_id]);
$submissions = $stmt->fetchAll();

// Calculate analytics for dropdown fields
$analytics = [];
foreach ($fields as $field) {
    if ($field['type'] === 'dropdown') {
        $field_name = $field['label'];
        $analytics[$field_name] = [];

        foreach ($submissions as $sub) {
            $response = json_decode($sub['response_json'], true);
            $value = $response[$field_name] ?? '';

            if (!empty($value)) {
                if (!isset($analytics[$field_name][$value])) {
                    $analytics[$field_name][$value] = 0;
                }
                $analytics[$field_name][$value]++;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submissions - <?= htmlspecialchars($form['title']) ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>
    <div class="container">
        <div class="header">
            <h1>📊 Submissions: <?= htmlspecialchars($form['title']) ?></h1>
            <div>
                <a href="../public/form.php?id=<?= $form_id ?>" target="_blank" class="btn btn-primary">👁️ View Form</a>
                <a href="forms-list.php" class="btn btn-secondary">← Back to Forms</a>
            </div>
        </div>

        <!-- Analytics Section -->
        <div class="analytics-section">
            <h2>📈 Analytics</h2>
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-number"><?= count($submissions) ?></div>
                    <div class="stat-label">Total Submissions</div>
                </div>

                <?php foreach ($analytics as $field_name => $options): ?>
                    <?php if (!empty($options)): ?>
                        <div class="stat-card">
                            <div class="stat-label"><strong><?= htmlspecialchars($field_name) ?></strong></div>
                            <div class="analytics-details">
                                <?php
                                arsort($options);
                                $most_selected = array_key_first($options);
                                foreach ($options as $option => $count):
                                ?>
                                    <div class="analytics-row">
                                        <span><?= htmlspecialchars($option) ?>:</span>
                                        <strong><?= $count ?></strong>
                                    </div>
                                <?php endforeach; ?>
                                <div class="most-selected">
                                    🏆 Most selected: <strong><?= htmlspecialchars($most_selected) ?></strong>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </div>

        <hr>

        <!-- Submissions List -->
        <h2>📋 All Responses</h2>

        <?php if (empty($submissions)): ?>
            <div class="no-data">
                <p>📭 No submissions yet</p>
                <a href="../public/form.php?id=<?= $form_id ?>" class="btn btn-primary">Fill Form</a>
            </div>
        <?php else: ?>
            <div class="submissions-list">
                <?php foreach ($submissions as $index => $sub): ?>
                    <?php $response = json_decode($sub['response_json'], true); ?>
                    <div class="submission-card">
                        <div class="submission-header">
                            <div>
                                <strong>Submission #<?= $sub['id'] ?></strong>
                                <span class="badge"><?= date('d M Y, h:i A', strtotime($sub['submitted_at'])) ?></span>
                            </div>
                            <a href="?form_id=<?= $form_id ?>&delete=<?= $sub['id'] ?>"
                                onclick="return confirm('Delete this submission?')"
                                class="btn btn-small btn-danger">
                                🗑️ Delete
                            </a>
                        </div>
                        <div class="submission-data">
                            <?php foreach ($response as $key => $value): ?>
                                <div class="data-row">
                                    <span class="data-label"><?= htmlspecialchars($key) ?>:</span>
                                    <span class="data-value"><?= htmlspecialchars($value) ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Export Button (Bonus Feature) -->
            <div class="form-actions">
                <a href="export-csv.php?form_id=<?= $form_id ?>" class="btn btn-success">
                    📥 Export as CSV
                </a>
            </div>
        <?php endif; ?>
    </div>
</body>

</html>