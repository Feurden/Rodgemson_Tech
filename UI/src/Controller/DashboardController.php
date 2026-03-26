<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Http\Response;
use Cake\Datasource\ConnectionManager;

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
                    'role'     => $user->role,
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

            $existing = $usersTable->find()
                ->where(['username' => $username])
                ->first();

            if ($existing) {
                $this->Flash->error('Username is already taken. Please choose another.');
                return null;
            }

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

        $totalRepairs      = $devicesTable->find()->count();
        $completedRepairs  = $devicesTable->find()->where(['status' => 'Completed'])->count();
        $inProgressRepairs = $devicesTable->find()->where(['status' => 'In Progress'])->count();
        $pendingRepairs    = $devicesTable->find()->where(['status' => 'Pending'])->count();
        $completionRate    = $totalRepairs > 0 ? round(($completedRepairs / $totalRepairs) * 100) : 0;

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

        // Monthly data — last 4 weeks with status breakdown
        $monthlyData = [];
        for ($week = 3; $week >= 0; $week--) {
            $weekStart = new \DateTime("-$week weeks");
            $weekEnd   = new \DateTime("-$week weeks +6 days");
            $weekLabel = 'Wk ' . (4 - $week);

            $completed  = $devicesTable->find()->where(['status' => 'Completed',   'DATE(date_received) >=' => $weekStart->format('Y-m-d'), 'DATE(date_received) <=' => $weekEnd->format('Y-m-d')])->count();
            $inProgress = $devicesTable->find()->where(['status' => 'In Progress', 'DATE(date_received) >=' => $weekStart->format('Y-m-d'), 'DATE(date_received) <=' => $weekEnd->format('Y-m-d')])->count();
            $pending    = $devicesTable->find()->where(['status' => 'Pending',     'DATE(date_received) >=' => $weekStart->format('Y-m-d'), 'DATE(date_received) <=' => $weekEnd->format('Y-m-d')])->count();

            $monthlyData[] = [
                'week'        => $weekLabel,
                'start'       => $weekStart->format('Y-m-d'),
                'end'         => $weekEnd->format('Y-m-d'),
                'count'       => $completed + $inProgress + $pending,
                'completed'   => $completed,
                'in_progress' => $inProgress,
                'pending'     => $pending,
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
            'inProgressRepairs', 'pendingRepairs', 'completionRate',
            'weeklyData', 'monthlyData', 'stockLevels'
        ));

        return null;
    }

    /**
     * AJAX: daily breakdown for a specific month + year (Weekly tab)
     */
    public function getWeeklyByMonth(): Response
    {
        $user = $this->getRequest()->getSession()->read('Auth.User');
        if (empty($user)) {
            return $this->response->withType('application/json')->withStatus(401)
                ->withStringBody(json_encode(['success' => false, 'error' => 'Unauthenticated']));
        }

        $month = (int)($this->request->getQuery('month') ?? date('n'));
        $year  = (int)($this->request->getQuery('year')  ?? date('Y'));

        $devicesTable = $this->getTableLocator()->get('Devices');
        $dayNames     = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
        $daysInMonth  = (int)(new \DateTime("$year-$month-01"))->format('t');
        $monthStart   = sprintf('%04d-%02d-01', $year, $month);
        $monthEnd     = sprintf('%04d-%02d-%02d', $year, $month, $daysInMonth);

        $days  = [];
        $total = 0;

        for ($d = 1; $d <= $daysInMonth; $d++) {
            $dateStr = sprintf('%04d-%02d-%02d', $year, $month, $d);
            $dayName = $dayNames[(int)(new \DateTime($dateStr))->format('w')];
            $count   = $devicesTable->find()->where(['DATE(date_received)' => $dateStr])->count();
            $days[]  = ['day' => $dayName . ' ' . $d, 'count' => $count];
            $total  += $count;
        }

        $completed = $devicesTable->find()->where(['status' => 'Completed', 'DATE(date_received) >=' => $monthStart, 'DATE(date_received) <=' => $monthEnd])->count();
        $pending   = $devicesTable->find()->where(['status !=' => 'Completed', 'DATE(date_received) >=' => $monthStart, 'DATE(date_received) <=' => $monthEnd])->count();

        return $this->response->withType('application/json')
            ->withStringBody(json_encode(['success' => true, 'days' => $days, 'total' => $total, 'completed' => $completed, 'pending' => $pending]));
    }

    /**
     * AJAX: week-by-week breakdown with status colors for a specific month + year (Monthly tab)
     */
    public function getMonthlyByYear(): Response
    {
        $user = $this->getRequest()->getSession()->read('Auth.User');
        if (empty($user)) {
            return $this->response->withType('application/json')->withStatus(401)
                ->withStringBody(json_encode(['success' => false, 'error' => 'Unauthenticated']));
        }

        $month       = (int)($this->request->getQuery('month') ?? date('n'));
        $year        = (int)($this->request->getQuery('year')  ?? date('Y'));
        $daysInMonth = (int)(new \DateTime("$year-$month-01"))->format('t');
        $monthStart  = sprintf('%04d-%02d-01', $year, $month);
        $monthEnd    = sprintf('%04d-%02d-%02d', $year, $month, $daysInMonth);

        $devicesTable = $this->getTableLocator()->get('Devices');
        $weeks        = [];
        $total        = 0;
        $weekNum      = 1;
        $day          = 1;

        while ($day <= $daysInMonth) {
            $weekStartStr = sprintf('%04d-%02d-%02d', $year, $month, $day);
            $weekEndStr   = sprintf('%04d-%02d-%02d', $year, $month, min($day + 6, $daysInMonth));

            $completed  = $devicesTable->find()->where(['status' => 'Completed',   'DATE(date_received) >=' => $weekStartStr, 'DATE(date_received) <=' => $weekEndStr])->count();
            $inProgress = $devicesTable->find()->where(['status' => 'In Progress', 'DATE(date_received) >=' => $weekStartStr, 'DATE(date_received) <=' => $weekEndStr])->count();
            $pending    = $devicesTable->find()->where(['status' => 'Pending',     'DATE(date_received) >=' => $weekStartStr, 'DATE(date_received) <=' => $weekEndStr])->count();
            $count      = $completed + $inProgress + $pending;

            $weeks[] = [
                'week'        => 'Wk ' . $weekNum,
                'count'       => $count,
                'completed'   => $completed,
                'in_progress' => $inProgress,
                'pending'     => $pending,
            ];

            $total   += $count;
            $weekNum++;
            $day     += 7;
        }

        $completedTotal  = $devicesTable->find()->where(['status' => 'Completed',   'DATE(date_received) >=' => $monthStart, 'DATE(date_received) <=' => $monthEnd])->count();
        $inProgressTotal = $devicesTable->find()->where(['status' => 'In Progress', 'DATE(date_received) >=' => $monthStart, 'DATE(date_received) <=' => $monthEnd])->count();
        $pendingTotal    = $devicesTable->find()->where(['status' => 'Pending',      'DATE(date_received) >=' => $monthStart, 'DATE(date_received) <=' => $monthEnd])->count();

        return $this->response->withType('application/json')
            ->withStringBody(json_encode([
                'success'     => true,
                'weeks'       => $weeks,
                'total'       => $total,
                'completed'   => $completedTotal,
                'in_progress' => $inProgressTotal,
                'pending'     => $pendingTotal,
            ]));
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
            if (in_array($device->id, $seenIds)) continue;
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
                'finished'        => $device->date_released ? $device->date_released->format('M d, Y g:i A') : '',
                'notes'           => $device->customer?->notes ?? '',
                'ai_confidence'   => $device->customer?->ai_confidence ?? null,
                'diagnostic'      => $device->customer?->diagnostic ?? '',
                'suggested_parts' => $device->customer?->suggested_part_replacement ?? '',
                'device_id'       => $device->id,
            ];
        }

        $usersTable     = $this->fetchTable('Users');
        $techUsers      = $usersTable->find()
            ->where(['id !=' => $user['id']])
            ->orderBy(['full_name' => 'ASC'])
            ->all();

        $technicianList = [];
        foreach ($techUsers as $tech) {
            $technicianList[] = !empty($tech->full_name) ? $tech->full_name : $tech->username;
        }

        $this->set(compact('user', 'repairs', 'technicianList'));
        return null;
    }

    public function stocks(): ?Response
    {
        $user = $this->getRequest()->getSession()->read('Auth.User');
        if (empty($user)) {
            return $this->redirect(['action' => 'login']);
        }

        $partsTable = $this->getTableLocator()->get('Parts');
        $parts      = $partsTable->find()->orderBy(['Parts.part_name' => 'ASC'])->all();

        $stocks = [];
        foreach ($parts as $part) {
            $status = $part->stock_quantity <= ($part->minimum_stock ?? 5) ? 'warning' : 'normal';
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

        $usersTable   = $this->fetchTable('Users');
        $devicesTable = $this->getTableLocator()->get('Devices');

        $adminRecord = $usersTable->find()->where(['id' => $user['id']])->first();

        if (!$adminRecord) {
            $this->getRequest()->getSession()->destroy();
            return $this->redirect(['action' => 'login']);
        }

        $profile = [
            'id'        => $adminRecord->id,
            'username'  => $adminRecord->username,
            'full_name' => $adminRecord->full_name ?? $adminRecord->username,
            'email'     => $adminRecord->email     ?? '',
            'specialty' => $adminRecord->specialty ?? '',
            'avatar'    => $adminRecord->avatar    ?? strtoupper(substr($adminRecord->username, 0, 2)),
            'role'      => $adminRecord->role      ?? 'technician',
        ];

        $techUsers   = $usersTable->find()->where(['id !=' => $user['id']])->orderBy(['full_name' => 'ASC'])->all();
        $technicians = [];

        foreach ($techUsers as $tech) {
            $name           = !empty($tech->full_name) ? $tech->full_name : $tech->username;
            $technicians[]  = [
                'id'             => $tech->id,
                'username'       => $tech->username,
                'full_name'      => $name,
                'email'          => $tech->email     ?? '',
                'specialty'      => $tech->specialty ?? 'General Repairs',
                'avatar'         => $tech->avatar    ?? strtoupper(substr($name, 0, 2)),
                'totalJobs'      => $devicesTable->find()->where(['technician' => $name])->count(),
                'completedJobs'  => $devicesTable->find()->where(['technician' => $name, 'status' => 'Completed'])->count(),
                'inProgressJobs' => $devicesTable->find()->where(['technician' => $name, 'status' => 'In Progress'])->count(),
                'pendingJobs'    => $devicesTable->find()->where(['technician' => $name, 'status' => 'Pending'])->count(),
                'waitingJobs'    => $devicesTable->find()->where(['technician' => $name, 'status' => 'Waiting Parts'])->count(),
            ];
        }

        $this->set(compact('user', 'profile', 'technicians'));
        return null;
    }

    public function updateProfile(): ?Response
    {
        $user = $this->getRequest()->getSession()->read('Auth.User');
        if (empty($user)) {
            return $this->redirect(['action' => 'login']);
        }

        $this->request->allowMethod(['post']);
        $data        = $this->request->getData();
        $usersTable  = $this->fetchTable('Users');
        $adminRecord = $usersTable->find()->where(['id' => $user['id']])->first();

        if (!$adminRecord) {
            $this->getRequest()->getSession()->destroy();
            return $this->redirect(['action' => 'login']);
        }

        $patch = [
            'full_name' => trim((string)($data['full_name'] ?? '')),
            'email'     => trim((string)($data['email']     ?? '')),
            'specialty' => trim((string)($data['specialty'] ?? '')),
        ];

        $newPassword = trim((string)($data['new_password'] ?? ''));
        if ($newPassword !== '') {
            if (strlen($newPassword) < 8) {
                $this->Flash->error('New password must be at least 8 characters.');
                return $this->redirect(['action' => 'profile']);
            }
            $patch['password'] = password_hash($newPassword, PASSWORD_DEFAULT);
        }

        $adminRecord = $usersTable->patchEntity($adminRecord, $patch);

        if ($usersTable->save($adminRecord)) {
            $this->Flash->success('Profile updated successfully.');
        } else {
            $this->Flash->error('Could not save changes. Please try again.');
        }

        return $this->redirect(['action' => 'profile']);
    }

    public function logout(): ?Response
    {
        $this->getRequest()->getSession()->destroy();
        return $this->redirect(['action' => 'login']);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Income AJAX endpoints
    // ─────────────────────────────────────────────────────────────────────────

    public function getIncomeDay(): Response
    {
        $user = $this->getRequest()->getSession()->read('Auth.User');
        if (empty($user)) {
            return $this->response->withType('application/json')->withStatus(401)
                ->withStringBody(json_encode(['success' => false, 'error' => 'Unauthenticated']));
        }

        $today = date('Y-m-d');
        $conn  = ConnectionManager::get('default');

        $partsRows = $conn->execute("
            SELECT HOUR(d.date_received) AS hr,
                   COALESCE(SUM(p.unit_price * rpu.quantity), 0) AS amount
            FROM devices d
            JOIN repair_parts_usage rpu ON rpu.device_id = d.id AND rpu.returned = 0
            JOIN parts p ON p.id = rpu.part_id
            WHERE DATE(d.date_received) = :today
            GROUP BY HOUR(d.date_received)
            ORDER BY hr
        ", ['today' => $today])->fetchAll('assoc');

        $servicesRows = $conn->execute("
            SELECT HOUR(d.date_received) AS hr,
                   COALESCE(SUM(s.price), 0) AS amount
            FROM devices d
            JOIN repair_services_usage rsu ON rsu.device_id = d.id
            JOIN services s ON s.id = rsu.service_id
            WHERE DATE(d.date_received) = :today
            GROUP BY HOUR(d.date_received)
            ORDER BY hr
        ", ['today' => $today])->fetchAll('assoc');

        $partsMap    = array_column($partsRows,    'amount', 'hr');
        $servicesMap = array_column($servicesRows, 'amount', 'hr');

        $bars          = [];
        $totalParts    = 0;
        $totalServices = 0;

        for ($h = 0; $h <= 23; $h++) {
            $p = (float)($partsMap[$h]    ?? 0);
            $s = (float)($servicesMap[$h] ?? 0);
            $totalParts    += $p;
            $totalServices += $s;
            $bars[] = [
                'label'    => ($h % 3 === 0) ? $this->_formatHour($h) : '',
                'parts'    => $p,
                'services' => $s,
                'total'    => $p + $s,
            ];
        }

        return $this->response->withType('application/json')->withStringBody(json_encode([
            'success'        => true,
            'period'         => 'Today, ' . date('M d, Y'),
            'total'          => number_format($totalParts + $totalServices, 2),
            'parts_total'    => number_format($totalParts, 2),
            'services_total' => number_format($totalServices, 2),
            'bars'           => $bars,
            'chart_label'    => 'Hourly Breakdown',
        ]));
    }

    public function getIncomeWeek(): Response
    {
        $user = $this->getRequest()->getSession()->read('Auth.User');
        if (empty($user)) {
            return $this->response->withType('application/json')->withStatus(401)
                ->withStringBody(json_encode(['success' => false, 'error' => 'Unauthenticated']));
        }

        $conn     = ConnectionManager::get('default');
        $dayNames = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
        $bars          = [];
        $totalParts    = 0;
        $totalServices = 0;

        for ($i = 6; $i >= 0; $i--) {
            $date    = (new \DateTime("-{$i} days"))->format('Y-m-d');
            $dayName = $dayNames[(int)(new \DateTime($date))->format('w')];

            $p = (float)$conn->execute("
                SELECT COALESCE(SUM(p.unit_price * rpu.quantity), 0)
                FROM devices d
                JOIN repair_parts_usage rpu ON rpu.device_id = d.id AND rpu.returned = 0
                JOIN parts p ON p.id = rpu.part_id
                WHERE DATE(d.date_received) = :date
            ", ['date' => $date])->fetchColumn(0);

            $s = (float)$conn->execute("
                SELECT COALESCE(SUM(s.price), 0)
                FROM devices d
                JOIN repair_services_usage rsu ON rsu.device_id = d.id
                JOIN services s ON s.id = rsu.service_id
                WHERE DATE(d.date_received) = :date
            ", ['date' => $date])->fetchColumn(0);

            $totalParts    += $p;
            $totalServices += $s;

            $bars[] = [
                'label'    => $dayName,
                'parts'    => $p,
                'services' => $s,
                'total'    => $p + $s,
            ];
        }

        $weekStart = (new \DateTime('-6 days'))->format('M d');
        $weekEnd   = (new \DateTime())->format('M d, Y');

        return $this->response->withType('application/json')->withStringBody(json_encode([
            'success'        => true,
            'period'         => "Week: {$weekStart} – {$weekEnd}",
            'total'          => number_format($totalParts + $totalServices, 2),
            'parts_total'    => number_format($totalParts, 2),
            'services_total' => number_format($totalServices, 2),
            'bars'           => $bars,
            'chart_label'    => 'Daily Breakdown',
        ]));
    }

    public function getIncomeMonth(): Response
    {
        $user = $this->getRequest()->getSession()->read('Auth.User');
        if (empty($user)) {
            return $this->response->withType('application/json')->withStatus(401)
                ->withStringBody(json_encode(['success' => false, 'error' => 'Unauthenticated']));
        }

        $month       = (int)($this->request->getQuery('month') ?? date('n'));
        $year        = (int)($this->request->getQuery('year')  ?? date('Y'));
        $daysInMonth = (int)(new \DateTime("{$year}-{$month}-01"))->format('t');
        $conn        = ConnectionManager::get('default');

        $bars          = [];
        $totalParts    = 0;
        $totalServices = 0;
        $weekNum       = 1;
        $day           = 1;

        while ($day <= $daysInMonth) {
            $wStart = sprintf('%04d-%02d-%02d', $year, $month, $day);
            $wEnd   = sprintf('%04d-%02d-%02d', $year, $month, min($day + 6, $daysInMonth));

            $p = (float)$conn->execute("
                SELECT COALESCE(SUM(p.unit_price * rpu.quantity), 0)
                FROM devices d
                JOIN repair_parts_usage rpu ON rpu.device_id = d.id AND rpu.returned = 0
                JOIN parts p ON p.id = rpu.part_id
                WHERE DATE(d.date_received) BETWEEN :wstart AND :wend
            ", ['wstart' => $wStart, 'wend' => $wEnd])->fetchColumn(0);

            $s = (float)$conn->execute("
                SELECT COALESCE(SUM(s.price), 0)
                FROM devices d
                JOIN repair_services_usage rsu ON rsu.device_id = d.id
                JOIN services s ON s.id = rsu.service_id
                WHERE DATE(d.date_received) BETWEEN :wstart AND :wend
            ", ['wstart' => $wStart, 'wend' => $wEnd])->fetchColumn(0);

            $totalParts    += $p;
            $totalServices += $s;

            $bars[] = [
                'label'    => 'Wk ' . $weekNum,
                'parts'    => $p,
                'services' => $s,
                'total'    => $p + $s,
            ];

            $weekNum++;
            $day += 7;
        }

        $monthNames = [1=>'January',2=>'February',3=>'March',4=>'April',
                       5=>'May',6=>'June',7=>'July',8=>'August',
                       9=>'September',10=>'October',11=>'November',12=>'December'];

        return $this->response->withType('application/json')->withStringBody(json_encode([
            'success'        => true,
            'period'         => $monthNames[$month] . ' ' . $year,
            'total'          => number_format($totalParts + $totalServices, 2),
            'parts_total'    => number_format($totalParts, 2),
            'services_total' => number_format($totalServices, 2),
            'bars'           => $bars,
            'chart_label'    => 'Weekly Breakdown',
        ]));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Private helpers
    // ─────────────────────────────────────────────────────────────────────────

    private function _formatHour(int $h): string
    {
        if ($h === 0)  return '12am';
        if ($h === 12) return '12pm';
        return $h < 12 ? "{$h}am" : ($h - 12) . 'pm';
    }
}