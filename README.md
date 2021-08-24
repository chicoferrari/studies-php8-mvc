## Lidando com requisições

Todo site que utiliza o PHP inicia-se com um simples arquivo, na maioria dos casos é o index.php.

As informações do PHP podem ser visualizadas em uma página web:

```php
<?php

phpinfo();
```

PHP permite o acesso as variáveis via requisições por scripts:

```php
<?php

$requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$requestPath = $_SERVER['REQUEST_URI'] ?? '/';

if ($requestMethod === 'GET' and $requestPath === '/') {
    print 'hello world';
} else {
    print '404 not found';
}
```

Adicionando HTML para as requisições das variáveis:
```php
<?php

$requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$requestPath = $_SERVER['REQUEST_URI'] ?? '/';

if ($requestMethod === 'GET' and $requestPath === '/') {
   print <<<HTML
       <!doctype html>
       <html lang="en">
           <body style="color:blue">
               hello human!
           </body>
       </html>
   HTML;
} else {
   include(__DIR__ . '/includes/404.php');
}
```

O PHP permite o redirecionamento de uma página cujo o URL não está mais em uso para um novo endereço, evitando assim a necessidade de alterar muitos trechos de código:

```php
<?php

$requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$requestPath = $_SERVER['REQUEST_URI'] ?? '/';

function redirectTo($path) {
   header("Location: {$path}", $replace = true, $code = 301);
   exit;
}

if ($requestMethod === 'GET' and $requestPath === '/') {
   print <<<HTML
       <!doctype html>
       <html lang="en">
           <body style="color:blue">
               hello human!
           </body>
       </html>
   HTML;
} else if ($requestPath === '/old-home') {
   redirectTo('/');
} else {
   include(__DIR__ . '/includes/404.php');
}
```

Os três tipos mais comuns de erros são:
* a URL está correta, mas a requisição está errada;
* a URL e a requisição estão corretas, mas o algum trecho do código está errado;
* a URL e a requisição estão corretas, mas o erro se encontra em algum outro parâmetro.

Para lidar com os casos descritos, pode-se rastrear todos os possíveis métodos de requisições:

```php
[...]
$routes = [
   'GET' => [
       '/' => fn() => print
       <<<HTML
           <!doctype html>
           <html lang="en">
               <body style="color:blue">
                   Hello human!
               </body>
           </html>
       HTML,
       '/old-home' => fn() => redirectTo('/'),
       '/has-server-error' => fn() => throw new Exception(),
       '/has-validation-error' => fn() => abort(400),
   ],
   'POST' => [],
   'PATCH' => [],
   'PUT' => [],
   'DELETE' => [],
   'HEAD' => [],
   '404' => fn() => include(__DIR__ . '/includes/404.php'),
   '400' => fn() => include(__DIR__ . '/includes/400.php'),
   '500' => fn() => include(__DIR__ . 'includes/500.php'),
];
$paths = array_merge(
   array_keys($routes['GET']),
   array_keys($routes['POST']),
   array_keys($routes['PATCH']),
   array_keys($routes['PUT']),
   array_keys($routes['DELETE']),
   array_keys($routes['HEAD']),
);
function abort($code) {
   global $routes;
   $routes[$code]();
}
set_error_handler(function() {
   abort(500);
});
if (isset(
   $routes[$requestMethod],
   $routes[$requestMethod][$requestPath],
)) {
   $routes[$requestMethod][$requestPath]();
} else if (in_array($requestPath, $paths)) {
   abort(400);
} else {
   abort(404);
}
```
