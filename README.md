# avy.ru

## Требование к окружению:
- Версия `Elasticsearch` >= 8.2
- Версия `PHP` >= 8.1
- Версия `npm` >= 8.12
- Версия `NodeJS` >= 16.5
- `Tesseract`

<hr>

## Внешнее Docker окружение
Для этого проекта есть внешнее Docker окружение  
с установленным PHP, Apache сервером и Tesseract.  
([Инструкция по установке](https://github.com/cherepushka/avy-dev-kit))

<hr>

### Внутреннее Docker кружение

Если вы используете свой веб сервер, то вы можете  
обойтись без внешнего Docker окружения.  
Достаточно запустить docker композицию внутри  
проекта, включающаю в себя Elasticsearch, Kibana и  
Mariadb.

#### Запуск

- `docker-compose up -d`

### Elasticsearch
адрес по умолчанию: `http://localhost:9200`.
Данные для аутентификации не требуются

### Kibana
адрес по умолчанию: `http://localhost:5601`.  
Данные для аутентификации не требуются

<hr>

## Установка (development)
1. `composer install --dev`
2. Заполнить `.env` файл необходимыми переменными
3. `npm install --save-dev`
4. `npm run dev` или `npm run watch`
5. `php bin/console doctrine:migrations:migrate`
6. `php bin/console doctrine:fixtures:load`
7. `php bin/console php bin/console MigrateTreeFromJson ./.dev-data/category-tree.json`  
    \- загрузка тестовых данных о дереве категорий
