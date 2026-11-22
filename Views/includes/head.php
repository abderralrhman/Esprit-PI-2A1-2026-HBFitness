<?php
/**
 * Views/includes/head.php
 * Shared <head> assets (Bootstrap 5, Font Awesome 6, Google Fonts Oswald + DM Sans, HB Fitness CSS)
 * Expects: $title (string), $isAdminLayout (bool)
 */
$pageTitle     = $title ?? 'HB FITNESS — Train Smart. Reuse More.';
$isAdminLayout = $isAdminLayout ?? false;
?>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&family=Oswald:wght@500;600;700&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
<link href="Views/assets/css/style.css?v=2" rel="stylesheet">
<?php if ($isAdminLayout): ?>
<link href="Views/assets/css/backoffice.css?v=2" rel="stylesheet">
<?php else: ?>
<link href="Views/assets/css/frontoffice.css?v=2" rel="stylesheet">
<?php endif; ?>
<link href="Views/assets/css/blog.css?v=2" rel="stylesheet">
