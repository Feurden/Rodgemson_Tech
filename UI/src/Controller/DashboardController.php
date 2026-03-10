<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Http\Response;

class DashboardController extends AppController
{
    public function initialize(): void
    {
        parent::initialize();
        $this->loadComponent('Flash');
    }

    public function login(): ?Response
    {
        $this->request->allowMethod(['get', 'post']);

        if ($this->request->is('post')) {
            $data     = $this->request->getData();
            $username = trim((string)($data['username'] ?? ''));
            $password = trim((string)($data['password'] ?? ''));

            if ($username === '' || $password === '') {
                $this->Flash->error('Username and password are required.');
                return null;
            }

            $usersTable = $this->fetchTable('Users');

            $user = $usersTable->find()
                ->where(['username' => $username])
                ->first();

            if ($user && password_verify($password, $user->password)) {
                $this->getRequest()->getSession()->write('Auth.User', [
                    'id'       => $user->id,
                    'username' => $user->username,
                ]);

                return $this->redirect(['action' => 'analytics']);
            }

            $this->Flash->error('Invalid username or password.');
        }

        return null;
    }

    public function signup(): ?Response
    {
        $this->request->allowMethod(['get', 'post']);

        if ($this->request->is('post')) {
            $data     = $this->request->getData();
            $username = trim((string)($data['username'] ?? ''));
            $password = trim((string)($data['password'] ?? ''));
            $confirm  = trim((string)($data['confirm_password'] ?? ''));

            // Basic validation
            if ($username === '' || $password === '' || $confirm === '') {
                $this->Flash->error('All fields are required.');
                return null;
            }

            if (strlen($password) < 8) {
                $this->Flash->error('Password must be at least 8 characters.');
                return null;
            }

            if ($password !== $confirm) {
                $this->Flash->error('Passwords do not match.');
                return null;
            }

            $usersTable = $this->fetchTable('Users');

            // Check if username already exists
            $existing = $usersTable->find()
                ->where(['username' => $username])
                ->first();

            if ($existing) {
                $this->Flash->error('Username is already taken. Please choose another.');
                return null;
            }

            // Create and save new user
            $newUser = $usersTable->newEntity([
                'username' => $username,
                'password' => password_hash($password, PASSWORD_DEFAULT),
            ]);

            if ($usersTable->save($newUser)) {
                $this->Flash->success('Account created successfully. Please log in.');
                return $this->redirect(['action' => 'login']);
            }

            $this->Flash->error('Could not create account. Please try again.');
        }

        return null;
    }

    public function analytics(): ?Response
    {
        $user = $this->getRequest()->getSession()->read('Auth.User');
        if (empty($user)) {
            return $this->redirect(['action' => 'login']);
        }

        $devicesTable = $this->getTableLocator()->get('Devices');

        $totalRepairs     = $devicesTable->find()->count();
        $completedRepairs = $devicesTable->find()->where(['status' => 'Completed'])->count();
        $pendingRepairs   = $devicesTable->find()->where(['status !=' => 'Completed'])->count();
        $completionRate   = $totalRepairs > 0 ? round(($completedRepairs / $totalRepairs) * 100) : 0;

        // Weekly data — last 7 days
        $weeklyData = [];
        $dayNames   = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];

        for ($i = 6; $i >= 0; $i--) {
            $date    = new \DateTime("-$i days");
            $dateStr = $date->format('Y-m-d');
            $dayName = $dayNames[(int)$date->format('w')];

            $count = $devicesTable->find()
                ->where(['DATE(date_received)' => $dateStr])
                ->count();

            $weeklyData[] = [
                'day'   => $dayName,
                'date'  => $dateStr,
                'count' => $count,
            ];
        }

        // Monthly data — last 4 weeks
        $monthlyData = [];
        for ($week = 3; $week >= 0; $week--) {
            $weekStart = new \DateTime("-$week weeks");
            $weekEnd   = new \DateTime("-$week weeks +6 days");
            $weekLabel = 'Wk ' . (4 - $week);

            $count = $devicesTable->find()
                ->where(['DATE(date_received) >=' => $weekStart->format('Y-m-d')])
                ->andWhere(['DATE(date_received) <=' => $weekEnd->format('Y-m-d')])
                ->count();

            $monthlyData[] = [
                'week'  => $weekLabel,
                'start' => $weekStart->format('Y-m-d'),
                'end'   => $weekEnd->format('Y-m-d'),
                'count' => $count,
            ];
        }

