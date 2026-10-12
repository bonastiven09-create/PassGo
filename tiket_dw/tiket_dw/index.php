<?php
require 'koneksi.php';

// Analisis: gabungkan tabel fakta dengan dimensi (JOIN), lalu kelompokkan (GROUP BY)
$t      = q("SELECT SUM(jumlah_tiket) tiket, SUM(total_harga) omzet FROM fact_penjualan")[0];
$film   = q("SELECT d.judul label, SUM(f.jumlah_tiket) nilai FROM fact_penjualan f JOIN dim_film d USING (id_film)
             GROUP BY d.id_film, d.judul ORDER BY nilai DESC LIMIT 5");
$jam    = q("SELECT w.jam_tayang label, SUM(f.jumlah_tiket) nilai FROM fact_penjualan f JOIN dim_waktu w USING (id_waktu)
             GROUP BY w.jam_tayang ORDER BY nilai DESC");
$studio = q("SELECT s.nama_studio label, SUM(f.total_harga) nilai FROM fact_penjualan f JOIN dim_studio s USING (id_studio)
             GROUP BY s.id_studio, s.nama_studio ORDER BY nilai DESC");
$pel    = q("SELECT p.nama label, SUM(f.jumlah_tiket) nilai FROM fact_penjualan f JOIN dim_pelanggan p USING (id_pelanggan)
             GROUP BY p.id_pelanggan, p.nama ORDER BY nilai DESC LIMIT 5");

function bar($rows, $rupiah = false) {
    if (!$rows) { echo '<p>Belum ada data penjualan.</p>'; return; }
    $max = max(array_column($rows, 'nilai'));
    foreach ($rows as $i => $r) {
        $w = round($r['nilai'] / $max * 100);
        echo "<div class='row" . ($i == 0 ? ' top' : '') . "'><span>" . h($r['label']) . "</span><div class='tr'><i style='width:$w%'></i></div><b>"
           . ($rupiah ? rp($r['nilai']) : angka($r['nilai'])) . "</b></div>";
    }
}

awal('Dashboard');
?>
<h2>Analisis penjualan tiket</h2>

<div class="ticket">
  <div><small>Tiket terjual</small><span class="big"><?= angka($t['tiket']) ?></span></div>
  <div><small>Total pendapatan</small><span class="big"><?= rp($t['omzet']) ?></span></div>
  <div><small>Film terlaris</small><span class="big"><?= h($film[0]['label'] ?? '-') ?></span></div>
  <div><small>Jam tayang favorit</small><span class="big"><?= h($jam[0]['label'] ?? '-') ?></span></div>
</div>

<div class="grid">
  <div class="panel"><h3>Film dan event terlaris</h3><?php bar($film) ?></div>
  <div class="panel"><h3>Jam tayang favorit</h3><?php bar($jam) ?></div>
  <div class="panel"><h3>Pendapatan per studio / venue</h3><?php bar($studio, true) ?></div>
  <div class="panel"><h3>Pelanggan teratas</h3><?php bar($pel) ?></div>
</div>

<p class="note">Skema bintang: tabel fakta <code>fact_penjualan</code> (jumlah_tiket, total_harga) terhubung ke dimensi
<code>dim_film</code>, <code>dim_studio</code>, <code>dim_pelanggan</code>, <code>dim_waktu</code>.
Ingin mengulang data contoh? Hapus database <code>dw_tiket</code> di phpMyAdmin, lalu buka halaman ini lagi.</p>
<?php akhir();
