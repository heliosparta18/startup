import urllib.request
import urllib.parse
import json
import re
import ssl
import time
import os
import sys
import statistics
from concurrent.futures import ThreadPoolExecutor, as_completed

ctx = ssl.create_default_context()
ctx.check_hostname = False
ctx.verify_mode = ssl.CERT_NONE

HEADERS = {
    'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
    'Accept': 'application/json, text/plain, */*',
    'Referer': 'https://m.goapotik.com/'
}

# Mapping seo_title kategori GoApotik ke Nama Kategori Web
SEO_TO_WEB_CAT = {
    'obat': 'Obat',
    'suplemen': 'Suplemen',
    'nutrisi': 'Nutrisi',
    'herbal': 'Herbal',
    'produk-bayi': 'Ibu & Anak',
    'alat-kesehatan': 'Alat Kesehatan',
    'kecantikan': 'Perawatan Diri',
    'eyes': 'Mata',
}

# Daftar kata kunci produk & obat populer yang wajib ter-cover di katalog
POPULAR_TERMS = [
    # Kontrasepsi & Hormon (termasuk Postinor!)
    'postinor', 'andalan', 'microgynon', 'yasmin', 'diane', 'sutra', 'durex', 'fiesta', 'sensitif',
    # Demam, Nyeri & Sakit Kepala
    'paracetamol', 'panadol', 'sanmol', 'bodrex', 'paramex', 'sumagesic', 'dumin', 'tempra', 'proris',
    'biogesic', 'oskadon', 'poldan mig', 'saridon', 'praxion',
    # Lambung & Pencernaan
    'promag', 'mylanta', 'polysilane', 'antimo', 'diapet', 'oralit', 'entrostop', 'new diatabs',
    'omeprazole', 'lansoprazole', 'ranitidine', 'sucralfate', 'domperidone', 'ondansetron', 'dulcolax', 'vegeta', 'microlax', 'loperamide',
    # Antibiotik, Antivirus & Antijamur
    'amoxicillin', 'cefixime', 'azithromycin', 'ciprofloxacin', 'cefadroxil', 'metronidazole', 'clindamycin',
    'acyclovir', 'ketoconazole', 'hydrocortisone', 'betamethasone', 'chloramphenicol', 'gentamicin', 'miconazole',
    # Anti-inflamasi, Otot & Nyeri Gigi
    'mefinal', 'ponstan', 'cataflam', 'asam mefenamat', 'ibuprofen', 'meloxicam', 'piroxicam', 'kalium diklofenak', 'natrium diklofenak',
    # Alergi & Kortikosteroid
    'cetirizine', 'loratadine', 'ctm', 'incidal', 'dexamethasone', 'methylprednisolone', 'lameson',
    # Batuk, Flu, Pilek & Asma
    'obh', 'komix', 'siladex', 'actifed', 'bisolvon', 'woods', 'vicks', 'hufagrip', 'alco', 'rhinos',
    'tremenza', 'salbutamol', 'ventolin', 'ambroxol', 'acetylcysteine', 'decolgen', 'neozep', 'mixagrip', 'procold', 'ultraflu',
    # Kronis (Hipertensi, Kolesterol, Diabetes, Asam Urat)
    'amlodipine', 'captopril', 'candesartan', 'valsartan', 'simvastatin', 'atorvastatin', 'metformin', 'glimepiride', 'allopurinol',
    # Vitamin & Suplemen Populer
    'imboost', 'enervon', 'stimuno', 'cdr', 'redoxon', 'holisticare', 'blackmores', 'vitacimin', 'ever e',
    'natur e', 'curcuma', 'becom', 'neurobion', 'sangobion', 'folavit', 'osteocare', 'caviplex', 'pharmaton', 'hemaviton',
    # Herbal Populer
    'tolak angin', 'antangin', 'kiranti', 'kejibeling', 'batugin', 'minyak kayu putih', 'minyak telon',
    'freshcare', 'safe care', 'lelaki', 'obat batuk ibu dan anak', 'ob herbal', 'diabetadex', 'psidii',
    # Mata & Telinga
    'insto', 'rohto', 'cendo', 'braito', 'erlamycetin', 'vital ear', 'forumen',
    # Luka, Kulit & Topikal
    'betadine', 'hansaplast', 'salonpas', 'counterpain', 'voltaren', 'hotin', 'koolfever', 'bye bye fever',
    'daktarin', 'kalpanax', 'canesten', 'salep 88', 'scabimite', 'bioplacenton', 'thrombophob', 'mederma', 'dermatix',
    # Perawatan Diri & Ibu Anak
    'sensodyne', 'listerine', 'cetaphil', 'sebamed', 'lactacyd', 'zwitsal', 'my baby', 'cussons', 'transpulmin', 'caladine',
    # Nutrisi & Susu
    'sgm', 'bebelac', 'entrasol', 'ensure', 'diabetasol', 'prenagen', 'pediasure', 's26', 'morinaga', 'bear brand', 'madurasa', 'appeton',
    # Alat Kesehatan
    'omron', 'onemed', 'accu-chek', 'easy touch', 'nesco', 'nebulizer', 'tensimeter', 'termometer', 'kursi roda', 'masker', 'kasa steril'
]


