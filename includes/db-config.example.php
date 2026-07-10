<?php
/* ==========================================================================
   DB-CONFIG.EXAMPLE.PHP
   Copy this file to includes/db-config.php and fill in your real values —
   directly on the server (Hostinger File Manager or FTP), never in chat.
   includes/db-config.php should NOT be committed anywhere public; it only
   needs to exist on the live server next to this example file.

   Until db-config.php exists, every form on the site degrades gracefully:
   submissions are logged to a local fallback file instead of failing silently
   (see api/_lib.php), so you never lose a lead while this is being set up.
   ========================================================================== */

return [
    'host'     => 'localhost',        // Hostinger DB host, from hPanel
    'database' => 'your_database_name',
    'username' => 'your_database_user',
    'password' => 'your_database_password',
    'charset'  => 'utf8mb4',
];
