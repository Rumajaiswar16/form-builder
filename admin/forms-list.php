<?php
require_once '../config/db.php';

// Delete form
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    try {
        $stmt = $pdo->prepare("DELETE FROM forms WHERE id = ?");
        $stmt->execute([$id]);
        $success = "Form deleted successfully!";
    } catch (PDOException $e) {
        $error = "Error: " . $e->getMessage();
    }
}

// Fetch all forms
$stmt = $pdo->query("SELECT f.*, COUNT(fs.id) as submission_count 
                     FROM forms f 
                     LEFT JOIN form_submissions fs ON f.id = fs.form_id 
                     GROUP BY f.id 
                     ORDER BY f.created_at DESC");
$forms = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Forms - Admin Dashboard</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>
    <div class="container">
        <div class="header">
            <h1>📋 Admin Dashboard - All Forms</h1>
            <a href="create-form.php" class="btn btn-primary">+ Create New Form</a>
        </div>

        <?php if (isset($success)): ?>
            <div class="alert alert-success"><?= $success ?></div>
        <?php endif; ?>

        <?php if (isset($error)): ?>
            <div class="alert alert-error"><?= $error ?></div>
        <?php endif; ?>

        <?php if (empty($forms)): ?>
            <div class="no-data">
                <p>📭 No forms created yet</p>
                <a href="create-form.php" class="btn btn-primary">Create Your First Form</a>
            </div>
        <?php else: ?>
            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Form Title</th>
                            <th>Description</th>
                            <th>Created</th>
                            <th>Submissions</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($forms as $form): ?>
                            <tr>
                                <td><?= $form['id'] ?></td>
                                <td><strong><?= htmlspecialchars($form['title']) ?></strong></td>
                                <td><?= htmlspecialchars(substr($form['description'] ?? '', 0, 50)) ?><?= strlen($form['description'] ?? '') > 50 ? '...' : '' ?></td>
                                <td><?= date('d M Y', strtotime($form['created_at'])) ?></td>
                                <td>
                                    <span class="badge badge-info"><?= $form['submission_count'] ?> responses</span>
                                </td>
                                <td class="actions">
                                    <a href="../public/form.php?id=<?= $form['id'] ?>" target="_blank" class="btn btn-small">👁️ View</a>
                                    <a href="submissions.php?form_id=<?= $form['id'] ?>" class="btn btn-small btn-success">📊 Submissions</a>
                                    <a href="edit-form.php?id=<?= $form['id'] ?>" class="btn btn-small">✏️ Edit</a>
                                    <a href="?delete=<?= $form['id'] ?>" onclick="return confirm('Delete this form and all submissions?')" class="btn btn-small btn-danger">🗑️ Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</body>

</html>