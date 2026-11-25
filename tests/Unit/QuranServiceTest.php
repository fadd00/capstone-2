<?php

namespace Tests\Unit;

use App\Services\QuranService;
use Tests\TestCase;

class QuranServiceTest extends TestCase
{
    public function test_quran_reference_data_is_consistent(): void
    {
        $this->assertCount(114, config('quran.ayat'));
        $this->assertSame(6236, array_sum(config('quran.ayat')));
        $this->assertCount(30, config('quran.juz'));
    }

    public function test_juz_boundaries(): void
    {
        $quran = new QuranService;

        $this->assertSame(1, $quran->juzOf(1, 1));
        $this->assertSame(1, $quran->juzOf(2, 141));
        $this->assertSame(2, $quran->juzOf(2, 142));
        $this->assertSame(3, $quran->juzOf(3, 92));
        $this->assertSame(4, $quran->juzOf(3, 93));
        $this->assertSame(15, $quran->juzOf(18, 74));
        $this->assertSame(16, $quran->juzOf(18, 75));
        $this->assertSame(29, $quran->juzOf(77, 50));
        $this->assertSame(30, $quran->juzOf(114, 6));
    }

    public function test_surah_lookup(): void
    {
        $quran = new QuranService;

        $this->assertSame(2, $quran->surahNumber('Al-Baqarah'));
        $this->assertNull($quran->surahNumber('Surat Tidak Valid'));
        $this->assertSame(286, $quran->ayatCount(2));
        $this->assertSame(6, $quran->ayatCount(114));
    }
}
