<?php
require_once '../config/db.php';

$form_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch form data
$stmt = $pdo->prepare("SELECT * FROM forms WHERE id = ?");
$stmt->execute([$form_id]);
$form = $stmt->fetch();

if (!$form) {
    die("Form not found!");
}

// Handle form update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $structure_json = $_POST['structure_json'];
    $update_form_id = isset($_POST['form_id']) ? (int)$_POST['form_id'] : $form_id;

    if (!empty($title) && !empty($structure_json)) {
        try {
            $stmt = $pdo->prepare("UPDATE forms SET title = ?, description = ?, structure_json = ? WHERE id = ?");
            $stmt->execute([$title, $description, $structure_json, $update_form_id]);

            // REDIRECT to prevent form resubmission on refresh
            header("Location: edit-form.php?id=" . $update_form_id . "&success=1");
            exit;
        } catch (PDOException $e) {
            $error = "Error: " . $e->getMessage();
        }
    } else {
        $error = "Please fill all required fields";
    }
}

// Check for success message from redirect
$success_message = '';
if (isset($_GET['success'])) {
    $success_message = "Form updated successfully!";

    // Refresh form data after update
    $stmt = $pdo->prepare("SELECT * FROM forms WHERE id = ?");
    $stmt->execute([$form_id]);
    $form = $stmt->fetch();
}

$existing_fields = json_decode($form['structure_json'], true);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Form - <?= htmlspecialchars($form['title']) ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>
    <div class="container">
        <div class="header">
            <h1>✏️ Edit Form</h1>
            <a href="forms-list.php" class="btn btn-secondary">← Back to Forms</a>
        </div>

        <?php if (!empty($success_message)): ?>
            <div class="alert alert-success">
                <?= $success_message ?>
                <br><br>
                <a href="forms-list.php" class="btn btn-primary">View All Forms</a>
                <a href="../public/form.php?id=<?= $form_id ?>" target="_blank" class="btn btn-secondary">Preview Form</a>
            </div>
        <?php endif; ?>

        <?php if (isset($error)): ?>
            <div class="alert alert-error"><?= $error ?></div>
        <?php endif; ?>

        <form id="formBuilderForm" method="POST">
            <!-- IMPORTANT: Hidden field for form ID -->
            <input type="hidden" name="form_id" value="<?= $form_id ?>">

            <div class="form-group">
                <label>Form Title *</label>
                <input type="text" name="title" id="formTitle" value="<?= htmlspecialchars($form['title']) ?>" required>
            </div>

            <div class="form-group">
                <label>Form Description</label>
                <textarea name="description" id="formDescription" rows="3"><?= htmlspecialchars($form['description'] ?? '') ?></textarea>
            </div>

            <hr>

            <h3>📋 Form Fields</h3>

            <div class="field-builder">
                <div class="form-group">
                    <label>Field Label *</label>
                    <input type="text" id="fieldLabel" placeholder="e.g., Full Name">
                </div>

                <div class="form-group">
                    <label>Field Type *</label>
                    <select id="fieldType">
                        <option value="text">Text</option>
                        <option value="number">Number</option>
                        <option value="dropdown">Dropdown</option>
                        <option value="checkbox">Checkbox</option>
                    </select>
                </div>

                <div class="form-group" id="optionsGroup" style="display: none;">
                    <label>Options (comma-separated) *</label>
                    <input type="text" id="fieldOptions" placeholder="e.g., Male, Female, Other">
                </div>

                <div class="form-group">
                    <label>
                        <input type="checkbox" id="fieldRequired"> Required Field
                    </label>
                </div>

                <button type="button" onclick="addField()" class="btn btn-primary">+ Add Field</button>
            </div>

            <div id="fieldsPreview" class="fields-preview"></div>

            <input type="hidden" name="structure_json" id="structureJson">

            <div class="form-actions">
                <button type="submit" class="btn btn-success">💾 Update Form</button>
                <a href="forms-list.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>

    <!-- Combined JavaScript -->
    <script src="../assets/js/app.js"></script>

    <!-- Load existing fields -->
    <script>
        // Load existing fields from PHP
        fields = <?= json_encode($existing_fields) ?>;
        displayFields();
    </script>
</body>

</html>