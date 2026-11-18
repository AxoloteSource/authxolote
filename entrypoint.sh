#!/bin/sh
set -e

if [ -n "${DB_HOST:-}" ]; then
	until php -r 'exit(@fsockopen(getenv("DB_HOST"), (int) (getenv("DB_PORT") ?: 3306), $e, $s, 2) ? 0 : 1);'; do
		echo "Waiting for database at ${DB_HOST}:${DB_PORT:-3306}..."
		sleep 2
	done
fi

if [ ! -f storage/oauth-private.key ] && [ -z "${PASSPORT_PRIVATE_KEY:-}" ]; then
	echo "Generating Passport keys..."
	php artisan passport:keys --force
fi

php artisan migrate --force
php artisan storage:link --force || true
php artisan optimize

# Documentación de la API (Scribe). Activar con SCRIBE_GENERATE=true.
# Se genera al arrancar porque necesita la BD (response calls). Un fallo no detiene el contenedor.
if [ "${SCRIBE_GENERATE:-false}" = "true" ]; then
	php artisan scribe:generate --no-interaction || echo "scribe:generate falló; se continúa sin docs."
fi

exec "$@"
