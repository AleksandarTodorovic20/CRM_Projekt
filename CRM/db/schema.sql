-- WICHTIG: Keine CREATE DATABASE / USE Befehle hier!

DROP TABLE IF EXISTS contacts;
DROP TABLE IF EXISTS orders;
DROP TABLE IF EXISTS customers;
DROP TABLE IF EXISTS users;

-- Benutzer (für Login / Kontakte.user_id)
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('CHEF','ANGESTELLTER') DEFAULT 'ANGESTELLTER'
);

-- Kunden
CREATE TABLE customers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_no VARCHAR(20) NOT NULL UNIQUE,
    company VARCHAR(100) NOT NULL,
    salutation VARCHAR(10),
    first_name VARCHAR(50),
    last_name VARCHAR(50),
    street VARCHAR(100),
    zip VARCHAR(10),
    city VARCHAR(100),
    country VARCHAR(50) DEFAULT 'Österreich',
    phone VARCHAR(30),
    email VARCHAR(100),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Bestellungen
CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    order_no VARCHAR(30) NOT NULL,
    order_date DATE NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    currency CHAR(3) DEFAULT 'EUR',
    status ENUM('offen','bezahlt','storniert') DEFAULT 'offen',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_orders_customer
      FOREIGN KEY (customer_id) REFERENCES customers(id)
      ON DELETE CASCADE
);

-- Kontakte
CREATE TABLE contacts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    user_id INT NOT NULL,
    contact_date DATETIME NOT NULL,
    contact_type ENUM('Telefon','E-Mail','Meeting','Online') NOT NULL,
    subject VARCHAR(150) NOT NULL,
    notes TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_contacts_customer
      FOREIGN KEY (customer_id) REFERENCES customers(id)
      ON DELETE CASCADE,
    CONSTRAINT fk_contacts_user
      FOREIGN KEY (user_id) REFERENCES users(id)
      ON DELETE CASCADE
);
