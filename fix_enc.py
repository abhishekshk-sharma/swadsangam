f = open('d:/XAMPP/htdocs/payserver/resources/views/admin/orders/index.blade.php', 'rb')
d = f.read()
f.close()

fixes = [
    # rupee sign ₹ (triple-encoded)
    (b'\xc3\x83\xc2\xa2\xc3\xa2\xc2\x80\xc2\x9a\xc3\x82\xc2\xb9', b'&#8377;'),
    # 📦 parcel emoji (triple-encoded)
    (b'\xc3\x83\xc2\xb0\xc3\x85\xc2\xb8\xc3\xa2\xc2\x80\xc2\x9c\xc3\x82\xc2\xa6', b'<i class="fas fa-box"></i>'),
    # × times sign (triple-encoded)
    (b'\xc3\x83\xc2\x83\xc3\xa2\xc2\x80\xc2\x94', b'&times;'),
    # → right arrow (triple-encoded)
    (b'\xc3\x83\xc2\xa2\xc3\xa2\xc2\x80\xc2\xa0\xc3\xa2\xc2\x80\xc2\x99', b'&rarr;'),
    # – en dash (triple-encoded)
    (b'\xc3\x83\xc2\xa2\xc3\xa2\xc2\x82\xc2\xac\xc3\x82\xc2\x93', b'&ndash;'),
    # ✅ green checkmark (triple-encoded)
    (b'\xc3\x83\xc2\xa2\xc3\x85\xc2\x93\xc3\xa2\xc2\x80\xc2\xa6', b'&#10003;'),
    # ✔ checkmark (triple-encoded)
    (b'\xc3\x83\xc2\xa2\xc3\x85\xc2\x93\xc3\xa2\xc2\x80\xc2\x9c', b'&#10003;'),
    # 📱 phone emoji (triple-encoded)
    (b'\xc3\x83\xc2\xb0\xc3\x85\xc2\xb8\xc3\xa2\xc2\x80\xc2\x9c\xc3\x82\xc2\xb1', b'<i class="fas fa-mobile-alt"></i>'),
    # 💵 cash emoji (triple-encoded)
    (b'\xc3\x83\xc2\xb0\xc3\x85\xc2\xb8\xc3\xa2\xc2\x80\xc2\x9c\xc3\x82\xc2\xb5', b'<i class="fas fa-money-bill"></i>'),
    # 🍽️ dish emoji (triple-encoded)
    (b'\xc3\x83\xc2\xb0\xc3\x85\xc2\xb8\xc3\x82\xc2\x8d\xc3\x82\xc2\xbd\xc3\x83\xc2\xaf\xc3\x82\xc2\xb8\xc3\x82\xc2\x8f', b'<i class="fas fa-utensils"></i>'),
    # 🗑️ trash emoji (triple-encoded)
    (b'\xc3\x83\xc2\xb0\xc3\x85\xc2\xb8\xc3\xa2\xc2\x80\xc2\x97\xc3\x83\xc2\xaf\xc3\x82\xc2\xb8\xc2\x8f', b'<i class="fas fa-trash"></i>'),
    # 👤 person emoji (triple-encoded)
    (b'\xc3\x83\xc2\xb0\xc3\x85\xc2\xb8\xc3\xa2\xc2\x80\xc2\xa4', b'<i class="fas fa-user"></i>'),
    # × close button (double-encoded Ã×)
    (b'\xc3\x83\xc3\x97', b'&times;'),
    # … ellipsis (triple-encoded)
    (b'\xc3\x83\xc2\xa2\xc3\xa2\xc2\x82\xc2\xac\xc3\x82\xc2\xa6', b'...'),
    # fix broken PHP: $order->branch_id  'null' -> $order->branch_id ?? 'null'
    (b"$order->branch_id  'null'", b"$order->branch_id ?? 'null'"),
    # fix broken PHP: $item->menuItem->name  '[Deleted Item]' -> ?? '[Deleted Item]'
    (b"$item->menuItem->name  '[Deleted Item]'", b"$item->menuItem->name ?? '[Deleted Item]'"),
    # fix broken PHP: $order->is_parcel  'Parcel' -> $order->is_parcel ?
    (b"$order->is_parcel  'Parcel'", b"$order->is_parcel ? 'Parcel'"),
]

for old, new in fixes:
    count = d.count(old)
    if count:
        d = d.replace(old, new)
        print(f'Replaced {count}x: {old[:20]}')

f = open('d:/XAMPP/htdocs/payserver/resources/views/admin/orders/index.blade.php', 'wb')
f.write(d)
f.close()
print('Done')
