<?php
// functions.php

function formatDate($dateTime): string {
    if (!$dateTime) return '';
    return date('d.m.Y', strtotime($dateTime));
}

function formatDateTime($dateTime): string {
    if (!$dateTime) return '';
    return date('d.m.Y H:i', strtotime($dateTime));
}

function formatMoney($value): string {
    return number_format((float)$value, 2, ',', '.') . ' €';
}

function currentUser(): ?array {
    return $_SESSION['user'] ?? null;
}

function requireLogin(): void {
    if (!currentUser()) {
        header('Location: login.php');
        exit;
    }
}
