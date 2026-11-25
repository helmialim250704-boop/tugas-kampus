<?php

    $nilai = 78;

    if ($nilai >= 85) {
        $grade = "A";
    }elseif ($nilai >= 70) {
        $grade = "B";
    }elseif ($nilai >= 60) {
        $grade = "C";
    }elseif ($nilai >= 50) {
        $grade = "D";
    }else {
        $grade = "E";
    }

    echo "Nilai: " . $nilai . "<br>";
    echo "Grade: " . $grade;
?>