<?php
session_start();

// ----------------------------------------- //
// Set $_SESSION['user_id'] =1 for debugging //
// ----------------------------------------- //
if (!isset($_SESSION['user_id'])) {
    if (in_array($_SERVER['REMOTE_ADDR'], ['127.0.0.1', '::1'])) {
        $_SESSION['user_id'] = 1;
    }
}
// ----------------------------------------- //

ini_set('display_errors', 1);
error_reporting(E_ALL);

if (!isset($_SESSION['user_id'])) {
  http_response_code(401);
  echo '<p class="msg msg--error">You must be logged in.</p>';
  exit;
}
$uid  = (int)$_SESSION['user_id'];
$minN = 5;

Include 'db.php'; // must set $link = new mysqli(...)

if (!isset($link) || !$link) {
  http_response_code(500);
  echo '<p class="msg msg--error">DB connection not available.</p>';
  exit;
}

// Replaces underscores with spaces for displaying to the user. 
function humanize($s){ return ucfirst(str_replace('_',' ',$s)); }

/*
  Schema query:
  - Count exposures per category for this user (via JOINs)
  - Compute bad_exposures and bad_pct
  - Filter by minimum exposures
*/
$sqlTop = "SELECT
    categories.id AS category_id,
    categories.name AS category,
    COUNT(*) AS total_exposures,
    SUM(meal_logs.experience = 1) AS bad_exposures,
    ROUND(100 * SUM(meal_logs.experience = 1) / NULLIF(COUNT(*), 0), 1) AS bad_pct -- Prevent division by 0 and round to 1 decimal 

  FROM meal_logs
  JOIN meal_log_categories ON meal_log_categories.meal_log_id = meal_logs.id
  JOIN categories ON categories.id = meal_log_categories.category_id               -- Merge tables 
  WHERE meal_logs.user_id = ?                                                      -- Only include meals belonging to the user
  GROUP BY categories.id, categories.name                                          -- Sums up the results for meals with the same category
  HAVING COUNT(*) >= ?                                                             -- Only include categories over minN
  ORDER BY bad_pct DESC, bad_exposures DESC, total_exposures DESC                  -- Rank Order
  LIMIT 3";

$stmt = $link->prepare($sqlTop);
if (!$stmt) {
  http_response_code(500);
  echo '<p class="msg msg--error">Prepare failed: '.htmlspecialchars($link->error).'</p>';
  exit;
}
$stmt->bind_param('ii', $uid, $minN);
if (!$stmt->execute()) {
  http_response_code(500);
  echo '<p class="msg msg--error">Execute failed: '.htmlspecialchars($stmt->error).'</p>';
  exit;
}

$res = $stmt->get_result();
$top = [];
while ($row = $res->fetch_assoc()) {
  $top[] = $row;
}
$stmt->close();


// Get related articles via category_id
$articlesByCatId = [];
if (!empty($top)) {
  $catIds = array_column($top, 'category_id');
  $n      = count($catIds);

  // Build placeholders, for example 3 categories would be ['?', '?', '?']
  $ph = implode(',', array_fill(0, $n, '?'));

$sqlArt = " SELECT research_articles.category_id, research_articles.title, research_articles.url
  FROM research_articles
  WHERE research_articles.category_id IN ($ph)
  ORDER BY FIELD(research_articles.category_id, $ph), research_articles.id DESC";

  $stmt2 = $link->prepare($sqlArt);
  if (!$stmt2) {
    http_response_code(500);
    echo '<p class="msg msg--error">Prepare (articles) failed: '.htmlspecialchars($link->error).'</p>';
    exit;
  }

  // We need the IDs twice for IN and FIELD
  $params = array_merge($catIds, $catIds);
  $types  = str_repeat('i', $n * 2);

  $stmt2->bind_param($types, ...$params);

  if (!$stmt2->execute()) {
    http_response_code(500);
    echo '<p class="msg msg--error">Execute (articles) failed: '.htmlspecialchars($stmt2->error).'</p>';
    exit;
  }

  $res2 = $stmt2->get_result();
  while ($r = $res2->fetch_assoc()) {
    $cid = (int)$r['category_id'];
    if (!isset($articlesByCatId[$cid])) $articlesByCatId[$cid] = [];
    if (count($articlesByCatId[$cid]) < 5) {                // Only keeps max 5 per category
      $articlesByCatId[$cid][] = [
        'title' => $r['title'],
        'url'   => $r['url'],
      ];
    }
  }
  $stmt2->close();
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Basic Frequency Analysis</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="style_home.css">
</head>
<body class="page page--analysis">
  <main class="container container--narrow">
    <header class="page-header">
      <h1 class="page-title">Top categories causing unpleasant experience</h1>
      <p class="page-subtitle">Based on your logged meals</p>
    </header>

    <?php if (empty($top)): ?>
      <div class="empty">
        <h2 class="empty__title">Too few insights yet</h2>
        <p class="empty__text">
          We analyze a category after at least <strong><?= (int)$minN ?></strong> meals that include it.
        </p>
        <a href="/meal_log.php" class="btn btn--secondary">← Back to meal logs</a>
      </div>

    <?php else: ?>
      <div class="analysis">
        <?php foreach ($top as $i => $row):
          $catId = (int)$row['category_id'];
          $arts  = $articlesByCatId[$catId] ?? [];
        ?>
          <div class="analysis-item">
            <div class="analysis-head">
              <span class="analysis-rank">#<?= $i + 1 ?></span>
              <h2 class="analysis-title"><?= htmlspecialchars(humanize($row['category'])) ?></h2>
              <div class="analysis-metrics">
                <span class="metric metric--percent"><?= $row['bad_pct'] ?>%</span>
                <span class="metric metric--counts">(<?= $row['bad_exposures'] ?>/<?= $row['total_exposures'] ?>)</span>
              </div>
            </div>

            <?php if ($arts): ?>
              <div class="analysis-links">
                <?php foreach ($arts as $a): ?>
                  <a class="analysis-link"
                     href="<?= htmlspecialchars($a['url']) ?>"
                     target="_blank" rel="noopener">
                    <?= htmlspecialchars($a['title']) ?>
                  </a>
                <?php endforeach; ?>
              </div>
            <?php else: ?>
              <div class="analysis-links analysis-links--empty">No articles yet for this category.</div>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      </div>

      <a href="/meal_log.php" class="btn btn--secondary">← Back to meal logs</a>
    <?php endif; ?>
  </main>

<?php
    if (session_status() === PHP_SESSION_NONE) { 
        session_start();
    }
    if (isset($_SESSION['user_id'])) {
        echo '<a href="logout.php" class="logout-btn">Log out</a>';
    }
?>
</body>
</html>
