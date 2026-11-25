<?php

function hitungNilaiAkhir($tugas, $uts, $uas) {
    $nilaiAkhir = ($tugas * 0.30) + ($uts * 0.30) + ($uas * 0.40);
    return $nilaiAkhir;
}

function tentukanGrade($nilaiAkhir) {
    if ($nilaiAkhir >= 90){
        return "A";
    } elseif ($nilaiAkhir >= 80){
        return "B";
    } elseif ($nilaiAkhir >= 70){
        return "C";
    } elseif ($nilaiAkhir >= 60){
        return "D";
    } else {
        return "E";
    }
}

function tentukanStatus($nilaiAkhir) {
    if ($nilaiAkhir >=75) {
        return "Lulus";
    } else {
        return "Tidak Lulus";
    }
}

function tampilkanHasil($nama, $tugas, $uts, $uas) {
    $nilaiAkhir = hitungNilaiAkhir($tugas, $uts, $uas);
    $grade = tentukanGrade($nilaiAkhir);
    $status = tentukanStatus($nilaiAkhir);

    echo "<tr>
        <td>$nama</td>
        <td>$tugas</td>
        <td>$uts</td>
        <td>$uas</td>
        <td>" . number_format($nilaiAkhir, 2) . "</td>
        <td>$grade</td>
        <td>$status</td>
    </tr>";
}

$mahasiswa = [
    [
        "nama"  => "muhammad mad",
        "tugas" => 80,
        "uts"   => 75,
        "uas"   => 82
    ],
    [
        "nama"  => "helmi am",
        "tugas" => 85,
        "uts"   => 90,
        "uas"   => 95
    ],
    [
        "nama"  => "ali m",
        "tugas" => 72,
        "uts"   => 75,
        "uas"   => 60
    ]
];
?>

<!DOCTYPE HTML>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Hasil Nilai Mahasiswa</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #020a31;
            padding: 30px;
        }

        h2 {
            text-align: center;
            color:  #f5f9f6
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background-color: white;
        }

        th, td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: center;
        }

        th {
            background-color: #3c3d8b;
            color: white;
        }

        tr:nth-child(even) {
            background-color: rgba(230, 141, 9, 0.33)
        }
    </style>
</head>
<body>

    <h2>Daftar Nilai Mahasiswa</h2>
    <table>
        <tr>
            <th>Nama</th>
            <th>Tugas</th>
            <th>UTS</th>
            <th>UAS</th>
            <th>Nilai Akhir</th>
            <th>Grade</th>
            <th>Status</th>
        </tr>

        <?php
        foreach ($mahasiswa as $mhs) {
            tampilkanHasil(
                $mhs["nama"],
                $mhs["tugas"],
                $mhs["uts"],
                $mhs["uas"]
            );
        }
        ?>
    </table>
</body>
</html>