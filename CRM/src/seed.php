<?php
// seed.php
require_once 'config.php';

echo "<pre>Seeding...\n";

$pdo->exec("DELETE FROM contacts");
$pdo->exec("DELETE FROM orders");
$pdo->exec("DELETE FROM customers");
$pdo->exec("DELETE FROM users");

echo "Tabellen geleert.\n";

/* Benutzer */
$users = [
    ['chef',   password_hash('chef123',   PASSWORD_DEFAULT), 'CHEF'],
    ['max',    password_hash('pass123',   PASSWORD_DEFAULT), 'ANGESTELLTER'],
    ['sabine', password_hash('pass123',   PASSWORD_DEFAULT), 'ANGESTELLTER'],
];

$stmtUser = $pdo->prepare("INSERT INTO users (username, password_hash, role) VALUES (?,?,?)");
foreach ($users as $u) {
    $stmtUser->execute($u);
}
echo "Benutzer eingefügt.\n";

$userIds = $pdo->query("SELECT id FROM users")->fetchAll(PDO::FETCH_COLUMN);

/* Kunden (mind. 10) */
$customers = [
    ['K-1001','Muster GmbH','Herr','Max','Mustermann','Musterstraße 1','1010','Wien','Österreich','+43111111','office@muster.at'],
    ['K-1002','Beispiel AG','Frau','Anna','Beispiel','Beispielgasse 5','1020','Wien','Österreich','+43122222','anna@example.com'],
    ['K-1003','Alpha KG','Herr','Markus','Alpha','Allee 3','1030','Wien','Österreich','+43133333','alpha@firma.at'],
    ['K-1004','Beta GmbH','Frau','Lisa','Beta','Betastraße 7','1040','Wien','Österreich','+43144444','lisa@beta.at'],
    ['K-1005','Gamma GmbH','Herr','Thomas','Gamma','Gammastraße 2','1050','Wien','Österreich','+43155555','thomas@gamma.at'],
    ['K-1006','Delta OG','Frau','Julia','Delta','Deltaplatz 9','1060','Wien','Österreich','+43166666','julia@delta.at'],
    ['K-1007','Epsilon e.U.','Herr','Peter','Epsilon','Epsweg 11','1070','Wien','Österreich','+43177777','peter@epsilon.at'],
    ['K-1008','Zeta GmbH','Frau','Katja','Zeta','Zetastraße 4','1080','Wien','Österreich','+43188888','katja@zeta.at'],
    ['K-1009','Eta KG','Herr','Jonas','Eta','Etaring 6','1090','Wien','Österreich','+43199999','jonas@eta.at'],
    ['K-1010','Theta AG','Frau','Mara','Theta','Thetaring 8','1100','Wien','Österreich','+43112345','mara@theta.at'],
];

$stmtCust = $pdo->prepare(
    "INSERT INTO customers (customer_no, company, salutation, first_name, last_name, street, zip, city, country, phone, email)
     VALUES (?,?,?,?,?,?,?,?,?,?,?)"
);

foreach ($customers as $c) {
    $stmtCust->execute($c);
}

$customerIds = $pdo->query("SELECT id FROM customers")->fetchAll(PDO::FETCH_COLUMN);
echo "Kunden eingefügt: ".count($customerIds)."\n";

/* Bestellungen (>= 50) */
$stmtOrder = $pdo->prepare(
    "INSERT INTO orders (customer_id, order_no, order_date, total_amount, status)
     VALUES (:customer_id, :order_no, :order_date, :total_amount, :status)"
);

$statuses = ['offen','bezahlt','bezahlt','bezahlt','storniert'];

for ($i = 1; $i <= 50; $i++) {
    $customerId = $customerIds[array_rand($customerIds)];

    // zufälliges Datum in den letzten 3 Jahren
    $timestamp = strtotime('-'.rand(0, 1000).' days');
    $orderDate = date('Y-m-d', $timestamp);
    $amount    = rand(50, 1500) + rand(0, 99)/100;
    $status    = $statuses[array_rand($statuses)];

    $stmtOrder->execute([
        ':customer_id'  => $customerId,
        ':order_no'     => 'ORD-' . str_pad((string)$i, 4, '0', STR_PAD_LEFT),
        ':order_date'   => $orderDate,
        ':total_amount' => $amount,
        ':status'       => $status,
    ]);
}
echo "Bestellungen eingefügt: 50\n";

/* Kontakte (>= 50) */
$stmtContact = $pdo->prepare(
    "INSERT INTO contacts (customer_id, user_id, contact_date, contact_type, subject, notes)
     VALUES (:customer_id, :user_id, :contact_date, :contact_type, :subject, :notes)"
);

$contactTypes = ['Telefon','E-Mail','Meeting','Online'];
$subjects     = ['Angebot besprochen','Reklamation','Supportfall','Neues Projekt','Nachfassgespräch'];

for ($i = 1; $i <= 50; $i++) {
    $customerId = $customerIds[array_rand($customerIds)];
    $userId     = $userIds[array_rand($userIds)];
    $timestamp  = strtotime('-'.rand(0, 700).' days');
    $date       = date('Y-m-d H:i:s', $timestamp);

    $type    = $contactTypes[array_rand($contactTypes)];
    $subject = $subjects[array_rand($subjects)];

    $stmtContact->execute([
        ':customer_id'  => $customerId,
        ':user_id'      => $userId,
        ':contact_date' => $date,
        ':contact_type' => $type,
        ':subject'      => $subject,
        ':notes'        => 'Automatisch generierter Beispielkontakt #'.$i,
    ]);
}

echo "Kontakte eingefügt: 50\n";
echo "Fertig.\n";
