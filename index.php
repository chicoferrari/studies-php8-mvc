<?php

//phpinfo();

//var_dump(getenv('PHP_ENV'), $_SERVER, $_REQUEST);

$requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$requestPath = $_SERVER['REQUEST_URI'] ?? '/';

/*if ($requestMethod === 'GET' and $requestPath === '/') {
    print 'hello world';
} else {
    print '404 not found';
} */

/*if ($requestMethod === 'GET' and $requestPath === '/') {
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
} */

function redirectTo($path)
{
    header("Location: {$path}", $replace = true, $code = 301);
    exit;
}

/*if ($requestMethod === 'GET' and $requestPath === '/') {
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
} */

$routes = [
    'GET' => [
        '/' => fn () => print
            <<<HTML
            <!doctype html>
            <html lang="en">
                <body style="color:blue">
                    Hello human!
                </body>
            </html>
        HTML,
        '/old-home' => fn () => redirectTo('/'),
        '/has-server-error' => fn () => throw new Exception(),
        '/has-validation-error' => fn () => abort(400),
    ],
    'POST' => [],
    'PATCH' => [],
    'PUT' => [],
    'DELETE' => [],
    'HEAD' => [],
    '404' => fn () => include(__DIR__ . '/includes/404.php'),
    '400' => fn () => include(__DIR__ . '/includes/400.php'),
    '500' => fn () => include(__DIR__ . 'includes/500.php'),
];
$paths = array_merge(
    array_keys($routes['GET']),
    array_keys($routes['POST']),
    array_keys($routes['PATCH']),
    array_keys($routes['PUT']),
    array_keys($routes['DELETE']),
    array_keys($routes['HEAD']),
);
function abort($code)
{
    global $routes;
    $routes[$code]();
}
set_error_handler(function () {
    abort(500);
});
set_exception_handler(function() {
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
