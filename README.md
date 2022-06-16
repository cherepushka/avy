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

<hr>

### Elasticsearch
адрес по умолчанию: `http://localhost:9200`.
Данные для аутентификации не требуются

### Kibana
адрес по умолчанию: `http://localhost:5601`

## Установка (development)
- `composer install --dev`
- `npm install --save-dev`
- `npm run dev` или `npm run watch`