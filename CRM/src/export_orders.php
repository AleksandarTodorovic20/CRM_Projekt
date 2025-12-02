<?php
require_once 'config.php';
requireLogin();

$customerId = (int)($_GET['customer_id'] ?? 0);
if ($customerId <= 0) {
    die("Ungültige Kunden-ID.");
}

// Kundendaten laden (für Dateiname)
$stmt = $pdo->prepare("SELECT company, customer_no FROM customers WHERE id = :id");
$stmt->execute([':id' => $customerId]);
$customer = $stmt->fetch();

if (!$customer) {
    die("Kunde nicht gefunden.");
}

// Bestellungen laden
$stmt = $pdo->prepare("SELECT order_date, order_no, total_amount, status 
                       FROM orders
                       WHERE customer_id = :id
                       ORDER BY order_date DESC");
$stmt->execute([':id' => $customerId]);
$orders = $stmt->fetchAll();


// CSV-Header setzen (Download)
$filename = "Bestellungen_" . $customer['customer_no'] . "_" . date("Ymd") . ".csv";

header("Content-Type: text/csv; charset=utf-8");
header("Content-Disposition: attachment; filename=\"$filename\"");

// Output stream öffnen
$output = fopen("php://output", "w");

// UTF-8 BOM (verhindert Umlaute-Probleme in Excel)
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// Kopfzeile schreiben
fputcsv($output, ["Datum", "Bestellnummer", "Betrag (€)", "Status"], ";");

// Zeilen ausgeben
foreach ($orders as $o) {
    fputcsv($output, [
        date("d.m.Y", strtotime($o['order_date'])),
        $o['order_no'],
        number_format($o['total_amount'], 2, ',', '.'),
        $o['status']
    ], ";");
}

fclose($output);
exit;
