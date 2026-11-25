<?php

$tugas = 80;
$uts = 75;
$uas = 90;

$NilaiAkhir =
    ($tugas * 0.30) +
    ($uts * 0.30) +
    ($uas * 0.40);

echo "Nilai Tugas : " . $tugas . "<br>";
echo "Nilai uts   : " . $uts . "<br>";
echo "Nilai uas   : " . $uas . "<br>";
echo "Nilai Akhir : " . $NilaiAkhir . "<br>";

$lulus = $NilaiAkhir >= 75;

echo "apakah Nilai Akhir >= 75? ";
var_dump($lulus);

?>