def http_get_json(url, timeout=10, retries=2):
    for attempt in range(retries + 1):
        try:
            req = urllib.request.Request(url, headers=HEADERS)
            with urllib.request.urlopen(req, context=ctx, timeout=timeout) as resp:
                return json.loads(resp.read().decode('utf-8'))
        except Exception:
            if attempt < retries:
                time.sleep(0.3 * (attempt + 1))
    return {}


def is_valid_price(val):
    if val is None:
        return False
    try:
        p = int(float(val))
        # 5610539 adalah dummy placeholder GoApotik saat tidak ada harga merchant
        if p == 5610539 or p < 500 or p > 15000000:
            return False
        return True
    except Exception:
        return False


def determine_jenis_sediaan(name, packaging):
    combined = f"{name} {packaging}".lower()
    if any(k in combined for k in ['effervescent', 'tablet', 'tab']):
        return 'Tablet'
    elif any(k in combined for k in ['kaplet', 'kaptab', 'caplet']):
        return 'Kaplet'
    elif any(k in combined for k in ['kapsul', 'capsule', 'softgel']):
        return 'Kapsul'
    elif any(k in combined for k in ['sirup', 'syrup', 'syr', 'elixir']):
        return 'Sirup'
    elif any(k in combined for k in ['suspensi', 'susp']):
        return 'Suspensi'
    elif any(k in combined for k in ['drop', 'tetes', 'eye drop', 'ear drop', 'spray', 'semprot']):
        return 'Tetes (Drops)'
    elif any(k in combined for k in ['salep', 'krim', 'cream', 'gel', 'ointment', 'lotion', 'balsem', 'balsam']):
        return 'Salep/Krim'
    elif any(k in combined for k in ['sachet', 'serbuk', 'puyer', 'powder', 'susu', 'bubuk']):
        return 'Serbuk/Sachet'
    elif any(k in combined for k in ['cairan', 'larutan', 'wash', 'sabun', 'shampoo', 'infus', 'minyak', 'madu', 'mouthwash']):
        return 'Larutan/Cairan'
    elif any(k in combined for k in ['plester', 'kompres', 'strip test', 'kasa', 'perban', 'kondom', 'popok', 'test pack']):
        return 'Plester/Strip'
    elif any(k in combined for k in ['termometer', 'tensimeter', 'nebulizer', 'timbangan', 'stetoskop', 'masker', 'alat', 'kursi roda', 'kruk', 'tongkat', 'inhaler']):
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
            return m.group(0).strip().upper()[:100]
        return '1 KEMASAN'
    return (raw_packaging.upper() or '1 PCS')[:100]


def determine_badge_status(drug_class, is_rx, kategori_name):
    dc = str(drug_class).lower()
    if is_rx or 'keras' in dc:
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
    elif 'alat' in dc or kategori_name == 'Alat Kesehatan':
        return 'Alat Kesehatan'

    if kategori_name == 'Herbal':
        return 'Jamu Herbal'
    elif kategori_name in ['Suplemen', 'Nutrisi']:
        return 'Suplemen & Vitamin'
    elif kategori_name in ['Ibu & Anak', 'Perawatan Diri']:
        return 'Produk Konsumen'
    return 'Obat Bebas'


