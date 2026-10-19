<?php
require 'koneksi.php';
awal('Tentang PassGo');
?>
<section class="hero">
  <?= logo_svg() ?>
  <h2>PassGo</h2>
  <p class="tagline">Satu pass, langsung berangkat.</p>
  <p class="lead">PassGo adalah layanan penjualan tiket film dan event. Halaman ini menjelaskan latar belakang sistem analisis di balik PassGo, arti nama dan logonya, serta aturan pemakaiannya di setiap layar.</p>
</section>

<section class="block split">
  <h3>Latar belakang</h3>
  <div class="prose">
    <p>Setiap studio dan venue selama ini mencatat penjualannya sendiri. Akibatnya, pengelola sulit menjawab pertanyaan sederhana: film mana yang paling laris, jam tayang mana yang paling ramai, dan venue mana yang paling besar pendapatannya.</p>
    <p>Untuk menjawab itu, PassGo mengumpulkan seluruh transaksi ke dalam satu gudang data (data warehouse) dengan model bintang. Tabel fakta <code>fact_penjualan</code> mencatat setiap transaksi, lalu terhubung ke empat tabel dimensi: film, studio, pelanggan, dan waktu. Dengan begitu, angka penjualan bisa dilihat dari sudut mana pun tanpa membaca catatan satu per satu.</p>
    <p>Tujuannya: pengelola bisa memahami kondisi penjualan dalam satu layar dan menentukan jadwal tayang berdasarkan data.</p>
  </div>
</section>

<section class="block split">
  <h3>Nama</h3>
  <div class="prose">
    <p><strong>Pass</strong> berarti tiket atau izin masuk. <strong>Go</strong> berarti langkah untuk berangkat. Gabungan keduanya menggambarkan pengalaman yang ingin dicapai: tiket sudah di tangan, penonton tinggal melangkah masuk.</p>
    <p>Nama ditulis selalu <strong>PassGo</strong>, dengan huruf P dan G kapital, tanpa spasi.</p>
  </div>
</section>

<section class="block split">
  <h3>Logo</h3>
  <div class="prose">
    <p>Logo berbentuk tiket yang dilintasi panah. Tiket mewakili pass, panah mewakili langkah maju. Dua lekukan di tepi kiri dan kanan menandakan tiket sungguhan yang bisa disobek di pintu masuk.</p>
    <div class="logo-demo">
      <div class="logo-tile dark"><?= logo_svg() ?></div>
      <div class="logo-tile light"><?= logo_svg() ?></div>
      <div class="logo-tile coral"><?= logo_svg() ?></div>
    </div>
    <p>Logo hanya dipakai di tiga latar di atas. Jangan diputar, diberi bayangan, atau diubah warnanya.</p>
  </div>
</section>

<section class="block">
  <h3>Warna</h3>
  <p>Enam warna ini menjadi sumber untuk semua tampilan PassGo. Coral hanya dipakai untuk satu hal terpenting di setiap layar, misalnya tombol utama atau bar teratas.</p>
  <div class="swatches">
    <div class="sw"><i style="background:#0B1F21"></i><div><b>Malam</b><small>#0B1F21, latar utama</small></div></div>
    <div class="sw"><i style="background:#112B2E"></i><div><b>Kartu</b><small>#112B2E, panel dan kartu</small></div></div>
    <div class="sw"><i style="background:#F1EBDD"></i><div><b>Kertas</b><small>#F1EBDD, teks utama</small></div></div>
    <div class="sw"><i style="background:#8FAFAA"></i><div><b>Kabut</b><small>#8FAFAA, teks pendukung</small></div></div>
    <div class="sw"><i style="background:#FF6B4A"></i><div><b>Coral</b><small>#FF6B4A, aksen utama</small></div></div>
    <div class="sw"><i style="background:#3FB8A6"></i><div><b>Teal</b><small>#3FB8A6, data dan status sukses</small></div></div>
  </div>
</section>

<section class="block">
  <h3>Tipografi</h3>
  <p>Unbounded dipakai untuk judul dan angka penting, karena bentuknya lebar dan tegas di layar. Figtree dipakai untuk teks isi, label, dan tabel, karena tetap nyaman dibaca saat teksnya panjang.</p>
  <div class="specimen">
    <div class="spec">
      <small>Unbounded, judul dan angka</small>
      <p class="sample u">Tiket terjual 1.284</p>
    </div>
    <div class="spec">
      <small>Figtree, teks isi</small>
      <p class="sample f">Pengelola bisa melihat kondisi penjualan dalam satu layar.</p>
    </div>
  </div>
</section>

<section class="block">
  <h3>Nada suara</h3>
  <p>PassGo bicara singkat, aktif, dan langsung ke tujuan. Kata kerja ditaruh di depan dan kata pengisi dibuang. Istilah yang dipakai di tombol harus sama dengan pesan yang muncul setelahnya.</p>
  <div class="pair">
    <div>
      <small>Pakai</small>
      <span>Simpan transaksi, lalu muncul "Transaksi tersimpan".</span>
    </div>
    <div>
      <small>Hindari</small>
      <span>Kirim data, lalu muncul pesan teknis tanpa penjelasan.</span>
    </div>
    <div>
      <small>Pakai</small>
      <span>Tiket terjual</span>
    </div>
    <div>
      <small>Hindari</small>
      <span>Jumlah tiket yang berhasil terjual selama periode ini</span>
    </div>
  </div>
</section>

<section class="block">
  <h3>Penerapan di aplikasi</h3>
  <p>Logo tampil di kiri atas setiap halaman dan berfungsi sebagai tautan ke dashboard. Strip tiket di dashboard memakai Unbounded dengan coral untuk angka utama. Bar teratas selalu coral, sedangkan bar lainnya teal. Warna tidak dipakai sebagai hiasan, melainkan untuk menunjuk hal yang paling penting.</p>
</section>

<p class="note">Halaman ini bagian dari tugas mata kuliah Data Warehouse. Seluruh data penjualan yang tampil adalah data contoh.</p>
<?php akhir();
