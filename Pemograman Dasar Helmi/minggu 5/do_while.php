<?php

echo "<h2>Deret Angka 10 sampai 1</h2>";

$i = 10;

do {
    if ($i % 2 == 0) {
        echo "$i = Genap<br>";
    } else {
        echo "$i = Ganjil<br>";
    }

    $i--;
} while ($i >= 1);

?>