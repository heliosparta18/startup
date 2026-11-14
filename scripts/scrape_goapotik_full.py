import urllib.request
import urllib.parse
import json
import re
import ssl
import time
import os
import sys
from concurrent.futures import ThreadPoolExecutor, as_completed

ctx = ssl.create_default_context()
ctx.check_hostname = False
ctx.verify_mode = ssl.CERT_NONE

HEADERS = {
    'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
    'Accept': 'application/json, text/plain, */*',
    'Referer': 'https://m.goapotik.com/'
}

# 8 Kategori resmi GoApotik & mapping ke nama ramah web katalog
CATEGORIES = [
    {
        'seo_title': 'obat',
        'category_id': 839,
        'nama_kategori': 'Obat',
        'subcats': [
            {'seo': 'obat-kepala', 'id': 1610, 'name': 'Pereda Demam & Sakit Kepala'},
            {'seo': 'obat-gangguan-saluran-pencernaan', 'id': 1826, 'name': 'Saluran Pencernaan & Lambung'},
            {'seo': 'obat-telinga-hidung-dan-tenggorokan', 'id': 1840, 'name': 'Flu, Batuk & Tenggorokan'},
            {'seo': 'obat-otot-sendi-dan-tulang', 'id': 1843, 'name': 'Otot, Sendi & Tulang'},
            {'seo': 'jual-obat-untuk-kulit', 'id': 1583, 'name': 'Salep & Obat Kulit'},
        ]
    },
    {
        'seo_title': 'suplemen',
        'category_id': 840,
        'nama_kategori': 'Suplemen',
        'subcats': [
            {'seo': 'suplemen-dan-vitamin-tubuh', 'id': 1900, 'name': 'Daya Tahan Tubuh & Multivitamin'},
            {'seo': 'suplemen-dan-vitamin-kulit', 'id': 1888, 'name': 'Kesehatan Kulit & Antioksidan'},
            {'seo': 'suplemen-dan-vitamin-tulang', 'id': 1931, 'name': 'Kalsium & Tulang'},
            {'seo': 'suplemen-dan-vitamin-otak', 'id': 1895, 'name': 'Nutrisi Otak & Daya Ingat'},
            {'seo': 'suplemen-dan-vitamin-kehamilan-dan-menyusui-asi', 'id': 1913, 'name': 'Suplemen Ibu Hamil & Menyusui'},
        ]
    },
    {
        'seo_title': 'nutrisi',
        'category_id': 841,
        'nama_kategori': 'Nutrisi',
        'subcats': [
            {'seo': 'nutrisi-madu', 'id': 1639, 'name': 'Madu & Propolis'},
            {'seo': 'nutrisi-susu-bayi', 'id': 1636, 'name': 'Susu Formula Bayi & Balita'},
            {'seo': 'nutrisi-susu-masa-hamil-dan-menyusui', 'id': 1635, 'name': 'Susu Ibu Hamil & Menyusui'},
            {'seo': 'makanan-pengganti-makanan', 'id': 2038, 'name': 'Nutrisi Medis Khusus & Lansia'},
            {'seo': 'nutrisi-minuman', 'id': 1906, 'name': 'Minuman Bernutrisi & Isotonik'},
        ]
    },
    {
        'seo_title': 'herbal',
        'category_id': 842,
        'nama_kategori': 'Herbal',
        'subcats': [
            {'seo': 'herbal-dan-tradisional-obat-herbal', 'id': 1804, 'name': 'Obat Herbal Terstandar'},
            {'seo': 'herbal-dan-tradisional-obat-tradisional', 'id': 1805, 'name': 'Jamu & Minyak Herbal Tradisional'},
            {'seo': 'herbal-dan-tradisional-obat-tradisional-cina', 'id': 1806, 'name': 'Pengobatan Tradisional Cina'},
        ]
    },
    {
        'seo_title': 'produk-bayi',
        'category_id': 844,
        'nama_kategori': 'Ibu & Anak',
        'subcats': [
            {'seo': 'produk-bayi-dan-anak-perawatan-tubuh', 'id': 2251, 'name': 'Minyak Telon & Perawatan Bayi'},
            {'seo': 'produk-bayi-dan-anak-keperluan-mandi', 'id': 1667, 'name': 'Sabun & Shampoo Bayi'},
            {'seo': 'produk-bayi-popok', 'id': 1669, 'name': 'Popok & Sanitasi Bayi'},
            {'seo': 'produk-bayi-dan-anak-perlengkapan-makan-dan-minum', 'id': 1668, 'name': 'Perlengkapan Makan & Botol Bayi'},
        ]
    },
    {
        'seo_title': 'alat-kesehatan',
        'category_id': 845,
        'nama_kategori': 'Alat Kesehatan',
        'subcats': [
            {'seo': 'alat-kesehatan-termometer', 'id': 1891, 'name': 'Termometer Digital'},
            {'seo': 'alat-kesehatan-tensimeter', 'id': 1889, 'name': 'Tensimeter Digital'},
            {'seo': 'alat-kesehatan-tes-gula-darah-asam-urat-dan-kolesterol', 'id': 1648, 'name': 'Alat & Strip Tes Darah'},
            {'seo': 'alat-kesehatan-barang-disposable-atau-sekali-pakai', 'id': 1886, 'name': 'Plester, Kasa & Masker Medis'},
            {'seo': 'alat-kesehatan-alat-bantu-jalan-dan-kursi-roda', 'id': 1649, 'name': 'Alat Bantu Jalan & Kursi Roda'},
        ]
    },
    {
        'seo_title': 'kecantikan',
        'category_id': 1753,
        'nama_kategori': 'Perawatan Diri',
        'subcats': [
            {'seo': 'personal-care-dan-beauty-perawatan-kulit-skincare-perawatan-kulit', 'id': 1854, 'name': 'Perawatan Kulit (Skincare)'},
            {'seo': 'personal-care-dan-beauty-kebersihan-diri', 'id': 1754, 'name': 'Kebersihan Diri & Antiseptik'},
            {'seo': 'personal-care-dan-beauty-perawatan-gigi-dan-mulut', 'id': 1850, 'name': 'Perawatan Gigi & Mulut'},
            {'seo': 'personal-care-dan-beauty-perawatan-rambut', 'id': 1856, 'name': 'Perawatan Rambut'},
        ]
    },
    {
        'seo_title': 'eyes',
        'category_id': 1592,
        'nama_kategori': 'Mata',
        'subcats': [
            {'seo': 'obat-mata-mata-kering-dan-iritasi-ringan', 'id': 1596, 'name': 'Tetes Mata Kering & Iritasi'},
            {'seo': 'obat-mata-infeksi-mata', 'id': 1595, 'name': 'Obat Infeksi & Radang Mata'},
            {'seo': 'suplemen-dan-vitamin-mata', 'id': 1890, 'name': 'Vitamin & Suplemen Mata'},
        ]
    }
]

