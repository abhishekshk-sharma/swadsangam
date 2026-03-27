$path = 'd:\XAMPP\htdocs\payserver\resources\views\admin\orders\index.blade.php'
$lines = Get-Content $path

# Helper: strip all non-ASCII from a line then apply known fixes
function Fix($line) {
    # rupee: ,1 before digit or {
    $line = [regex]::Replace($line, ',1(?=[\d{])', '&#8377;')
    # multiplication: digit + A- + space
    $line = [regex]::Replace($line, '(\d+)A-\s', '$1&times; ')
    # parcel emoji: any dY...Parcel
    $line = [regex]::Replace($line, 'dY[^\s<"]*\s*Parcel', '&#128230; Parcel')
    # user icon: dY`...space
    $line = [regex]::Replace($line, 'dY`[^\s<"]*\s', '&#128100; ')
    # food/plate emoji
    $line = [regex]::Replace($line, 'dY\?[^\s<"]*', '&#127869;')
    # cash emoji
    $line = [regex]::Replace($line, "dY'[^\s<""]*\s*Cash", 'Cash')
    # UPI emoji
    $line = [regex]::Replace($line, 'dY"[^\s<"]*\s*UPI', 'UPI')
    # trash emoji
    $line = [regex]::Replace($line, 'dY-`[^\s<"]*', '&#128465;')
    # checkmark/tick
    $line = [regex]::Replace($line, 'バ[".]+', '&#10003;')
    # arrow for notes: +' followed by space or {
    $line = [regex]::Replace($line, "\+'(?=\s|\{)", '&rarr;')
    # em dash in JS: ?\"
    $line = $line.Replace(' ?" ', ' &mdash; ')
    # Processing garbled
    $line = [regex]::Replace($line, 'Processing\?[^\s<"'']*', 'Processing...')
    # comment decorators: "?"? sequences
    $line = [regex]::Replace($line, '("?\?"?)+', '')
    # close button x: A- alone
    $line = [regex]::Replace($line, '>A-<', '>&times;<')
    return $line
}

for ($i = 0; $i -lt $lines.Count; $i++) {
    if ($lines[$i] -match '[^\x00-\x7F]') {
        $lines[$i] = Fix($lines[$i])
    }
}

Set-Content $path -Value $lines -Encoding UTF8
Write-Host "Fixed. Total lines: $($lines.Count)"
