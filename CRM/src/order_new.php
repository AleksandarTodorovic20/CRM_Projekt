<?php
require_once 'config.php';
requireLogin();

// customer_id aus GET holen
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
    'order_date'   => date('Y-m-d'),
    'total_amount' => '',
    'status'       => 'offen',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data['order_date']   = trim($_POST['order_date'] ?? '');
    $data['total_amount'] = trim($_POST['total_amount'] ?? '');
    $data['status']       = trim($_POST['status'] ?? 'offen');

    if ($data['order_date'] === '') {
        $errors[] = 'Bitte ein Bestelldatum wählen.';
    }
    if ($data['total_amount'] === '' || !is_numeric(str_replace(',', '.', $data['total_amount']))) {
        $errors[] = 'Bitte einen gültigen Betrag eingeben.';
    }

    if (empty($errors)) {
        // Betrag in Dezimal-Format (Punkt als Trennzeichen)
        $amount = (float)str_replace(',', '.', $data['total_amount']);

        // nächste Bestellnummer generieren (ORD-0001, ORD-0002, ...)
        $stmt = $pdo->query("SELECT id FROM orders ORDER BY id DESC LIMIT 1");
        $last = $stmt->fetch();
        $next = $last ? ((int)$last['id'] + 1) : 1;
        $orderNo = 'ORD-' . str_pad((string)$next, 4, '0', STR_PAD_LEFT);

        $sql = "INSERT INTO orders (customer_id, order_no, order_date, total_amount, currency, status)
                VALUES (:customer_id, :order_no, :order_date, :total_amount, 'EUR', :status)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':customer_id'  => $customerId,
            ':order_no'     => $orderNo,
            ':order_date'   => $data['order_date'],
            ':total_amount' => $amount,
            ':status'       => $data['status'],
        ]);

        // zurück zur Kunden-Detailseite
        header('Location: customer.php?id=' . $customerId);
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Neue Bestellung – <?= htmlspecialchars($customer['company']) ?></title>
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
            Neue Bestellung für: <?= htmlspecialchars($customer['company']) ?>
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
                        <label class="form-label">Bestelldatum</label>
                        <input type="date" name="order_date" class="form-control"
                               value="<?= htmlspecialchars($data['order_date']) ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Betrag (in €)</label>
                        <input type="text" name="total_amount" class="form-control"
                               placeholder="z.B. 199,90"
                               value="<?= htmlspecialchars($data['total_amount']) ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <?php
                            $statuses = ['offen','bezahlt','storniert'];
                            foreach ($statuses as $s):
                            ?>
                                <option value="<?= $s ?>" <?= $s === $data['status'] ? 'selected' : '' ?>>
                                    <?= $s ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">Bestellung speichern</button>
                <a href="customer.php?id=<?= $customerId ?>" class="btn btn-secondary">Abbrechen</a>
            </form>
        </div>
    </div>
</div>
</body>
</html>
