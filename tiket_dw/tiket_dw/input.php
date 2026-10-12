<?php
require 'koneksi.php';

$pesan = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $d    = DateTime::createFromFormat('Y-m-d', $_POST['tanggal'] ?? '');
        $jam  = $_POST['jam'] ?? '';
        $jml  = max(1, min(20, (int)($_POST['jumlah'] ?? 1)));
        $harga = q("SELECT harga FROM dim_film WHERE id_film = ?", [(int)$_POST['film']]);
        if (!$d || !in_array($jam, JAM) || !$harga) throw new Exception('Data yang diisi belum valid.');
        $tgl = $d->format('Y-m-d');

        // 1) pastikan baris waktu ada di dimensi, 2) simpan transaksi ke tabel fakta
        $pdo->prepare("INSERT IGNORE INTO dim_waktu (tanggal,hari,bulan,tahun,jam_tayang) VALUES (?,?,?,?,?)")
            ->execute([$tgl, HARI[$d->format('w')], $d->format('n'), $d->format('Y'), $jam]);
        $idw   = q("SELECT id_waktu FROM dim_waktu WHERE tanggal = ? AND jam_tayang = ?", [$tgl, $jam])[0]['id_waktu'];
        $total = $jml * $harga[0]['harga'];
        $pdo->prepare("INSERT INTO fact_penjualan (id_film,id_studio,id_pelanggan,id_waktu,jumlah_tiket,total_harga) VALUES (?,?,?,?,?,?)")
            ->execute([(int)$_POST['film'], (int)$_POST['studio'], (int)$_POST['pelanggan'], $idw, $jml, $total]);
        $pesan = "Transaksi tersimpan: $jml tiket, total " . rp($total);
    } catch (Exception $e) {
        $pesan = 'Gagal menyimpan: ' . $e->getMessage();
    }
}

$films   = q("SELECT id_film, judul, jenis, harga FROM dim_film ORDER BY jenis, judul");
$studios = q("SELECT id_studio, nama_studio FROM dim_studio");
$pels    = q("SELECT id_pelanggan, nama FROM dim_pelanggan ORDER BY nama");
$last    = q("SELECT w.tanggal, w.jam_tayang, d.judul, s.nama_studio, p.nama, f.jumlah_tiket, f.total_harga
              FROM fact_penjualan f JOIN dim_film d USING (id_film) JOIN dim_studio s USING (id_studio)
              JOIN dim_pelanggan p USING (id_pelanggan) JOIN dim_waktu w USING (id_waktu)
              ORDER BY f.id_fakta DESC LIMIT 10");

awal('Input penjualan');
?>
<h2>Input penjualan tiket</h2>
<?php if ($pesan): ?><div class="ok"><?= h($pesan) ?></div><?php endif ?>

<form method="post" class="panel" style="max-width:520px">
  <label for="film">Film / event</label>
  <select id="film" name="film"><?php foreach ($films as $r): ?>
    <option value="<?= $r['id_film'] ?>"><?= h($r['judul']) ?> (<?= $r['jenis'] ?>, <?= rp($r['harga']) ?>)</option><?php endforeach ?>
  </select>
  <label for="studio">Studio / venue</label>
  <select id="studio" name="studio"><?php foreach ($studios as $r): ?>
    <option value="<?= $r['id_studio'] ?>"><?= h($r['nama_studio']) ?></option><?php endforeach ?>
  </select>
  <label for="pelanggan">Pelanggan</label>
  <select id="pelanggan" name="pelanggan"><?php foreach ($pels as $r): ?>
    <option value="<?= $r['id_pelanggan'] ?>"><?= h($r['nama']) ?></option><?php endforeach ?>
  </select>
  <label for="tanggal">Tanggal</label>
  <input id="tanggal" type="date" name="tanggal" value="<?= date('Y-m-d') ?>" required>
  <label for="jam">Jam tayang</label>
  <select id="jam" name="jam"><?php foreach (JAM as $j): ?><option><?= $j ?></option><?php endforeach ?></select>
  <label for="jumlah">Jumlah tiket</label>
  <input id="jumlah" type="number" name="jumlah" value="1" min="1" max="20" required>
  <button>Simpan transaksi</button>
</form>

<div class="panel" style="margin-top:18px;overflow:auto">
  <h3>10 transaksi terakhir</h3>
  <table>
    <tr><th>Tanggal</th><th>Jam</th><th>Film / event</th><th>Studio / venue</th><th>Pelanggan</th><th>Tiket</th><th>Total</th></tr>
    <?php foreach ($last as $r): ?>
    <tr><td><?= $r['tanggal'] ?></td><td><?= $r['jam_tayang'] ?></td><td><?= h($r['judul']) ?></td><td><?= h($r['nama_studio']) ?></td>
        <td><?= h($r['nama']) ?></td><td><?= $r['jumlah_tiket'] ?></td><td><?= rp($r['total_harga']) ?></td></tr>
    <?php endforeach ?>
  </table>
</div>
<?php akhir();
