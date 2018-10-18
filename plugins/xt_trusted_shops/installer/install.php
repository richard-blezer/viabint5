<?php

defined('_VALID_CALL') or die('Direct Access is not allowed.');

require_once _SRV_WEBROOT . _SRV_WEB_PLUGINS. 'xt_trusted_shops/classes/constants.php';

global $db;

// ############################### Table Shop Settings
$db->Execute("
        CREATE TABLE IF NOT EXISTS ".TABLE_TS_CERTIFICATES." (
          ".COL_TS_CERTS_ID." INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
          ".COL_TS_CERTS_KEY." VARCHAR(33) NOT NULL UNIQUE,
          ".COL_TS_CERTS_STATUS." VARCHAR(64),
          ".COL_TS_CERTS_RATING_ENABLED." SMALLINT(1),
          ".COL_TS_CERTS_PROTECTION_ITEMS." TEXT,
          ".COL_TS_CERTS_STATE." VARCHAR(64),
          ".COL_TS_CERTS_TYPE ." VARCHAR(64),
          ".COL_TS_CERTS_WSLOGIN." SMALLINT(1),
          ".COL_TS_CERTS_CURRENCIES." TEXT,
          ".COL_TS_CERTS_URL." VARCHAR(255),
          ".COL_TS_CERTS_LANG." VARCHAR(2),
          ".COL_TS_CERTS_SHOW_BADGE." VARCHAR(32),
          ".COL_TS_CERTS_SHOW_BADGE_POS." SMALLINT,
          ".COL_TS_CERTS_SHOW_SEAL." VARCHAR(128),
          ".COL_TS_CERTS_SHOW_VIDEO." VARCHAR(128),
          ".COL_TS_CERTS_SHOW_RATING." SMALLINT(1),
          ".COL_TS_CERTS_SHOW_RICH_SNIPPETS." SMALLINT(1),
          ".COL_TS_CERTS_RATE_LATER_AFTER." INT,

          PRIMARY KEY(".COL_TS_CERTS_ID.")
        );
        ");
