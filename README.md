# Aivo Twitter API Challenge

Con esta aplicación puedes obtener los feeds de un usuario de Twitter ingresando su username en la url
Ejemplo:
  - http://dominio.com/jorgeeferreyra

### Tecnologías

Las tecnologías utilizadas para el desafío fueron:

* [SlimFramwrodk](http://www.slimframework.com/) - a micro framework for PHP

### Installation

El proyecto necesita [Composer](https://getcomposer.org/) y [cURL](https://curl.haxx.se/)

Antes de comenzar verifique que estén seteadas las credenciales de Twitter apropiadamente en el archivo `app/constants.php`

Luego de clonado el proyecto

```sh
$ cd aivo-challenge
$ composer install
$ php -S localhost:8080 -t public public/index.php
```
