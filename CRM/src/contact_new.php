<?php
require_once 'config.php';
requireLogin();

$customerId = (int)($_GET['customer_id'] ?? 0);
if ($customerId <= 0) {
    header('Location: index.php?section=customers');
    exit;
}

// Kunde laden
$stmt = $pdo->prepare("SELECT * FROM customers WHERE id = :id");
$stmt->execute([':id' => $customerId]);
$customer = $stmt->fetch();

if (!$customer) {
    die("Kunde nicht gefunden.");
}

$errors = [];
$data = [
    'contact_date' => date('Y-m-d\TH:i'), // für datetime-local
    'contact_type' => 'Telefon',
    'subject'      => '',
    'notes'        => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data['contact_date'] = trim($_POST['contact_date'] ?? '');
    $data['contact_type'] = trim($_POST['contact_type'] ?? 'Telefon');
    $data['subject']      = trim($_POST['subject'] ?? '');
    $data['notes']        = trim($_POST['notes'] ?? '');

    if ($data['contact_date'] === '') {
        $errors[] = 'Bitte Datum/Uhrzeit wählen.';
    }
    if ($data['subject'] === '') {
        $errors[] = 'Bitte einen Betreff eingeben.';
    }

    if (empty($errors)) {
        // Datum ins Format Y-m-d H:i:s bringen
        $dt = date('Y-m-d H:i:s', strtotime($data['contact_date']));
        $user = currentUser();

        $sql = "INSERT INTO contacts (customer_id, user_id, contact_date, contact_type, subject, notes)
                VALUES (:customer_id, :user_id, :contact_date, :contact_type, :subject, :notes)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':customer_id'  => $customerId,
            ':user_id'      => $user['id'],
            ':contact_date' => $dt,
            ':contact_type' => $data['contact_type'],
            ':subject'      => $data['subject'],
            ':notes'        => $data['notes'],
        ]);

        header('Location: customer.php?id=' . $customerId);
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Neuer Kontakt – <?= htmlspecialchars($customer['company']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<nav class="navbar navbar-dark bg-dark navbar-expand-lg">
    <div class="container-fluid">
        <a class="navbar-brand" href="index.php">Mini CRM</a>
        <div class="d-flex">
            <a class="btn btn-outline-light btn-sm" href="customer.php?id=<?= $customerId ?>">Zurück zum Kunden</a>
        </div>
    </div>
</nav>

<div class="container mt-3">
    <div class="card">
        <div class="card-header">
            Neuen Kontakt erfassen für: <?= htmlspecialchars($customer['company']) ?>
        </div>
        <div class="card-body">
            <?php if ($errors): ?>
                <div class="alert alert-danger">
                    <?php foreach ($errors as $e): ?>
                        <?= htmlspecialchars($e) ?><br>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form method="post">
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Datum / Uhrzeit</label>
                        <input type="datetime-local" name="contact_date" class="form-control"
                               value="<?= htmlspecialchars($data['contact_date']) ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Art</label>
                        <select name="contact_type" class="form-select">
                            <?php
                            $types = ['Telefon','E-Mail','Meeting','Online'];
                            foreach ($types as $t):
                            ?>
                                <option value="<?= $t ?>" <?= $t === $data['contact_type'] ? 'selected' : '' ?>>
                                    <?= $t ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Betreuer (eingeloggt)</label>
                        <input type="text" class="form-control"
                               value="<?= htmlspecialchars(currentUser()['username']) ?>" disabled>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Betreff</label>
                    <input type="text" name="subject" class="form-control"
                           value="<?= htmlspecialchars($data['subject']) ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Notizen</label>
                    <textarea name="notes" class="form-control" rows="4"><?= htmlspecialchars($data['notes']) ?></textarea>
                </div>

                <button type="submit" class="btn btn-primary">Kontakt speichern</button>
                <a href="customer.php?id=<?= $customerId ?>" class="btn btn-secondary">Abbrechen</a>
            </form>
        </div>
    </div>
</div>
</body>
</html>
