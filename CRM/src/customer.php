<?php
require_once 'config.php';
requireLogin();

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    header('Location: index.php?section=customers');
    exit;
}

/* Kunde laden */
$stmt = $pdo->prepare("SELECT * FROM customers WHERE id = :id");
$stmt->execute([':id' => $id]);
$customer = $stmt->fetch();

if (!$customer) {
    die("Kunde nicht gefunden.");
}

/* Umsatz gesamt */
$stmt = $pdo->prepare("SELECT SUM(total_amount) AS sum_all FROM orders WHERE customer_id = :id AND status <> 'storniert'");
$stmt->execute([':id' => $id]);
$sumAll = (float)$stmt->fetch()['sum_all'];

/* Umsatz letztes Jahr */
$yearLast = date('Y') - 1;
$stmt = $pdo->prepare(
    "SELECT SUM(total_amount) AS sum_year
     FROM orders
     WHERE customer_id = :id
       AND status <> 'storniert'
       AND YEAR(order_date) = :y"
);
$stmt->execute([':id' => $id, ':y' => $yearLast]);
$sumYear = (float)$stmt->fetch()['sum_year'];

/* Filter-Zeitraum */
$from = $_GET['from'] ?? '';
$to   = $_GET['to'] ?? '';
$errors = [];

if ($from && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $from)) {
    $errors[] = 'Von-Datum muss im Format JJJJ-MM-TT sein.';
}
if ($to && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $to)) {
    $errors[] = 'Bis-Datum muss im Format JJJJ-MM-TT sein.';
}

/* Umsatz im Zeitraum */
$sumRange = null;
if (!$errors && ($from || $to)) {
    $conds = ["customer_id = :id", "status <> 'storniert'"];
    $params = [':id' => $id];

    if ($from) {
        $conds[] = "order_date >= :from";
        $params[':from'] = $from;
    }
    if ($to) {
        $conds[] = "order_date <= :to";
        $params[':to'] = $to;
    }

    $sql = "SELECT SUM(total_amount) AS s FROM orders WHERE " . implode(' AND ', $conds);
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $sumRange = (float)$stmt->fetch()['s'];
}

/* letzte Bestellungen (max 10) */
$stmt = $pdo->prepare(
    "SELECT * FROM orders
     WHERE customer_id = :id
     ORDER BY order_date DESC
     LIMIT 10"
);
$stmt->execute([':id' => $id]);
$orders = $stmt->fetchAll();

/* letzte Kontakte (max 10) */
$stmt = $pdo->prepare(
    "SELECT ct.*, u.username
     FROM contacts ct
     JOIN users u ON u.id = ct.user_id
     WHERE ct.customer_id = :id
     ORDER BY ct.contact_date DESC
     LIMIT 10"
);
$stmt->execute([':id' => $id]);
$contacts = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Kunde – Detail</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<nav class="navbar navbar-dark bg-dark navbar-expand-lg">
    <div class="container-fluid">
        <a class="navbar-brand" href="index.php">Mini CRM</a>
        <div class="d-flex">
            <a class="btn btn-outline-light btn-sm" href="index.php?section=customers">Zurück zur Liste</a>
        </div>
    </div>
</nav>

