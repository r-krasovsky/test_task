Для работы JS библиотек я настраивал у себя симлинки из vendor в public, но у верен, что они сохранятся при копировании.
Возможно, симлинки нужно будет создать заново.
В любом случае я не стал удалять эти файлы из public

Пути симлинков:

public/js/jquery  ->  vendor/components/jquery

public/js/bootstrap  ->  vendor/components/bootstrap/js
public/css/bootstrap  ->  vendor/components/bootstrap/css

public/js/datatables  ->  vendor/datatables/datatables/media/js
public/css/datatables  ->  vendor/datatables/datatables/media/css

В файле config.php базовая конфигурация приложения.
Как минимум стоит прописать своё значение параметра "site_url"
Там же хранятся настройки подключения к БД

Для работы CAPTCHA необходимо, чтобы сайт работал по HTTPS

API находится по пути {HOST}/api/
Доступны команды:
{HOST}/api/list  - Вернет список пользователей
{HOST}/api/user/{id} - Вернет данные пользователя

GET параметры для доступа к API:
key - API ключ пользователя
type - Тип возвращаемых данных xml или json


