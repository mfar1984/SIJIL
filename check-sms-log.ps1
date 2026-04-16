Get-Content storage/logs/laravel.log -Tail 100 | Select-String -Pattern "SMS|Infobip|certificate" -Context 3
