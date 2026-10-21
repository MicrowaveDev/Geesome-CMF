# PHP2Shell: common-sites, 21 октября 2026

Закрыты CVE-2026-109618 и CVE-2026-109619 на всех PHP upstream Nginx:
Zeligen (`zeligen.art`, `zeligen.me`, `zeligen.blog`, `zeligen.jonybang.cc`),
`bjd.guru` и `games-please.com`.

## Установленная версия

- До обновления: веб PHP 7.0.33 / 7.3.16, CLI PHP 7.1.28; Ubuntu 18.04.
- После: PHP **7.4.34-dev**, собранный из официальной ветки
  [PHP-7.4-CVE-2026-109618-109619](https://github.com/php/php-src/tree/PHP-7.4-CVE-2026-109618-109619),
  commit `712e60f9edfd03f0960fc4fc3c2c375f42b90aa2`.
- Каталог: `/opt/php74-security`; служба: `php74-security-fpm.service`.
- Пулы: `/run/php/php74-security-zeligen.sock` и
  `/run/php/php74-security-legacy73.sock`.
- CLI `/usr/bin/php` переключён через `update-alternatives` на новую сборку.
- Старые FPM 7.0 и 7.3 остановлены и отключены из автозапуска.
- `session.upload_progress.enabled=0`; память 256 МБ, POST 32 МБ,
  один загружаемый файл — до 24 МБ. Пределы закреплены в FPM pool.
- Сокет MySQL: `/var/run/mysqld/mysqld.sock`.
- Ротация журналов: `/etc/logrotate.d/php74-security`.

Это адресный официальный backport двух уязвимостей, а не возобновление
поддержки PHP 7.4. Обновление Ubuntu, Laravel и прочих зависимостей ещё
не выполнено. Эта сборка не обновляется через APT; нужен дальнейший
переход на поддерживаемый стек.

## Совместимость Laravel 5.3

В `vendor/laravel/framework/src/Illuminate/Database/Query/Builder.php`
из `addWhereExistsQuery()` удалён несуществующий аргумент `operator`
в `compact()`. На PHP 7.0 он пропускался, на PHP 7.4 вызывал исключение.
Изменение сохранено в [laravel53-php74.patch](laravel53-php74.patch).

Composer автоматически запускает `patch-legacy-laravel` первым шагом
`post-install-cmd` и `post-update-cmd`, до Laravel callbacks и `artisan optimize`.
Скрипт не требует загрузки Laravel, повторный запуск не изменяет уже исправленный
файл. Неизвестная структура Query Builder или отсутствие файла останавливают
Composer с ошибкой вместо пропуска исправления.

Ручной запуск, в том числе после установки с `--no-scripts`:

```sh
composer run-script patch-legacy-laravel
```

Этот скрипт обеспечивает совместимость приложения с PHP 7.4. Сами CVE
исправлены в установленном PHP runtime; Composer не обновляет системный PHP.

## Проверки и материалы на сервере

В `/root/php2shell-20261021/` сохранены исходники, команда configure,
журналы сборки, резервные копии `/etc/php`, `/etc/nginx` и изменённого
Laravel Builder, скрипты и результаты проверок.
Отпечатки установленных бинарников: `/opt/php74-security/BINARY-SHA256`.

Проверено:

- 90 URL из sitemap до обновления, на тестовом FPM и после переключения:
  HTTP 200, одинаковые статусы и заголовки страниц.
- Форма входа Zeligen: HTTP 200, включая внешний HTTPS-запрос.
- Главная, обсуждения и вход BJD; главная, вход и REST API WordPress:
  одинаковые статусы и заголовки до/после обновления.
- Оба FPM pool используют новую версию и необходимые расширения.
- Контрольный shutdown callback работает; при OOM исправленный PHP
  немедленно завершает процесс с кодом 1 без выполнения небезопасного callback.
- Nginx, автозапуск нового FPM и остановка старых FPM.
- Автопатч: исходный vendor, повторный запуск без изменения файла,
  отказ без записи при неизвестном или отсутствующем файле; оба Composer
  event hook на установленном Composer 1.8.5. Синтаксис проверен на PHP 7.0 и 7.4.

Временные тестовые HTTP-порты удалены из Nginx. Авторизованные действия
в админках и полноценная проверка сервера на компрометацию не выполнялись.
Возврат к старым FPM снова откроет уязвимости.

## Освобождённое место

Удалён неиспользуемый Docker build cache (`docker builder prune -a -f`).
Тома, работающие контейнеры и образы не удалялись. На Docker-разделе
стало свободно около 10 ГБ вместо 0. Архивный `syslog.1` сжат с 455 МБ
до 14 МБ с сохранением записей. Промежуточные файлы сборки PHP очищены.
