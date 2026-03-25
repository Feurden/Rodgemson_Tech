<?php
/**
 * Routes configuration.
 */

use Cake\Routing\Route\DashedRoute;
use Cake\Routing\RouteBuilder;

return function (RouteBuilder $routes): void {

    $routes->setRouteClass(DashedRoute::class);

    $routes->scope('/', function (RouteBuilder $builder): void {

        // Root → Dashboard login
        $builder->connect('/', ['controller' => 'Dashboard', 'action' => 'login']);

        $builder->connect('/pages/*', 'Pages::display');

        // Dashboard routes
        $builder->connect('/dashboard/login',          ['controller' => 'Dashboard', 'action' => 'login']);
        $builder->connect('/dashboard/signup',         ['controller' => 'Dashboard', 'action' => 'signup']);
        $builder->connect('/dashboard/logout',         ['controller' => 'Dashboard', 'action' => 'logout']);
        $builder->connect('/dashboard/analytics',      ['controller' => 'Dashboard', 'action' => 'analytics']);
        $builder->connect('/dashboard/repairs',        ['controller' => 'Dashboard', 'action' => 'repairs']);
        $builder->connect('/dashboard/stocks',         ['controller' => 'Dashboard', 'action' => 'stocks']);
        $builder->connect('/dashboard/profile',        ['controller' => 'Dashboard', 'action' => 'profile']);

        // POST-only — save profile edits from the Edit modal
        $builder->connect('/dashboard/update-profile', ['controller' => 'Dashboard', 'action' => 'updateProfile'],
            ['_method' => 'POST']
        );

        $builder->fallbacks();
    });

    // API — Device management
    $routes->post('/devices/add', [
        'controller' => 'Devices',
        'action'     => 'add',
    ]);

    $routes->patch('/devices/update', [
        'controller' => 'Devices',
        'action'     => 'update',
    ]);

    $routes->post('/devices/update', [
        'controller' => 'Devices',
        'action'     => 'update',
    ]);

    // API — Parts management
    $routes->post('/parts/add', [
        'controller' => 'Parts',
        'action'     => 'add',
    ]);

    $routes->post('/parts/restock', [
        'controller' => 'Parts',
        'action'     => 'restock',
    ]);

    // API — AI diagnosis
    $routes->post('/ai/diagnose', [
        'controller' => 'Ai',
        'action'     => 'diagnose',
    ]);

    $routes->post('/ai/saveFeedback', [
        'controller' => 'Ai',
        'action'     => 'saveFeedback',
    ]);

    $routes->post('/ai/checkFeedback', [
        'controller' => 'Ai',
        'action'     => 'checkFeedback',
    ]);

    // API — Parts usage (for repair parts selection)
    $routes->post('/parts-usage/get-by-names', ['controller' => 'PartsUsage', 'action' => 'getByNames']);
    $routes->post('/parts-usage/get-by-diagnosis', ['controller' => 'PartsUsage', 'action' => 'getByDiagnosis']);
    $routes->post('/parts-usage/deduct', ['controller' => 'PartsUsage', 'action' => 'deduct']);
    $routes->post('/parts-usage/return', ['controller' => 'PartsUsage', 'action' => 'returnParts']);
    $routes->post('/parts-usage/get-used', ['controller' => 'PartsUsage', 'action' => 'getUsed']);

    // API — Repair services usage
    $routes->post('/repair-services-usage/get-by-diagnosis', [
        'controller' => 'RepairServicesUsage',
        'action'     => 'getByDiagnosis',
    ]);

    $routes->post('/repair-services-usage/get-used', [
        'controller' => 'RepairServicesUsage',
        'action'     => 'getUsed',
    ]);

    $routes->post('/repair-services-usage/add', [
        'controller' => 'RepairServicesUsage',
        'action'     => 'add',
    ]);
};