def determine_jenis_sediaan(name, packaging):
    combined = f"{name} {packaging}".lower()
    if any(k in combined for k in ['effervescent', 'tablet', 'tab']):
        return 'Tablet'
    elif any(k in combined for k in ['kaplet', 'kaptab', 'caplet']):
        return 'Kaplet'
    elif any(k in combined for k in ['kapsul', 'capsule', 'softgel']):
        return 'Kapsul'
    elif any(k in combined for k in ['sirup', 'syrup', 'syr']):
        return 'Sirup'
    elif any(k in combined for k in ['suspensi', 'susp']):
        return 'Suspensi'
    elif any(k in combined for k in ['drop', 'tetes', 'eye drop', 'ear drop']):
        return 'Tetes (Drops)'
    elif any(k in combined for k in ['salep', 'krim', 'cream', 'gel', 'ointment', 'lotion']):
        return 'Salep/Krim'
    elif any(k in combined for k in ['sachet', 'serbuk', 'puyer', 'powder']):
        return 'Serbuk/Sachet'
    elif any(k in combined for k in ['cairan', 'larutan', 'wash', 'sabun', 'shampoo', 'infus', 'minyak']):
        return 'Larutan/Cairan'
    elif any(k in combined for k in ['plester', 'kompres', 'strip test', 'kasa', 'perban']):
        return 'Plester/Strip'
    elif any(k in combined for k in ['termometer', 'tensimeter', 'nebulizer', 'timbangan', 'stetoskop', 'masker', 'alat', 'kursi roda']):
        return 'Alat Medis'
    return 'Tablet'

def clean_packaging(name, raw_packaging):
    if not raw_packaging:
        raw_packaging = ''
    raw_packaging = raw_packaging.replace('Â', '').replace('@  ', '@ ').strip()
    raw_packaging = re.sub(r'\s+', ' ', raw_packaging)
    lower = raw_packaging.lower()
    if len(raw_packaging) > 70 or any(w in lower for w in ['meredakan', 'membantu', 'mengandung', 'khasiat', 'digunakan', 'indikasi']):
        m = re.search(r'\b(dus\b.*|box\b.*|strip\b.*|botol\b.*|btl\b.*|tube\b.*|sachet\b.*|ampul\b.*|blister\b.*|\d+\s*(?:ml|mg|gram|g|pcs|tablet|kaplet|kapsul).*)\b', name, re.IGNORECASE)
        if m:
            return m.group(0).strip().upper()
        return '1 KEMASAN'
    return raw_packaging.upper() or '1 PCS'