<div class="container mt-3">
    <div class="row">
        <div class="col-md-6">
            <div class="card mb-3">
                <div class="card-header">Kundendaten</div>
                <div class="card-body">
                    <h5 class="card-title"><?= htmlspecialchars($customer['company']) ?></h5>
                    <p class="card-text">
                        <?= htmlspecialchars($customer['salutation'].' '.$customer['first_name'].' '.$customer['last_name']) ?><br>
                        <?= htmlspecialchars($customer['street']) ?><br>
                        <?= htmlspecialchars($customer['zip'].' '.$customer['city']) ?><br>
                        <?= htmlspecialchars($customer['country']) ?><br>
                        Tel: <?= htmlspecialchars($customer['phone']) ?><br>
                        E-Mail: <?= htmlspecialchars($customer['email']) ?><br>
                        Kundennr.: <?= htmlspecialchars($customer['customer_no']) ?>
                    </p>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card mb-3">
                <div class="card-header">Umsatzübersicht</div>
                <div class="card-body">
                    <p>Umsatz gesamt: <strong><?= formatMoney($sumAll) ?></strong></p>
                    <p>Umsatz Jahr <?= $yearLast ?>: <strong><?= formatMoney($sumYear) ?></strong></p>

                    <hr>
                    <form class="row g-2" method="get">
                        <input type="hidden" name="id" value="<?= $id ?>">
                        <div class="col-12"><strong>Umsatz nach Datumsbereich</strong></div>
                        <div class="col-md-5">
                            <label class="form-label">Von (JJJJ-MM-TT)</label>
                            <input type="date" name="from" class="form-control" value="<?= htmlspecialchars($from) ?>">
                        </div>
                        <div class="col-md-5">
                            <label class="form-label">Bis (JJJJ-MM-TT)</label>
                            <input type="date" name="to" class="form-control" value="<?= htmlspecialchars($to) ?>">
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button class="btn btn-secondary w-100" type="submit">Berechnen</button>
                        </div>
                    </form>
                    <?php if ($errors): ?>
                        <div class="alert alert-danger mt-2">
                            <?php foreach ($errors as $e) echo htmlspecialchars($e)."<br>"; ?>
                        </div>
                    <?php elseif ($sumRange !== null): ?>
                        <div class="alert alert-info mt-2">
                            Umsatz im Zeitraum:
                            <strong><?= formatMoney($sumRange) ?></strong>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Letzte Bestellungen -->
        <div class="col-md-6">
            <div class="card mb-3">
    <div class="card-header d-flex justify-content-between align-items-center">
    <span>Letzte Bestellungen</span>

    <div class="d-flex gap-2">
        <a href="export_orders.php?customer_id=<?= $id ?>" class="btn btn-sm btn-secondary">
            CSV Export
        </a>
        <a href="order_new.php?customer_id=<?= $id ?>" class="btn btn-sm btn-primary">
            Neue Bestellung
        </a>
    </div>
</div>

    <div class="card-body table-responsive">
        ...

                    <table class="table table-sm table-hover">
                        <thead>
                        <tr>
                            <th>Datum</th>
                            <th>Bestellnr.</th>
                            <th>Betrag</th>
                            <th>Status</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($orders as $o): ?>
                            <tr>
                                <td><?= formatDate($o['order_date']) ?></td>
                                <td><?= htmlspecialchars($o['order_no']) ?></td>
                                <td><?= formatMoney($o['total_amount']) ?></td>
                                <td><?= htmlspecialchars($o['status']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (!$orders): ?>
                            <tr><td colspan="4">Keine Bestellungen vorhanden.</td></tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Letzte Kontakte -->
        <div class="col-md-6">
            <div class="card mb-3">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span>Letzte Kontakte</span>
        <a href="contact_new.php?customer_id=<?= $id ?>" class="btn btn-sm btn-primary">
            Neuer Kontakt
        </a>
    </div>
    <div class="card-body table-responsive">
        ...

                    <table class="table table-sm table-hover">
                        <thead>
                        <tr>
                            <th>Datum</th>
                            <th>Art</th>
                            <th>Betreuer</th>
                            <th>Betreff</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($contacts as $ct): ?>
                            <tr>
                                <td><?= formatDateTime($ct['contact_date']) ?></td>
                                <td><?= htmlspecialchars($ct['contact_type']) ?></td>
                                <td><?= htmlspecialchars($ct['username']) ?></td>
                                <td><?= htmlspecialchars($ct['subject']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (!$contacts): ?>
                            <tr><td colspan="4">Keine Kontakte vorhanden.</td></tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
