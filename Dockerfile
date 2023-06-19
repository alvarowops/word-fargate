FROM wordpress:latest

# Variables de entorno para la configuración de WordPress
ENV WORDPRESS_DB_NAME=nginx \
    WORDPRESS_DB_USER=admin \
    WORDPRESS_DB_PASSWORD=Duoc.2022 \
    WORDPRESS_DB_HOST=databasewordpress-instance-1.cdufmw0hwyde.us-east-1.rds.amazonaws.com \
    WORDPRESS_DEBUG=false \
    WORDPRESS_TABLE_PREFIX=wp_ \
    WORDPRESS_DISABLE_FILE_EDIT=false \
    WORDPRESS_AUTOMATIC_UPDATER_DISABLED=true \
    WORDPRESS_POST_REVISIONS=false \
    WORDPRESS_CACHE=true \
    WORDPRESS_DISALLOW_FILE_MODS=true \
    WORDPRESS_FORCE_SSL_ADMIN=true \
    WORDPRESS_COOKIE_SECURE=true \
    WORDPRESS_DISALLOW_UNFILTERED_HTML=true \
    WORDPRESS_DISABLE_WP_CRON=true \
    WORDPRESS_TIMEZONE=America/Los_Angeles

# Configurar las variables de entorno para la instalación de WordPress
ENV WORDPRESS_CONFIG_EXTRA="\
    define('WP_SITEURL', 'www.alvaronavarro.com	'); \
    define('WP_HOME', 'www.alvaronavarro.com'); \
    define('WP_POST_REVISIONS', false); \
    define('WP_CACHE', true); \
    define('DISALLOW_FILE_MODS', true); \
    define('FORCE_SSL_ADMIN', true); \
    define('COOKIE_SECURE', true); \
    define('DISALLOW_UNFILTERED_HTML', true); \
    define('DISABLE_WP_CRON', true); \
    define('WP_TIMEZONE', 'America/Los_Angeles');"

# Copiar el archivo wp-config.php personalizado al contenedor
COPY wp-config.php /var/www/html/wp-config.php
