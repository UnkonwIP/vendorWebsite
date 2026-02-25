Add this to your crontab to run the cleanup daily at 03:00 and log output.

Example crontab line (edit path as needed):

0 3 * * * php /Users/danielho/Documents/vendorWebsite/scripts/cleanup_pending_accounts.php >> /var/log/cleanup_pending_accounts.log 2>&1

Notes:
- The script attempts to use the `$conn` mysqli instance from `config.php`. If `$conn` is not present, it will try a PDO fallback using `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS` constants defined in `config.php`.
- Review and adjust SQL table/column names in `scripts/cleanup_pending_accounts.php` if your schema differs.
- The script performs permanent DELETEs. Consider backing up or modifying it to perform soft-deletes or move rows to an archive table if you need auditing.
