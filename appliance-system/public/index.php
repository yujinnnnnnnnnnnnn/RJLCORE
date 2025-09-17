<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/app/bootstrap.php';

$routePath = $_GET['r'] ?? trim(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '', '/');
$baseScriptDir = trim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/');
if ($baseScriptDir !== '') {
    $routePath = preg_replace('#^' . preg_quote($baseScriptDir, '#') . '/?#', '', $routePath);
}
if ($routePath === '' || $routePath === 'index.php') {
    $routePath = 'home';
}

$routes = [
    'home' => ['HomeController', 'index'],
    'about' => ['HomeController', 'about'],
    'login' => ['AuthController', 'login'],
    'register' => ['AuthController', 'register'],
    'logout' => ['AuthController', 'logout'],
    'forgot' => ['AuthController', 'forgot'],
    'reset' => ['AuthController', 'reset'],
    'dashboard' => ['DashboardController', 'index'],
    'customer' => ['CustomerController', 'index'],
];

$controllerName = $routes[$routePath][0] ?? null;
$actionName = $routes[$routePath][1] ?? null;

if ($controllerName === null || $actionName === null) {
    http_response_code(404);
    echo render('errors/404', ['title' => 'Page Not Found']);
    exit;
}

$controllerFile = BASE_PATH . '/app/controllers/' . $controllerName . '.php';
$controllerClass = 'App\\Controllers\\' . $controllerName;

if (file_exists($controllerFile)) {
    require_once $controllerFile;
}

if (!class_exists($controllerClass) || !method_exists($controllerClass, $actionName)) {
    http_response_code(404);
    echo render('errors/404', ['title' => 'Page Not Found']);
    exit;
}

$controllerInstance = new $controllerClass();
echo call_user_func([$controllerInstance, $actionName]);

