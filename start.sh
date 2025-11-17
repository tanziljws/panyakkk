#!/bin/bash

# Try to run migrations with retries
MAX_RETRIES=10
RETRY_DELAY=3

echo "Fixing migrations table structure if needed..."

# Fix migrations table structure before running migrations
# This fixes the issue where id column doesn't have AUTO_INCREMENT
php artisan db:show 2>/dev/null && php -r "
try {
    require __DIR__ . '/vendor/autoload.php';
    \$app = require_once __DIR__ . '/bootstrap/app.php';
    \$kernel = \$app->make('Illuminate\Contracts\Console\Kernel');
    \$kernel->bootstrap();
    
    \$db = Illuminate\Support\Facades\DB::connection();
    \$schema = Illuminate\Support\Facades\Schema::getConnection();
    
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
" 2>/dev/null || true

echo "Attempting to run database migrations..."

for i in $(seq 1 $MAX_RETRIES); do
    # Try to run migration, suppress error output to avoid log spam
    if php artisan migrate --force 2>/dev/null; then
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

echo "Starting Laravel server on port $PORT..."
exec php artisan serve --host=0.0.0.0 --port=$PORT

