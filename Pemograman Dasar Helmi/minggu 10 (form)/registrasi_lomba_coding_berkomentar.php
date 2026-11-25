<?php
// ============================================================
// BAGIAN 1: PENGOLAHAN DATA DAN VALIDASI DENGAN PHP
// Kode PHP dijalankan di server sebelum halaman HTML dikirim ke browser.
// Validasi server tetap bekerja walaupun JavaScript pada browser dimatikan.
// ============================================================


// Daftar kategori yang diizinkan (whitelist).
// Pengguna hanya boleh mengirim salah satu nilai yang ada di daftar ini.
$kategoriWhitelist = [
    'Web Development',
    'Mobile Development',
    'Data Science',
    'Artificial Intelligence',
    'Cyber Security'
];

// Daftar pilihan jenis peserta yang sah: Individu atau Tim.
$pesertaWhitelist = [
    'Individu',
    'Tim'
];

// Menyimpan nilai awal/kiriman form.
// Nilai ini juga dipakai untuk mengisi kembali form ketika validasi gagal,
// sehingga pengguna tidak perlu mengetik ulang semua data.
$old = [
    'nama' => '',
    'email' => '',
    'telepon' => '',
    'umur' => '',
    'github' => '',
    'kategori' => '',
    'peserta' => ''
];

// Array untuk menampung pesan kesalahan berdasarkan nama field.
$errors = [];

// Penanda apakah seluruh data berhasil melewati validasi.
$success = false;

// Fungsi untuk mengambil data yang dikirim melalui form (metode POST).
// Parameter $key berisi nama field, misalnya 'nama' atau 'email'.
function ambilInput(string $key): string
{
    // Periksa apakah input tersedia dan benar-benar berupa teks/string.
    // Jika tidak tersedia atau tipenya tidak sesuai, kembalikan teks kosong.
    if (!isset($_POST[$key]) || !is_string($_POST[$key])) {
        return '';
    }

    // trim() menghapus spasi yang tidak diperlukan di awal dan akhir input.
    return trim($_POST[$key]);
}

// Fungsi keamanan untuk mengubah karakter khusus menjadi entitas HTML.
// Tujuannya mencegah input pengguna dibaca browser sebagai kode HTML/script.
function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

