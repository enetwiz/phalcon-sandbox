<?php

use Phalcon\Loader;
use Phalcon\Db\Adapter\Pdo\Mysql as DbAdapter;
use Phalcon\Di\FactoryDefault;
use Phalcon\Events\Manager as EventsManager;
use Phalcon\Flash\Direct as FlashDirect;
use Phalcon\Mvc\View;
use Phalcon\Mvc\Application;
use Phalcon\Mvc\Dispatcher;
use Phalcon\Mvc\Url as UrlProvider;
use Phalcon\Session\Adapter\Files as SessionAdapter;

//phpinfo();
//die();

try {

    /**
     * Debug configuration
     */
    error_reporting(-1);
    ini_set('display_errors', 'On');

    /**
     * Define the constants
     */
    define('APP_PATH', realpath('..') . '/');

    // Register an autoloader
    $loader = new Loader();
    $loader->registerDirs(array(
        APP_PATH . '/app/controllers/',
        APP_PATH . '/app/models/',
        APP_PATH . '/app/plugins/'
    ))->register();

    // Stwórz DI
    $di = new FactoryDefault();

    // Register the flash service with custom CSS classes
    $di->set('flash', function () {
        $flash = new FlashDirect(
            array(
                'error'   => 'alert alert-danger',
                'success' => 'alert alert-success',
                'notice'  => 'alert alert-info',
                'warning' => 'alert alert-warning'
            )
        );

        return $flash;
    });

    /**
     * Start the session the first time some component request the session service
     */
    $di->set('session', function () {
        $session = new SessionAdapter();
        $session->start();
        return $session;
    });

    // Setup the database service
    $di->set('db', function () {
        return new DbAdapter(array(
            "host"     => "localhost",
            "username" => "root",
            "password" => "root",
            "dbname"   => "phalcon_sandbox"
        ));
    });

    // Setup event dispatcher
    $di->set('dispatcher', function () {

        // Create an events manager
        $eventsManager = new EventsManager();

        // Listen for events produced in the dispatcher using the Security plugin
        $eventsManager->attach('dispatch:beforeExecuteRoute', new SecurityPlugin);

        $dispatcher = new Dispatcher();

        // Assign the events manager to the dispatcher
        $dispatcher->setEventsManager($eventsManager);

        return $dispatcher;
    });

    // Setup the view component
    $di->set('view', function () {
        $view = new View();
        $view->setViewsDir(APP_PATH . '/app/views/');
        return $view;
    });

    // Setup a base URI so that all generated URIs include the "tutorial" folder
    $di->set('url', function () {
        $url = new UrlProvider();
        $url->setBaseUri('/');
        return $url;
    });

    // Handle the request
    $application = new Application($di);

    echo $application->handle()->getContent();

} catch (\Exception $e) {
    echo "Exception: ", $e->getMessage();
}