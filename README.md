## Lidando com requisições

Todo site PHP inicia-se com um simples arquivo, neste caso, o index.php é responsável por receber as requisições do servidor web.
Por exemplo, as informações sobre o servidor PHP podem ser visualizadas por uma página web:

```php
<?php

phpinfo();
```

O PHP permite o acesso a variáveis solicitadas via scripts e para melhor entendimento, os nomes das variáveis tem que ser objetivo, ou seja, ter um significado claro:

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

As requisições podem ser apresentadas por HTML, e também via JSON ou XML. Para retornar diretamente no HTML:

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

Obs: A sintaxe <b>??</b> declarada acima diz que tudo que está à sua esquerda é nulo ou indefinido, logo será utilizado o que foi declarado à direita.

<br>

Também é possível fazer redirecionamentos das requisições, permitindo que uma URL antiga aponte diretamente para a URL nova:

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
