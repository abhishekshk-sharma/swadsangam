$path = 'd:\XAMPP\htdocs\payserver\resources\views\admin\orders\index.blade.php'

# Read as raw bytes
$bytes = [System.IO.File]::ReadAllBytes($path)
$content = [System.Text.Encoding]::UTF8.GetString($bytes)

# Each corrupted sequence and its clean replacement
# Rupee sign: the bytes 0xE2 0x82 0xB9 = ₹ but stored as latin1 gives â‚¹
# When PHP/browser reads UTF-8 file as latin1 it shows â‚¹
# The fix: ensure the file is clean UTF-8 with actual ₹ or use &#8377;

# Replace all known bad patterns with safe ASCII/HTML-entity equivalents
$fixes = [ordered]@{
    # Rupee variants (,1 before digit or brace)
    ",1{{"         = "&#8377;{{"
    ",1`${"        = "&#8377;`${"
    ",10.00"       = "&#8377;0.00"
    "',1'"         = "'&#8377;'"
    " ,1'"         = " '&#8377;'"
    # Multiplication (A- between digit and space)
    "}}A- {{"      = "}}&times; {{"
    # Notes arrow
    ">+' {{"       = ">&rarr; {{"
    # Parcel emoji (dY"ﾝ)
    "dY`"          = "&#128100;"   # user icon
    # Checkmark
    ">バ`"."        = ">&#10003;<"
    ">バ`"."        = ">&#10003;<"
    # Processing
    "Processing?Y" = "Processing..."
    # em dash in JS
    " ?`" "        = " -- "
    # arrow in JS toast
    ") +' '"       = ") -> '"
    # close button
    ">A-<"         = ">&times;<"
}

# Apply simple string replacements that are safe
$content = $content.Replace(',1{{ number_format', '&#8377;{{ number_format')
$content = $content.Replace(',1{{ $mi->price', '&#8377;{{ $mi->price')
$content = $content.Replace(',1${item.price}', '&#8377;${item.price}')
$content = $content.Replace("',10.00'", "'&#8377;0.00'")
$content = $content.Replace('"&#8377;0.00"', '"&#8377;0.00"')
$content = $content.Replace("',1' + total", "'&#8377;' + total")
$content = $content.Replace("',1' + parseFloat", "'&#8377;' + parseFloat")
$content = $content.Replace("',1' + (cash", "'&#8377;' + (cash")
$content = $content.Replace("'Cash must be at least ,1'", "'Cash must be at least &#8377;'")
$content = $content.Replace('Processing?Ý', 'Processing...')
$content = $content.Replace('>A-<', '>&times;<')

[System.IO.File]::WriteAllText($path, $content, [System.Text.Encoding]::UTF8)
Write-Host "Done"
