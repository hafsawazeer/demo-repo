<?php
// FitVerse - Member Dashboard (Enhanced)
// Gracefully falls back to demo data if backend not present

declare(strict_types=1);

session_start();

$authUser = null;
$userId = null;
$user = [ 'name' => 'Athlete' ];
$stats = [
    'calories' => 450,
    'steps' => 7200,
    'minutes' => 42,
    'resting_hr' => 60,
    'streak_days' => 3,
    'active_times' => '6 AM - 9 AM, 6 PM - 8 PM',
    'month_label' => date('M Y'),
];
$assignments = [
    [ 'title' => 'Lower Body Strength', 'meta' => '3 sets x 12 reps', 'status' => 'Due Today' ],
    [ 'title' => '30-min HIIT Cardio', 'meta' => 'Zone 4 intervals', 'status' => 'In Progress' ],
    [ 'title' => 'Mobility & Stretching', 'meta' => '15 min routine', 'status' => 'Completed' ],
];

$authLoaded = false;
$profileLoaded = false;

// Attempt to load backend classes
$authPath = __DIR__ . '/../../controllers/authController.php';
$profilePath = __DIR__ . '/../../models/clientProfileModel.php';

if (file_exists($authPath)) {
    require_once $authPath;
    if (class_exists('AuthController')) {
        $authLoaded = true;
    }
}

if (file_exists($profilePath)) {
    require_once $profilePath;
    if (class_exists('ClientProfileModel')) {
        $profileLoaded = true;
    }
}

// Use backend if available
if ($authLoaded) {
    try {
        AuthController::requireAuthentication();
        $authUser = AuthController::getCurrentUser();
        $userId = $authUser['user_id'] ?? null;
        if (!$userId) {
            header('Location: /login.php');
            exit();
        }
    } catch (Throwable $e) {
        // Fall back to demo mode
    }
}

if ($profileLoaded && $userId) {
    try {
        $model = new ClientProfileModel();
        $fetchedUser = $model->getMemberProfile($userId);
        if (is_array($fetchedUser) && !empty($fetchedUser['name'])) {
            $user['name'] = (string)$fetchedUser['name'];
        }
        $fetchedStats = $model->getBasicStats($userId);
        if (is_array($fetchedStats)) {
            $stats = array_merge($stats, $fetchedStats);
        }
        $fetchedAssignments = $model->getAssignments($userId);
        if (is_array($fetchedAssignments) && count($fetchedAssignments) > 0) {
            $assignments = $fetchedAssignments;
        }
    } catch (Throwable $e) {
        // Keep demo values
    }
}

$hour = (int)date('G');
$greeting = $hour < 12 ? 'Good Morning,' : ($hour < 18 ? 'Good Afternoon,' : 'Good Evening,');
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>FitVerse - Member Dashboard</title>
    <script src="https://cdn.jsdelivr.net/npm/lucide@0.364.0/dist/umd/lucide.min.js"></script>
    <link rel="stylesheet" href="../../assets/css/member_dash.css" />
  </head>
  <body>
    <header class="header login-header">
      <div class="logo">FIT<span>VERSE</span></div>
      <nav class="nav-links">
        <a href="/views/member/member_workout.php"><i data-lucide="dumbbell"></i> Workouts</a>
        <a href="/shop/index.html"><i data-lucide="shopping-bag"></i> Shop</a>
        <a href="/views/member/member_profile.php"><i data-lucide="user"></i> Profile</a>
        <a href="?logout=1" class="logout-btn">Logout</a>
      </nav>
    </header>

    <main class="main">
      <section class="hero">
        <div class="hero-card">
          <h2><?= htmlspecialchars($greeting) ?></h2>
          <div class="greeting">Welcome back, <span class="username"><?= htmlspecialchars($user['name']) ?></span></div>
          <div style="margin-top: 10px; color: var(--muted);">
            Keep the streak alive — you're on day <strong><?= (int)$stats['streak_days'] ?></strong>!
          </div>
        </div>

        <div class="stats-grid">
          <div class="stat-tile">
            <h3>Calories Burnt</h3>
            <div class="stat-value"><?= (int)$stats['calories'] ?> kcal</div>
          </div>
          <div class="stat-tile">
            <h3>Steps</h3>
            <div class="stat-value"><?= number_format((int)$stats['steps']) ?></div>
          </div>
          <div class="stat-tile">
            <h3>Active Minutes</h3>
            <div class="stat-value"><?= (int)$stats['minutes'] ?> min</div>
          </div>
        </div>
      </section>

      <section class="grid-2">
        <div class="card">
          <h3><i data-lucide="activity"></i> Today's Activity</h3>
          <div class="activity-list">
            <div class="activity-item">
              <div class="label">Active Times</div>
              <div class="time"><?= htmlspecialchars((string)$stats['active_times']) ?></div>
            </div>
            <div class="activity-item">
              <div class="label">Resting Heart Rate</div>
              <div class="time"><?= (int)$stats['resting_hr'] ?> bpm</div>
            </div>
            <div class="activity-item">
              <div class="label">Streak</div>
              <div class="time"><?= (int)$stats['streak_days'] ?> days</div>
            </div>
          </div>
        </div>

        <div class="card">
          <h3><i data-lucide="calendar"></i> Calendar <span style="color: var(--muted); font-weight: 600;">(<?= htmlspecialchars((string)$stats['month_label']) ?>)</span></h3>
          <div class="calendar">
            <?php for ($d = 1; $d <= 28; $d++): $active = ($d % 3 === 0); ?>
              <div class="day <?= $active ? 'active' : '' ?>"><?= $d ?></div>
            <?php endfor; ?>
          </div>
        </div>
      </section>

      <section class="grid-2" style="margin-top: 16px;">
        <div class="card">
          <h3><i data-lucide="list-check"></i> Assignments</h3>
          <div class="assignments">
            <?php foreach ($assignments as $item): 
              $status = strtolower((string)($item['status'] ?? ''));
              $badgeClass = 'warning';
              if (str_contains($status, 'complete')) { $badgeClass = 'success'; }
              elseif (str_contains($status, 'progress')) { $badgeClass = 'warning'; }
              elseif (str_contains($status, 'overdue')) { $badgeClass = 'danger'; }
            ?>
            <div class="assignment">
              <div>
                <div style="font-weight: 700;"><?= htmlspecialchars((string)$item['title']) ?></div>
                <div class="meta"><?= htmlspecialchars((string)$item['meta']) ?></div>
              </div>
              <div class="badge <?= $badgeClass ?>"><?= htmlspecialchars((string)$item['status']) ?></div>
            </div>
            <?php endforeach; ?>
          </div>
        </div>

        <div class="card">
          <h3><i data-lucide="bolt"></i> Quick Actions</h3>
          <div style="display:flex; flex-wrap: wrap; gap: 10px;">
            <a href="/views/member/member_workout.php" class="badge success" style="text-decoration:none;">Start Workout</a>
            <a href="#" class="badge warning" style="text-decoration:none;">Log Meal</a>
            <a href="#" class="badge" style="text-decoration:none; border:1px solid rgba(148,163,184,.25); color: var(--text);">Sync Wearable</a>
          </div>
        </div>
      </section>
    </main>

    <footer class="footer">
      <div class="logo">FIT<span>VERSE</span></div>
      <div>Copyright © <?= date('Y') ?> FitVerse. | All rights reserved.</div>
    </footer>

    <script>
      lucide.createIcons();
    </script>
  </body>
</html>
