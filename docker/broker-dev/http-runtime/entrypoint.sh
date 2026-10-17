#!/bin/sh
set -eu

: "${APP_PORT:?APP_PORT must be set}"
: "${APP_DOCUMENT_ROOT:?APP_DOCUMENT_ROOT must be set}"

cat > /etc/apache2/ports.conf <<EOF
Listen ${APP_PORT}
EOF

cat > /etc/apache2/sites-available/000-default.conf <<EOF
<VirtualHost *:${APP_PORT}>
    DocumentRoot ${APP_DOCUMENT_ROOT}
    <Directory ${APP_DOCUMENT_ROOT}>
        AllowOverride All
        Require all granted
    </Directory>
    ErrorLog /proc/self/fd/2
    CustomLog /proc/self/fd/1 combined
</VirtualHost>
EOF

# Apache's prefork MPM runs several workers, so a request forwarded by one
# broker can make a nested request to another service in this namespace.
exec apache2-foreground
