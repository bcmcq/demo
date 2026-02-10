#!/bin/bash
set -e

# Parse --key argument
CRYPT_KEY=""
for arg in "$@"; do
    case "$arg" in
        --key=*)
            CRYPT_KEY="${arg#--key=}"
            ;;
    esac
done

# 1. Environment — must come first so docker-compose picks up DB_DATABASE etc.
cp .env.example .env

# Decrypt MUX tokens if a key was provided
if [[ -n "$CRYPT_KEY" ]]; then
    echo 'Decrypting MUX tokens…'
    ./crypt.sh --key="$CRYPT_KEY" --decrypt
fi

# 2. Install Composer dependencies (matching PHP 8.3 runtime)
echo 'Installing Composer dependencies…'
docker run --rm \
    -u "$(id -u):$(id -g)" \
    -v "$(pwd):/var/www/html" \
    -w /var/www/html \
    laravelsail/php83-composer:latest \
    composer install --ignore-platform-reqs

# 3. Start Sail (MySQL auto-creates DB from MYSQL_DATABASE in .env)
echo 'Starting Sail containers…'
./vendor/bin/sail up -d

# 4. Wait for MySQL, then initialize Laravel
echo 'Waiting for database…'
until ./vendor/bin/sail artisan db:show &>/dev/null; do
    sleep 2
done

echo 'Running migrations and seeding…'
./vendor/bin/sail artisan key:generate
./vendor/bin/sail artisan migrate:fresh --seed

echo 'Done! Visit http://localhost'
