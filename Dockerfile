FROM webdevops/php-nginx:8.2-alpine

# Installation des dépendances nécessaires
RUN apk add --no-cache oniguruma-dev libxml2-dev \
    && docker-php-ext-install bcmath ctype fileinfo mbstring pdo_mysql xml

# Installation de Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Installation de NodeJS et npm
RUN apk add --no-cache nodejs npm

# Configuration de l'environnement
ENV WEB_DOCUMENT_ROOT /app/public
ENV APP_ENV production
WORKDIR /app
COPY . .

# Copie et configuration du fichier .env
RUN cp -n .env.example .env

# Installation et optimisation de l'application
# RUN composer install --no-interaction --optimize-autoloader --no-dev \
#     && php artisan key:generate \
#     && php artisan config:cache \
#     && php artisan route:cache \
#     && php artisan view:cache

# Compilation des assets
# RUN npm install \
#     && npm run build

# Changement de propriétaire pour les fichiers
RUN chown -R application:application .

# Expose le port 8101 pour Laravel
EXPOSE 8101

# Démarre le serveur Laravel en parallèle avec Nginx
CMD ["sh", "-c", "php artisan serve --host=0.0.0.0 --port=8101 & nginx -g 'daemon off;'"]
