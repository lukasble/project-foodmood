<?php
session_start();

ini_set('display_errors', 1);
error_reporting(E_ALL);

if (!isset($_SESSION['user_id'])) {
  http_response_code(401);
  echo '<p class="msg msg--error">You must be logged in.</p>';
  exit;
}
$uid  = (int)$_SESSION['user_id'];

$minN = 5; // minimum meals per category to include

include 'db.php';

if (!isset($link) || !$link) {
  http_response_code(500);
  echo '<p class="msg msg--error">DB connection not available.</p>';
  exit;}

  // This is used later to replace underscores with spaces when displayed in html
function humanize($s){ return ucfirst(str_replace('_',' ',$s)); }

/* The section below selects the categories and sums their respective total exposures and bad exposures etc Example for one category (flag):
  category = 'dairy' 
  total_exposures = 3 
  bad_exposures = 2 
  bad_pct = 66.7 
  
  This is calculated from a table (cat) which selects all meals as row for each
  category containing the binary value and binary experience. */

$sqlTop = "SELECT category, SUM(flag) AS total_exposures, SUM(flag AND experience = 1) AS bad_exposures, ROUND(100 * SUM(flag AND experience = 1) / NULLIF(SUM(flag),0), 1) AS bad_pct
  FROM (
    SELECT 'dairy' AS category, dairy AS flag, experience FROM meal_logs WHERE user_id = ?
    UNION ALL SELECT 'gluten', gluten, experience FROM meal_logs WHERE user_id = ?
    UNION ALL SELECT 'legumes', legumes, experience FROM meal_logs WHERE user_id = ?
    UNION ALL SELECT 'cruciferous_vegetables', cruciferous_vegetables, experience FROM meal_logs WHERE user_id = ?
    UNION ALL SELECT 'alliums', alliums, experience FROM meal_logs WHERE user_id = ?
    UNION ALL SELECT 'fruits', fruits, experience FROM meal_logs WHERE user_id = ?
    UNION ALL SELECT 'sugar_alcohols_artificial_sweeteners', sugar_alcohols_artificial_sweeteners, experience FROM meal_logs WHERE user_id = ?
    UNION ALL SELECT 'high_fat_fried', high_fat_fried, experience FROM meal_logs WHERE user_id = ?
    UNION ALL SELECT 'spicy', spicy, experience FROM meal_logs WHERE user_id = ?
    UNION ALL SELECT 'acidic', acidic, experience FROM meal_logs WHERE user_id = ?
    UNION ALL SELECT 'caffeine', caffeine, experience FROM meal_logs WHERE user_id = ?
    UNION ALL SELECT 'alcohol', alcohol, experience FROM meal_logs WHERE user_id = ?
    UNION ALL SELECT 'processed_food', processed_food, experience FROM meal_logs WHERE user_id = ?) AS cat
  GROUP BY category
  HAVING total_exposures >= ?
  ORDER BY bad_pct DESC, bad_exposures DESC, total_exposures DESC
  LIMIT 3";

$stmt = $link->prepare($sqlTop);
if (!$stmt) {
  http_response_code(500);
  echo '<p class="msg msg--error">Prepare failed: '.htmlspecialchars($link->error).'</p>';
  exit;
}

// 13 copies of $uid + 1 copy of $minN (14 ? )
$paramsTop = array_merge(array_fill(0, 13, $uid), [$minN]);
$typesTop  = str_repeat('i', 14); // integers

// bind_param requires individual args, "..." expands the array:
$stmt->bind_param($typesTop, ...$paramsTop);

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

// Fetch related articles for found categories (ordered to match ranking)
$articlesByCat = [];
if (!empty($top)) {
  $cats = array_column($top, 'category'); // Category names
  $n    = count($cats); // (3) placeholders

  // Build placeholders for IN (...) and FIELD(...):
  // We need the categories twice (once for IN, once for FIELD order)
  $place = implode(',', array_fill(0, $n, '?'));
  
  $sqlArt = "SELECT category, title, url
    FROM research_articles
    WHERE category IN ($place)
    ORDER BY FIELD(category, $place), id DESC
    LIMIT 7";

  $stmt2 = $link->prepare($sqlArt);
  if (!$stmt2) {
    http_response_code(500);
    echo '<p class="msg msg--error">Prepare (articles) failed: '.htmlspecialchars($link->error).'</p>';
    exit;
  }

  $paramsArt = array_merge($cats, $cats);   // first set for IN, second for FIELD
  $typesArt  = str_repeat('s', $n*2);       // all strings

  $stmt2->bind_param($typesArt, ...$paramsArt);
  if (!$stmt2->execute()) {
    http_response_code(500);
    echo '<p class="msg msg--error">Execute (articles) failed: '.htmlspecialchars($stmt2->error).'</p>';
    exit;
  }

  $res2 = $stmt2->get_result();
  while ($r = $res2->fetch_assoc()) {
    $c = $r['category'];
    if (!isset($articlesByCat[$c])) $articlesByCat[$c] = [];
    if (count($articlesByCat[$c]) < 5) {
      $articlesByCat[$c][] = [
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
  <link rel="stylesheet" href="style.css">
</head>
<body class="page page--analysis">
  <main class="container container--narrow">
    <header class="page-header">
      <h1 class="page-title">Top categories causing unpleasant experience</h1>
      <p class="page-subtitle">Based on your logged meals</p>
    </header>

    <?php if (empty($top)): ?>
      <section class="empty-state">
        <h2 class="empty-state__title">Too few insights yet</h2>
        <p class="empty-state__text">
          We only analyze a category after at least <strong><?= (int)$minN ?></strong> meals that include it.
        </p>
        <div class="button-row">
          <a href="meal_log.php" class="btn btn--secondary">← Back to meal log</a>
        </div>
      </section>
    <?php else: ?>
      <ol class="analysis-list">
        <?php foreach ($top as $i => $row):
          $cat   = $row['category'];
          $bad   = (int)$row['bad_exposures'];
          $total = (int)$row['total_exposures'];
          $pct   = (float)$row['bad_pct'];
          $arts  = $articlesByCat[$cat] ?? [];
        ?>
          <li class="analysis-item">
            <div class="analysis-item__header">
              <span class="analysis-item__rank">#<?= $i+1 ?></span>
              <h2 class="analysis-item__title"><?= htmlspecialchars(humanize($cat)) ?></h2>
              <div class="analysis-item__metrics">
                <span class="metric metric--percent"><?= $pct ?>%</span>
                <span class="metric metric--counts">(<?= $bad ?>/<?= $total ?>)</span>
              </div>
            </div>
            <div class="analysis-item__body">
              <?php if ($arts): ?>
                <h3 class="section-title">Related articles</h3>
                <ul class="link-list">
                  <?php foreach ($arts as $a): ?>
                    <li class="link-list__item">
                      <a class="link" href="<?= htmlspecialchars($a['url']) ?>" target="_blank" rel="noopener">
                        <?= htmlspecialchars($a['title']) ?>
                      </a>
                    </li>
                  <?php endforeach; ?>
                </ul>
              <?php else: ?>
                <p class="muted">No articles yet for this category.</p>
              <?php endif; ?>
            </div>
          </li>
        <?php endforeach; ?>
      </ol>
      <div class="button-row">
        <a href="/meal_log.php" class="btn btn--secondary">← Back to meal logs</a>
      </div>
    <?php endif; ?>
  </main>
</body>
</html>