// Periksa apakah halaman dibuka setelah tombol Kirim ditekan.
// REQUEST_METHOD bernilai POST ketika form dikirim.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ambil semua nilai input dan simpan ke array $old.
    // Perulangan ini membuat setiap isian dapat ditampilkan kembali nantinya.
    foreach ($old as $key => $value) {
        $old[$key] = ambilInput($key);
    }

    // ------------------------------------------------------------
    // VALIDASI 1: NAMA LENGKAP
    // Nama harus diisi dan panjangnya minimal 3, maksimal 50 karakter.
    // ------------------------------------------------------------

    // Validasi nama: wajib, 3 sampai 50 karakter.
    // Jika tersedia, mb_strlen menghitung karakter Unicode dengan lebih tepat.
    // Jika tidak, program memakai preg_match_all sebagai alternatif.
    if (function_exists('mb_strlen')) {
        $panjangNama = mb_strlen($old['nama'], 'UTF-8');
    } else {
        // Fallback jika ekstensi mbstring belum aktif di server PHP.
        preg_match_all('/./us', $old['nama'], $karakterNama);
        $panjangNama = count($karakterNama[0]);
    }
    if ($old['nama'] === '') {
        $errors['nama'] = 'Nama wajib diisi.';
    } elseif ($panjangNama < 3 || $panjangNama > 50) {
        $errors['nama'] = 'Nama harus terdiri dari 3–50 karakter.';
    }

    // ------------------------------------------------------------
    // VALIDASI 2: ALAMAT EMAIL
    // Email harus diisi dan mengikuti format alamat email yang benar.
    // ------------------------------------------------------------
    if ($old['email'] === '') {
        $errors['email'] = 'Email wajib diisi.';
    } elseif (!filter_var($old['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Format email tidak valid.';
    }

    // ------------------------------------------------------------
    // VALIDASI 3: NOMOR HANDPHONE
    // Nomor boleh diawali 08, 62, atau +62.
    // Spasi dan tanda hubung dihapus agar format penulisan lebih fleksibel.
    // ------------------------------------------------------------
    // preg_replace() membersihkan spasi dan tanda hubung dari nomor HP.
    $teleponBersih = preg_replace('/[\s-]+/', '', $old['telepon']);
    if ($old['telepon'] === '') {
        $errors['telepon'] = 'Nomor HP wajib diisi.';
    // REGEX di bawah memeriksa awalan nomor Indonesia dan jumlah digitnya.
    // ^ berarti awal teks; (?:\+62|62|0) berarti salah satu awalan;
    // 8[1-9] memastikan digit berikutnya sesuai pola nomor seluler;
    // [0-9]{7,10} berarti diikuti 7 sampai 10 angka; $ berarti akhir teks.
    } elseif (!preg_match('/^(?:\+62|62|0)8[1-9][0-9]{7,10}$/', $teleponBersih)) {
        $errors['telepon'] = 'Nomor HP tidak valid. Contoh: 081234567890.';
    }

    // ------------------------------------------------------------
    // VALIDASI 4: UMUR PESERTA
    // Umur wajib berupa angka bulat dan hanya boleh 17, 18, atau 19 tahun.
    // ------------------------------------------------------------
    if ($old['umur'] === '') {
        $errors['umur'] = 'Umur wajib diisi.';
    } elseif (!ctype_digit($old['umur']) || (int) $old['umur'] < 17 || (int) $old['umur'] > 19) {
        $errors['umur'] = 'Pendaftaran hanya untuk peserta berumur 17–19 tahun.';
    }

    // ------------------------------------------------------------
    // VALIDASI 5: URL GITHUB
    // Kolom ini opsional. Jika diisi, URL harus valid dan memakai HTTP/HTTPS.
    // ------------------------------------------------------------
    if ($old['github'] !== '') {
        // filter_var() memeriksa apakah teks memiliki format URL yang valid.
        $urlValid = filter_var($old['github'], FILTER_VALIDATE_URL);
        // parse_url() memecah URL agar bagian protokolnya dapat diperiksa.
        $bagianUrl = parse_url($old['github']);
        $skemaValid = isset($bagianUrl['scheme']) && in_array(strtolower($bagianUrl['scheme']), ['http', 'https'], true);

        if (!$urlValid || !$skemaValid) {
            $errors['github'] = 'URL GitHub tidak valid. Gunakan alamat lengkap, misalnya https://github.com/username.';
        }
    }

    // ------------------------------------------------------------
    // VALIDASI 6: KATEGORI LOMBA
    // Nilai kategori harus cocok dengan salah satu isi $kategoriWhitelist.
    // ------------------------------------------------------------
    // in_array() mengecek apakah pilihan pengguna ada di daftar kategori.
    // Parameter true mengaktifkan perbandingan tipe dan nilai secara ketat.
    if (!in_array($old['kategori'], $kategoriWhitelist, true)) {
        $errors['kategori'] = 'Silakan pilih kategori lomba yang tersedia.';
    }

    // ------------------------------------------------------------
    // VALIDASI 7: JENIS PESERTA
    // Nilai peserta hanya boleh 'Individu' atau 'Tim'.
    // ------------------------------------------------------------
    // Periksa pilihan jenis peserta dengan cara yang sama seperti kategori.
    if (!in_array($old['peserta'], $pesertaWhitelist, true)) {
        $errors['peserta'] = 'Silakan pilih jenis peserta yang tersedia.';
    }

    // ------------------------------------------------------------
    // HASIL VALIDASI
    // empty($errors) berarti tidak ada pesan error pada field mana pun.
    // Jika lolos, tampilkan ringkasan; pada contoh ini data belum disimpan
    // ke database karena tugas belum meminta fitur penyimpanan database.
    // ------------------------------------------------------------
    if (empty($errors)) {
        $success = true;
    }
}
?>

<!-- ============================================================ -->
<!-- BAGIAN 2: STRUKTUR HALAMAN HTML                               -->
<!-- HTML membentuk susunan judul, keterangan, dan elemen formulir. -->
<!-- ============================================================ -->
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrasi Lomba Coding</title>
    <!-- ======================================================== -->
    <!-- BAGIAN 3: CSS UNTUK DESAIN DAN TATA LETAK HALAMAN       -->
    <!-- CSS mengatur warna, ukuran, jarak, dan tata letak.      -->
    <!-- ======================================================== -->
    <style>
        /* Reset sederhana: ukuran elemen sudah termasuk padding dan border. */
        * {
            box-sizing: border-box;
        }

        /* Tampilan dasar seluruh halaman: font, warna teks, dan latar belakang. */
        body {
            margin: 0;
            min-height: 100vh;
            padding: 32px 16px;
            font-family: Arial, Helvetica, sans-serif;
            color: #f4f6fb;
            background:
                radial-gradient(circle at 85% 10%, rgba(255, 91, 48, .19), transparent 28%),
                radial-gradient(circle at 8% 88%, rgba(35, 115, 190, .20), transparent 32%),
                linear-gradient(135deg, #10151f, #202735 55%, #10151f);
        }

        /* Membatasi lebar konten dan menempatkannya di tengah halaman. */
        .container {
            width: 100%;
            max-width: 900px;
            margin: 0 auto;
        }

        /* Kartu utama yang membungkus seluruh isi formulir. */
        .form-card {
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, .12);
            border-radius: 13px;
            background: rgba(13, 18, 27, .94);
            box-shadow: 0 18px 50px rgba(0, 0, 0, .35);
        }

        /* Bagian header berwarna oranye di bagian paling atas form. */
        .topbar {
            padding: 17px 22px;
            text-align: center;
            background: #200c08;
        }

        /* Mengatur tampilan judul utama pada header. */
        .topbar h1 {
            margin: 0;
            color: #fff;
            font-size: 22px;
            letter-spacing: .3px;
        }

        /* Paragraf pengantar yang menjelaskan tujuan pendaftaran. */
        .intro {
            padding: 22px 26px 10px;
            color: #cbd2df;
            line-height: 1.65;
            font-size: 14px;
        }

        /* Membuat nama acara pada paragraf pengantar lebih menonjol. */
        .intro strong {
            color: #fff;
        }

        /* Memberi ruang di dalam area formulir. */
        form {
            padding: 10px 26px 28px;
        }

        /* Judul pembatas untuk kelompok informasi peserta dan informasi lomba. */
        .section-title {
            margin: 20px 0 16px;
            padding: 10px 13px;
            border-radius: 3px;
            color: #fff;
            background: #1b0f0c;
            font-size: 15px;
            font-weight: bold;
        }

        /* Menyusun setiap baris formulir menjadi kolom label dan kolom input. */
        .field {
            display: grid;
            grid-template-columns: 190px minmax(0, 1fr);
            gap: 14px;
            align-items: start;
            margin-bottom: 17px;
        }

        /* Menyamakan tampilan label untuk setiap input. */
        .field > label,
        .field-label {
            padding-top: 10px;
            color: #eef1f7;
            font-size: 14px;
            font-weight: bold;
        }

        /* Area yang menampung input, petunjuk, dan pesan kesalahan. */
        .control {
            min-width: 0;
        }

        /* Tampilan umum untuk kotak input dan dropdown pilihan. */
        input,
        select {
            width: 100%;
            min-height: 42px;
            padding: 10px 12px;
            border: 1px solid #4b5668;
            border-radius: 5px;
            outline: none;
            color: #f8fafc;
            background: #202a39;
            font-size: 14px;
            transition: border-color .2s, box-shadow .2s;
        }

        /* Warna teks contoh di dalam input sebelum pengguna mengetik. */
        input::placeholder {
            color: #9ba6b6;
        }

        /* Efek visual ketika input atau dropdown sedang dipilih/difokuskan. */
        input:focus,
        select:focus {
            border-color: #ff775e;
            box-shadow: 0 0 0 3px rgba(255, 87, 56, .16);
        }

        /* Warna latar dan teks untuk item di dalam dropdown kategori. */
        select option {
            color: #f8fafc;
            background: #202a39;
        }

        /* Menyusun pilihan radio Individu dan Tim secara sejajar. */
        .radio-group {
            display: flex;
            flex-wrap: wrap;
            gap: 18px;
            min-height: 42px;
            align-items: center;
        }

        /* Mengatur label dan teks untuk masing-masing pilihan radio. */
        .radio-option {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: #e8edf5;
            font-size: 14px;
            cursor: pointer;
        }

        /* Mengatur ukuran dan warna lingkaran radio button. */
        .radio-option input {
            width: 16px;
            min-height: 16px;
            height: 16px;
            margin: 0;
            accent-color: #ff5738;
        }

        /* Teks bantuan kecil di bawah input, berisi contoh atau petunjuk. */
        .hint {
            display: block;
            margin-top: 6px;
            color: #9da9ba;
            font-size: 12px;
            line-height: 1.5;
        }

        /* Pesan error ditampilkan tepat di bawah field yang bermasalah. */
        .error {
            display: block;
            margin-top: 6px;
            color: #ff8d80;
            font-size: 12px;
            line-height: 1.45;
        }

        /* Tempat menampilkan peringatan umur secara langsung dari JavaScript. */
        .age-warning {
            display: none;
            margin-top: 7px;
            color: #ffc36d;
            font-size: 12px;
            line-height: 1.45;
        }

        /* Gaya umum untuk kotak pemberitahuan hasil pengiriman form. */
        .notice {
            margin: 18px 0;
            padding: 13px 15px;
            border-radius: 6px;
            line-height: 1.55;
            font-size: 14px;
        }

        /* Warna khusus untuk pemberitahuan ketika ada validasi yang gagal. */
        .notice.error-box {
            border: 1px solid rgba(255, 111, 97, .45);
            color: #ffd4ce;
            background: rgba(160, 45, 35, .18);
        }

        /* Warna khusus untuk pemberitahuan ketika semua data valid. */
        .notice.success-box {
            border: 1px solid rgba(68, 197, 135, .45);
            color: #c9f7df;
            background: rgba(31, 125, 82, .18);
        }

        /* Kotak ringkasan yang menampilkan data setelah validasi berhasil. */
        .summary {
            margin-top: 13px;
            padding: 14px;
            border-radius: 6px;
            color: #e7edf7;
            background: rgba(255, 255, 255, .055);
            line-height: 1.8;
            overflow-wrap: anywhere;
        }

        /* Menegaskan label seperti Nama, Email, dan Nomor HP pada ringkasan. */
        .summary strong {
            color: #fff;
        }

        /* Menata posisi tombol Kirim dan Reset. */
        .actions {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 12px;
            margin-top: 25px;
        }

        /* Tampilan utama tombol kirim pendaftaran. */
        button {
            min-height: 42px;
            padding: 0 24px;
            border: 0;
            border-radius: 4px;
            color: #fff;
            background: #ff5738;
            font-size: 14px;
            font-weight: bold;
            cursor: pointer;
            transition: background .2s, transform .2s;
        }

        /* Mengubah tampilan tombol saat kursor diarahkan ke atasnya. */
        button:hover {
            background: #e9472b;
            transform: translateY(-1px);
        }

        /* Tombol reset dibuat berbeda agar mudah dibedakan dari tombol kirim. */
        .reset-button {
            border: 1px solid #687487;
            color: #dce3ed;
            background: transparent;
        }

        /* Efek saat kursor berada di atas tombol reset. */
        .reset-button:hover {
            background: #303b4b;
        }

        /* Catatan pengingat di bagian bawah formulir. */
        .footer-note {
            margin-top: 17px;
            color: #9da9ba;
            font-size: 12px;
            line-height: 1.5;
        }

        /* RESPONSIVE: aturan berikut aktif pada layar kecil seperti HP.
           Kolom label dan input diubah menjadi satu kolom agar mudah dibaca. */
        @media (max-width: 650px) {
            body {
                padding: 14px 9px;
            }

            .topbar h1 {
                font-size: 19px;
            }

            .intro {
                padding: 18px 17px 8px;
            }

            form {
                padding: 8px 17px 22px;
            }

            .field {
                grid-template-columns: 1fr;
                gap: 7px;
                margin-bottom: 15px;
            }

            .field > label,
            .field-label {
                padding-top: 0;
            }
        }
    </style>
</head>
<body>
<!-- Pembungkus halaman untuk menjaga konten berada di tengah. -->
<div class="container">
    <!-- Kartu utama yang berisi header, pengantar, pesan, dan formulir. -->
    <main class="form-card">
        <!-- Header: menampilkan judul formulir pendaftaran. -->
        <header class="topbar">
            <h1>FORMULIR REGISTRASI LOMBA CODING</h1>
        </header>

        <!-- Pengantar: nama acara dan aturan umum pendaftaran. -->
        <div class="intro">
            <strong>CodeSprint 2026</strong><br>
            Tunjukkan kemampuanmu dalam menyelesaikan tantangan pemrograman.<br>
            Isi formulir pendaftaran dengan data yang benar. Peserta wajib berusia 17–19 tahun.
        </div>

        <!-- Jika semua data valid, tampilkan pemberitahuan dan ringkasan data. -->
        <?php if ($success): ?>
            <div class="notice success-box" style="margin: 16px 26px 0;">
                <strong>Validasi berhasil!</strong> Data pendaftaran sudah lolos pemeriksaan.
                <div class="summary">
                    <!-- Tampilkan data yang sudah lolos validasi. Fungsi e() menjaga output tetap aman. -->
                    <strong>Nama:</strong> <?= e($old['nama']) ?><br>
                    <strong>Email:</strong> <?= e($old['email']) ?><br>
                    <strong>Nomor HP:</strong> <?= e($old['telepon']) ?><br>
                    <strong>Umur:</strong> <?= e($old['umur']) ?> tahun<br>
                    <strong>GitHub:</strong>
                    <?php if ($old['github'] !== ''): ?>
                        <?= e($old['github']) ?>
                    <?php else: ?>
                        Tidak dicantumkan
                    <?php endif; ?><br>
                    <strong>Kategori:</strong> <?= e($old['kategori']) ?><br>
                    <strong>Jenis peserta:</strong> <?= e($old['peserta']) ?>
                </div>
                <span class="hint">Catatan: contoh ini baru melakukan validasi dan menampilkan ringkasan. Data belum disimpan ke database.</span>
            </div>
        <!-- Jika form sudah dikirim tetapi ada kesalahan, tampilkan pesan umum. -->
        <?php elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($errors)): ?>
            <div class="notice error-box" style="margin: 16px 26px 0;">
                <strong>Formulir belum dapat dikirim.</strong> Periksa kembali kolom yang memiliki pesan kesalahan.
            </div>
        <?php endif; ?>

        <!--
            Form menggunakan POST agar data dikirim ke server tanpa ditampilkan
            di URL. action kosong berarti data dikirim ke halaman ini sendiri.
            novalidate menonaktifkan validasi bawaan browser agar pesan validasi
            yang kita buat di PHP dapat ditampilkan secara konsisten.
        -->
        <form method="POST" action="" novalidate>
            <!-- Bagian pertama berisi identitas peserta. -->
            <h2 class="section-title">Informasi Peserta</h2>

            <!-- Input nama: wajib diisi dan dibatasi 3 sampai 50 karakter. -->
            <div class="field">
                <label for="nama">Nama Lengkap</label>
                <div class="control">
                    <input type="text" id="nama" name="nama" minlength="3" maxlength="50"
                           placeholder="Masukkan nama lengkap" value="<?= e($old['nama']) ?>" required>
                    <!-- Pesan error hanya muncul jika field nama memiliki kesalahan. -->
                    <?php if (isset($errors['nama'])): ?><span class="error"><?= e($errors['nama']) ?></span><?php endif; ?>
                </div>
            </div>

            <!-- Input email: browser menggunakan tipe email, PHP memvalidasi ulang di server. -->
            <div class="field">
                <label for="email">Alamat Email</label>
                <div class="control">
                    <input type="email" id="email" name="email"
                           placeholder="contoh@email.com" value="<?= e($old['email']) ?>" required>
                    <!-- Tampilkan pesan error email jika format atau isian tidak valid. -->
                    <?php if (isset($errors['email'])): ?><span class="error"><?= e($errors['email']) ?></span><?php endif; ?>
                </div>
            </div>

            <!-- Input nomor HP: tipe tel memudahkan pengguna memasukkan nomor di ponsel. -->
            <div class="field">
                <label for="telepon">Nomor HP</label>
                <div class="control">
                    <input type="tel" id="telepon" name="telepon" inputmode="tel"
                           placeholder="081234567890" value="<?= e($old['telepon']) ?>" required>
                    <span class="hint">Gunakan nomor Indonesia, contoh: 081234567890 atau +6281234567890.</span>
                    <!-- Tampilkan pesan error nomor HP jika pemeriksaan REGEX gagal. -->
                    <?php if (isset($errors['telepon'])): ?><span class="error"><?= e($errors['telepon']) ?></span><?php endif; ?>
                </div>
            </div>

            <!-- Input umur: harus memenuhi rentang 17 sampai 19 tahun. -->
            <div class="field">
                <label for="umur">Umur</label>
                <div class="control">
                    <input type="number" id="umur" name="umur" min="1" max="100" step="1"
                           placeholder="Masukkan umur" value="<?= e($old['umur']) ?>" required>
                    <span class="hint">Rentang umur yang diperbolehkan: 17–19 tahun.</span>
                    <span class="age-warning" id="ageWarning" role="status"></span>
                    <!-- Tampilkan pesan error umur jika belum diisi atau di luar 17–19. -->
                    <?php if (isset($errors['umur'])): ?><span class="error"><?= e($errors['umur']) ?></span><?php endif; ?>
                </div>
            </div>

            <!-- Bagian kedua berisi informasi pilihan lomba peserta. -->
            <h2 class="section-title">Informasi Lomba</h2>

            <!-- URL GitHub bersifat opsional; jika diisi harus berupa URL yang valid. -->
            <div class="field">
                <label for="github">URL Repositori GitHub</label>
                <div class="control">
                    <input type="url" id="github" name="github"
                           placeholder="https://github.com/username" value="<?= e($old['github']) ?>">
                    <span class="hint">Opsional. Masukkan URL lengkap jika ingin mencantumkan repositori.</span>
                    <!-- GitHub opsional; pesan error muncul hanya jika URL yang diisi tidak valid. -->
                    <?php if (isset($errors['github'])): ?><span class="error"><?= e($errors['github']) ?></span><?php endif; ?>
                </div>
            </div>

            <!-- Dropdown kategori dibuat dari whitelist agar pilihannya konsisten. -->
            <div class="field">
                <label for="kategori">Kategori Lomba</label>
                <div class="control">
                    <select id="kategori" name="kategori" required>
                        <option value="">-- Pilih kategori --</option>
                        <!-- Loop ini membuat pilihan dropdown dari daftar kategori yang diizinkan. -->
                        <?php foreach ($kategoriWhitelist as $kategori): ?>
                            <!-- Pilihan sebelumnya diberi atribut selected agar tetap terpilih setelah error. -->
                            <option value="<?= e($kategori) ?>" <?= $old['kategori'] === $kategori ? 'selected' : '' ?>>
                                <?= e($kategori) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <!-- Tampilkan pesan jika kategori belum dipilih atau nilainya tidak masuk whitelist. -->
                    <?php if (isset($errors['kategori'])): ?><span class="error"><?= e($errors['kategori']) ?></span><?php endif; ?>
                </div>
            </div>

            <!-- Radio button: pengguna hanya dapat memilih Individu atau Tim. -->
            <div class="field">
                <span class="field-label">Jenis Peserta</span>
                <div class="control">
                    <div class="radio-group">
                        <!-- Loop membuat radio button dari pilihan Individu dan Tim. -->
                        <?php foreach ($pesertaWhitelist as $jenis): ?>
                            <label class="radio-option">
                                <input type="radio" name="peserta" value="<?= e($jenis) ?>"
                                    <!-- Radio yang sebelumnya dipilih tetap ditandai checked setelah error. -->
                                    <?= $old['peserta'] === $jenis ? 'checked' : '' ?> required>
                                <?= e($jenis) ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                    <!-- Tampilkan pesan jika jenis peserta belum dipilih atau tidak diizinkan. -->
                    <?php if (isset($errors['peserta'])): ?><span class="error"><?= e($errors['peserta']) ?></span><?php endif; ?>
                </div>
            </div>

            <!-- Tombol submit mengirim form; tombol reset mengosongkan isian di browser. -->
            <div class="actions">
                <button type="submit">Kirim Pendaftaran</button>
                <button type="reset" class="reset-button">Reset Form</button>
            </div>

            <p class="footer-note">Pastikan seluruh data sudah benar sebelum menekan tombol Kirim Pendaftaran.</p>
        </form>
    </main>
