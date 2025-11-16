<?php
session_start();

if (!isset($_SESSION['contacts'])) {
    $_SESSION['contacts'] = [];
}

function validateContact($data) {
    $errors = [];
    $validated_data = [];

    if (empty($data["nama"])) {
        $errors[] = "Nama harus diisi.";
    } else {
        $validated_data['nama'] = trim($data["nama"]);
        if (!preg_match("/^[a-zA-Z\s]+$/", $validated_data['nama'])) {
            $errors[] = "Nama hanya boleh mengandung huruf dan spasi.";
        }
    }

    if (empty($data["telepon"])) {
        $errors[] = "Nomor Telepon harus diisi.";
    } else {
        $validated_data['telepon'] = trim($data["telepon"]);
        if (!preg_match("/^[0-9+\s]+$/", $validated_data['telepon'])) {
            $errors[] = "Nomor Telepon tidak valid.";
        }
    }

    if (empty($data["email"])) {
        $errors[] = "Email harus diisi.";
    } else {
        $validated_data['email'] = trim($data["email"]);
        if (!filter_var($validated_data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Format email tidak valid.";
        }
    }

    $validated_data['catatan'] = htmlspecialchars($data['catatan'] ?? '');

    return ['errors' => $errors, 'data' => $validated_data];
}


function addContact($data) {
    $id = uniqid();
    $_SESSION['contacts'][$id] = $data;
}

function getContacts() {
    return $_SESSION['contacts'];
}

function updateContact($id, $data) {
    if (isset($_SESSION['contacts'][$id])) {
        $_SESSION['contacts'][$id] = $data;
        return true;
    }
    return false;
}

function deleteContact($id) {
    if (isset($_SESSION['contacts'][$id])) {
        unset($_SESSION['contacts'][$id]);
        return true;
    }
    return false;
}

function getContactById($id) {
    return $_SESSION['contacts'][$id] ?? null;
}