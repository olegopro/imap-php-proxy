# olegopro/imap-php-proxy

Этот модуль позволяет подключаться к протоколу imap через прокси.

Достоинства:
* Модуль очень гибкий, вы можете заменить любой элемент на свой собственный (ResponseContainer, Parser, Commander, реализовать свои собственные типы прокси).
реализовать свои собственные типы прокси).
* Уже реализованы Socks5 и Https прокси.

Ограничения
* Необходимо реализовать авторизацию через прокси-сервер
* Parser, Commander - эти объекты я бы не рекомендовал использовать в реальном проекте.
   Я включил его для примера, чтобы показать, как вы можете использовать потоки.



Для использования в вашем проекте, добавьте следующий код в composer.json:

```
    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/olegopro/imap-php-proxy.git"
           
        }
    ],
    "require": {
        "olegopro/imap-php-proxy": "dev-master"
    }
```

## Использование

```php
// Этот объект содержит все успешные и ошибочные действия
$responseContainer = \ImapConnector\Containers\ResponseContainer::getInstance();

$connector = new \ImapConnector\Connector($responseContainer);

// Этот объект предназначен для разбора ответа imap 
$parser = new \ImapConnector\Parsers\Parser();

// Экземпляр прокси Socks 5
$socks5Proxy = new \ImapConnector\Proxies\Socks5Proxy($responseContainer, "ip", 'port');

// Подключение к прокси (если вы пропустите эту строку, скрипт будет подключаться к imap напрямую, без прокси)
$connector->connectToProxy($socks5Proxy);

// Здесь мы получаем поток, который идет через прокси (Вы можете использовать этот поток в своем собственном порядк)
$stream = $connector->connectToImap("imap_host", 'imap_port');

// Здесь мы проверяем, успешно ли мы подключились к imap
if(is_resource($stream)) {

    // Здесь мы создаем обработчик комманд и передаем поток
    $commander = new \ImapConnector\Commander($stream, $parser, $responseContainer);

    // Вход через imap
    if($commander->login("login", "password")){
        echo "Success!";
    }

}
```

