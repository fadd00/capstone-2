<?php

namespace App\Services;

class QuranService
{
    /** Nomor surat (1-114) dari nama di config/quran.php, atau null jika tidak dikenal. */
    public function surahNumber(string $surah): ?int
    {
        $index = array_search($surah, config('quran.surah'), true);

        return $index === false ? null : $index + 1;
    }

    public function ayatCount(int $surahNumber): int
    {
        return config('quran.ayat')[$surahNumber - 1];
    }

    /** Juz tempat ayat tertentu berada. */
    public function juzOf(int $surahNumber, int $ayat): int
    {
        $juz = 1;
        foreach (config('quran.juz') as $index => [$startSurah, $startAyat]) {
            if ([$surahNumber, $ayat] >= [$startSurah, $startAyat]) {
                $juz = $index + 1;
            }
        }

        return $juz;
    }
}
