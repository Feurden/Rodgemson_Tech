<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Event\EventInterface;
use Cake\Http\Response;          // ← ADD THIS

class ErrorController extends AppController
{
    public function initialize(): void
    {
        // Only add parent::initialize() if you are confident your `AppController` is safe.
    }

    /**
     * @param \Cake\Event\EventInterface<\Cake\Controller\Controller> $event Event.
     * @return \Cake\Http\Response|null       // ← fix docblock too
     */
    public function beforeFilter(EventInterface $event): ?Response  // ← use imported Response
    {
    }

    /**
     * @param \Cake\Event\EventInterface<\Cake\Controller\Controller> $event Event.
     * @return void
     */
    public function beforeRender(EventInterface $event): void
    {
        parent::beforeRender($event);
        $this->viewBuilder()->setTemplatePath('Error');
    }

    /**
     * @param \Cake\Event\EventInterface<\Cake\Controller\Controller> $event Event.
     * @return void
     */
    public function afterFilter(EventInterface $event): void
    {
    }
}