# PowerShell script to auto-fix common Blade errors in Blade files with progress reporting

# Get all blade files
$files = Get-ChildItem -Recurse -Filter *.blade.php
$total = $files.Count
$fixed = 0

Write-Host "Total .blade.php files found: $total"

foreach ($i in 0..($total-1)) {
    $file = $files[$i].FullName
    # Backup the file
    Copy-Item $file ($file + ".bak") -Force
    # Read content
    $content = Get-Content $file -Raw
    # Automated replacements
    $content = $content -replace '->field', '->id'
    $content = $content -replace '\{\{\s*\{\{', '{{'
    $content = $content -replace '->\s*\{\{', '->'
    $content = $content -replace '\{\{\s*_', '{{' # This just removes the underscore, you may want to review these manually
    # Write back
    Set-Content $file $content
    $fixed++
    Write-Host ("Processing {0}/{1}: {2}" -f ($i+1), $total, $file)
}

Write-Host "Finished fixing $fixed files. Backups created with .bak extension." 