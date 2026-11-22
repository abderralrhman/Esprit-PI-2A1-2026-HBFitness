<?php
/**
 * Views/includes/head.php — Shared HTML <head> for Frontoffice & Backoffice
 */
$pageTitle = isset($title) && is_string($title) ? $title : 'HB FITNESS — Blog Model';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?></title>
    <link rel="icon" type="image/svg+xml" href="Views/assets/images/hb-monogram.svg">

    <!-- Google Fonts: Oswald + DM Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Oswald:wght@500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap 5.3 & Font Awesome 6.5 Free -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">

    <!-- HB FITNESS Stylesheets -->
    <link rel="stylesheet" href="Views/assets/css/style.css">
    <link rel="stylesheet" href="Views/assets/css/frontoffice.css">
    <link rel="stylesheet" href="Views/assets/css/backoffice.css">
    <link rel="stylesheet" href="Views/assets/css/blog.css">
</head>
