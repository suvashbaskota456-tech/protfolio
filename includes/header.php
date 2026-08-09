<?php require_once __DIR__ . '/../config/config.php'; ?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($page_title ?? SITE_NAME) ?></title>
<link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body>
<header class="site-header">
  <div class="container nav">
    <a class="brand" href="<?= BASE_URL ?>">Suvash<span>.</span></a>
    <button class="menu-btn" id="menuBtn">☰</button>
    <nav id="mainNav">
      <a href="<?= BASE_URL ?>#home">Home</a>
      <a href="<?= BASE_URL ?>#about">About</a>
      <a href="<?= BASE_URL ?>#experience">Experience</a>
      <a href="<?= BASE_URL ?>#education">Education</a>
      <a href="<?= BASE_URL ?>#skills">Skills</a>
      <a href="<?= BASE_URL ?>#projects">Projects</a>
      <a href="<?= BASE_URL ?>#certificates">Certificates</a>
      <a href="<?= BASE_URL ?>blog.php">Blog</a>
      <a href="<?= BASE_URL ?>#contact">Contact</a>
    </nav>
  </div>
</header>
<main>