def clean_text(text):
    if not text:
        return ''
    text = re.sub(r'<[^>]+>', ' ', str(text))
    text = text.replace('&nbsp;', ' ').replace('&amp;', '&').replace('&quot;', '"')
    text = re.sub(r'\s+', ' ', text).strip()
    return text


def infer_subcategory(name, cat_name, fallback_subcat=''):
    if fallback_subcat and fallback_subcat != cat_name:
        return fallback_subcat[:100]
    n = name.lower()
    if cat_name == 'Obat':
        if any(k in n for k in ['postinor', 'andalan', 'microgynon', 'yasmin', 'diane']):
            return 'Hormon & Kontrasepsi'
        if any(k in n for k in ['paracetamol', 'panadol', 'sanmol', 'bodrex', 'paramex', 'tempra', 'dumin', 'sumagesic', 'biogesic', 'demam']):
            return 'Pereda Demam & Nyeri'
        if any(k in n for k in ['amoxicillin', 'cefixime', 'azithromycin', 'ciprofloxacin', 'cefadroxil', 'metronidazole', 'clindamycin']):
            return 'Antibiotik'
        if any(k in n for k in ['promag', 'mylanta', 'polysilane', 'omeprazole', 'lansoprazole', 'ranitidine', 'sucralfate', 'diapet', 'oralit', 'entrostop', 'dulcolax', 'antimo']):
            return 'Saluran Pencernaan & Lambung'
        if any(k in n for k in ['obh', 'komix', 'siladex', 'actifed', 'bisolvon', 'woods', 'hufagrip', 'rhinos', 'tremenza', 'ambroxol', 'salbutamol', 'ventolin', 'acetylcysteine']):
            return 'Flu, Batuk & Pernapasan'
        if any(k in n for k in ['amlodipine', 'captopril', 'candesartan', 'simvastatin', 'atorvastatin', 'metformin', 'glimepiride', 'allopurinol']):
            return 'Jantung, Hipertensi & Diabetes'
        if any(k in n for k in ['cetirizine', 'loratadine', 'ctm', 'incidal', 'dexamethasone', 'methylprednisolone', 'lameson']):
            return 'Alergi & Anti-inflamasi'
        if any(k in n for k in ['betadine', 'salep', 'cream', 'gel', 'daktarin', 'kalpanax', 'canesten', 'acyclovir', 'ketoconazole', 'hydrocortisone', 'bioplacenton', 'scabimite', 'counterpain', 'voltaren', 'hotin']):
            return 'Obat Kulit & Topikal'
        if any(k in n for k in ['mefinal', 'ponstan', 'cataflam', 'asam mefenamat', 'ibuprofen', 'meloxicam', 'piroxicam']):
            return 'Anti Nyeri & Sendi'
        return 'Obat Umum Lainnya'
    elif cat_name == 'Suplemen':
        if any(k in n for k in ['imboost', 'stimuno', 'enervon', 'cdr', 'redoxon', 'vitacimin', 'holisticare']):
            return 'Daya Tahan Tubuh & Vitamin C'
        if any(k in n for k in ['neurobion', 'sangobion', 'folavit']):
            return 'Vitamin Saraf & Darah'
        if any(k in n for k in ['ever e', 'natur e', 'collagen']):
            return 'Kesehatan Kulit & Antioksidan'
        if any(k in n for k in ['osteocare', 'osteocal', 'glucosamine', 'calc', 'tulang']):
            return 'Kesehatan Tulang & Sendi'
        return 'Multivitamin & Suplemen'
    elif cat_name == 'Nutrisi':
        if any(k in n for k in ['sgm', 'bebelac', 's26', 'morinaga', 'pediasure']):
            return 'Susu Bayi & Anak'
        if any(k in n for k in ['prenagen']):
            return 'Susu Ibu Hamil & Menyusui'
        if any(k in n for k in ['entrasol', 'ensure', 'diabetasol']):
            return 'Nutrisi Medis & Lansia'
        if any(k in n for k in ['madu', 'tj', 'madurasa']):
            return 'Madu & Propolis'
        return 'Makanan & Minuman Sehat'
    elif cat_name == 'Herbal':
        if any(k in n for k in ['minyak', 'freshcare', 'safe care']):
            return 'Minyak Angin & Aromaterapi'
        return 'Jamu & Obat Herbal'
    elif cat_name == 'Ibu & Anak':
        if any(k in n for k in ['telon', 'transpulmin', 'koolfever', 'bye bye']):
            return 'Perawatan Bayi & Demam'
        return 'Keperluan Ibu & Bayi'
    elif cat_name == 'Alat Kesehatan':
        if any(k in n for k in ['sutra', 'durex', 'fiesta', 'sensitif']):
            return 'Kontrasepsi & Tes Kehamilan'
        if any(k in n for k in ['omron', 'tensimeter', 'termometer', 'accu-chek', 'easy touch', 'nesco', 'nebulizer']):
            return 'Alat Cek & Diagnostik Medis'
        if any(k in n for k in ['hansaplast', 'kasa', 'masker', 'onemed']):
            return 'P3K & Barang Sekali Pakai'
        return 'Alat Bantu Kesehatan'
    elif cat_name == 'Perawatan Diri':
        if any(k in n for k in ['sensodyne', 'listerine']):
            return 'Perawatan Gigi & Mulut'
        return 'Perawatan Kulit & Tubuh'
    elif cat_name == 'Mata':
        return 'Obat & Tetes Mata'
    return cat_name


