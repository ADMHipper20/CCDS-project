# Tubes Impal

## Development standard (bisa bertambah jika diperlukan)
1. Gunakan indentation 2 space agar kode tidak terlalu panjang
2. Berikan jarak antar section sehingga tiap bagian mudah dicek
6. Develop sesuai dengan branchnya untuk menghindari conflict atau tabrakan
7. Sebelum push pastikan sudah pull terlebih dahulu
8. PASTIKAN untuk tidak merge ke branch `main` jika fitur belum final dan/atau tanpa konfirmasi bersama

---

## Initial setup
Command untuk setup awal, pastikan file `.env` sudah disetup. Isi `BEARER_TOKEN` dengan string random. 
Lalu copy konten dari `storage/app/public` ke folder `public/storage`
```shell
# Install dependency
$ composer install

# Clear cache
$ composer dump-autoload
$ php artisan clear-compiled
$ php artisan optimize:clear
$ php artisan config:cache

# To generate APP_KEY in .env
$ php artisan key:generate

# Fresh migrate db schema
$ php artisan migrate:fresh --seed

# Link storage
$ php artisan storage:link
```

## Pull repo changes
```shell
# Run composer to install packages
$ composer install

# To migrate database changes
$ php artisan migrate

# To fill database tables
$ php artisan db:seed
```

## Start project
```shell
# Aktifkan main project
$ php artisan serve

# Aktifkan API_HOST
$ php artisan serve --port=8001
```

---

## Unit Test
```shell
# Pastikan project dan API_HOST sudah aktif
$ php artisan test
```
## Dusk Test
```shell
# Install Laravel Dusk
$ php artisan dusk:install

# Run all Dusk test
$ php artisan dusk

# Run spesific Dusk test
$ php artisan dusk tests/Browser/{NamaTest}.php
```

---

## Docker
```
Prasyarat:
1. Docker Desktop sudah aktif
2. Setting `DB_` di `.env` sesuaikan dengan setting mysql di `docker-compose.yml` (`DB_HOST` isi dengan "mysql")
3. Gunakan "http://nginx-api" pada setting `API_HOST` di `.env`
```
```shell
# Init docker swarm
$ docker swarm init

# Build image
$ docker build -t laravel-filament-app -f docker/php/Dockerfile .

# Deploy stack
$ docker stack deploy -c docker-compose.yml laravel

# Cek services apakah sudah aktif
$ docker service ls

# Masuk ke container app
$ docker exec -it $(docker ps -q -f name=laravel_app) bash

# Setup container app
$ composer install
$ php artisan key:generate
$ php artisan migrate:fresh --seed
$ php artisan storage:link

# Untuk menghentikan docker gunakan
$ docker stack rm laravel
```
