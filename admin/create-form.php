<?php
require_once '../config/db.php';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $structure_json = $_POST['structure_json'];

    if (!empty($title) && !empty($structure_json)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO forms (title, description, structure_json) VALUES (?, ?, ?)");
            $stmt->execute([$title, $description, $structure_json]);
            $success = "Form created successfully! Form ID: " . $pdo->lastInsertId();
        } catch (PDOException $e) {
            $error = "Error: " . $e->getMessage();
        }
    } else {
        $error = "Please fill all required fields";
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Form - Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>
    <div class="container">
        <div class="header">
            <h1>🎯 Create New Form</h1>
            <a href="forms-list.php" class="btn btn-secondary">← Back to Forms</a>
        </div>

        <?php if (isset($success)): ?>
            <div class="alert alert-success"><?= $success ?></div>
        <?php endif; ?>

        <?php if (isset($error)): ?>
            <div class="alert alert-error"><?= $error ?></div>
        <?php endif; ?>

        <form id="formBuilderForm" method="POST">
            <div class="form-group">
                <label>Form Title *</label>
                <input type="text" name="title" id="formTitle" required placeholder="e.g., Contact Form">
            </div>

            <div class="form-group">
                <label>Form Description</label>
                <textarea name="description" id="formDescription" rows="3" placeholder="Optional description"></textarea>
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
                <button type="submit" class="btn btn-success" id="saveFormBtn">💾 Save Form</button>
                <button type="button" onclick="resetForm()" class="btn btn-secondary">🔄 Reset</button>
            </div>
        </form>
    </div>

    <!-- External JavaScript -->
    <script src="../assets/js/app.js"></script>
</body>

</html>