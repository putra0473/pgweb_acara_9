<?php
// Aktifkan error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Koneksi ke MySQL
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "acara 8 pgweb"; // Ubah nama database - hindari spasi

try {
    // Membuat koneksi
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Memeriksa koneksi
    if ($conn->connect_error) {
        throw new Exception("Koneksi gagal: " . $conn->connect_error);
    }

    // Query untuk mengambil data dari tabel 'penduduk'
    $sql = "SELECT * FROM penduduk";
    $result = $conn->query($sql);

    // Array untuk menyimpan data marker
    $markers = [];

    if ($result && $result->num_rows > 0) {
        // Menyimpan data dalam array untuk digunakan di peta
        while ($row = $result->fetch_assoc()) {
            $markers[] = [
                "kecamatan" => $row["Kecamatan"],
                "longitude" => (float)$row["Longitude"],
                "latitude" => (float)$row["Latitude"]
            ];
        }
    } else {
        echo "Tidak ada data ditemukan.";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
    exit();
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Peta Leaflet dengan Tabel Mengambang</title>

    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
        integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    
    <style>
        body {
            margin: 60px;
            padding: 0px;
            background-color: #ADD8E6;
        }
        
        .header {
            text-align: center;
            background-color: #ADD8E6; /* Warna biru muda */
            padding: 30px ;
            margin-bottom: 20px;
        }

        .header h1, .header h2 {
            margin: 10px 0;
        }

        .logo-container {
            text-align: center;
            margin: 20px 0;
        }

        .logo {
            max-width: 300px; /* Ukuran maksimal logo */
            height: auto;
        }
        /* Gaya untuk peta */
        #map {
            width: 100%;
            height: calc(100vh - 300px); /* Kurangi tinggi header */
            margin-top: 20px;
        }
        
        /* Gaya untuk tombol toggle */
        .toggle-button {
            position: absolute;
            top: 530px; /* Sesuaikan dengan tinggi header */
            right: 60px;
            z-index: 1000;
            padding: 10px 100px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 10px;
            cursor: pointer;
        }

        .toggle-button:hover {
            background-color: #45a049;
        }

        /* Gaya untuk tabel mengambang */
        .floating-table {
            position: absolute;
            top: 570px;
            right: 60px;
            width: 800px;
            background-color: rgba(255, 255, 255, 0.95);
            border: 2px solid #ccc;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            max-height: 400px;
            overflow-y: auto;
            z-index: 1000;
            display: none; /* Tampilkan tabel secara default */
            display: block; /* Sembunyikan tabel secara default */
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 8px;
            text-align: left;
            border-bottom: 1px solid #ddd;
            font-size: 14px;
        }

        th {
            background-color: #f2f2f2;
            position: sticky;
            top: 0;
        }

        tr:hover {
            background-color: #f5f5f5;
        }
    </style>
</head>

<body>
    <div class="header">
        <div class="logo-container">
    <img src="ugmlogo.jpg" alt="Logo" class="logo"> <!-- Ganti dengan path logo Anda -->
    </div>
    <h1>WEB GIS</h1> <!-- Judul halaman ditambahkan -->
    <h2>KABUPATEN SLEMAN</h2>
    </div>
    

<!-- Tombol Buka-Tutup Tabel -->
<button class="toggle-button" onclick="toggleTable()">Tampilkan Tabel</button>


    <!-- Peta -->
    <div id="map"></div>

    <!-- Tabel mengambang -->
    <div class="floating-table">
        <table>
            <thead>
                <tr>
                    <th>Kecamatan</th>
                    <th>Longitude</th>
                    <th>Latitude</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($markers)): ?>
                    <?php foreach ($markers as $marker): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($marker['kecamatan']); ?></td>
                            <td><?php echo htmlspecialchars($marker['longitude']); ?></td>
                            <td><?php echo htmlspecialchars($marker['latitude']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="3">Tidak ada data tersedia</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

    <script>
        // Fungsi untuk toggle tabel
        function toggleTable() {
            var table = document.getElementById('floatingTable');
            var button = document.querySelector('.toggle-button');
            if (table.style.display === 'none' || table.style.display === '') {
                table.style.display = 'block';
                button.textContent = 'Sembunyikan Tabel';
            } else {
                table.style.display = 'none';
                button.textContent = 'Tampilkan Tabel';
            }
        }

        // Inisialisasi peta
        var map = L.map('map');

        // Tambahkan tile layer OpenStreetMap
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);

        // Data marker dari PHP
        var markers = <?php echo json_encode($markers, JSON_NUMERIC_CHECK); ?>;

        // Jika tidak ada marker, set view ke Indonesia
        if (markers.length === 0) {
            map.setView([-2.5489, 118.0149], 5); // Koordinat default Indonesia
        } else {
            // Membuat bounds peta
            var bounds = L.latLngBounds();

            // Menambahkan marker dan memperluas bounds peta
            markers.forEach(function(marker) {
                var latLng = [marker.latitude, marker.longitude];
                L.marker(latLng)
                    .addTo(map)
                    .bindPopup("<b>Kecamatan " + marker.kecamatan + "</b><br>" +
                              "Latitude: " + marker.latitude + "<br>" +
                              "Longitude: " + marker.longitude);
                bounds.extend(latLng);
            });

            // Menyesuaikan peta agar sesuai dengan semua marker
            map.fitBounds(bounds);
        }
    </script>
</body>

</html>