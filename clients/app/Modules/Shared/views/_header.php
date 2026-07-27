<?php
/** @var array<string,mixed>|null $currentUser */
/** @var string $csrfField */
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= isset($pageTitle) ? \App\Core\View::e($pageTitle) . ' — ' : '' ?>OCTG SEO</title>
<link rel="stylesheet" href="/assets/css/app.css">
</head>
<body>
<nav class="octg-nav">
  <div>
    <a href="/" class="brand">OCTG SEO</a>
    <?php if ($currentUser !== null): ?>
      <a href="/projects">Projects</a>
    <?php endif; ?>
  </div>
  <div>
    <?php if ($currentUser !== null): ?>
      <span class="octg-muted" style="color:#cbd5e1; margin-right: 14px;"><?= \App\Core\View::e($currentUser['name']) ?></span>
      <form method="post" action="/logout" style="display:inline">
        <?= $csrfField ?>
        <button type="submit" class="linklike">Log out</button>
      </form>
    <?php else: ?>
      <a href="/login">Log in</a>
    <?php endif; ?>
  </div>
</nav>
<div class="octg-container">