def determine_badge_status(drug_class, is_rx, kategori_name):
    if is_rx:
        return 'Obat Keras'
    dc = str(drug_class).lower()
    if 'keras' in dc:
        return 'Obat Keras'
    elif 'terbatas' in dc:
        return 'Obat Bebas Terbatas'
    elif 'bebas' in dc:
        return 'Obat Bebas'
    elif 'suplemen' in dc or 'vitamin' in dc:
        return 'Suplemen & Vitamin'
    elif 'jamu' in dc:
        return 'Jamu Herbal'
    elif 'herbal' in dc:
        return 'Obat Herbal Terstandar'
    elif 'fitofarmaka' in dc:
        return 'Fitofarmaka'
    
    # Fallback berdasarkan kategori
    if kategori_name == 'Herbal':
        return 'Jamu Herbal'
    elif kategori_name in ['Suplemen', 'Nutrisi']:
        return 'Suplemen & Vitamin'
    elif kategori_name == 'Alat Kesehatan':
        return 'Alat Kesehatan'
    elif kategori_name in ['Ibu & Anak', 'Perawatan Diri']:
        return 'Produk Konsumen'
    return 'Obat Bebas'

def fetch_category_products(seo_title, cat_id, limit=20, page=1):
    url = f"https://m.goapotik.com/api/category/list?seo_title={seo_title}&id={cat_id}&limit={limit}&page={page}&order=1&latitude=-6.2088&longitude=106.8456"
    req = urllib.request.Request(url, headers=HEADERS)
    try:
        with urllib.request.urlopen(req, context=ctx, timeout=10) as resp:
            data = json.loads(resp.read().decode('utf-8'))
            return data.get('data', {}).get('products', [])
    except Exception as e:
        print(f"Error fetching list {seo_title}: {e}", file=sys.stderr)
        return []

def fetch_product_detail(slug):
    url = f"https://www.goapotik.com/api/category/product/detail?product_url_key={slug}"
    req = urllib.request.Request(url, headers=HEADERS)
    try:
        with urllib.request.urlopen(req, context=ctx, timeout=8) as resp:
            data = json.loads(resp.read().decode('utf-8'))
            return data.get('data', {})
    except Exception as e:
        # print(f"Error detail {slug}: {e}", file=sys.stderr)
        return {}

def clean_text(text):
    if not text:
        return ''
    # remove html tags
    text = re.sub(r'<[^>]+>', ' ', str(text))
    # normalize spaces
    text = re.sub(r'\s+', ' ', text).strip()
    return text

