<?php
/** Configuración de la base de datos de WordPress */
define('DB_NAME', 'nginx');
define('DB_USER', 'admin');
define('DB_PASSWORD', 'Duoc.2022');
define('DB_HOST', 'databasewordpress-instance-1.cdufmw0hwyde.us-east-1.rds.amazonaws.com');
define('DB_CHARSET', 'utf8mb4');
define('DB_COLLATE', '');

/** Claves únicas de autenticación y sal */
define('AUTH_KEY',         'Duoc.2022');
define('SECURE_AUTH_KEY',  'Duoc.2022');
define('LOGGED_IN_KEY',    'Duoc.2022');
define('NONCE_KEY',        'Duoc.2022');
define('AUTH_SALT',        'Duoc.2022');
define('SECURE_AUTH_SALT', 'Duoc.2022');
define('LOGGED_IN_SALT',   'Duoc.2022');
define('NONCE_SALT',       'Duoc.2022');

/** Prefijo de la tabla de la base de datos */
$table_prefix = 'wp_';

/** Dirección URL de WordPress */
define('WP_SITEURL', 'http://www.alvaronavarro.com	');
define('WP_HOME', 'http://www.alvaronavarro.com	');

/** Activar el modo de depuración (desactivar en producción) */
define('WP_DEBUG', false);

/** Configuración del límite de memoria */
define('WP_MEMORY_LIMIT', '256M');

/** Configuración del límite de tiempo de ejecución */
set_time_limit(300);

/** Habilitar la edición de temas y plugins desde el panel de administración */
define('DISALLOW_FILE_EDIT', false);

/** Deshabilitar las actualizaciones automáticas */
define('AUTOMATIC_UPDATER_DISABLED', true);

/** Salvar las revisiones de las entradas de forma ilimitada */
define('WP_POST_REVISIONS', false);

/** Activar el almacenamiento en caché de objetos de WordPress */
define('WP_CACHE', true);

/** Deshabilitar la edición de archivos a través del editor de temas y plugins */
define('DISALLOW_FILE_MODS', true);

/** Configuración de seguridad adicional */
define('FORCE_SSL_ADMIN', true);
define('COOKIE_SECURE', true);
define('DISALLOW_UNFILTERED_HTML', true);
define('DISABLE_WP_CRON', true);

/** Configuración de la zona horaria */
define('WP_TIMEZONE', 'America/Los_Angeles');

/** Configuración de idioma */
define('WPLANG', '');

/** ¡Eso es todo, deja de editar! ¡Feliz blogging! */

/** Ruta absoluta al directorio de WordPress */
if (!defined('ABSPATH')) {
    define('ABSPATH', dirname(__FILE__) . '/');
}

/** Configuración de WordPress en variables globales y requerir archivos */
require_once(ABSPATH . 'wp-settings.php');
