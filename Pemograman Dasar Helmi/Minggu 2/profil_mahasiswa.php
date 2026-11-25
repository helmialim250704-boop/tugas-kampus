<?php
echo"<h1>PROFIL MAHASISWA</h1>";

$nama = "MUHAMMAD HELMI ALIM MUSTOFA";
$nim = "E41263240";
$prodi = "Teknik Informatika";
$umur = 22;
$ipk = 4.00;
$statusaktif = true;

echo "Nama Lengkap: " . $nama . "<br>";
echo "NIM: " . $nim . "<br>";
echo "prodi: " . $prodi."<br>";
echo "Umur: " . $umur . " tahun<br>";
echo "IPK: " . $ipk . "<br>";
echo "status aktif: " . ($statusaktif ?
"Aktif" : "Tidak Aktif"). "<br>";

echo "<h1>TIPE DATA VARIABEL</h1>";

var_dump($nama);
echo "<br>";
var_dump($nim);
echo "<br>";
var_dump($prodi);
echo "<br>";
var_dump($umur);
echo "<br>";
var_dump($ipk);
echo "<br>";
var_dump($statusaktif);
echo "<br>";
?>