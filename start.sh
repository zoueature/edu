composer install
php artisan key:genrate

php artisan migrate
php artisan passport:client --password
php artisan queue:table
php artisan migrate


#nohup php artisan websocket:start &

#php artisan queue:listen
php artisan serve