        // Stock levels from parts table
        $partsTable  = $this->getTableLocator()->get('Parts');
        $parts       = $partsTable->find()->all();
        $stockLevels = [];

        foreach ($parts as $part) {
            $stockLevels[] = [
                'name'    => $part->part_name,
                'current' => $part->stock_quantity,
                'total'   => $part->minimum_stock ? $part->stock_quantity + 50 : 100,
                'color'   => '#38bdf8',
            ];
        }

        $this->set(compact(
            'user', 'totalRepairs', 'completedRepairs',
            'pendingRepairs', 'completionRate',
            'weeklyData', 'monthlyData', 'stockLevels'
        ));

        return null;
    }

    public function repairs(): ?Response
    {
        $user = $this->getRequest()->getSession()->read('Auth.User');
        if (empty($user)) {
            return $this->redirect(['action' => 'login']);
        }

        $devicesTable = $this->getTableLocator()->get('Devices');

        $devices = $devicesTable->find()
            ->contain(['Customers'])
            ->orderBy(['Devices.date_received' => 'DESC'])
            ->all();

        $repairs = [];
        $seenIds = [];

        foreach ($devices as $device) {
            if (in_array($device->id, $seenIds)) {
                continue;
            }
            $seenIds[] = $device->id;

            $repairs[] = [
                'id'              => 'D' . $device->id,
                'device'          => $device->brand . ' ' . $device->model,
                'issue'           => $device->issue_description,
                'customer'        => $device->customer?->full_name ?? 'N/A',
                'contact_no'      => $device->customer?->contact_no ?? '',
                'technician'      => $device->technician ?? 'Unassigned',
                'date'            => $device->date_received->format('M d, Y'),
                'status'          => strtolower($device->status ?? 'pending'),
                'finished'        => $device->date_released
                                        ? $device->date_released->format('M d, Y g:i A')
                                        : '',
                'notes'           => $device->customer?->phone_issue ?? '',
                'diagnostic'      => $device->customer?->diagnostic ?? '',
                'suggested_parts' => $device->customer?->suggested_part_replacement ?? '',
                'device_id'       => $device->id,
            ];
        }

        $this->set(compact('user', 'repairs'));
        return null;
    }

    public function stocks(): ?Response
    {
        $user = $this->getRequest()->getSession()->read('Auth.User');
        if (empty($user)) {
            return $this->redirect(['action' => 'login']);
        }

        $partsTable = $this->getTableLocator()->get('Parts');
        $parts      = $partsTable->find()
            ->orderBy(['Parts.part_name' => 'ASC'])
            ->all();

        $stocks = [];
        foreach ($parts as $part) {
            $status = $part->stock_quantity <= ($part->minimum_stock ?? 5)
                ? 'warning'
                : 'normal';

            $stocks[] = [
                'id'       => $part->id,
                'part'     => $part->part_name,
                'category' => $part->category ?? 'Uncategorized',
                'quantity' => $part->stock_quantity,
                'minimum'  => $part->minimum_stock ?? 5,
                'price'    => $part->unit_price,
                'status'   => $status,
            ];
        }

        $this->set(compact('user', 'stocks'));
        return null;
    }

    public function profile(): ?Response
    {
        $user = $this->getRequest()->getSession()->read('Auth.User');
        if (empty($user)) {
            return $this->redirect(['action' => 'login']);
        }

        $devicesTable   = $this->getTableLocator()->get('Devices');
        $totalJobs      = $devicesTable->find()->count();
        $completedJobs  = $devicesTable->find()->where(['status' => 'Completed'])->count();
        $inProgressJobs = $devicesTable->find()->where(['status' => 'In Progress'])->count();
        $pendingJobs    = $devicesTable->find()->where(['status' => 'Pending'])->count();

        $profile = [
            'username'       => $user['username'] ?? 'User',
            'role'           => 'Technician',
            'totalJobs'      => $totalJobs,
            'completedJobs'  => $completedJobs,
            'inProgressJobs' => $inProgressJobs,
            'pendingJobs'    => $pendingJobs,
        ];

        $this->set(compact('user', 'profile'));
        return null;
    }

    public function logout(): ?Response
    {
        $this->getRequest()->getSession()->destroy();
        return $this->redirect(['action' => 'login']);
    }
}
