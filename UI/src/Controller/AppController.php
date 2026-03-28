<?php
declare(strict_types=1);

/**
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link      https://cakephp.org CakePHP(tm) Project
 * @since     0.2.9
 * @license   https://opensource.org/licenses/mit-license.php MIT License
 */
namespace App\Controller;

use Cake\Controller\Controller;
use Cake\Http\Response;

/**
 * Application Controller
 *
 * Enforces session-based authentication for all controllers that extend this
 * class. Public actions (login, signup) are explicitly allowlisted below.
 * API routes return 401 JSON; page routes redirect to login.
 */
class AppController extends Controller
{
    /**
     * Actions that do not require an authenticated session.
     * Format: 'ControllerName.actionName'
     */
    private const PUBLIC_ACTIONS = [
        'Dashboard.login',
        'Dashboard.signup',
    ];

    public function initialize(): void
{
    parent::initialize();

    $this->loadComponent('Flash');

    // ADD THIS LINE:
    // This sends headers to the browser to prevent it from caching any page.
    $this->setResponse($this->getResponse()->withDisabledCache());
}

    /**
     * Runs before every action. Rejects unauthenticated requests before any
     * controller logic executes.
     */
    public function beforeFilter(\Cake\Event\EventInterface $event): ?Response
    {
        parent::beforeFilter($event);

        $controller = $this->getName();
        $action     = $this->request->getParam('action');
        $key        = "$controller.$action";

        if (in_array($key, self::PUBLIC_ACTIONS, true)) {
            return null;
        }

        $user = $this->getRequest()->getSession()->read('Auth.User');

        if (empty($user)) {
            // API requests get a 401 JSON response
            if ($this->request->is('json') || $this->request->accepts('application/json')) {
                return $this->response
                    ->withType('application/json')
                    ->withStatus(401)
                    ->withStringBody(json_encode([
                        'success' => false,
                        'error'   => 'Unauthenticated. Please log in.',
                    ]));
            }

            // Browser requests redirect to login
            return $this->redirect(['controller' => 'Dashboard', 'action' => 'login']);
        }

        return null;
    }
}