<?php

echo "<h2>Deret Angka 1 sampai 10</h2>";

$i = 1;

while ($i <= 10) {
    if ($i % 2 == 0) {
        echo "$i = Genap<br>";
    } else {
        echo "$i = Ganjil<br>";
    }

    $i++;
}
?>