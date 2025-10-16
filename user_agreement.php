<?php
session_start();
$isLoggedIn = !empty($_SESSION['user_id']); 

require_once __DIR__.'/user_agreement_config.php';
require_once __DIR__.'/db.php';

$requestedVersion = $_GET['v'] ?? TERMS_VERSION_CURRENT;
$mode             = $_GET['mode'] ?? 'view';
$return           = $_GET['return'] ?? 'home.php';

if ($mode === 'accept') {
    $requestedVersion = TERMS_VERSION_CURRENT;
}

if (preg_match('#^\s*https?://#i', $return) || str_contains($return, "\n")) {
    $return = 'home.php';
}

$file = termsFile($requestedVersion);
if (!is_file($file)) {
    http_response_code(404);
    exit('Requested terms version not found.');
}

$agreementHtml = file_get_contents($file);

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accept_terms'])) {
    if (empty($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        http_response_code(400);
        exit('Invalid request token.');
    }
    if (empty($_SESSION['user_id'])) {
        header('Location: index.php');
        exit;
    }
    $returnPosted = $_POST['return'] ?? $return;
    if (preg_match('#^\s*https?://#i', $returnPosted) || str_contains($returnPosted, "\n")) {
        $returnPosted = 'home.php';
    }
    $posted_version = $_POST['terms_version'] ?? '';
    if ($posted_version !== TERMS_VERSION_CURRENT) {
        $error = "Användarvillkoren har uppdaterats. Läs igenom den senaste versionen innan du accepterar.";
    } else {
        $now = gmdate('Y-m-d H:i:s');
        if (!$stmt = $link->prepare("UPDATE users SET terms_version = ?, terms_accepted_at = ? WHERE id = ?")) {
            $error = "Tekniskt fel (prepare misslyckades). Försök igen.";
        } else {
            $userId = (int)$_SESSION['user_id'];
            $stmt->bind_param("ssi", $posted_version, $now, $userId);
            if ($stmt->execute()) {
                $_SESSION['terms_version'] = $posted_version;
                $stmt->close();
                $link->close();
                header("Location: " . $return);
                exit;
            } else {
                $error = "Kunde inte spara ditt godkännande. Försök igen.";
                $stmt->close();
            }
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>FoodMood: User (v<?=htmlspecialchars($requestedVersion)?>)</title>
  <link rel="stylesheet" href="index_style.css">
  <style> 
    .agreement-container {
        background: #ffffff;
        padding: 40px;
        border-radius: 16px;
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1);
        text-align: left;
        width: 800px;
    }
    .center {
        display: flex;
        justify-content: center;
        align-items: center;
    }
  </style>
</head>

<body>
<div class="agreement-container"> 
  <main class="terms-container">
    <p><strong>Version:</strong> <?= htmlspecialchars($requestedVersion) ?></p>
    <?= $agreementHtml ?>
    <?php if (!empty($_SESSION['user_id']) && $mode === 'accept' && (($_SESSION['terms_version'] ?? null) !== TERMS_VERSION_CURRENT)): ?>
      <form method="post" class="center">
        <input type="hidden" name="terms_version" value="<?= htmlspecialchars($requestedVersion) ?>">
        <input type="hidden" name="return" value="<?= htmlspecialchars($return) ?>">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
        <label>
          <input type="checkbox" name="accept_terms" value="1" required>I accept this version (<?=htmlspecialchars($requestedVersion)?>)
        </label>
          <button type="submit">Accept</button>
          <a href="<?=$return?>"><button type="button">Back</button></a>
      </form>
      <?php if (isset($_SESSION['user_id'])) {echo '<a href="logout.php"><button type="button" class="logout-btn">Log out</button></a>';} ?>
    <?php else: ?>
      <div class="center">
        <a href="<?=$return?>"><button type="button">Back</button></a>
      </div>
    <?php endif; ?>
    </div>
  </main>
</div>
</body>
</html>

