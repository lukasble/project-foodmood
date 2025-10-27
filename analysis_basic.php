<?php
include 'login_control.php';
include 'db.php';

$uid  = (int)$_SESSION['user_id'];
$minN = 5;
$lookback_min = isset($_SESSION['lookback_min']) ? (int)$_SESSION['lookback_min'] : 60; // default ON

if (!isset($link) || !$link) {
  http_response_code(500);
  echo '<p class="msg msg--error">DB connection not available.</p>';
  exit;
}

// Replaces underscores with spaces for displaying to the user. 
function humanize($s){ return ucfirst(str_replace('_',' ',$s)); }

/* OLD ANALYSIS
  Schema query:
  - Count exposures per category for this user (via JOINs)
  - Compute bad_exposures and bad_pct
  - Filter by minimum exposures
*//*
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
*/

$sqlTop = "SELECT
/* 1) Final columns per category */
categories.id AS category_id,
  categories.name AS category,
  total_exposures_by_category.total_exposures,
  COALESCE(bad_window_exposures_by_category.bad_exposures, 0) AS bad_exposures,
  ROUND(100 * COALESCE(bad_window_exposures_by_category.bad_exposures, 0)/ NULLIF(total_exposures_by_category.total_exposures, 0), 1
  ) AS bad_pct

FROM categories -- One output row per category

/* 2) Overall exposures per category for this user */
JOIN (
  SELECT
    meal_log_categories.category_id, COUNT(*) AS total_exposures
  FROM meal_log_categories
  JOIN meal_logs
  ON meal_logs.id = meal_log_categories.meal_log_id
  WHERE meal_logs.user_id = ?
  GROUP BY meal_log_categories.category_id
) AS total_exposures_by_category
  ON total_exposures_by_category.category_id = categories.id

/* 3) Exposures within lookback window before each bad meal (same user) */
/* A meal can be counted at most once as the meal id prevents this */
LEFT JOIN (
  SELECT
    meal_log_categories.category_id, COUNT(DISTINCT meals.id) AS bad_exposures
  FROM meal_logs AS meals
  JOIN meal_log_categories
  ON meal_log_categories.meal_log_id = meals.id
  WHERE meals.user_id = ?
    AND EXISTS (SELECT 1
      FROM meal_logs AS bad_meals
      WHERE bad_meals.user_id = meals.user_id
      AND bad_meals.experience = 1
      AND meals.eaten_at BETWEEN DATE_SUB(bad_meals.eaten_at, INTERVAL ? MINUTE)
      AND bad_meals.eaten_at
    )
  GROUP BY meal_log_categories.category_id
) AS bad_window_exposures_by_category
  ON bad_window_exposures_by_category.category_id = categories.id


/* 4) Only keep categories with enough total exposures */
WHERE total_exposures_by_category.total_exposures >= ?

/* 5) Rank by %, then counts, show top 3 categories */
ORDER BY bad_pct DESC, bad_exposures DESC, total_exposures_by_category.total_exposures DESC
LIMIT 3
";


$stmt = $link->prepare($sqlTop);
if (!$stmt) {
  http_response_code(500);
  echo '<p class="msg msg--error">Prepare failed: '.htmlspecialchars($link->error).'</p>';
  exit;
}
$stmt->bind_param('iiii', $uid, $uid, $lookback_min ,$minN);
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

  // Build placeholders ['?', '?', '?']
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
  <link rel="stylesheet" href="style.css">
  <link rel="stylesheet" href="index_style.css">
</head>
<body class="analysis-bg">
  <div class="analysis-wrap"><div id="analysis">
  <main class="container container--narrow">
    <header class="page-header">
      <h1 class="page-title">Top categories causing unpleasant experiences</h1>
      <p class="page-subtitle">Based on your logged meals:</p>
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
  <section class="analysis-disclaimer" style="margin-top:2rem;font-size:0.9rem;line-height:1.4;color:#555;">
  <strong>Note:</strong> This page shows a frequency-based association only. It highlights categories that often
  appear shortly before (or during) bad experiences within the selected time window. It does not prove medical
  cause and effect, and it may miss factors that were not logged.
    </section>
  </div></div>
</body>
</html>
