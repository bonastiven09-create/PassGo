<?php
// ===== Pengaturan database (default XAMPP / Laragon) =====
$DB = ['host' => 'localhost', 'user' => 'root', 'pass' => '', 'nama' => 'dw_tiket'];
const JAM  = ['10:00', '13:00', '16:00', '19:00', '21:30'];
const HARI = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];

try {
    $pdo = new PDO("mysql:host={$DB['host']};charset=utf8mb4", $DB['user'], $DB['pass'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]);
} catch (PDOException $e) {
    die('Tidak bisa konek ke MySQL. Pastikan MySQL di XAMPP sudah di-Start. Detail: ' . $e->getMessage());
}
$pdo->exec("CREATE DATABASE IF NOT EXISTS `{$DB['nama']}` CHARACTER SET utf8mb4");
$pdo->exec("USE `{$DB['nama']}`");

// ===== Star schema: 4 dimensi + 1 fakta (dibuat otomatis kalau belum ada) =====
foreach ([
    "CREATE TABLE IF NOT EXISTS dim_film (id_film INT AUTO_INCREMENT PRIMARY KEY, judul VARCHAR(80), genre VARCHAR(30), jenis ENUM('Film','Event'), harga INT)",
    "CREATE TABLE IF NOT EXISTS dim_studio (id_studio INT AUTO_INCREMENT PRIMARY KEY, nama_studio VARCHAR(50), kategori ENUM('Bioskop','Event'), kapasitas INT)",
    "CREATE TABLE IF NOT EXISTS dim_pelanggan (id_pelanggan INT AUTO_INCREMENT PRIMARY KEY, nama VARCHAR(60), kota VARCHAR(40), member ENUM('Reguler','Gold'))",
    "CREATE TABLE IF NOT EXISTS dim_waktu (id_waktu INT AUTO_INCREMENT PRIMARY KEY, tanggal DATE, hari VARCHAR(10), bulan TINYINT, tahun SMALLINT, jam_tayang CHAR(5), UNIQUE KEY (tanggal, jam_tayang))",
    "CREATE TABLE IF NOT EXISTS fact_penjualan (id_fakta INT AUTO_INCREMENT PRIMARY KEY, id_film INT, id_studio INT, id_pelanggan INT, id_waktu INT, jumlah_tiket INT, total_harga INT,
        FOREIGN KEY (id_film) REFERENCES dim_film(id_film), FOREIGN KEY (id_studio) REFERENCES dim_studio(id_studio),
        FOREIGN KEY (id_pelanggan) REFERENCES dim_pelanggan(id_pelanggan), FOREIGN KEY (id_waktu) REFERENCES dim_waktu(id_waktu))",
] as $sql) $pdo->exec($sql);

// ===== Fungsi bantu =====
function q($sql, $p = []) { global $pdo; $s = $pdo->prepare($sql); $s->execute($p); return $s->fetchAll(); }
function h($s)     { return htmlspecialchars((string)$s); }
function angka($n) { return number_format((float)$n, 0, ',', '.'); }
function rp($n)    { return 'Rp ' . angka($n); }
function masuk($sql, $rows) {            // insert banyak baris, kembalikan daftar ID
    global $pdo; $st = $pdo->prepare($sql); $ids = [];
    foreach ($rows as $r) { $st->execute($r); $ids[] = $pdo->lastInsertId(); }
    return $ids;
}
function pilih($bobot) {                 // pilih acak berbobot
    $r = mt_rand(1, array_sum($bobot));
    foreach ($bobot as $k => $b) if (($r -= $b) <= 0) return $k;
}

