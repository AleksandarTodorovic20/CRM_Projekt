<?php
require_once 'config.php';
requireLogin();

$errors = [];
$success = false;

// Standardwerte (für erstes Laden oder wenn es Fehler gibt)
$data = [
    'company'    => '',
    'salutation' => '',
    'first_name' => '',
    'last_name'  => '',
    'street'     => '',
    'zip'        => '',
    'city'       => '',
    'country'    => 'Österreich',
    'phone'      => '',
    'email'      => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Formulardaten einlesen
    foreach ($data as $key => $val) {
        $data[$key] = trim($_POST[$key] ?? '');
    }

    // Validierung
    if ($data['company'] === '') {
        $errors[] = 'Firma darf nicht leer sein.';
    }
    if ($data['last_name'] === '' && $data['first_name'] === '') {
        $errors[] = 'Mindestens Vorname oder Nachname muss ausgefüllt sein.';
    }
    if ($data['email'] !== '' && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Die E-Mail-Adresse ist ungültig.';
    }

    if (empty($errors)) {
        // Kundennummer automatisch generieren (K-XXXX)
        $stmt = $pdo->query("SELECT customer_no FROM customers ORDER BY id DESC LIMIT 1");
        $last = $stmt->fetch();
        $nextNumber = 1001;

        if ($last && preg_match('/K-(\d+)/', $last['customer_no'], $m)) {
            $nextNumber = (int)$m[1] + 1;
        }

        $customerNo = 'K-' . $nextNumber;

        $sql = "INSERT INTO customers
                    (customer_no, company, salutation, first_name, last_name, street, zip, city, country, phone, email)
                VALUES
                    (:customer_no, :company, :salutation, :first_name, :last_name, :street, :zip, :city, :country, :phone, :email)";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':customer_no' => $customerNo,
            ':company'     => $data['company'],
            ':salutation'  => $data['salutation'],
            ':first_name'  => $data['first_name'],
            ':last_name'   => $data['last_name'],
            ':street'      => $data['street'],
            ':zip'         => $data['zip'],
            ':city'        => $data['city'],
            ':country'     => $data['country'],
            ':phone'       => $data['phone'],
            ':email'       => $data['email'],
        ]);

        $newId = (int)$pdo->lastInsertId();
        // nach erfolgreichem Speichern direkt zur Detailseite des Kunden
        header('Location: customer.php?id=' . $newId);
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Neuen Kunden anlegen</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<nav class="navbar navbar-dark bg-dark navbar-expand-lg">
    <div class="container-fluid">
        <a class="navbar-brand" href="index.php">Mini CRM</a>
        <div class="d-flex">
            <a class="btn btn-outline-light btn-sm" href="index.php?section=customers">Zurück zur Kundenliste</a>
        </div>
    </div>
</nav>

<div class="container mt-3">
    <div class="card">
        <div class="card-header">
            Neuen Kunden anlegen
        </div>
        <div class="card-body">
            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <?php foreach ($errors as $e): ?>
                        <?= htmlspecialchars($e) ?><br>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form method="post">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Firma *</label>
                        <input type="text" name="company" class="form-control"
                               value="<?= htmlspecialchars($data['company']) ?>" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Anrede</label>
                        <input type="text" name="salutation" class="form-control"
                               placeholder="Herr/Frau"
                               value="<?= htmlspecialchars($data['salutation']) ?>">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Vorname</label>
                        <input type="text" name="first_name" class="form-control"
                               value="<?= htmlspecialchars($data['first_name']) ?>">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Nachname</label>
                        <input type="text" name="last_name" class="form-control"
                               value="<?= htmlspecialchars($data['last_name']) ?>">
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Straße</label>
                        <input type="text" name="street" class="form-control"
                               value="<?= htmlspecialchars($data['street']) ?>">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">PLZ</label>
                        <input type="text" name="zip" class="form-control"
                               value="<?= htmlspecialchars($data['zip']) ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Ort</label>
                        <input type="text" name="city" class="form-control"
                               value="<?= htmlspecialchars($data['city']) ?>">
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Land</label>
                        <input type="text" name="country" class="form-control"
                               value="<?= htmlspecialchars($data['country']) ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Telefon</label>
                        <input type="text" name="phone" class="form-control"
                               value="<?= htmlspecialchars($data['phone']) ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">E-Mail</label>
                        <input type="email" name="email" class="form-control"
                               value="<?= htmlspecialchars($data['email']) ?>">
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">Kunden speichern</button>
                <a href="index.php?section=customers" class="btn btn-secondary">Abbrechen</a>
            </form>
        </div>
    </div>
</div>
</body>
</html>
