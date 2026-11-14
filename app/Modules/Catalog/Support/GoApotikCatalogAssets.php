<?php

namespace App\Modules\Catalog\Support;

use Illuminate\Support\Str;

/**
 * Helper mapping untuk ikon kategori, subkategori, badge golongan obat,
 * serta SVG UI resmi dari GoApotik (disimpan lokal di public/images/goapotik).
 */
final class GoApotikCatalogAssets
{
    /**
     * Mengembalikan konfigurasi ikon untuk kategori induk katalog.
     *
     * @return array{url: string, svg_url: string, svg: string, label: string}
     */
    public static function categoryIcon(string $name): array
    {
        $normalized = strtolower(trim($name));

        $map = [
            'obat' => [
                'file' => 'obat',
                'label' => 'Obat',
                'svg' => '<svg class="w-6 h-6 text-emerald-700" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>',
            ],
            'suplemen' => [
                'file' => 'suplemen',
                'label' => 'Suplemen',
                'svg' => '<svg class="w-6 h-6 text-emerald-700" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>',
            ],
            'nutrisi' => [
                'file' => 'nutrisi',
                'label' => 'Nutrisi',
                'svg' => '<svg class="w-6 h-6 text-emerald-700" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>',
            ],
            'herbal' => [
                'file' => 'herbal',
                'label' => 'Herbal',
                'svg' => '<svg class="w-6 h-6 text-emerald-700" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13c0-4.97 4.03-9 9-9 4.97 0 5 4.03 5 9 0 4.97-4.03 7-9 7s-5-2.03-5-7z"/></svg>',
            ],
            'ibu & anak' => [
                'file' => 'ibu-dan-anak',
                'label' => 'Ibu & Anak',
                'svg' => '<svg class="w-6 h-6 text-emerald-700" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>',
            ],
            'alat kesehatan' => [
                'file' => 'alat-kesehatan',
                'label' => 'Alat Kesehatan',
                'svg' => '<svg class="w-6 h-6 text-emerald-700" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>',
            ],
            'perawatan diri' => [
                'file' => 'perawatan-diri',
                'label' => 'Perawatan Diri',
                'svg' => '<svg class="w-6 h-6 text-emerald-700" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>',
            ],
            'mata' => [
                'file' => 'mata',
                'label' => 'Mata',
                'svg' => '<svg class="w-6 h-6 text-emerald-700" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>',
            ],
        ];

        $entry = $map[$normalized] ?? $map['obat'];

        return [
            'url' => asset("images/goapotik/categories/{$entry['file']}.png"),
            'svg_url' => asset("images/goapotik/svg/cat-{$entry['file']}.svg"),
            'svg' => $entry['svg'],
            'label' => $entry['label'],
        ];
    }

