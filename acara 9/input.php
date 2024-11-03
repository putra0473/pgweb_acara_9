<?php
// Menampilkan kesalahan jika ada
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Mengambil data dari form dengan pemeriksaan
$Kecamatan = isset($_POST['kecamatan']) ? $_POST['kecamatan'] : null;
$Luas = isset($_POST['luas']) ? $_POST['luas'] : null;
$Jumlah_Penduduk = isset($_POST['jumlah_penduduk']) ? $_POST['jumlah_penduduk'] : null;
$Longitude = isset($_POST['longitude']) ? $_POST['longitude'] : null;
$Latitude = isset($_POST['latitude']) ? $_POST['latitude'] : null;

// Memeriksa apakah semua field telah diisi
if ($Kecamatan === null || $Luas === null || $Jumlah_Penduduk === null || $Longitude === null || $Latitude === null) {
    echo "<script>alert('Semua field harus diisi.'); window.history.back();</script>";
    exit();
}

// Konfigurasi koneksi database
$servername = "localhost";
$username = "root"; // Default username untuk XAMPP
$password = ""; // Default password kosong untuk XAMPP
$dbname = "acara 8 pgweb"; // Nama database tanpa spasi

// Membuat koneksi
$conn = new mysqli($servername, $username, $password, $dbname);

// Memeriksa koneksi
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Menyiapkan pernyataan untuk menghindari SQL Injection
$stmt = $conn->prepare("INSERT INTO penduduk (Kecamatan, Luas, Jumlah_Penduduk, Longitude, Latitude) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("sdidd", $Kecamatan, $Luas, $Jumlah_Penduduk, $Longitude, $Latitude);

// Menjalankan pernyataan
if ($stmt->execute()) {
    echo "Data baru berhasil ditambahkan";
} else {
    echo "Error: " . $stmt->error;
}

// Menutup pernyataan dan koneksi
$stmt->close();
$conn->close();
?>
