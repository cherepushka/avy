# search.avy.ru

## Описание
Этот репозиторий представляет из себя API сервиса поиска для поиска по PDF
каталогам продукции, а также интерфейс их загрузки в сам сервис поиска, которым
является Elasticsearch

## TODO
- Потестить загрузку каталогов
- Потестировать загрузку каталогов
- Тесты
- Линтер
- Сделать страницы с ошибками для админ панели

## Требование к окружению:
- Версия `Elasticsearch` = 8.2.2
- Версия `PHP` = 8.1
- Версия `npm` = 8.12
- Версия `NodeJS` = 16.5

<hr>

## Docker окружение
Для этого проекта есть внешнее Docker окружение  
с установленным PHP, Elasticsearch и Apache сервером.  
([Инструкция по установке](https://github.com/cherepushka/avy-dev-kit))

<hr>

## Установка (development)
1. `composer install --dev`
2. Заполнить `.env` файл необходимыми переменными
3. `npm install --save-dev`
4. `npm run dev` или `npm run watch`
5. `php bin/console doctrine:migrations:migrate`
6. `php bin/console doctrine:fixtures:load`
7. `php bin/console migrate:TreeFromJson ./.dev-data/category-tree.json`  
    \- загрузка тестовых данных о дереве категорий
8. `php bin/console migrate:productsWithExistingSeries` -  
    Загрузка информации о сериях с существующими продуктами
9. `mkdir ./.credentials` - данные для авторизации в API