    /**
     * Mengembalikan ikon GoApotik untuk setiap subkategori di database.
     *
     * @return array{url: string, svg_url: string, svg: string}
     */
    public static function subcategoryIcon(string $subName, ?string $catName = null): array
    {
        $sub = strtolower(trim($subName));
        $cat = strtolower(trim((string) $catName));

        $subSlug = Str::slug($subName);
        $catFolder = match ($cat) {
            'suplemen' => 'suplemen',
            'nutrisi' => 'nutrisi',
            'herbal' => 'herbal',
            'ibu & anak' => 'ibu-dan-anak',
            'alat kesehatan' => 'alat-kesehatan',
            'perawatan diri' => 'perawatan-diri',
            'mata' => 'mata',
            default => 'obat',
        };

        // 1. Cek kecocokan langsung ke aset subkategori GoApotik
        $directSubFile = "images/goapotik/subcategories/{$catFolder}--{$subSlug}.png";
        $directSvgFile = "images/goapotik/svg/sub-{$catFolder}--{$subSlug}.svg";
        if (file_exists(public_path($directSubFile))) {
            return [
                'url' => asset($directSubFile),
                'svg_url' => asset($directSvgFile),
                'svg' => self::fallbackSubcategorySvg($sub),
            ];
        }

        // 2. Pemetaan sinonim subkategori database -> aset GoApotik resmi
        $aliasMap = [
            // Obat
            'pereda-demam-nyeri' => ['subcategories/obat--kepala.png', 'svg/diag-anti-nyeri.svg'],
            'batuk-flu' => ['diagnosis/batuk-flu.png', 'svg/diag-batuk-flu.svg'],
            'flu-batuk-pernapasan' => ['diagnosis/batuk-flu.png', 'svg/diag-batuk-flu.svg'],
            'antibiotik' => ['diagnosis/antibiotik.png', 'svg/diag-antibiotik.svg'],
            'jantung-hipertensi-diabetes' => ['diagnosis/jantung.png', 'svg/diag-jantung.svg'],
            'lambung-pencernaan' => ['subcategories/obat--obat-saluran-pencernaan.png', 'svg/sub-obat--obat-saluran-pencernaan.svg'],
            'saluran-pencernaan-lambung' => ['subcategories/obat--obat-saluran-pencernaan.png', 'svg/sub-obat--obat-saluran-pencernaan.svg'],
            'obat-kulit-topikal' => ['subcategories/obat--kulit.png', 'svg/sub-obat--kulit.svg'],
            'hormon-kontrasepsi' => ['subcategories/obat--hormon.png', 'svg/sub-obat--hormon.svg'],
            'anti-nyeri-sendi' => ['subcategories/obat--otot-sendi-tulang.png', 'svg/sub-obat--otot-sendi-tulang.svg'],
            'obat-umum-lainnya' => ['subcategories/obat--obat-lainnya.png', 'svg/sub-obat--obat-lainnya.svg'],
            'alergi-anti-inflamasi' => ['subcategories/obat--tubuh.png', 'svg/sub-obat--tubuh.svg'],
            'mata' => ['categories/mata.png', 'svg/cat-mata.svg'],
            'tradisional' => ['subcategories/herbal--tradisional.png', 'svg/sub-herbal--tradisional.svg'],
            'perawatan-tubuh-bayi' => ['subcategories/ibu-dan-anak--perawatan-tubuh-bayi.png', 'svg/sub-ibu-dan-anak--perawatan-tubuh-bayi.svg'],

            // Suplemen
            'multivitamin-mineral' => ['subcategories/suplemen--kesehatan-tubuh.png', 'svg/sub-suplemen--kesehatan-tubuh.svg'],
            'multivitamin-suplemen' => ['categories/suplemen.png', 'svg/cat-suplemen.svg'],
            'daya-tahan-tubuh-vitamin-c' => ['subcategories/suplemen--kesehatan-tubuh.png', 'svg/sub-suplemen--kesehatan-tubuh.svg'],
            'kesehatan-tulang-sendi' => ['subcategories/suplemen--tulang.png', 'svg/sub-suplemen--tulang.svg'],
            'kesehatan-darah-stamina' => ['subcategories/suplemen--kesehatan-darah.png', 'svg/sub-suplemen--kesehatan-darah.svg'],
            'kehamilan-menyusui' => ['subcategories/suplemen--kehamilan-menyusui-asi.png', 'svg/sub-suplemen--kehamilan-menyusui-asi.svg'],
            'makanan-pengganti-makanan' => ['subcategories/nutrisi--makanan-pengganti-makanan.png', 'svg/sub-nutrisi--makanan-pengganti-makanan.svg'],

            // Nutrisi
            'madu-alami' => ['subcategories/nutrisi--madu.png', 'svg/sub-nutrisi--madu.svg'],
            'madu-propolis' => ['subcategories/nutrisi--madu.png', 'svg/sub-nutrisi--madu.svg'],
            'nutrisi-khusus-diabetes' => ['diagnosis/diabetes.png', 'svg/diag-diabetes.svg'],
            'makanan-camilan-sehat' => ['subcategories/nutrisi--makanan-pengganti-makanan.png', 'svg/sub-nutrisi--makanan-pengganti-makanan.svg'],
            'makanan-minuman-sehat' => ['subcategories/nutrisi--minuman.png', 'svg/sub-nutrisi--minuman.svg'],
            'susu-pertumbuhan-anak' => ['subcategories/nutrisi--susu-untuk-anak-remaja.png', 'svg/sub-nutrisi--susu-untuk-anak-remaja.svg'],
            'susu-dewasa-lansia' => ['subcategories/nutrisi--susu-untuk-dewasa-lanjut-usia.png', 'svg/sub-nutrisi--susu-untuk-dewasa-lanjut-usia.svg'],
            'nutrisi-medis-lansia' => ['subcategories/nutrisi--susu-untuk-dewasa-lanjut-usia.png', 'svg/sub-nutrisi--susu-untuk-dewasa-lanjut-usia.svg'],
            'susu-bayi-anak' => ['subcategories/nutrisi--susu-bayi.png', 'svg/sub-nutrisi--susu-bayi.svg'],

            // Herbal
            'herbal-masuk-angin' => ['subcategories/herbal--herbal.png', 'svg/sub-herbal--herbal.svg'],
            'kesehatan-liver-nafsu-makan' => ['subcategories/suplemen--hati-liver.png', 'svg/sub-suplemen--hati-liver.svg'],
            'daya-tahan-tubuh-herbal' => ['subcategories/herbal--herbal.png', 'svg/sub-herbal--herbal.svg'],
            'jamu-obat-herbal' => ['subcategories/herbal--tradisional.png', 'svg/sub-herbal--tradisional.svg'],
            'minyak-angin-aromaterapi' => ['subcategories/herbal--herbal.png', 'svg/sub-herbal--herbal.svg'],
            'otot-sendi-tulang' => ['subcategories/obat--otot-sendi-tulang.png', 'svg/sub-obat--otot-sendi-tulang.svg'],

            // Ibu & Anak
            'minyak-telon-hangat' => ['subcategories/ibu-dan-anak--perawatan-tubuh-bayi.png', 'svg/sub-ibu-dan-anak--perawatan-tubuh-bayi.svg'],
            'perawatan-rambut-anak' => ['subcategories/perawatan-diri--perawatan-rambut.png', 'svg/sub-perawatan-diri--perawatan-rambut.svg'],
            'bedak-ruam-bayi' => ['subcategories/ibu-dan-anak--perawatan-tubuh-bayi.png', 'svg/sub-ibu-dan-anak--perawatan-tubuh-bayi.svg'],
            'keperluan-ibu-bayi' => ['categories/ibu-dan-anak.png', 'svg/cat-ibu-dan-anak.svg'],
            'perawatan-bayi-demam' => ['diagnosis/demam.png', 'svg/diag-demam.svg'],

            // Alat Kesehatan
            'plester-kompres' => ['subcategories/alat-kesehatan--barang-disposable-sekali-pakai.png', 'svg/sub-alat-kesehatan--barang-disposable-sekali-pakai.svg'],
            'alat-cek-diagnostik-medis' => ['subcategories/alat-kesehatan--alat-tes-gula-darah-asam-urat-kolesterol.png', 'svg/sub-alat-kesehatan--alat-tes-gula-darah-asam-urat-kolesterol.svg'],
            'alat-bantu-kesehatan' => ['subcategories/alat-kesehatan--body-support.png', 'svg/sub-alat-kesehatan--body-support.svg'],
            'p3k-barang-sekali-pakai' => ['subcategories/alat-kesehatan--barang-disposable-sekali-pakai.png', 'svg/sub-alat-kesehatan--barang-disposable-sekali-pakai.svg'],

            // Perawatan Diri
            'perawatan-kulit-wajah' => ['subcategories/perawatan-diri--perawatan-kulit-skincare.png', 'svg/sub-perawatan-diri--perawatan-kulit-skincare.svg'],
            'kebersihan-tubuh' => ['subcategories/perawatan-diri--kebersihan-diri.png', 'svg/sub-perawatan-diri--kebersihan-diri.svg'],
            'tabir-surya-sunscreen' => ['subcategories/perawatan-diri--perawatan-kulit-skincare.png', 'svg/sub-perawatan-diri--perawatan-kulit-skincare.svg'],
            'deodoran-kesegaran' => ['subcategories/perawatan-diri--kebersihan-diri.png', 'svg/sub-perawatan-diri--kebersihan-diri.svg'],
            'perawatan-kulit-tubuh' => ['subcategories/perawatan-diri--perawatan-kulit-skincare.png', 'svg/sub-perawatan-diri--perawatan-kulit-skincare.svg'],

            // Mata
            'obat-tetes-mata' => ['subcategories/mata--mata-kering-iritasi-ringan.png', 'svg/sub-mata--mata-kering-iritasi-ringan.svg'],
            'infeksi-obat-mata' => ['subcategories/mata--infeksi-mata.png', 'svg/sub-mata--infeksi-mata.svg'],
        ];

        if (isset($aliasMap[$subSlug])) {
            [$imgRel, $svgRel] = $aliasMap[$subSlug];

            return [
                'url' => asset("images/goapotik/{$imgRel}"),
                'svg_url' => asset("images/goapotik/{$svgRel}"),
                'svg' => self::fallbackSubcategorySvg($sub),
            ];
        }

        // 3. Fallback ke ikon kategori induk
        $catAsset = self::categoryIcon($catName ?: 'Obat');

        return [
            'url' => $catAsset['url'],
            'svg_url' => $catAsset['svg_url'],
            'svg' => self::fallbackSubcategorySvg($sub),
        ];
    }

