# PowerShell script to check all Blade files for common error patterns
Get-ChildItem -Recurse -Filter *.blade.php |
    Select-String -Pattern '->field', '{{ {{', '->{{', '{{ _', 'ParseError', 'syntax error', 'unexpected token' |
    Select-Object Filename, LineNumber, Line |
    Format-Table -AutoSize 