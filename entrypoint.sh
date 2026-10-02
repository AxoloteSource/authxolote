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
php artisan storage:link || true
php artisan optimize

exec "$@"
