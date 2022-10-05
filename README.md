# search.avy.ru

## Описание
Этот репозиторий представляет из себя API сервиса поиска для поиска по PDF
каталогам продукции, а также интерфейс их загрузки в сам сервис поиска, которым
является Elasticsearch

## TODO
- Потестировать загрузку каталогов
- Тесты
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

## Документация
- [Установка проекта](docs/INSTALL.md)
- [API спецификация](docs/API.md)