// ===== Data contoh (hanya sekali, saat tabel masih kosong) =====
if ($pdo->query("SELECT COUNT(*) FROM dim_film")->fetchColumn() == 0) {
    $pdo->beginTransaction();
    $film = masuk("INSERT INTO dim_film (judul,genre,jenis,harga) VALUES (?,?,?,?)", [
        ['Langit Merah', 'Action', 'Film', 45000], ['Cinta di Tepi Danau', 'Romance', 'Film', 40000],
        ['Pulang Kampung', 'Drama', 'Film', 40000], ['Kuntilanak Kembali', 'Horor', 'Film', 45000],
        ['Si Kancil Petualang', 'Animasi', 'Film', 35000], ['Misi Rahasia 7', 'Action', 'Film', 50000],
        ['Konser Musik Nusantara', 'Konser', 'Event', 250000], ['Stand Up Comedy Night', 'Komedi', 'Event', 150000]]);
    $studio = masuk("INSERT INTO dim_studio (nama_studio,kategori,kapasitas) VALUES (?,?,?)", [
        ['Studio 1 Reguler', 'Bioskop', 120], ['Studio 2 Reguler', 'Bioskop', 120], ['Studio 3 Premiere', 'Bioskop', 60],
        ['Studio 4 IMAX', 'Bioskop', 200], ['Hall Utama', 'Event', 800], ['Open Air Stage', 'Event', 1500]]);
    $kota = ['Jakarta', 'Bandung', 'Surabaya', 'Medan', 'Yogyakarta', 'Makassar'];
    $nama = ['Budi Santoso', 'Siti Aisyah', 'Andi Pratama', 'Dewi Lestari', 'Rizky Hasibuan', 'Putri Nasution', 'Agus Siregar',
             'Nur Hidayah', 'Fajar Ramadhan', 'Maya Sari', 'Dian Purba', 'Rina Simanjuntak', 'Hendra Lubis', 'Sri Wahyuni',
             'Joko Susilo', 'Lina Marpaung', 'Eko Prasetyo', 'Tika Damanik', 'Bayu Saputra', 'Yuni Harahap'];
    $pel = masuk("INSERT INTO dim_pelanggan (nama,kota,member) VALUES (?,?,?)",
        array_map(fn($n) => [$n, $kota[array_rand($kota)], mt_rand(0, 1) ? 'Gold' : 'Reguler'], $nama));

    $info = $pdo->query("SELECT id_film, jenis, harga FROM dim_film")->fetchAll(PDO::FETCH_UNIQUE | PDO::FETCH_ASSOC);
    $stW = $pdo->prepare("INSERT INTO dim_waktu (tanggal,hari,bulan,tahun,jam_tayang) VALUES (?,?,?,?,?)");
    $waktu = [];
    for ($i = 0; $i < 60; $i++) {                       // 60 hari terakhir x 5 jam tayang
        $t = strtotime("-$i days");
        foreach (JAM as $j) {
            $stW->execute([date('Y-m-d', $t), HARI[date('w', $t)], date('n', $t), date('Y', $t), $j]);
            $waktu[$i][$j] = $pdo->lastInsertId();
        }
    }
    $bobotFilm = array_combine($film, [10, 7, 6, 9, 4, 12, 8, 5]);   // makin besar = makin laris
    $bobotJam  = array_combine(JAM, [5, 10, 15, 35, 20]);            // malam lebih ramai
    $stF = $pdo->prepare("INSERT INTO fact_penjualan (id_film,id_studio,id_pelanggan,id_waktu,jumlah_tiket,total_harga) VALUES (?,?,?,?,?,?)");
    for ($n = 0; $n < 600; $n++) {
        $f = pilih($bobotFilm);
        $pool = $info[$f]['jenis'] == 'Event' ? array_slice($studio, 4) : array_slice($studio, 0, 4);
        $jml = mt_rand(1, 5);
        $stF->execute([$f, $pool[array_rand($pool)], $pel[array_rand($pel)], $waktu[mt_rand(0, 59)][pilih($bobotJam)], $jml, $jml * $info[$f]['harga']]);
    }
    $pdo->commit();
}

function awal($judul) {
    $f = basename($_SERVER['PHP_SELF']);
    $a = $f == 'index.php' ? 'on' : '';
    $b = $f == 'input.php' ? 'on' : '';
?>
<!doctype html>
<html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= h($judul) ?> | Data Warehouse Tiket</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=DM+Sans:wght@400;500;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="style.css">
</head><body>
<header>
  <div class="brand">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round">
      <path d="M3 8a2 2 0 0 0 2-2h14a2 2 0 0 0 2 2v2a2 2 0 0 0 0 4v2a2 2 0 0 0-2 2H5a2 2 0 0 0-2-2v-2a2 2 0 0 0 0-4z"/>
      <path d="M15 6v12" stroke-dasharray="2 2"/>
    </svg>
    DW TIKET
  </div>
  <nav>
    <a class="<?= $a ?>" href="index.php">Dashboard</a>
    <a class="<?= $b ?>" href="input.php">Input penjualan</a>
  </nav>
</header>
<main>
<?php
}
function akhir() { echo '</main></body></html>'; }