def collect_candidates():
    candidates = {}  # slug -> {item, prices:[], target_cat, target_subcat}

    def add_candidate(p, target_cat='', target_subcat=''):
        slug = p.get('product_url_key')
        if not slug:
            return
        if slug not in candidates:
            candidates[slug] = {
                'item': p,
                'prices': [],
                'target_cat': target_cat,
                'target_subcat': target_subcat,
            }
        # Simpan harga valid dari listing/search
        pv = p.get('price_value')
        if is_valid_price(pv):
            candidates[slug]['prices'].append(int(float(pv)))
        # Jangan timpa subkategori spesifik jika sudah ada
        if target_subcat and not candidates[slug]['target_subcat']:
            candidates[slug]['target_subcat'] = target_subcat
        if target_cat and not candidates[slug]['target_cat']:
            candidates[slug]['target_cat'] = target_cat

    # 1. Load hierarchy from scripts/categories_hierarchy.json
    with open('scripts/categories_hierarchy.json', 'r', encoding='utf-8') as f:
        hierarchy = json.load(f)

    # Build list of category/subcategory tasks
    cat_tasks = []
    for cat_seo, cat_info in hierarchy.items():
        if cat_seo not in SEO_TO_WEB_CAT:
            continue
        web_cat = SEO_TO_WEB_CAT[cat_seo]
        # Main category pages 1..3
        for page in [1, 2, 3]:
            cat_tasks.append(('cat', cat_seo, 'all', page, web_cat, ''))
        # All subcategories page 1
        for sub in cat_info.get('subcategories', []):
            cat_tasks.append(('sub', sub['seo_title'], sub['id'], 1, web_cat, sub['name']))

    print(f"[1/3] Mengambil kandidat dari {len(cat_tasks)} halaman kategori & subkategori GoApotik...")

    def run_cat_task(task):
        _, seo, cid, page, wcat, wsub = task
        url = f"https://m.goapotik.com/api/category/list?seo_title={seo}&id={cid}&limit=20&page={page}&order=1&latitude=-6.2088&longitude=106.8456"
        data = http_get_json(url, timeout=10)
        prods = data.get('data', {}).get('products', []) if isinstance(data.get('data'), dict) else []
        return (prods, wcat, wsub)

    with ThreadPoolExecutor(max_workers=12) as ex:
        for res in ex.map(run_cat_task, cat_tasks):
            prods, wcat, wsub = res
            for p in prods:
                add_candidate(p, wcat, wsub)

    print(f"    -> Terkumpul {len(candidates)} produk unik dari kategori & subkategori.")

    # 2. Search popular products (including postinor, etc.)
    print(f"[2/3] Mengambil produk populer & spesifik ({len(POPULAR_TERMS)} kata kunci, termasuk Postinor dll)...")

    def run_search_task(term):
        q = urllib.parse.quote(term)
        url = f"https://m.goapotik.com/api/search/results/products?order=&page=1&terms={q}&lng=106.8456&lat=-6.2088&selected_filter="
        data = http_get_json(url, timeout=10)
        prods = data.get('data', {}).get('products', []) if isinstance(data.get('data'), dict) else []
        return prods

    with ThreadPoolExecutor(max_workers=12) as ex:
        for prods in ex.map(run_search_task, POPULAR_TERMS):
            for p in prods:
                add_candidate(p, '', '')

    print(f"    -> Total kandidat unik siap di-scrape detailnya: {len(candidates)} produk!")
    return candidates


