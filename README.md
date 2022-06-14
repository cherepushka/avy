# avy.ru

## Требование к окружению:
- Версия `Elasticsearch` >= 8.2
- Версия `PHP` >= 8.1
- Версия `npm` >= 8.12
- Версия `NodeJS` >= 16.5

## Docker окружение

### запуск
- `docker-compose up -d`

### Elasticsearch
адрес по умолчанию: `http://localhost:9200`.
Данные для аутентификации не требуются

### Kibana
адрес по умолчанию: `http://localhost:5601`

## Установка (development)
- `composer install --dev`
- `npm install --save-dev`
- `npm run dev` или `npm run watch`