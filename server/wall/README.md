# server/wall/

Server-side PHP for Wall of Edge™ persistence.

## Files
- `submit.php` — receives POST from /edge/, writes to /wall/data/entries.json
- `entries.php` — serves entries as JSON to wall-of-edge.html

## Deploy
These files deploy via SCP — they are NOT part of the npm build.
```bash
scp server/wall/submit.php sharper-one:/var/www/e1508a19-43fd-42c4-97a1-958e8b5e6763/public_html/wall/
scp server/wall/entries.php sharper-one:/var/www/e1508a19-43fd-42c4-97a1-958e8b5e6763/public_html/wall/
```

## Notes
- /wall/data/ must exist on server with Deny from all in .htaccess
- WOE_SECRET in entries.php must match WOE_SECRET in public/wall/index.html
