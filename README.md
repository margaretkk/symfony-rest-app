# Symfony REST API Project

Простий REST API для керування користувачами з MongoDB.  
Кожен користувач зберігає: `name`, `email`, `ip`, `country`.

---

## Запуск проекту

### 1. Клонування репозиторію
```bash
git clone git@github.com:margaretkk/symfony-rest-app.git
cd symfony-rest-app
```

### 2. Встановлення залежностей 
```bash
composer install
```

### 3. Налаштування і запуск
```bash
cp .env .env.local
```
DATABASE_URL=mongodb://localhost:27017
```
docker run -d -p 27017:27017 --name mongo mongo:7
php -S 127.0.0.1:8000 -t public
```

### 4. Залежності
```
Symfony 8
PHP 8.4
MongoDB PHP Driver
symfony/http-client (для визначення країни по IP)
PHPUnit (для тестів)
Docker (для запуску MongoDB)
```




