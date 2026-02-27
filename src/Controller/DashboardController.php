<?php
    declare(strict_types=1);

    namespace App\Controller;

    use App\Controller\AppController; // REQUIRED

    class DashboardController extends AppController
    {
        // Login action
        public function login()
    {
        $this->viewBuilder()->setLayout('login');

        if ($this->request->is('post')) {
            $username = $this->request->getData('username');
            $password = $this->request->getData('password');

            $user = $this->Users->find('all')
                ->where(['username' => $username, 'role' => 'technician'])
                ->first();

            if ($user && password_verify($password, $user->password)) {
                // Save session
                $this->request->getSession()->write('Auth.User', [
                    'id' => $user->id,
                    'username' => $user->username,
                    'role' => $user->role
                ]);

                // Redirect to Repairs dashboard
                return $this->redirect(['controller' => 'Repairs', 'action' => 'index']);
            } else {
                $this->Flash->error('Invalid username or password');
            }
        }
    }
        // Repairs dashboard
        public function repairs()
        {
            // Automatically renders templates/Dashboard/repairs.php
        }

        // Other dashboard sections
        public function stocks() {}
        public function analytics() {}
        public function profile() {}

        // Logout
        public function logout()
        {
            return $this->redirect(['controller' => 'Users', 'action' => 'login']);
        }
    }