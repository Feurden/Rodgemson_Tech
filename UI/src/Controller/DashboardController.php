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
    // 1. Check if the user is already authenticated
    // This MUST be the first thing in the function
    $session = $this->getRequest()->getSession();
    if ($session->check('Auth.User')) {
        return $this->redirect(['action' => 'analytics']);
    }

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

        // Stock levels from parts table (exclude services/unlimited stock items)
        $partsTable  = $this->getTableLocator()->get('Parts');
        $parts       = $partsTable->find()
            ->where(['minimum_stock >' => 0])
            ->orderBy(['part_name' => 'ASC'])
            ->all();
        $stockLevels = [];

        foreach ($parts as $part) {
            // Use minimum_stock as the threshold: total capacity = current + minimum as buffer scale
            $minStock = max(1, $part->minimum_stock ?? 5);
            // Scale: if current >= 3x minimum, treat as 100%; otherwise proportional
            $scale = $minStock * 3;
            $current = $part->stock_quantity;
            $stockLevels[] = [
                'name'    => $part->part_name,
                'current' => $current,
                'total'   => $scale,
                'min'     => $minStock,
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
        $dayNames     = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
        
        // Get all days in the month
        $daysInMonth = (int)(new \DateTime("$year-$month-01"))->format('t');
        $monthStart  = sprintf('%04d-%02d-01', $year, $month);
        $monthEnd    = sprintf('%04d-%02d-%02d', $year, $month, $daysInMonth);

        // Get all repairs for the month
        $allRepairs = $devicesTable->find()
            ->where(['DATE(date_received) >=' => $monthStart, 'DATE(date_received) <=' => $monthEnd])
            ->all();
        
        // Group by date
        $repairsByDate = [];
        foreach ($allRepairs as $repair) {
            $dateKey = $repair->date_received->format('Y-m-d');
            if (!isset($repairsByDate[$dateKey])) {
                $repairsByDate[$dateKey] = 0;
            }
            $repairsByDate[$dateKey]++;
        }
        
        $days  = [];
        $total = 0;
        
        // Get current week's dates (last 7 days including today)
        $today = new \DateTime();
        $startDate = (new \DateTime())->modify('-6 days');
        
        for ($i = 0; $i < 7; $i++) {
            $dateObj = clone $startDate;
            $dateObj->modify("+$i days");
            $dateStr = $dateObj->format('Y-m-d');
            $dayName = $dayNames[(int)$dateObj->format('w')];
            $dayNum = (int)$dateObj->format('j');
            
            $count = $repairsByDate[$dateStr] ?? 0;
            $days[] = [
                'day' => $dayName . ' ' . $dayNum,
                'short_day' => substr($dayName, 0, 3),
                'date' => $dateStr,
                'count' => $count
            ];
            $total += $count;
        }
        
        // Calculate completed and pending for the period
        $completed = $devicesTable->find()->where([
            'status' => 'Completed',
            'DATE(date_received) >=' => $startDate->format('Y-m-d'),
            'DATE(date_received) <=' => $today->format('Y-m-d')
        ])->count();
        
        $pending = $devicesTable->find()->where([
            'status !=' => 'Completed',
            'DATE(date_received) >=' => $startDate->format('Y-m-d'),
            'DATE(date_received) <=' => $today->format('Y-m-d')
        ])->count();

        return $this->response->withType('application/json')
            ->withStringBody(json_encode([
                'success' => true,
                'days' => $days,
                'total' => $total,
                'completed' => $completed,
                'pending' => $pending
            ]));
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
        $parts      = $partsTable->find()
            ->where(['minimum_stock >' => 0])  // services have minimum_stock=0
            ->orderBy(['Parts.part_name' => 'ASC'])
            ->all();

        $stocks = [];
        foreach ($parts as $part) {
            $minStock = $part->minimum_stock ?? 5;
            if ($part->stock_quantity <= 0) {
                $status = 'out';
            } elseif ($part->stock_quantity < $minStock) {
                $status = 'warning';  // strictly BELOW minimum
            } else {
                $status = 'normal';
            }
            $stocks[] = [
                'id'       => $part->id,
                'part'     => $part->part_name,
                'category' => $part->category ?? 'Uncategorized',
                'quantity' => $part->stock_quantity,
                'minimum'  => $minStock,
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
    public function editTechnician(): Response
{
    $user = $this->getRequest()->getSession()->read('Auth.User');
    if (empty($user)) {
        return $this->response->withType('application/json')->withStatus(401)
            ->withStringBody(json_encode(['success' => false, 'error' => 'Unauthenticated']));
    }

    $this->request->allowMethod(['post', 'ajax']);

    // Parse JSON body (same pattern as addTechnician)
    $body     = (array)json_decode((string)$this->request->getBody(), true);
    $techId   = (int)($body['id'] ?? 0);

    if ($techId <= 0) {
        return $this->response->withType('application/json')
            ->withStringBody(json_encode(['success' => false, 'error' => 'Invalid technician ID.']));
    }

    $usersTable = $this->fetchTable('Users');
    $tech       = $usersTable->find()->where(['id' => $techId])->first();

    if (!$tech) {
        return $this->response->withType('application/json')
            ->withStringBody(json_encode(['success' => false, 'error' => 'Technician not found.']));
    }

    $patch = [
        'full_name' => trim((string)($body['full_name'] ?? '')),
        'email'     => trim((string)($body['email']     ?? '')),
        'specialty' => trim((string)($body['specialty'] ?? '')),
    ];

    $newPassword = trim((string)($body['password'] ?? ''));
    if ($newPassword !== '') {
        if (strlen($newPassword) < 8) {
            return $this->response->withType('application/json')
                ->withStringBody(json_encode(['success' => false, 'error' => 'Password must be at least 8 characters.']));
        }
        $patch['password'] = password_hash($newPassword, PASSWORD_DEFAULT);
    }

    $tech = $usersTable->patchEntity($tech, $patch);

    if ($usersTable->save($tech)) {
        return $this->response->withType('application/json')
            ->withStringBody(json_encode(['success' => true]));
    }

    return $this->response->withType('application/json')
        ->withStringBody(json_encode(['success' => false, 'error' => 'Could not save changes. Please try again.']));
}

    public function logout(): ?Response
    {
        $this->getRequest()->getSession()->destroy();
        return $this->redirect(['action' => 'login']);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Income AJAX endpoints
    // ─────────────────────────────────────────────────────────────────────────

   // ─────────────────────────────────────────────────────────────────────────
// Income AJAX endpoints
// ─────────────────────────────────────────────────────────────────────────
/**
 * Get daily income — today only, broken into hourly bars (6 slots)
 */
public function getIncomeDay()
{
    $this->request->allowMethod(['ajax', 'get']);

    $conn = ConnectionManager::get('default');

    $today = date('Y-m-d');

    $results = $conn->execute("
        SELECT
            HOUR(COALESCE(d.date_released, d.modified)) as hour_slot,
            COUNT(DISTINCT d.id) as repair_count,
            COALESCE(SUM(
                (SELECT COALESCE(SUM(s.price), 0)
                 FROM repair_services_usage rsu
                 JOIN services s ON rsu.service_id = s.id
                 WHERE rsu.device_id = d.id) +
                (SELECT COALESCE(SUM(p.unit_price * rpu.quantity), 0)
                 FROM repair_parts_usage rpu
                 JOIN parts p ON rpu.part_id = p.id
                 WHERE rpu.device_id = d.id AND rpu.returned = 0)
            ), 0) as total_income
        FROM devices d
        WHERE d.status IN ('Completed', 'Released')
            AND DATE(COALESCE(d.date_released, d.modified)) = :today
        GROUP BY HOUR(COALESCE(d.date_released, d.modified))
        ORDER BY hour_slot
    ", ['today' => $today])->fetchAll('assoc');

    // Build hourly data map
    $hourlyData = [];
    $totalIncome  = 0;
    $totalRepairs = 0;
    foreach ($results as $row) {
        $hourlyData[(int)$row['hour_slot']] = [
            'income'  => (float)$row['total_income'],
            'repairs' => (int)$row['repair_count'],
        ];
        $totalIncome  += (float)$row['total_income'];
        $totalRepairs += (int)$row['repair_count'];
    }

    // 6 time-of-day slots: Morning, Late Morning, Noon, Afternoon, Evening, Night
    $slots = [
        ['label' => 'Morn',  'hours' => [6,  7,  8,  9]],
        ['label' => 'Late M','hours' => [10, 11]],
        ['label' => 'Noon',  'hours' => [12, 13]],
        ['label' => 'Aft',   'hours' => [14, 15, 16, 17]],
        ['label' => 'Eve',   'hours' => [18, 19, 20]],
        ['label' => 'Night', 'hours' => [21, 22, 23, 0, 1, 2, 3, 4, 5]],
    ];

    $bars = [];
    foreach ($slots as $slot) {
        $slotIncome  = 0;
        $slotRepairs = 0;
        foreach ($slot['hours'] as $h) {
            $slotIncome  += $hourlyData[$h]['income']  ?? 0;
            $slotRepairs += $hourlyData[$h]['repairs'] ?? 0;
        }
        $bars[] = [
            'label'   => $slot['label'],
            'total'   => $slotIncome,
            'repairs' => $slotRepairs,
        ];
    }

    return $this->response->withType('application/json')
        ->withStringBody(json_encode([
            'success'      => true,
            'total'        => $totalIncome,
            'repairs'      => $totalRepairs,
            'period'       => date('F j, Y') . ' (Today)',
            'chart_label'  => "Today's Income by Time of Day",
            'bars'         => $bars,
        ]));
}

/**
 * Get weekly income — current Mon–Sun week, one bar per day
 */
public function getIncomeWeek()
{
    $this->request->allowMethod(['ajax', 'get']);

    $conn = ConnectionManager::get('default');

    // Find Monday of the current week
    $mondayTs  = strtotime('monday this week');
    // If today is Sunday, PHP 'monday this week' may jump forward; force it back
    if (date('N') == 7) {
        $mondayTs = strtotime('last monday');
    }
    $startDate = date('Y-m-d', $mondayTs);
    $endDate   = date('Y-m-d', strtotime($startDate . ' +6 days')); // Sunday

    $results = $conn->execute("
        SELECT
            DATE(COALESCE(d.date_released, d.modified)) as date,
            COUNT(DISTINCT d.id) as repair_count,
            COALESCE(SUM(
                (SELECT COALESCE(SUM(s.price), 0)
                 FROM repair_services_usage rsu
                 JOIN services s ON rsu.service_id = s.id
                 WHERE rsu.device_id = d.id) +
                (SELECT COALESCE(SUM(p.unit_price * rpu.quantity), 0)
                 FROM repair_parts_usage rpu
                 JOIN parts p ON rpu.part_id = p.id
                 WHERE rpu.device_id = d.id AND rpu.returned = 0)
            ), 0) as total_income
        FROM devices d
        WHERE d.status IN ('Completed', 'Released')
            AND DATE(COALESCE(d.date_released, d.modified)) BETWEEN :start_date AND :end_date
        GROUP BY DATE(COALESCE(d.date_released, d.modified))
        ORDER BY date
    ", ['start_date' => $startDate, 'end_date' => $endDate])->fetchAll('assoc');

    $dailyData    = [];
    $totalIncome  = 0;
    $totalRepairs = 0;
    foreach ($results as $row) {
        $dailyData[$row['date']] = [
            'income'  => (float)$row['total_income'],
            'repairs' => (int)$row['repair_count'],
        ];
        $totalIncome  += (float)$row['total_income'];
        $totalRepairs += (int)$row['repair_count'];
    }

    // Build Mon → Sun bars
    $bars = [];
    $dayAbbr = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
    $date = new \DateTime($startDate);
    for ($i = 0; $i < 7; $i++) {
        $key    = $date->format('Y-m-d');
        $bars[] = [
            'label'   => $dayAbbr[$i],
            'date'    => $key,
            'total'   => $dailyData[$key]['income']  ?? 0,
            'repairs' => $dailyData[$key]['repairs'] ?? 0,
        ];
        $date->modify('+1 day');
    }

    return $this->response->withType('application/json')
        ->withStringBody(json_encode([
            'success'     => true,
            'total'       => $totalIncome,
            'repairs'     => $totalRepairs,
            'period'      => date('M j', strtotime($startDate)) . ' – ' . date('M j, Y', strtotime($endDate)),
            'chart_label' => 'This Week\'s Daily Income (Mon – Sun)',
            'bars'        => $bars,
        ]));
}

/**
 * Get monthly income
 */
public function getIncomeMonth()
{
    $this->request->allowMethod(['ajax', 'get']);
    
    $month = (int)$this->request->getQuery('month', date('n'));
    $year = (int)$this->request->getQuery('year', date('Y'));
    
    $conn = ConnectionManager::get('default');
    
    $results = $conn->execute("
        SELECT 
            DATE(COALESCE(d.date_released, d.modified)) as date,
            COUNT(DISTINCT d.id) as repair_count,
            COALESCE(SUM(
                (SELECT COALESCE(SUM(s.price), 0) 
                 FROM repair_services_usage rsu 
                 JOIN services s ON rsu.service_id = s.id 
                 WHERE rsu.device_id = d.id) +
                (SELECT COALESCE(SUM(p.unit_price * rpu.quantity), 0) 
                 FROM repair_parts_usage rpu 
                 JOIN parts p ON rpu.part_id = p.id 
                 WHERE rpu.device_id = d.id AND rpu.returned = 0)
            ), 0) as total_income
        FROM devices d
        WHERE d.status IN ('Completed', 'Released')
            AND MONTH(COALESCE(d.date_released, d.modified)) = :month
            AND YEAR(COALESCE(d.date_released, d.modified)) = :year
        GROUP BY DATE(COALESCE(d.date_released, d.modified))
        ORDER BY date
    ", ['month' => $month, 'year' => $year])->fetchAll('assoc');
    
    $dailyIncome = [];
    $totalIncome = 0;
    $totalRepairs = 0;
    
    foreach ($results as $row) {
        $dailyIncome[$row['date']] = [
            'income' => $row['total_income'],
            'repairs' => $row['repair_count']
        ];
        $totalIncome += $row['total_income'];
        $totalRepairs += $row['repair_count'];
    }
    
    // Group by week
    $lastDay = (int)(new \DateTime("$year-$month-01"))->modify('last day of this month')->format('d');
    $weeks = [];
    $weekData = [];
    $weekNum = 1;
    
    for ($day = 1; $day <= $lastDay; $day++) {
        $dateKey = sprintf("%04d-%02d-%02d", $year, $month, $day);
        $weekData[] = [
            'day' => $day,
            'income' => isset($dailyIncome[$dateKey]['income']) ? $dailyIncome[$dateKey]['income'] : 0,
            'repairs' => isset($dailyIncome[$dateKey]['repairs']) ? $dailyIncome[$dateKey]['repairs'] : 0
        ];
        
        if (count($weekData) === 7 || $day === $lastDay) {
            $weekIncome = 0;
            $weekRepairs = 0;
            foreach ($weekData as $wd) {
                $weekIncome += $wd['income'];
                $weekRepairs += $wd['repairs'];
            }
            $weeks[] = [
                'label' => 'Week ' . $weekNum,
                'total' => $weekIncome,
                'repairs' => $weekRepairs
            ];
            $weekData = [];
            $weekNum++;
        }
    }
    
    // If no data for the month, add a placeholder
    if (empty($weeks)) {
        $weeks[] = [
            'label' => 'No Data',
            'total' => 0,
            'repairs' => 0
        ];
    }
    
    return $this->response->withType('application/json')
        ->withStringBody(json_encode([
            'success' => true,
            'total' => $totalIncome,
            'repairs' => $totalRepairs,
            'period' => date('F Y', mktime(0, 0, 0, $month, 1, $year)),
            'chart_label' => 'Weekly Income',
            'bars' => $weeks
        ]));
}
}