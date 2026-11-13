import urllib.request
import urllib.parse
import json
import re
import sys
import time
import argparse

HEADERS = {
    'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
    'Accept': 'application/json',
    'Referer': 'https://www.goapotik.com/produk'
}

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
    elif any(k in combined for k in ['salep', 'krim', 'cream', 'gel', 'ointment']):
        return 'Salep/Krim'
    elif any(k in combined for k in ['sachet', 'serbuk', 'puyer', 'powder']):
        return 'Serbuk/Sachet'
    elif any(k in combined for k in ['cairan', 'larutan', 'lotion', 'wash', 'sabun', 'shampoo', 'infus', 'minyak']):
        return 'Larutan/Cairan'
    elif any(k in combined for k in ['plester', 'kompres', 'strip test', 'kasa', 'perban']):
        return 'Plester/Strip'
    elif any(k in combined for k in ['termometer', 'tensimeter', 'nebulizer', 'timbangan', 'stetoskop', 'masker', 'alat']):
        return 'Alat Medis'
    return 'Tablet'

def clean_packaging(name, raw_packaging):
    if not raw_packaging:
        raw_packaging = ''
    raw_packaging = raw_packaging.replace('Â', '').replace('@  ', '@ ').strip()
    lower = raw_packaging.lower()
    if len(raw_packaging) > 60 or any(w in lower for w in ['meredakan', 'membantu', 'mengandung', 'khasiat', 'digunakan', 'indikasi']):
        m = re.search(r'\b(dus\b.*|box\b.*|strip\b.*|botol\b.*|btl\b.*|tube\b.*|sachet\b.*|ampul\b.*|blister\b.*|\d+\s*(?:ml|mg|gram|g|pcs|tablet|kaplet|kapsul).*)\b', name, re.IGNORECASE)
        if m:
            return m.group(0).strip().capitalize()
        return '1 Kemasan'
    return raw_packaging or '1 Pcs'

def determine_badge_status(drug_class, is_rx):
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
    return 'Obat Bebas'

def search_products(term, limit=10):
    params = urllib.parse.urlencode({'seo_title': 'obat', 'terms': term, 'limit': limit, 'page': 1})
    url = f"https://www.goapotik.com/api/category/product?{params}"
    req = urllib.request.Request(url, headers=HEADERS)
    try:
        with urllib.request.urlopen(req, timeout=8) as resp:
            data = json.loads(resp.read().decode('utf-8'))
            return data.get('data', [])
    except Exception as e:
        print(f"Error searching '{term}': {e}", file=sys.stderr)
        return []

def get_product_detail(key):
    url = f"https://www.goapotik.com/api/category/product/detail?product_url_key={key}"
    req = urllib.request.Request(url, headers=HEADERS)
    try:
        with urllib.request.urlopen(req, timeout=8) as resp:
            data = json.loads(resp.read().decode('utf-8'))
            return data.get('data', {})
    except Exception as e:
        print(f"Error detail '{key}': {e}", file=sys.stderr)
        return {}

def scrape_category(category_name, default_subcat, terms, max_products=15):
    results = []
    seen_keys = set()

    for term in terms:
        if len(results) >= max_products:
            break
        items = search_products(term, limit=5)
        for it in items:
            key = it.get('product_url_key')
            if not key or key in seen_keys:
                continue
            seen_keys.add(key)

            detail = get_product_detail(key)
            if not detail or not detail.get('product_name'):
                continue

            desc = detail.get('description_detail', {})
            price = detail.get('price_value') or 15000
            is_rx = bool(detail.get('product_rx'))
            
            # Extract images
            images = detail.get('images', [])
            image_url = images[0].get('product_path') if images else (it.get('product_image') or '')
            
            raw_packaging = clean_packaging(detail.get('product_name', ''), desc.get('product_packaging') or '')
            jenis_sediaan = determine_jenis_sediaan(detail.get('product_name', ''), raw_packaging)
            badge_status = determine_badge_status(desc.get('drug_class_name', ''), is_rx)
            product_obj = {
                'external_id': detail.get('id', 0),
                'sku': detail.get('product_sku') or f"GOA-{len(results)+1:05d}",
                'nama_produk': detail.get('product_name', '').strip(),
                'slug': key,
                'kategori': category_name,
                'subkategori': default_subcat,
                'harga': int(price),
                'satuan_kemasan': raw_packaging.strip(),
                'jenis_sediaan': jenis_sediaan,
                'badge_status': badge_status,
                'gambar_url': image_url,
                'nomor_ijin_edar_bpom': desc.get('product_nie') or 'POM-TR-REGISTERED',
                'komposisi': desc.get('product_composition') or '',
                'dosis': desc.get('product_dosis') or 'Sesuai petunjuk kemasan atau anjuran dokter.',
                'indikasi': desc.get('product_indication') or 'Membantu memelihara kesehatan tubuh.',
                'cara_pakai': desc.get('product_how_to_use') or 'Dapat dikonsumsi setelah makan.',
                'deskripsi': desc.get('product_short_description') or desc.get('product_description') or detail.get('product_name', ''),
                'manufaktur': desc.get('product_manufacture') or desc.get('principal_name') or 'Farmasi Indonesia',
                'wajib_resep': is_rx
            }
            results.append(product_obj)
            print(f"[{category_name}] Scraped: {product_obj['nama_produk']} (Sediaan: {jenis_sediaan})")
            if len(results) >= max_products:
                break
            time.sleep(0.3)

    return results

if __name__ == '__main__':
    parser = argparse.ArgumentParser()
    parser.add_argument('--category', required=True)
    parser.add_argument('--subcat', required=True)
    parser.add_argument('--terms', required=True, help='Comma separated search terms')
    parser.add_argument('--limit', type=int, default=12)
    parser.add_argument('--output', required=True)
    args = parser.parse_args()

    terms_list = [t.strip() for t in args.terms.split(',') if t.strip()]
    data = scrape_category(args.category, args.subcat, terms_list, args.limit)

    with open(args.output, 'w', encoding='utf-8') as f:
        json.dump(data, f, indent=2, ensure_ascii=False)
    print(f"Successfully saved {len(data)} items to {args.output}")
