*** HOW TO RUN ***

composer install

cp .env.example .env

php artisan jwt/secret

php artisan migrate --seed

php artisan serve 