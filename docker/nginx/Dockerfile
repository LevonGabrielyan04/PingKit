FROM nginx:1.27-alpine

COPY docker/nginx/default.conf /etc/nginx/conf.d/default.conf

# Serve the Laravel app from the compiled project files.
COPY public /var/www/html/public

# Create the same symlink as `php artisan storage:link` so cached assets work.
# The target is expected to be backed by the mounted `laravel_storage` volume at runtime.
RUN ln -sf ../storage/app/public /var/www/html/public/storage

CMD ["nginx", "-g", "daemon off;"]

