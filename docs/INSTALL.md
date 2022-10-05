# Установка
Ниже приведены команды для установки DEV и PROD версии проекта

## DEV
Каждый коммит должен пройти через утилиту `php-cs-fixer`, которая установится composer\`ом.
Поэтому перед запускам установки, вам необходимо выполнить слудующую команду:  
  
`git config core.hooksPath .githooks`  
  
Она поставит в ваш локальный репозиторий pre-commit хук, который будет вызывать `php-cs-fixer` перед каждым коммитом.  
  
Далее выполните следующие команды:
1. Заполнить `.env` файл необходимыми переменными
2. `composer install --dev`
3. `npm install --save-dev`
4. `npm run build-admin`
5. `npm run dev` или `npm run watch`
6. `php bin/console doctrine:migrations:migrate`
7. `php bin/console doctrine:fixtures:load` - загрузка фикстур для БД
8. `php bin/console migrate:TreeFromJson ./.dev-data/category-tree.json`  
    \- загрузка тестовых данных о дереве категорий
9. `php bin/console migrate:productsWithExistingSeries` -  
    Загрузка информации о сериях с существующими продуктами
10. `mkdir ./.credentials` - создание папки для хранения API-токенов

## PROD

1. Заполнить `.env` файл необходимыми переменными
2. `composer install`
3. `npm install`
4. `npm run build`
5. `npm run build-admin`
6. `php bin/console doctrine:migrations:migrate`
7. `php bin/console doctrine:fixtures:load` - загрузка фикстур для БД
8. `php bin/console migrate:TreeFromJson ./.dev-data/category-tree.json`  
    \- загрузка тестовых данных о дереве категорий
9.  `php bin/console migrate:productsWithExistingSeries` -  
    Загрузка информации о сериях с существующими продуктами
10. `mkdir ./.credentials` - создание папки для хранения API-токенов