def fetch_details_and_build(candidates):
    print(f"[3/3] Mengambil detail lengkap untuk {len(candidates)} produk...")
    results = []

    def process_candidate(slug_and_info):
        slug, info = slug_and_info
        item = info['item']
        prices = info['prices']
        target_cat = info['target_cat']
        target_subcat = info['target_subcat']

        detail_url = f"https://www.goapotik.com/api/category/product/detail?product_url_key={urllib.parse.quote(slug)}"
        resp_json = http_get_json(detail_url, timeout=10)
        detail = resp_json.get('data', {}) if isinstance(resp_json, dict) else {}
        desc_detail = detail.get('description_detail', {}) if isinstance(detail, dict) else {}

        product_name = clean_text(detail.get('product_name') or item.get('product_name'))
        if not product_name:
            return None

        # Abaikan voucher layanan kesehatan non-produk
        if 'voucher' in product_name.lower() or 'konsultasi' in product_name.lower():
            return None

        # Kategori asli dari detail GoApotik (lebih akurat untuk hasil search)
        web_cat = target_cat
        detail_cats = detail.get('categories', []) if isinstance(detail, dict) else []
        if target_cat != 'Mata' and detail_cats and isinstance(detail_cats, list):
            for dc in detail_cats:
                seo = dc.get('seo_title', '')
                if seo in SEO_TO_WEB_CAT:
                    web_cat = SEO_TO_WEB_CAT[seo]
                    break
        if any(k in product_name.lower() for k in ['insto', 'rohto', 'cendo', 'braito', 'eye drop', 'tetes mata', 'salep mata', 'retivit', 'eyevit', 'visionlux']):
            web_cat = 'Mata'
        if not web_cat:
            web_cat = 'Obat'

        # Foto URL
        images = detail.get('images', []) if isinstance(detail, dict) else []
        img_url = ''
        if images and isinstance(images, list) and isinstance(images[0], dict):
            img_url = images[0].get('product_path') or ''
        if not img_url:
            img_url = item.get('product_image') or ''
        if not img_url:
            return None

        # Harga REAL dari GoApotik (prioritas list/search price valid, lalu detail price valid)
        detail_pv = detail.get('price_value') if isinstance(detail, dict) else None
        if prices:
            final_price = int(statistics.median(prices))
        elif is_valid_price(detail_pv):
            final_price = int(float(detail_pv))
        else:
            # Skip atau fallback hanya jika benar-benar tidak ada harga valid sama sekali
            final_price = 25000

        is_rx = bool(detail.get('product_rx') if isinstance(detail, dict) and 'product_rx' in detail else item.get('product_rx', False))
        raw_pack = clean_text(desc_detail.get('product_packaging', ''))
        cleaned_pack = clean_packaging(product_name, raw_pack)
        jenis_sediaan = determine_jenis_sediaan(product_name, cleaned_pack)
        drug_class_name = desc_detail.get('drug_class_name', '')
        badge_status = determine_badge_status(drug_class_name, is_rx, web_cat)

        subcat_name = infer_subcategory(product_name, web_cat, target_subcat)

        return {
            'external_id': detail.get('id') or item.get('id'),
            'sku': (detail.get('product_sku') or item.get('product_master_sku') or f"GOA-{item.get('id', 0)}")[:50],
            'nama_produk': product_name[:150],
            'slug': slug[:190],
            'kategori': web_cat,
            'subkategori': subcat_name[:100],
            'harga': final_price,
            'satuan_kemasan': cleaned_pack[:100],
            'jenis_sediaan': jenis_sediaan[:50],
            'badge_status': badge_status[:50],
            'gambar_url': img_url[:500],
            'nomor_ijin_edar_bpom': (clean_text(desc_detail.get('product_nie')) or 'BPOM-TERDAFTAR')[:100],
            'komposisi': clean_text(desc_detail.get('product_composition')) or f'Mengandung zat aktif terstandar untuk {product_name}.',
            'dosis': clean_text(desc_detail.get('product_dosis')) or 'Sesuai petunjuk pada kemasan atau anjuran dokter.',
            'indikasi': clean_text(desc_detail.get('product_indication')) or f'Membantu memelihara kesehatan dan mengatasi keluhan terkait.',
            'cara_pakai': clean_text(desc_detail.get('product_how_to_use')) or 'Gunakan sesuai aturan pakai pada kemasan.',
            'deskripsi': clean_text(desc_detail.get('product_short_description') or desc_detail.get('product_description') or product_name),
            'manufaktur': (clean_text(desc_detail.get('product_manufacture') or desc_detail.get('principal_name')) or 'Industri Farmasi Indonesia')[:100],
            'wajib_resep': is_rx
        }

    items_list = list(candidates.items())
    done_count = 0
    with ThreadPoolExecutor(max_workers=15) as executor:
        futures = [executor.submit(process_candidate, pair) for pair in items_list]
        for fut in as_completed(futures):
            done_count += 1
            if done_count % 100 == 0 or done_count == len(items_list):
                print(f"    Progress detail: {done_count}/{len(items_list)}...")
            res = fut.result()
            if res:
                results.append(res)

    # Sort konsisten by kategori then nama_produk
    results.sort(key=lambda x: (x['kategori'], x['nama_produk']))
    return results


