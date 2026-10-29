# syntax=docker/dockerfile:1

# ---------------------------------------------------------------------------
# 1) PHP dependencies (Composer, no dev)
# ---------------------------------------------------------------------------
FROM dunglas/frankenphp:php8.4-trixie AS vendor

RUN install-php-extensions zip

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

COPY composer.json composer.lock ./
RUN composer install \
        --no-dev \
        --no-interaction \
        --no-progress \
        --prefer-dist \
        --no-scripts \
        --no-autoloader

COPY . .

RUN composer dump-autoload --no-dev --optimize --classmap-authoritative \
    && php artisan package:discover --ansi


# ---------------------------------------------------------------------------
# 2) Runtime image (FrankenPHP)
# ---------------------------------------------------------------------------
FROM dunglas/frankenphp:php8.4-trixie AS app

RUN install-php-extensions pdo_mysql

WORKDIR /app

COPY --from=vendor /app /app

COPY Caddyfile /etc/caddy/Caddyfile
COPY entrypoint.sh /usr/local/bin/entrypoint.sh

RUN chmod +x /usr/local/bin/entrypoint.sh \
    && mkdir -p \
        storage/framework/cache \
        storage/framework/sessions \
        storage/framework/views \
        storage/logs \
        bootstrap/cache \
    && chmod -R ug+rwX storage bootstrap/cache

EXPOSE 80 443 443/udp

HEALTHCHECK --interval=30s --timeout=5s --start-period=60s --retries=3 \
    CMD php -r 'exit(@file_get_contents("http://127.0.0.1:".(getenv("PORT") ?: "80")."/up") ? 0 : 1);'

ENTRYPOINT ["entrypoint.sh"]
CMD ["frankenphp", "run", "--config", "/etc/caddy/Caddyfile"]
