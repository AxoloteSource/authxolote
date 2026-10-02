# syntax=docker/dockerfile:1

# ---------------------------------------------------------------------------
# 1) Frontend assets (Vite + pnpm)
# ---------------------------------------------------------------------------
FROM node:22-bookworm-slim AS assets

RUN corepack enable && corepack prepare pnpm@9 --activate

WORKDIR /app

COPY package.json pnpm-lock.yaml pnpm-workspace.yaml ./
RUN pnpm install --frozen-lockfile

COPY resources ./resources
COPY public ./public
COPY vite.config.ts tsconfig.json ./

ARG VITE_APP_NAME
ARG VITE_APP_URL
ARG VITE_AUTH_URL
ARG VITE_MENU_SLUG

ENV VITE_APP_NAME=$VITE_APP_NAME \
    VITE_APP_URL=$VITE_APP_URL \
    VITE_AUTH_URL=$VITE_AUTH_URL \
    VITE_MENU_SLUG=$VITE_MENU_SLUG

RUN pnpm build


# ---------------------------------------------------------------------------
# 2) PHP dependencies (Composer, no dev)
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
# 3) Runtime image (FrankenPHP)
# ---------------------------------------------------------------------------
FROM dunglas/frankenphp:php8.4-trixie AS app

RUN install-php-extensions pdo_mysql

WORKDIR /app

COPY --from=vendor /app /app
COPY --from=assets /app/public/build /app/public/build

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
