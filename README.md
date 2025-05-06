# Getting Started
Install dependencies.
```
docker compose run php composer install
```

# Shell
Run the command below and access http://127.0.0.1:8009.
```
docker compose up
```

Run the command below to run the sample code 'main.php'.
```
docker compose run --rm php /bin/bash -c 'php src/main.php'
```

Run the command below to run the test with mocking the HTTP stream response.
```
docker compose run --rm php /bin/bash -c 'vendor/bin/phpunit tests'
```
