#!/bin/bash

# Try to run migrations with retries
MAX_RETRIES=10
RETRY_DELAY=3

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

