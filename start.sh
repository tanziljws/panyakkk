#!/bin/bash

# Try to run migrations with retries
MAX_RETRIES=10
RETRY_DELAY=3

echo "=== Starting Laravel Application ==="
echo "Port: ${PORT:-8080}"

# Fix migrations table structure before running migrations
echo "Fixing migrations table structure if needed..."
php artisan db:show 2>/dev/null && php -r "
try {
    require __DIR__ . '/vendor/autoload.php';
    \$app = require_once __DIR__ . '/bootstrap/app.php';
    \$kernel = \$app->make('Illuminate\Contracts\Console\Kernel');
    \$kernel->bootstrap();
    
    if (Illuminate\Support\Facades\Schema::hasTable('migrations')) {
        \$result = Illuminate\Support\Facades\DB::select(\"SHOW COLUMNS FROM migrations WHERE Field = 'id'\");
        if (!empty(\$result)) {
            \$col = \$result[0];
            if (stripos(\$col->Extra ?? '', 'auto_increment') === false) {
                Illuminate\Support\Facades\DB::statement('ALTER TABLE migrations MODIFY id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT');
                echo 'Fixed migrations table structure' . PHP_EOL;
            }
        }
    }
} catch (Exception \$e) {
    // Ignore errors
}
" 2>/dev/null || echo "Could not fix migrations table (may not exist yet)"

echo "Attempting to run database migrations..."

for i in $(seq 1 $MAX_RETRIES); do
    if php artisan migrate --force 2>&1 | grep -q "Nothing to migrate"; then
        echo "No migrations to run."
        break
    elif php artisan migrate --force > /dev/null 2>&1; then
        echo "Migrations completed successfully!"
        break
    else
        if [ $i -eq $MAX_RETRIES ]; then
            echo "Migration failed after $MAX_RETRIES attempts. Database may not be ready yet."
            echo "Starting server anyway. Migrations can be run manually later."
        else
            echo "Migration attempt $i/$MAX_RETRIES failed. Retrying in ${RETRY_DELAY}s..."
            sleep $RETRY_DELAY
        fi
    fi
done

echo "Starting Laravel server on 0.0.0.0:${PORT:-8080}..."
exec php artisan serve --host=0.0.0.0 --port=${PORT:-8080}