    /**
     * Mengembalikan badge golongan obat BPOM resmi dari GoApotik.
     *
     * @return array{type: string, title: string, url: string, svg_url: string, label: string}
     */
    public static function drugClassBadge(?string $badgeStatus, bool $wajibResep = false, ?string $kategoriName = null): array
    {
        $statusLower = strtolower((string) $badgeStatus);
        $katLower = strtolower((string) $kategoriName);

        if (str_contains($statusLower, 'keras') || $wajibResep) {
            return [
                'type' => 'keras',
                'title' => 'Obat Keras (Wajib Resep)',
                'url' => asset('images/goapotik/badges/obat-keras.png'),
                'svg_url' => asset('images/goapotik/svg/badge-obat-keras.svg'),
                'label' => 'K',
            ];
        }

        if (str_contains($statusLower, 'terbatas') || str_contains($katLower, 'terbatas')) {
            return [
                'type' => 'terbatas',
                'title' => 'Obat Bebas Terbatas',
                'url' => asset('images/goapotik/badges/obat-bebas-terbatas.png'),
                'svg_url' => asset('images/goapotik/svg/badge-obat-bebas-terbatas.svg'),
                'label' => 'T',
            ];
        }

        if (str_contains($statusLower, 'fitofarmaka')) {
            return [
                'type' => 'fitofarmaka',
                'title' => 'Fitofarmaka',
                'url' => asset('images/goapotik/badges/fitofarmaka.png'),
                'svg_url' => asset('images/goapotik/svg/badge-fitofarmaka.svg'),
                'label' => 'F',
            ];
        }

        if (str_contains($statusLower, 'jamu') || str_contains($statusLower, 'herbal') || str_contains($katLower, 'herbal')) {
            return [
                'type' => 'jamu',
                'title' => 'Jamu / Herbal Terstandar',
                'url' => asset('images/goapotik/badges/jamu.png'),
                'svg_url' => asset('images/goapotik/svg/badge-jamu.svg'),
                'label' => 'J',
            ];
        }

        return [
            'type' => 'bebas',
            'title' => 'Obat Bebas',
            'url' => asset('images/goapotik/badges/obat-bebas.png'),
            'svg_url' => asset('images/goapotik/svg/badge-obat-bebas.svg'),
            'label' => 'B',
        ];
    }

