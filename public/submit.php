<?php
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../admin/forms-list.php');
    exit;
}

$form_id = isset($_POST['form_id']) ? (int)$_POST['form_id'] : 0;

// Verify form exists
$stmt = $pdo->prepare("SELECT * FROM forms WHERE id = ?");
$stmt->execute([$form_id]);
$form = $stmt->fetch();

if (!$form) {
    die("Invalid form!");
}

// Get form structure for validation
$fields = json_decode($form['structure_json'], true);

// Validate required fields
$errors = [];
$response_data = [];

foreach ($fields as $field) {
    $field_name = $field['label'];
    $field_value = isset($_POST[$field_name]) ? $_POST[$field_name] : null;

    // Check required fields
    if ($field['required']) {
        if ($field['type'] === 'checkbox') {
            if (empty($field_value) || !is_array($field_value)) {
                $errors[] = "$field_name is required";
            }
        } else {
            if (empty($field_value)) {
                $errors[] = "$field_name is required";
            }
        }
    }

    // Validate number type
    if ($field['type'] === 'number' && !empty($field_value)) {
        if (!is_numeric($field_value)) {
            $errors[] = "$field_name must be a number";
        }
    }

    // Store response
    if ($field['type'] === 'checkbox' && is_array($field_value)) {
        $response_data[$field_name] = implode(', ', $field_value);
    } else {
        $response_data[$field_name] = $field_value ?? '';
    }
}

// If validation fails
if (!empty($errors)) {
    echo "<!DOCTYPE html>
    <html>
    <head>
        <title>Submission Error</title>
        <link rel='stylesheet' href='../assets/css/style.css'>
    </head>
    <body>
        <div class='container'>
            <div class='alert alert-error'>
                <h2>❌ Submission Failed</h2>
                <ul>";
    foreach ($errors as $error) {
        echo "<li>" . htmlspecialchars($error) . "</li>";
    }
    echo "</ul>
            </div>
            <a href='form.php?id=$form_id' class='btn btn-primary'>← Go Back</a>
        </div>
    </body>
    </html>";
    exit;
}

// Save submission
try {
    $response_json = json_encode($response_data);
    $stmt = $pdo->prepare("INSERT INTO form_submissions (form_id, response_json) VALUES (?, ?)");
    $stmt->execute([$form_id, $response_json]);

    $submission_id = $pdo->lastInsertId();

    // Success page
    echo "<!DOCTYPE html>
    <html>
    <head>
        <title>Submission Successful</title>
        <link rel='stylesheet' href='../assets/css/style.css'>
    </head>
    <body>
        <div class='container'>
            <div class='alert alert-success'>
                <h1>✅ Thank You!</h1>
                <p>Your response has been submitted successfully.</p>
                <p><strong>Submission ID:</strong> #$submission_id</p>
            </div>
            <div class='form-actions'>
                <a href='form.php?id=$form_id' class='btn btn-primary'>Submit Another Response</a>
            </div>
        </div>
    </body>
    </html>";
} catch (PDOException $e) {
    echo "<!DOCTYPE html>
    <html>
    <head>
        <title>Error</title>
        <link rel='stylesheet' href='../assets/css/style.css'>
    </head>
    <body>
        <div class='container'>
            <div class='alert alert-error'>
                <h2>❌ Database Error</h2>
                <p>" . htmlspecialchars($e->getMessage()) . "</p>
            </div>
            <a href='form.php?id=$form_id' class='btn btn-primary'>← Go Back</a>
        </div>
    </body>
    </html>";
}
