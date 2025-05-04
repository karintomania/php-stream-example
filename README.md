# Getting Started
Install dependencies.
```
docker compose run php composer install
```


# Shell
Run the command below and access http://127.0.0.1:8009
```
docker compose up
```

```
docker compose run --rm php /bin/bash -c 'php src/main.php'
```

```
docker compose run --rm php /bin/bash -c 'vendor/bin/phpunit tests'
```
