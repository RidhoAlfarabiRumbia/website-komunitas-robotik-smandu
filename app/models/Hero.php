<?php

class Hero extends Model
{
    protected static string $table = 'hero';
    protected static array $fillable = ['foto', 'status'];

    public static function tampil(): array
    {
        // Fitur "urutan" sudah dihapus dari admin: slider tampil sesuai urutan
        // input (id ASC), status tetap menyaring baris yang disembunyikan.
        return static::where('status', 'Tampil', 'id ASC');
    }

    /**
     * Get slider data with side photos shuffled for variety.
     * Returns array with 'slides' (first slide data) and 'deferred' (remaining slides for lazy load).
     */
    public static function getSliderData(): array
    {
        $slides = static::tampil();
        $nSlides = count($slides);
        if ($nSlides === 0) {
            return ['slides' => [], 'deferred' => []];
        }

        // Build pool of unique photos
        $fotoPool = [];
        $pusatList = [];
        foreach ($slides as $i => $hs) {
            $f = $hs['foto'] ?? '';
            $pusatList[$i] = $f !== '' ? $f : 'assets/img/hero-illustration.svg';
            if ($f !== '' && !in_array($f, $fotoPool, true)) {
                $fotoPool[] = $f;
            }
        }

        // Pick side photos with variety constraints
        $fotoSamping = [];
        $sampingKiriSebelum = null;
        $sampingKananSebelum = null;
        for ($i = 0; $i < $nSlides; $i++) {
            $pusat = $pusatList[$i];
            $pusatSebelum = $pusatList[($i - 1 + $nSlides) % $nSlides];
            $pusatSesudah = $pusatList[($i + 1) % $nSlides];
            $pasanganSebelum = [$sampingKiriSebelum, $sampingKananSebelum];

            $tingkat = [
                ['tolak' => [$pusatSebelum, $pusat, $pusatSesudah, $sampingKiriSebelum, $sampingKananSebelum], 'jagaKolom' => true],
                ['tolak' => [$pusatSebelum, $pusat, $pusatSesudah, $sampingKiriSebelum, $sampingKananSebelum], 'jagaKolom' => false],
                ['tolak' => [$pusat, $pusatSesudah, $sampingKiriSebelum, $sampingKananSebelum], 'jagaKolom' => true],
                ['tolak' => [$pusat, $pusatSesudah, $sampingKiriSebelum, $sampingKananSebelum], 'jagaKolom' => false],
                ['tolak' => [$pusat, $pusatSesudah], 'jagaKolom' => true],
                ['tolak' => [$pusat, $pusatSesudah], 'jagaKolom' => false],
                ['tolak' => [$pusat], 'jagaKolom' => true],
            ];

            $pilih = null;
            foreach ($tingkat as $level) {
                $tolak = array_values(array_filter($level['tolak'], static fn ($x) => $x !== null));
                $jagaKolom = $level['jagaKolom'];
                $kiri = array_values(array_filter($fotoPool, static function ($f) use ($tolak, $jagaKolom, $sampingKananSebelum) {
                    return !in_array($f, $tolak, true) && !($jagaKolom && $f === $sampingKananSebelum);
                }));
                $kanan = array_values(array_filter($fotoPool, static function ($f) use ($tolak, $jagaKolom, $sampingKiriSebelum) {
                    return !in_array($f, $tolak, true) && !($jagaKolom && $f === $sampingKiriSebelum);
                }));

                $pasangan = [];
                foreach ($kiri as $lk) {
                    foreach ($kanan as $rk) {
                        if ($lk !== $rk) {
                            $pasangan[] = [$lk, $rk];
                        }
                    }
                }
                if ($pasangan === []) continue;

                $skor = static function ($p) use ($pasanganSebelum) {
                    return (in_array($p[0], $pasanganSebelum, true) ? 1 : 0)
                         + (in_array($p[1], $pasanganSebelum, true) ? 1 : 0);
                };
                $terendah = min(array_map($skor, $pasangan));
                $kandidat = array_values(array_filter($pasangan, static fn ($p) => $skor($p) === $terendah));
                shuffle($kandidat);
                $pilih = $kandidat[0];
                break;
            }

            if ($pilih === null) {
                $pilih = [$pusat, $pusat];
            }
            $fotoSamping[$i] = $pilih;
            $sampingKiriSebelum = $pilih[0];
            $sampingKananSebelum = $pilih[1];
        }

        // Build result
        $result = ['slides' => [], 'deferred' => []];
        foreach ($slides as $i => $h) {
            $foto = $h['foto'] ?? 'assets/img/hero-illustration.svg';
            $fotoKiri = $fotoSamping[$i][0] ?? $foto;
            $fotoKanan = $fotoSamping[$i][1] ?? $foto;

            $slideData = [
                'img' => $foto,
                'kiri' => $fotoKiri,
                'kanan' => $fotoKanan,
            ];

            if ($i === 0) {
                $result['slides'][] = $slideData;
            } else {
                $result['deferred'][] = $slideData;
            }
        }

        return $result;
    }
}