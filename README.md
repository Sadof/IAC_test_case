Для установки требуется скачать репозиторий из гитхаба
git clone https://github.com/Sadof/IAC_test_case.git
Либо этот же репозиторий будет в архиве.
Из основной директории репозитория выполнить команды:
docker compose build --no-cache
docker compose up -d --wait
docker exec -it iac_test_case-php-1 /bin/sh
npm install
npm run build
php bin/console doctrine:fixtures:load


Перейти на https://localhost

Для данного задания используется докер из вики symfony

Сделано:
На бэке:
- Добавлены базы с продуктами, каталогами(цветов и категорий) и роли.
*В Product поле updated не является стандартным полем, а является примером “ Поле с датой и временем” с неудачным названием.
- Добавлены фикстуры для заполнения баз данных. 

Для создания таблиц используется система миграций Doctrine.

При заполнение базы создаются 4 роли:

ROLE_LIST_VIEW - с ролью ROLE_LIST_VIEW(возможен только просмотр таблицы)

ROLE_ADD - с ролью ROLE_ADD(возможен только добавление продукта)

ROLE_EDIT - с ролями ROLE_LIST_VIEW и ROLE_EDIT(возможен просмотр 
таблицы, и при клике на строку таблицы, кнопка “Редактировать” становится 
активной и ведет на страницу редактирования выбранной записи)

ROLE_DELETE - с ролями ROLE_LIST_VIEW и ROLE_DELETE(возможен просмотр 
таблицы, и при клике на строку таблицы, кнопка “Удалить” становится 
активной и при нажатие появляется confirm для удаления выбранной записи)
У всех аккаунтов устанавливается пароль “Qwerty123”.

-  Сделаны указанные апи методы для работы с продуктом

На фронте:


- Vue не в виде spa, а подключается на отдельных страницах и bootstrap для оформления. К сожалению не было времени подключить vuex

Проблемы:


При “Error response from daemon: Ports are not available: listen tcp 0.0.0.0:80: bind: address already in use” выполнить команду “sudo service apache2 stop && sudo service nginx stop”

