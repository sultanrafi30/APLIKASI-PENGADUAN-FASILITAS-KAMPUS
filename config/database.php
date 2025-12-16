<?php
// config/database.php

// 🔐 Konfigurasi Database (SESUAIKAN DENGAN SETTINGAN XAMPP ANDA!)
$host = 'localhost';
$username = 'root';
$password = ''; // default XAMPP: kosong
$database = 'polmed_pengaduan';

// 🧩 Buat koneksi
$koneksi = new mysqli($host, $username, $password, $database);

// ❌ Cek koneksi gagal
if ($koneksi->connect_error) {
    die("<div style='padding:20px; background:#f8d7da; color:#721c24; border:1px solid #f5c6cb;'>
        ❌ Koneksi database gagal: " . $koneksi->connect_error . "<br>
        ⚠️ Pastikan: <br>
        - MySQL sudah dijalankan di XAMPP <br>
        - Database <b>polmed_pengaduan</b> sudah dibuat <br>
        - Username/password benar
        </div>");
}

// ✅ Aktifkan charset UTF-8 (hindari error karakter)
$koneksi->set_charset("utf8");

// 🔒 Nonaktifkan error reporting di production (opsional)
// error_reporting(0);
// ini_set('display_errors', 0);
?>