# The howdoo test app

## Requirements

* PHP 7.2 or above
* Laravel 6 or above
* Any relational db supported by laravel

## Installation

```bash
git clone git@github.com:mjelamanov/howdoo-test.git
```

Copy .env file

```bash
cp .env.example .env
```

Set your DB connection in copied .env file above

```
DB_CONNECTION=sqlite
```

Install dependencies

``` bash
composer install
```

Set application key

``` bash
php artisan key:generate
```

Migrate and seeding DB

``` bash
php artisan migrate --seed
```

Run built-in server

``` bash
php artisan serve --host=localhost --port=8000
```

Done!

## Docker

Using docker-compose

```bash
git clone git@github.com:mjelamanov/howdoo-test.git
```

```bash
cd howdoo-test
```

``` bash
docker-compose up --build -d
```

## Usage

Retrieve a list of documents

```
GET http://localhost:8000/api/v1/document?page=1&perPage=10
```

Retrieve a document by id

```
GET http://localhost:8000/api/v1/document/{id}
```

Create a new draft document

```
POST http://localhost:8000/api/v1/document
```

Edit a document

```
PUT http://localhost:8000/api/v1/document/{id}
```

Publish a document

```
POST http://localhost:8000/api/v1/document/{id}/publish
```

## Auth

Getting an auth token

```
POST http://localhost:8000/api/v1/login
```

Sending the auth token to the endpoints above

```http
POST /api/v1/document HTTP/1.1
Authorization: Bearer <token>
Content-Type: application/json
Accept: application/json
```

## Tests

Run via console

```bash
php vendor/bin/phpunit
```

Run via docker

```bash
docker-compose exec app php vendor/bin/phpunit
```