</div>

<!-- ============================================================ -->
<!-- BAGIAN 4: JAVASCRIPT UNTUK PERINGATAN UMUR LANGSUNG          -->
<!-- JavaScript memberi umpan balik cepat; PHP tetap menjadi validasi utama. -->
<!-- ============================================================ -->
<script>
    // Ambil elemen input umur dan tempat untuk menampilkan pesan peringatan.
    const inputUmur = document.getElementById('umur');
    const ageWarning = document.getElementById('ageWarning');

    // Fungsi ini dijalankan setiap kali pengguna mengubah nilai umur.
    function periksaUmur() {
        // Ambil nilai input dan hilangkan spasi di awal/akhir.
        const nilai = inputUmur.value.trim();

        // Jika kolom umur kosong, sembunyikan pesan dan hentikan fungsi.
        if (nilai === '') {
            ageWarning.style.display = 'none';
            ageWarning.textContent = '';
            return;
        }

        // Ubah teks menjadi angka agar dapat dibandingkan dengan batas umur.
        const umur = Number(nilai);
        // Periksa apakah umur bilangan bulat dan berada pada rentang 17–19.
        if (!Number.isInteger(umur) || umur < 17 || umur > 19) {
            ageWarning.textContent = 'Peringatan: umur harus 17–19 tahun. Formulir tidak akan lolos validasi jika di luar rentang tersebut.';
            ageWarning.style.color = '#ffc36d';
            ageWarning.style.display = 'block';
        } else {
            ageWarning.textContent = 'Umur memenuhi persyaratan lomba.';
            ageWarning.style.color = '#8ce0b4';
            ageWarning.style.display = 'block';
        }
    }

    // Panggil fungsi setiap kali pengguna mengetik atau mengubah nilai umur.
    inputUmur.addEventListener('input', periksaUmur);

    // Periksa juga saat halaman pertama kali selesai dimuat.
    periksaUmur();
</script>
</body>
</html>
