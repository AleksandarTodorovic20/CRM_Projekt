<?php
require_once 'config.php';
requireLogin();

$section = $_GET['section'] ?? 'customers';

$page     = max(1, (int)($_GET['page'] ?? 1));
$pageSize = 10;
$offset   = ($page - 1) * $pageSize;

$search   = trim($_GET['search'] ?? '');
$type     = trim($_GET['type'] ?? ''); // für Kontakte

?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>CRM – Start</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<nav class="navbar navbar-dark bg-dark navbar-expand-lg">
    <div class="container-fluid">
        <a class="navbar-brand" href="index.php">Mini CRM</a>
        <div class="d-flex">
            <span class="navbar-text me-3">
                Eingeloggt als <?= htmlspecialchars(currentUser()['username']) ?>
            </span>
            <a class="btn btn-outline-light btn-sm" href="logout.php">Logout</a>
        </div>
    </div>
</nav>

<div class="container mt-3">
    <ul class="nav nav-tabs">
        <li class="nav-item">
            <a class="nav-link <?= $section === 'customers' ? 'active' : '' ?>" href="?section=customers">Kunden</a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= $section === 'orders' ? 'active' : '' ?>" href="?section=orders">Bestellungen</a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= $section === 'contacts' ? 'active' : '' ?>" href="?section=contacts">Kontakte</a>
        </li>
    </ul>

    <div class="card mt-3">
        <div class="card-body">
            <?php if ($section === 'customers'): ?>

                <h5 class="d-flex justify-content-between align-items-center">
    <span>Kunden (Suche)</span>
    <a href="customer_new.php" class="btn btn-sm btn-primary">Neuen Kunden anlegen</a>
