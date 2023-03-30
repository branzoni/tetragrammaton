# tet

---

## Описание
Простая библиотека, реализующая минимально необходимый функционал для комфортного создания простых php-приложений.

## Установка

```bash
    composer require branzoni/tet
```

## Объявление

```php
    use Tet\Tet;

    $tet = new Tet;

    $router = $tet->router();
    $router->setRoot("/");
    $router->get("/", function () {
        return "Hello!";
    });

    $router->get("/foo/bar", function () {
        return Foo::bar();
    });

    $tet->_run();

```

## Использование в классах

```php
    use Tet\Traits\Tet;

    class Foo{

        use Tet;

        static function bar()
        {
            // получение данных запрос
            $request = self::tet()->server()->getRequest();

            // запись в лог
            self::tet()->log()->error("Что-то пошло нет так");

            // выполнение запроса
            $result = self::tet()->mySQL()->execute("SELECT * FROM mytable");

            // получение файла
            $myfile = self::tet()->filesystem()->getFile($pathToMyFile);

            return new Response("", 200);

        }
    }

```