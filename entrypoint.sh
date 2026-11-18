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

# Seeders: los seeders no son necesariamente idempotentes, por eso es opt-in (RUN_SEED=true).
if [ "${RUN_SEED:-false}" = "true" ]; then
	php artisan db:seed --force || echo "db:seed falló; se continúa."
fi

# Sincronización con Authxolote (acciones/roles). Desactivar con SYNC_AUTHXOLOTE=false.
# Un fallo (p. ej. auth caído o token inválido) no detiene el contenedor.
if [ "${SYNC_AUTHXOLOTE:-true}" = "true" ]; then
	php artisan authxolote:actions || echo "authxolote:actions falló; se continúa."
fi
php artisan storage:link --force || true
php artisan optimize

# Documentación de la API (Scribe). Activar con SCRIBE_GENERATE=true.
# Se genera al arrancar porque necesita la BD (response calls). Un fallo no detiene el contenedor.
if [ "${SCRIBE_GENERATE:-false}" = "true" ]; then
	php artisan scribe:generate --no-interaction || echo "scribe:generate falló; se continúa sin docs."
fi

exec "$@"