    /**
     * Mengembalikan inline SVG UI asli dari bundle GoApotik.
     */
    public static function uiSvg(string $name, string $class = 'w-5 h-5'): string
    {
        return match ($name) {
            'checkout_resep' => '<svg class="'.$class.'" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path fill="currentColor" fill-rule="evenodd" d="M3.922 7c0-.957.001-1.624.069-2.128.066-.49.186-.748.37-.933.185-.184.444-.304.933-.37C5.798 3.5 6.465 3.5 7.422 3.5h7.905a3.35 3.35 0 0 0-.968 2.36v14.435l-2.194-.767a.5.5 0 0 0-.33 0l-2.694.942-2.694-.942a.5.5 0 0 0-.33 0l-2.195.767zM15.36 5.86a2.36 2.36 0 1 1 4.719 0v2.283c0 .177 0 .274-.008.343v.006h-.007c-.069.007-.166.008-.342.008h-4.362zm0 15.14V9.5h4.38c.151 0 .298 0 .42-.012.137-.013.297-.045.45-.141a1 1 0 0 0 .315-.315c.097-.154.128-.314.142-.45.012-.123.012-.27.012-.42V5.859a3.36 3.36 0 0 0-3.36-3.359H7.386c-.912 0-1.647 0-2.225.078-.6.08-1.105.253-1.507.654-.4.402-.573.907-.654 1.507-.078.578-.078 1.313-.078 2.224V21a.5.5 0 0 0 .665.472l2.694-.942 2.695.942a.5.5 0 0 0 .33 0L12 20.53l2.694.942A.5.5 0 0 0 15.36 21M5.88 6.5a.5.5 0 1 0 0 1h5.358a.5.5 0 1 0 0-1zM7.72 11a.5.5 0 0 1-.5.5H5.88a.5.5 0 0 1 0-1h1.34a.5.5 0 0 1 .5.5m-1.84 3.5a.5.5 0 0 0 0 1h4.018a.5.5 0 0 0 0-1z" clip-rule="evenodd"/></svg>',
            'icon_camera' => '<svg class="'.$class.'" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32 32" fill="none" aria-hidden="true"><path fill="currentColor" fill-rule="evenodd" d="M23.683 3.965c.368 0 .667.299.667.667v2.666h2.667a.667.667 0 1 1 0 1.334H24.35v2.667a.667.667 0 0 1-1.334 0V8.632H20.35a.667.667 0 0 1 0-1.334h2.666V4.632c0-.368.299-.667.667-.667m-11.029.702h-.039c-.29 0-.49 0-.68.021-.986.113-1.87.66-2.413 1.492-.104.16-.193.338-.323.598l-.018.035-1.094 2.189A1.8 1.8 0 0 1 6.472 10a3.14 3.14 0 0 0-3.139 3.139v6.706c0 1.306 0 2.336.08 3.158.082.84.251 1.535.636 2.146a4.7 4.7 0 0 0 1.468 1.47c.612.384 1.307.553 2.147.635.822.08 1.851.08 3.158.08h9.229c1.842 0 3.293 0 4.427-.153 1.163-.156 2.092-.484 2.822-1.215.73-.73 1.058-1.658 1.214-2.821.153-1.134.153-2.585.153-4.428V14h-2c-1.887 0-3.251 0-4.314-.109-1.052-.107-1.734-.313-2.279-.677a4.7 4.7 0 0 1-1.288-1.288c-.364-.545-.57-1.227-.677-2.279C18 8.585 18 7.22 18 5.333v-.666zm-1.32 12.746a4.667 4.667 0 1 1 9.333 0 4.667 4.667 0 0 1-9.334 0" clip-rule="evenodd"/></svg>',
            'ic_search_outline' => '<svg class="'.$class.'" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path fill="currentColor" fill-rule="evenodd" d="M4.062 10.742a6.683 6.683 0 1 1 13.366 0 6.683 6.683 0 0 1-13.366 0m6.683-7.683a7.683 7.683 0 1 0 5.1 13.429l4.274 4.272a.5.5 0 0 0 .707-.707l-4.277-4.277A7.683 7.683 0 0 0 10.745 3.06" clip-rule="evenodd"/></svg>',
            'ic_official_fill' => '<svg class="'.$class.'" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path fill="currentColor" fill-rule="evenodd" d="M10.42 2.53a2.616 2.616 0 0 1 3.16 0c.245.187.477.469.82.885l.05.06c.191.234.258.313.327.378.278.26.64.41 1.02.422.095.003.198-.006.499-.035l.078-.007c.536-.053.9-.088 1.206-.046a2.616 2.616 0 0 1 2.233 2.233c.042.307.007.67-.046 1.206l-.007.078c-.03.3-.038.404-.035.499.012.38.163.742.422 1.02.065.069.144.136.378.328l.06.05c.416.342.698.573.885.82a2.616 2.616 0 0 1 0 3.158c-.187.246-.469.478-.885.82l-.06.05a4 4 0 0 0-.378.328c-.26.278-.41.64-.422 1.02-.003.095.006.198.035.499l.007.078c.053.536.088.9.046 1.206a2.616 2.616 0 0 1-2.233 2.233c-.307.042-.67.007-1.206-.046l-.078-.007c-.3-.03-.404-.038-.498-.035a1.57 1.57 0 0 0-1.021.422 4 4 0 0 0-.328.378l-.05.06c-.342.416-.573.698-.82.885a2.616 2.616 0 0 1-3.158 0c-.246-.187-.478-.469-.82-.885l-.05-.06a4 4 0 0 0-.328-.378 1.57 1.57 0 0 0-1.02-.422 4 4 0 0 0-.499.035l-.078.007c-.536.053-.9.088-1.206.046a2.616 2.616 0 0 1-2.233-2.233c-.042-.307-.007-.67.046-1.206l.007-.078c.03-.3.038-.404.035-.498a1.57 1.57 0 0 0-.422-1.021 4 4 0 0 0-.378-.328l-.06-.05c-.416-.342-.698-.573-.885-.82a2.616 2.616 0 0 1 0-3.158c.187-.246.469-.478.885-.82l.06-.05c.234-.192.313-.26.378-.328.26-.278.41-.64.422-1.02a4 4 0 0 0-.035-.499l-.007-.078c-.053-.536-.088-.9-.046-1.206A2.616 2.616 0 0 1 6.42 4.187c.307-.042.67-.007 1.206.046l.078.007c.3.03.404.038.499.035a1.57 1.57 0 0 0 1.02-.422c.069-.065.136-.144.328-.378l.05-.06c.342-.416.573-.698.82-.885m6.546 6.632a1.046 1.046 0 1 0-1.631-1.31l-4.489 5.585-2.314-2.4a1.046 1.046 0 1 0-1.507 1.452l3.14 3.256a1.046 1.046 0 0 0 1.569-.07z" clip-rule="evenodd"/></svg>',
            'filter' => '<svg class="'.$class.'" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" clip-rule="evenodd" d="M14.5 8C14.5 6.61929 15.6193 5.5 17 5.5C18.3807 5.5 19.5 6.61929 19.5 8C19.5 9.38071 18.3807 10.5 17 10.5C15.6193 10.5 14.5 9.38071 14.5 8ZM17 4.5C15.2368 4.5 13.7781 5.80385 13.5354 7.5L4 7.5C3.72386 7.5 3.5 7.72386 3.5 8C3.5 8.27614 3.72386 8.5 4 8.5L13.5354 8.5C13.7781 10.1961 15.2368 11.5 17 11.5C18.933 11.5 20.5 9.933 20.5 8C20.5 6.067 18.933 4.5 17 4.5ZM20 15.5C20.2761 15.5 20.5 15.7239 20.5 16C20.5 16.2761 20.2761 16.5 20 16.5L10.4646 16.5C10.2219 18.1961 8.76324 19.5 7 19.5C5.067 19.5 3.5 17.933 3.5 16C3.5 14.067 5.067 12.5 7 12.5C8.76324 12.5 10.2219 13.8039 10.4646 15.5L20 15.5ZM7 13.5C5.61929 13.5 4.5 14.6193 4.5 16C4.5 17.3807 5.61929 18.5 7 18.5C8.38071 18.5 9.5 17.3807 9.5 16C9.5 14.6193 8.38071 13.5 7 13.5Z"/></svg>',
            default => '',
        };
    }

    private static function fallbackSubcategorySvg(string $subNameLower): string
    {
        $path = match (true) {
            str_contains($subNameLower, 'darah') || str_contains($subNameLower, 'stamina') || str_contains($subNameLower, 'jantung') => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>',
            str_contains($subNameLower, 'demam') || str_contains($subNameLower, 'nyeri') || str_contains($subNameLower, 'kepala') => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>',
            str_contains($subNameLower, 'mata') => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>',
            default => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 5l7 7-7 7"/>',
        };

        return '<svg class="w-4 h-4 text-emerald-700" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">' . $path . '</svg>';
    }
}