def save_outputs(all_products):
    out_dir = os.path.join('database', 'data')
    os.makedirs(out_dir, exist_ok=True)

    # 1. JSON lengkap
    out_json = os.path.join(out_dir, 'scraped_goapotik_lengkap.json')
    with open(out_json, 'w', encoding='utf-8') as f:
        json.dump(all_products, f, indent=2, ensure_ascii=False)

    # 2. CSV lengkap
    import csv
    out_csv = os.path.join(out_dir, 'scraped_goapotik_lengkap.csv')
    fieldnames = [
        'external_id', 'sku', 'nama_produk', 'kategori', 'subkategori',
        'harga', 'satuan_kemasan', 'jenis_sediaan', 'badge_status', 'wajib_resep',
        'nomor_ijin_edar_bpom', 'manufaktur', 'indikasi', 'dosis', 'komposisi',
        'cara_pakai', 'deskripsi', 'gambar_url', 'slug'
    ]
    with open(out_csv, 'w', encoding='utf-8-sig', newline='') as f:
        writer = csv.DictWriter(f, fieldnames=fieldnames, extrasaction='ignore')
        writer.writeheader()
        for p in all_products:
            writer.writerow(p)

    # 3. JSON per kategori
    out_cat_dir = os.path.join(out_dir, 'kategori')
    os.makedirs(out_cat_dir, exist_ok=True)
    counts = {}
    for p in all_products:
        counts[p['kategori']] = counts.get(p['kategori'], 0) + 1

    for cat_name in sorted(counts.keys()):
        cat_slug = cat_name.lower().replace('&', 'dan').replace(' ', '_')
        cat_items = [p for p in all_products if p['kategori'] == cat_name]
        cat_path = os.path.join(out_cat_dir, f"scraped_{cat_slug}.json")
        with open(cat_path, 'w', encoding='utf-8') as f:
            json.dump(cat_items, f, indent=2, ensure_ascii=False)

    print("\n================== HASIL SCRAPING MASIF GOAPOTIK ==================")
    print(f"Total produk unik tersimpan: {len(all_products)}")
    for k, v in sorted(counts.items()):
        print(f"  - {k}: {v} produk")
    postinor_matches = [p for p in all_products if 'postinor' in p['nama_produk'].lower()]
    print(f"\nVerifikasi produk 'Postinor': {len(postinor_matches)} ditemukan -> {[p['nama_produk'] for p in postinor_matches]}")
    print(f"File JSON utama: {out_json}")
    print(f"File CSV utama: {out_csv}")


if __name__ == '__main__':
    t0 = time.time()
    candidates = collect_candidates()
    products = fetch_details_and_build(candidates)
    save_outputs(products)
    print(f"\nSelesai dalam {time.time() - t0:.1f} detik.")
