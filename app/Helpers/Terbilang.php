<?php
namespace App\Helpers;

class Terbilang
{
    public static function make($nilai)
    {
        $huruf = array("", "Satu", "Dua", "Tiga", "Empat", "Lima", "Enam", "Tujuh", "Delapan", "Sembilan", "Sepuluh", "Sebelas");

        if ($nilai < 12)
            return " " . $huruf[$nilai];

        elseif ($nilai < 20)
            return self::make($nilai - 10) . " Belas";

        elseif ($nilai < 100)
            return self::make($nilai / 10) . " Puluh" . self::make($nilai % 10);

        elseif ($nilai < 200)
            return " Seratus" . self::make($nilai - 100);

        elseif ($nilai < 1000)
            return self::make($nilai / 100) . " Ratus" . self::make($nilai % 100);

        elseif ($nilai < 2000)
            return " Seribu" . self::make($nilai - 1000);

        elseif ($nilai < 1000000)
            return self::make($nilai / 1000) . " Ribu" . self::make($nilai % 1000);

        elseif ($nilai < 1000000000)
            return self::make($nilai / 1000000) . " Juta" . self::make($nilai % 1000000);
    }
}