</h5>

                <form class="row g-2 mb-3" method="get">
                    <input type="hidden" name="section" value="customers">
                    <div class="col-sm-4">
                        <input type="text" name="search" class="form-control"
                               placeholder="Name, Firma, Kundennr."
                               value="<?= htmlspecialchars($search) ?>">
                    </div>
                    <div class="col-sm-auto">
                        <button class="btn btn-secondary" type="submit">Suchen</button>
                    </div>
                    <div class="col-sm-auto">
                        <a class="btn btn-outline-secondary" href="?section=customers">Reset</a>
                    </div>
                </form>
                <?php
                // Daten holen
                $params = [];
                $where  = '';

                if ($search !== '') {
                    $where = "WHERE company LIKE :s OR first_name LIKE :s OR last_name LIKE :s OR customer_no LIKE :s";
                    $params[':s'] = "%$search%";
                }

                $sqlCount = "SELECT COUNT(*) AS c FROM customers $where";
                $stmt     = $pdo->prepare($sqlCount);
                $stmt->execute($params);
                $totalRows = (int)$stmt->fetch()['c'];

                $sql = "SELECT * FROM customers $where ORDER BY company ASC LIMIT :limit OFFSET :offset";
                $stmt = $pdo->prepare($sql);
                foreach ($params as $k => $v) {
                    $stmt->bindValue($k, $v, PDO::PARAM_STR);
                }
                $stmt->bindValue(':limit', $pageSize, PDO::PARAM_INT);
                $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
                $stmt->execute();
                $customers = $stmt->fetchAll();
                ?>
                <div class="table-responsive">
                    <table class="table table-sm table-hover">
                        <thead>
                        <tr>
                            <th>Kundennr.</th>
                            <th>Firma</th>
                            <th>Name</th>
                            <th>Ort</th>
                            <th>Telefon</th>
                            <th>E-Mail</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($customers as $c): ?>
                            <tr onclick="window.location='customer.php?id=<?= (int)$c['id'] ?>' "
                                style="cursor:pointer">
                                <td><?= htmlspecialchars($c['customer_no']) ?></td>
                                <td><?= htmlspecialchars($c['company']) ?></td>
                                <td><?= htmlspecialchars(trim($c['salutation'] . ' ' . $c['first_name'] . ' ' . $c['last_name'])) ?></td>
                                <td><?= htmlspecialchars($c['zip'] . ' ' . $c['city']) ?></td>
                                <td><?= htmlspecialchars($c['phone']) ?></td>
                                <td><?= htmlspecialchars($c['email']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php
                $totalPages = max(1, (int)ceil($totalRows / $pageSize));
                ?>
                <nav>
                    <ul class="pagination pagination-sm">
                        <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                            <li class="page-item <?= $p === $page ? 'active' : '' ?>">
                                <a class="page-link"
                                   href="?section=customers&page=<?= $p ?>&search=<?= urlencode($search) ?>"><?= $p ?></a>
                            </li>
                        <?php endfor; ?>
                    </ul>
                </nav>

            <?php elseif ($section === 'orders'): ?>

                <h5>Bestellungen (global, chronologisch ↓, Suche)</h5>
                <form class="row g-2 mb-3" method="get">
                    <input type="hidden" name="section" value="orders">
                    <div class="col-sm-4">
                        <input type="text" name="search" class="form-control"
                               placeholder="Firma, Kundennr., Bestellnr."
                               value="<?= htmlspecialchars($search) ?>">
                    </div>
                    <div class="col-sm-auto">
                        <button class="btn btn-secondary" type="submit">Suchen</button>
                    </div>
                    <div class="col-sm-auto">
                        <a class="btn btn-outline-secondary" href="?section=orders">Reset</a>
                    </div>
                </form>
                <?php
                $params = [];
                $where = '';

                if ($search !== '') {
                    $where = "WHERE c.company LIKE :s OR c.customer_no LIKE :s OR o.order_no LIKE :s";
                    $params[':s'] = "%$search%";
                }

                $sqlCount = "SELECT COUNT(*) AS c
                             FROM orders o
                             JOIN customers c ON c.id = o.customer_id
                             $where";
                $stmt = $pdo->prepare($sqlCount);
                $stmt->execute($params);
                $totalRows = (int)$stmt->fetch()['c'];

                $sql = "SELECT o.*, c.company, c.customer_no
                        FROM orders o
                        JOIN customers c ON c.id = o.customer_id
                        $where
                        ORDER BY o.order_date DESC
                        LIMIT :limit OFFSET :offset";
                $stmt = $pdo->prepare($sql);
                foreach ($params as $k => $v) {
                    $stmt->bindValue($k, $v, PDO::PARAM_STR);
                }
                $stmt->bindValue(':limit', $pageSize, PDO::PARAM_INT);
                $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
                $stmt->execute();
                $orders = $stmt->fetchAll();

                $totalPages = max(1, (int)ceil($totalRows / $pageSize));
                ?>
                <div class="table-responsive">
                    <table class="table table-sm table-hover">
                        <thead>
                        <tr>
                            <th>Datum</th>
                            <th>Bestellnr.</th>
                            <th>Kundennr.</th>
                            <th>Firma</th>
                            <th>Gesamt</th>
                            <th>Status</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($orders as $o): ?>
                            <tr>
                                <td><?= formatDate($o['order_date']) ?></td>
                                <td><?= htmlspecialchars($o['order_no']) ?></td>
                                <td><?= htmlspecialchars($o['customer_no']) ?></td>
                                <td><?= htmlspecialchars($o['company']) ?></td>
                                <td><?= formatMoney($o['total_amount']) ?></td>
                                <td><?= htmlspecialchars($o['status']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <nav>
                    <ul class="pagination pagination-sm">
                        <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                            <li class="page-item <?= $p === $page ? 'active' : '' ?>">
                                <a class="page-link"
                                   href="?section=orders&page=<?= $p ?>&search=<?= urlencode($search) ?>"><?= $p ?></a>
                            </li>
                        <?php endfor; ?>
                    </ul>
                </nav>

            <?php elseif ($section === 'contacts'): ?>

                <h5>Kontakte (global, chronologisch ↓, Filter nach Art)</h5>
                <form class="row g-2 mb-3" method="get">
                    <input type="hidden" name="section" value="contacts">
                    <div class="col-sm-3">
                        <input type="text" name="search" class="form-control"
                               placeholder="Firma, Betreff"
                               value="<?= htmlspecialchars($search) ?>">
                    </div>
                    <div class="col-sm-3">
                        <select name="type" class="form-select">
                            <option value="">Alle Kontaktarten</option>
                            <?php
                            $types = ['Telefon','E-Mail','Meeting','Online'];
                            foreach ($types as $t): ?>
                                <option value="<?= $t ?>" <?= $t === $type ? 'selected' : '' ?>><?= $t ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-sm-auto">
                        <button class="btn btn-secondary" type="submit">Filtern</button>
                    </div>
                    <div class="col-sm-auto">
                        <a class="btn btn-outline-secondary" href="?section=contacts">Reset</a>
                    </div>
                </form>
                <?php
                $params = [];
                $whereParts = [];

                if ($search !== '') {
                    $whereParts[] = "(c.company LIKE :s OR ct.subject LIKE :s)";
                    $params[':s'] = "%$search%";
                }
                if ($type !== '') {
                    $whereParts[] = "ct.contact_type = :type";
                    $params[':type'] = $type;
                }

                $where = '';
                if ($whereParts) {
                    $where = 'WHERE ' . implode(' AND ', $whereParts);
                }

                $sqlCount = "SELECT COUNT(*) AS c
                             FROM contacts ct
                             JOIN customers c ON c.id = ct.customer_id
                             JOIN users u ON u.id = ct.user_id
                             $where";
                $stmt = $pdo->prepare($sqlCount);
                $stmt->execute($params);
                $totalRows = (int)$stmt->fetch()['c'];

                $sql = "SELECT ct.*, c.company, u.username
                        FROM contacts ct
                        JOIN customers c ON c.id = ct.customer_id
                        JOIN users u ON u.id = ct.user_id
                        $where
                        ORDER BY ct.contact_date DESC
                        LIMIT :limit OFFSET :offset";
                $stmt = $pdo->prepare($sql);
                foreach ($params as $k => $v) {
                    $stmt->bindValue($k, $v, PDO::PARAM_STR);
                }
                $stmt->bindValue(':limit', $pageSize, PDO::PARAM_INT);
                $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
                $stmt->execute();
                $contacts = $stmt->fetchAll();

                $totalPages = max(1, (int)ceil($totalRows / $pageSize));
                ?>
                <div class="table-responsive">
                    <table class="table table-sm table-hover">
                        <thead>
                        <tr>
                            <th>Datum</th>
                            <th>Art</th>
                            <th>Firma</th>
                            <th>Betreuer</th>
                            <th>Betreff</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($contacts as $ct): ?>
                            <tr>
                                <td><?= formatDateTime($ct['contact_date']) ?></td>
                                <td><?= htmlspecialchars($ct['contact_type']) ?></td>
                                <td><?= htmlspecialchars($ct['company']) ?></td>
                                <td><?= htmlspecialchars($ct['username']) ?></td>
                                <td><?= htmlspecialchars($ct['subject']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <nav>
                    <ul class="pagination pagination-sm">
                        <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                            <li class="page-item <?= $p === $page ? 'active' : '' ?>">
                                <a class="page-link"
                                   href="?section=contacts&page=<?= $p ?>&search=<?= urlencode($search) ?>&type=<?= urlencode($type) ?>"><?= $p ?></a>
                            </li>
                        <?php endfor; ?>
                    </ul>
                </nav>

            <?php endif; ?>
        </div>
    </div>
</div>
</body>
</html>
