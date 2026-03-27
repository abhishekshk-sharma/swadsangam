import sys

f = open('d:/XAMPP/htdocs/payserver/resources/views/admin/orders/index.blade.php', 'rb')
d = f.read()
f.close()

replacements = [
    # ₹ (rupee sign) - triple encoded
    (b'\xc3\x83\xc2\xa2\xc3\xa2\xc2\x80\xc2\x9a\xc3\x82\xc2\xb9', b'&#8377;'),
    # 📦 (parcel emoji) - triple encoded
    (b'\xc3\x83\xc2\xb0\xc3\x85\xc2\xb8\xc3\xa2\xc2\x80\xc2\x9c\xc3\x82\xc2\xa6', b'&#128230;'),
    # × (times sign) - triple encoded
    (b'\xc3\x83\xc2\x83\xc3\xa2\xc2\x80\xc2\x94', b'&times;'),
    # → (right arrow) - triple encoded
    (b'\xc3\x83\xc2\xa2\xc3\xa2\xc2\x80\xc2\xa0\xc3\xa2\xc2\x80\xc2\x99', b'&rarr;'),
]

# Find em dash and other sequences dynamically
# em dash – in assign button label
idx = d.find(b"'#{{ $order->id }}")
if idx >= 0:
    chunk = d[idx+18:idx+28]
    sys.stdout.write('emdash chunk: ' + repr(chunk) + '\n')

# close X button
idx2 = d.find(b'closeAdminAddItems()')
if idx2 >= 0:
    chunk2 = d[idx2+50:idx2+62]
    sys.stdout.write('close x chunk: ' + repr(chunk2) + '\n')

# person emoji
idx3 = d.find(b'order->user->name')
if idx3 >= 0:
    chunk3 = d[max(0,idx3-8):idx3]
    sys.stdout.write('person chunk: ' + repr(chunk3) + '\n')

# checkmark in empty state
idx4 = d.find(b'No pending payments')
if idx4 >= 0:
    chunk4 = d[max(0,idx4-20):idx4]
    sys.stdout.write('checkmark chunk: ' + repr(chunk4) + '\n')

# green checkmark
idx5 = d.find(b'Payment Complete!')
if idx5 >= 0:
    chunk5 = d[max(0,idx5-20):idx5]
    sys.stdout.write('green check chunk: ' + repr(chunk5) + '\n')

# UPI phone emoji
idx6 = d.find(b'UPI Payment')
if idx6 >= 0:
    chunk6 = d[max(0,idx6-10):idx6]
    sys.stdout.write('phone chunk: ' + repr(chunk6) + '\n')

# confirm checkmark
idx7 = d.find(b'Payment Received')
if idx7 >= 0:
    chunk7 = d[max(0,idx7-10):idx7]
    sys.stdout.write('confirm chunk: ' + repr(chunk7) + '\n')

# processing ellipsis
idx8 = d.find(b"'Processing")
if idx8 >= 0:
    chunk8 = d[idx8+11:idx8+20]
    sys.stdout.write('ellipsis chunk: ' + repr(chunk8) + '\n')

# dish emoji
idx9 = d.find(b'No active orders today')
if idx9 >= 0:
    chunk9 = d[max(0,idx9-20):idx9]
    sys.stdout.write('dish chunk: ' + repr(chunk9) + '\n')

# trash emoji
idx10 = d.find(b'aoRemove(${i})')
if idx10 >= 0:
    chunk10 = d[idx10+30:idx10+50]
    sys.stdout.write('trash chunk: ' + repr(chunk10) + '\n')

sys.stdout.flush()
