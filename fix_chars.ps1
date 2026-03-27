$path = 'd:\XAMPP\htdocs\payserver\resources\views\admin\orders\index.blade.php'
$content = [System.IO.File]::ReadAllText($path, [System.Text.Encoding]::UTF8)

# Rupee sign variants
$content = $content -replace ',1(?=[\d\{])', '&#8377;'

# Parcel emoji variants
$content = $content -replace 'dY"[^\s]*\s*Parcel', '&#128230; Parcel'

# Multiplication sign (item quantity)
$content = $content -replace '(\d+)A-\s', '$1&times; '

# Arrow (notes)
$content = $content -replace "\+'\s", '&rarr; '

# User icon
$content = $content -replace 'dY`[^\s]*\s', '&#128100; '

# Plate/food emoji
$content = $content -replace 'dY\?[^\s<"]*', '&#127869;'

# Cash button
$content = $content -replace "dY'[^\s]*\s*Cash", 'Cash'

# UPI button/heading
$content = $content -replace 'dY"[^\s]*\s*UPI', 'UPI'

# Checkmark / tick
$content = $content -replace 'バ["\.]', '&#10003;'

# Trash/delete icon
$content = $content -replace 'dY-`[^\s<"]*', '&#128465;'

# Processing text
$content = $content -replace 'Processing\?[^\s<"]*', 'Processing...'

# Em dash in JS strings
$content = $content -replace " \?\" ", ' &mdash; '

# Arrow in JS toast
$content = $content -replace " \+' ", ' &rarr; '

# Comment decorators (garbled box-drawing)
$content = $content -replace '"[?]"[?]', ''

[System.IO.File]::WriteAllText($path, $content, [System.Text.Encoding]::UTF8)
Write-Host 'Done'
