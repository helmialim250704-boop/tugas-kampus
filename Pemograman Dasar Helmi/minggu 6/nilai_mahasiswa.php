<?php
// File: nilai_mahasiswa.php

$dataMahasiswa = [
    [
        "nama"  => "Andi",
        "nim"   => "E412564",
        "tugas" => 80,
        "uts"   => 75,
        "uas"   => 90
    ],
    [
        "nama"  => "Budi",
        "nim"   => "E412357",
        "tugas" => 85,
        "uts"   => 88,
        "uas"   => 82
    ],
    [
        "nama"  => "Citra",
        "nim"   => "E412864",
        "tugas" => 90,
        "uts"   => 85,
        "uas"   => 95
    ],
    [
        "nama"  => "Deni",
        "nim"   => "E412987",
        "tugas" => 78,
        "uts"   => 80,
        "uas"   => 84
    ],
    [
        "nama"  => "Eka",
        "nim"   => "E412781",
        "tugas" => 88,
        "uts"   => 92,
        "uas"   => 89
    ]
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Nilai Mahasiswa</title>
</head>
<body>
    <h2>Daftar Nilai Mahasiswa</h2>
    <p>Nilai Akhir = (Tugas × 30%) + (UTS × 30%) + (UAS × 40%)</p>
    <p>Jumlah mahasiswa: <?php echo count($dataMahasiswa); ?></p>

    <table border="1" cellpadding="5" cellspacing="0">
        <tr>
            <th>No</th>
            <th>Nama</th>
            <th>NIM</th>
            <th>Tugas</th>
            <th>UTS</th>
            <th>UAS</th>
            <th>Nilai Akhir</th>
        </tr>
        <?php
        $no = 1;
        $nilaiTertinggi = 0;
$mahasiswaTerbaik = "";

        foreach ($dataMahasiswa as $mhs) {
            $nilaiAkhir = ($mhs["tugas"] * 0.30) + ($mhs["uts"] * 0.30) + ($mhs["uas"] * 0.40);

            echo "<tr>";
            echo "<td>" . $no++ . "</td>";
            echo "<td>" . $mhs["nama"] . "</td>";
            echo "<td>" . $mhs["nim"] . "</td>";
            echo "<td>" . $mhs["tugas"] . "</td>";
            echo "<td>" . $mhs["uts"] . "</td>";
            echo "<td>" . $mhs["uas"] . "</td>";
            echo "<td>" . number_format($nilaiAkhir, 2) . "</td>";
            if ($nilaiAkhir > $nilaiTertinggi) {
    $nilaiTertinggi = $nilaiAkhir;
    $mahasiswaTerbaik = $mhs["nama"];
}
            echo "</tr>";
        }
        ?>
    </table><h3>Mahasiswa dengan Nilai Tertinggi</h3>

<?php
echo $mahasiswaTerbaik . " dengan nilai akhir " .
number_format($nilaiTertinggi, 2);
?>
</body>
</html>