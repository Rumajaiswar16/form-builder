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

$fields = json_decode($form['structure_json'], true);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($form['title']) ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>
    <div class="container form-public">
        <div class="form-header">
            <h1>📝 <?= htmlspecialchars($form['title']) ?></h1>
            <?php if (!empty($form['description'])): ?>
                <p class="form-description"><?= nl2br(htmlspecialchars($form['description'])) ?></p>
            <?php endif; ?>
        </div>

        <form id="publicForm" action="submit.php" method="POST">
            <input type="hidden" name="form_id" value="<?= $form_id ?>">

            <?php foreach ($fields as $index => $field): ?>
                <div class="form-group">
                    <label for="field_<?= $index ?>">
                        <?= htmlspecialchars($field['label']) ?>
                        <?php if ($field['required']): ?>
                            <span class="required">*</span>
                        <?php endif; ?>
                    </label>

                    <?php if ($field['type'] === 'text'): ?>
                        <input
                            type="text"
                            id="field_<?= $index ?>"
                            name="<?= htmlspecialchars($field['label']) ?>"
                            <?= $field['required'] ? 'required' : '' ?>
                            placeholder="Enter <?= htmlspecialchars($field['label']) ?>">

                    <?php elseif ($field['type'] === 'number'): ?>
                        <input
                            type="number"
                            id="field_<?= $index ?>"
                            name="<?= htmlspecialchars($field['label']) ?>"
                            <?= $field['required'] ? 'required' : '' ?>
                            placeholder="Enter <?= htmlspecialchars($field['label']) ?>">

                    <?php elseif ($field['type'] === 'dropdown'): ?>
                        <select
                            id="field_<?= $index ?>"
                            name="<?= htmlspecialchars($field['label']) ?>"
                            <?= $field['required'] ? 'required' : '' ?>>
                            <option value="">-- Select --</option>
                            <?php foreach ($field['options'] as $option): ?>
                                <option value="<?= htmlspecialchars($option) ?>">
                                    <?= htmlspecialchars($option) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>

                    <?php elseif ($field['type'] === 'checkbox'): ?>
                        <div class="checkbox-group">
                            <?php foreach ($field['options'] as $optIndex => $option): ?>
                                <label class="checkbox-label">
                                    <input
                                        type="checkbox"
                                        name="<?= htmlspecialchars($field['label']) ?>[]"
                                        value="<?= htmlspecialchars($option) ?>"
                                        <?= $field['required'] && $optIndex === 0 ? 'required' : '' ?>>
                                    <?= htmlspecialchars($option) ?>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary btn-large">
                    ✅ Submit Response
                </button>
                <button type="reset" class="btn btn-secondary">
                    🔄 Reset Form
                </button>
            </div>
        </form>

        <div class="form-footer">
            <p>Powered by Form Builder</p>
        </div>
    </div>

    <!-- External JavaScript -->
    <script src="../assets/js/app.js"></script>
</body>

</html>