def scrape_full_catalog(items_per_category=25):
    print(f"=== Memulai Scraping GoApotik ({items_per_category} produk per kategori) ===")
    all_products = []
    seen_slugs = set()
    category_summary = {}

    for cat_info in CATEGORIES:
        cat_seo = cat_info['seo_title']
        cat_name = cat_info['nama_kategori']
        print(f"\n[+] Memproses Kategori: {cat_name} (SEO: {cat_seo})...")
        
        cat_products = []
        raw_candidates = []

        # 1. Ambil dari subkategori spesifik terlebih dahulu agar variasi kaya
        for sub in cat_info.get('subcats', []):
            sub_prods = fetch_category_products(sub['seo'], sub['id'], limit=6, page=1)
            for sp in sub_prods:
                sp['_target_subcat'] = sub['name']
                raw_candidates.append(sp)

        # 2. Ambil dari halaman utama kategori jika masih butuh lebih banyak
        if len(raw_candidates) < items_per_category:
            main_prods_p1 = fetch_category_products(cat_seo, 'all', limit=20, page=1)
            for mp in main_prods_p1:
                mp['_target_subcat'] = cat_info['subcats'][0]['name'] if cat_info['subcats'] else cat_name
                raw_candidates.append(mp)
            
            if len(raw_candidates) < items_per_category:
                main_prods_p2 = fetch_category_products(cat_seo, 'all', limit=20, page=2)
                for mp in main_prods_p2:
                    mp['_target_subcat'] = cat_info['subcats'][-1]['name'] if cat_info['subcats'] else cat_name
                    raw_candidates.append(mp)

        # Filter unik slug
        unique_for_cat = []
        for rc in raw_candidates:
            slug = rc.get('product_url_key')
            if slug and slug not in seen_slugs:
                seen_slugs.add(slug)
                unique_for_cat.append(rc)
            if len(unique_for_cat) >= items_per_category:
                break

        print(f"    Ditemukan {len(unique_for_cat)} kandidat unik untuk {cat_name}. Mengambil detail...")

        # Fetch detail concurrently
        def process_single(item):
            slug = item.get('product_url_key')
            detail = fetch_product_detail(slug)
            desc_detail = detail.get('description_detail', {}) if detail else {}

            product_name = clean_text(detail.get('product_name') or item.get('product_name'))
            if not product_name:
                return None

            # Foto URL: prioritaskan CDN Cloudinary/GoApotik
            images = detail.get('images', []) if detail else []
            img_url = ''
            if images and isinstance(images, list) and images[0].get('product_path'):
                img_url = images[0].get('product_path')
            if not img_url:
                img_url = item.get('product_image') or ''

            price_val = detail.get('price_value') or item.get('price_value') or 15000
            try:
                price_val = int(float(price_val))
            except:
                price_val = 15000

            is_rx = bool(detail.get('product_rx') if detail and 'product_rx' in detail else item.get('product_rx', False))
            
            raw_pack = clean_text(desc_detail.get('product_packaging', ''))
            cleaned_pack = clean_packaging(product_name, raw_pack)
            jenis_sediaan = determine_jenis_sediaan(product_name, cleaned_pack)
            drug_class_name = desc_detail.get('drug_class_name', '')
            badge_status = determine_badge_status(drug_class_name, is_rx, cat_name)

            subcat_name = item.get('_target_subcat') or cat_name

            # Bersihkan dan siapkan record data lengkap
            product_record = {
                'external_id': detail.get('id') or item.get('id'),
                'sku': detail.get('product_sku') or item.get('product_master_sku') or f"GOA-{item.get('id', 0)}",
                'nama_produk': product_name,
                'slug': slug,
                'kategori': cat_name,
                'subkategori': subcat_name,
                'harga': price_val,
                'satuan_kemasan': cleaned_pack,
                'jenis_sediaan': jenis_sediaan,
                'badge_status': badge_status,
                'gambar_url': img_url,
                'nomor_ijin_edar_bpom': clean_text(desc_detail.get('product_nie')) or 'BPOM-TERDAFTAR',
                'komposisi': clean_text(desc_detail.get('product_composition')) or f'Mengandung bahan aktif terstandar untuk {product_name}.',
                'dosis': clean_text(desc_detail.get('product_dosis')) or 'Sesuai petunjuk pada kemasan atau anjuran dokter/tenaga medis.',
                'indikasi': clean_text(desc_detail.get('product_indication')) or f'Digunakan untuk membantu memelihara kesehatan tubuh dan terapi terkait.',
                'cara_pakai': clean_text(desc_detail.get('product_how_to_use')) or 'Gunakan sesuai instruksi aturan pakai pada kemasan.',
                'deskripsi': clean_text(desc_detail.get('product_short_description') or desc_detail.get('product_description') or product_name),
                'manufaktur': clean_text(desc_detail.get('product_manufacture') or desc_detail.get('principal_name')) or 'Industri Farmasi Indonesia',
                'wajib_resep': is_rx
                # Catatan: Sengaja TIDAK memasukkan nama apotek maupun lokasi (merchant_name / merchant_address)
            }
            return product_record

        with ThreadPoolExecutor(max_workers=5) as executor:
            future_to_item = {executor.submit(process_single, it): it for it in unique_for_cat}
            for future in as_completed(future_to_item):
                res = future.result()
                if res:
                    cat_products.append(res)
                    print(f"      OK: {res['nama_produk'][:38]} | Rp {res['harga']:,} | {res['badge_status']}")

        category_summary[cat_name] = len(cat_products)
        all_products.extend(cat_products)
        time.sleep(0.5)

    print("\n================== RINGKASAN SCRAPING ==================")
    print(f"Total produk berhasil di-scrape: {len(all_products)}")
    for k, v in category_summary.items():
        print(f"  - {k}: {v} produk")

    # Simpan hasil utama
    out_dir = os.path.join('database', 'data')
    os.makedirs(out_dir, exist_ok=True)
    
    out_path_lengkap = os.path.join(out_dir, 'scraped_goapotik_lengkap.json')
    with open(out_path_lengkap, 'w', encoding='utf-8') as f:
        json.dump(all_products, f, indent=2, ensure_ascii=False)
    print(f"\n[+] Berhasil menyimpan katalog lengkap ke: {out_path_lengkap}")

    # Simpan juga per kategori terpisah untuk kemudahan analisa tugas
    out_cat_dir = os.path.join(out_dir, 'kategori')
    os.makedirs(out_cat_dir, exist_ok=True)
    for cat_name in category_summary.keys():
        cat_file_slug = cat_name.lower().replace('&', 'dan').replace(' ', '_')
        cat_items = [p for p in all_products if p['kategori'] == cat_name]
        cat_file_path = os.path.join(out_cat_dir, f"scraped_{cat_file_slug}.json")
        with open(cat_file_path, 'w', encoding='utf-8') as f:
            json.dump(cat_items, f, indent=2, ensure_ascii=False)
        print(f"  -> File kategori: {cat_file_path} ({len(cat_items)} item)")

    return all_products

if __name__ == '__main__':
    limit = 20
    if len(sys.argv) > 1:
        try:
            limit = int(sys.argv[1])
        except:
            pass
    scrape_full_catalog(items_per_category=limit)
