<?php
declare(strict_types=1);

$escape = static fn (mixed $value): string => htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="theme-color" content="#111113">
  <title><?= isset($selectedListing) && $selectedListing ? $escape($selectedListing['title']) . ' — ' : '' ?>Atelier Market</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="front-office.css">
</head>
<body class="storefront">
  <header class="store-header">
    <a class="store-brand" href="index.php?page=store" aria-label="Atelier Market storefront">
      <span class="store-brand-mark">a<span>.</span></span>
      <span>atelier<small>sports marketplace</small></span>
    </a>
    <nav class="store-nav" aria-label="Store navigation">
      <a class="store-nav-active" href="index.php?page=store">Store</a>
      <a href="#categories">Categories</a>
    </nav>
    <a class="store-admin-link" href="index.php?page=admin" aria-label="Switch to Back Office">Back Office <span>↗</span></a>
  </header>

  <main>
    <?php if (!empty($flashMessage)): ?>
      <div class="store-flash store-flash-<?= $escape($flashMessage['type']) ?>" role="status"><?= $escape($flashMessage['text']) ?></div>
    <?php endif; ?>

    <?php if (isset($selectedListing) && $selectedListing !== null): ?>
      <section class="product-detail">
        <a class="back-link" href="index.php?page=store">← Back to store</a>
        <div class="detail-grid">
          <div class="detail-visual"><span><?= $escape($selectedListing['icon']) ?></span></div>
          <div class="detail-copy">
            <span class="product-category"><?= $escape($selectedListing['categoryLabel']) ?></span>
            <h1><?= $escape($selectedListing['title']) ?></h1>
            <p class="detail-condition"><?= $escape($selectedListing['condition']) ?> · listed by <?= $escape($selectedListing['seller']) ?></p>
            <?php if ($selectedListing['description'] !== ''): ?>
              <p class="detail-description"><?= nl2br($escape($selectedListing['description'])) ?></p>
            <?php else: ?>
              <p class="detail-description">A sports item selected by our community. Contact the seller by reserving this item.</p>
            <?php endif; ?>
            <div class="detail-price">€<?= number_format($selectedListing['price'], 2, '.', ',') ?></div>
            <?php if ($selectedListing['status'] === 'Available'): ?>
              <form class="reservation-form" method="post" action="index.php?page=store">
                <input type="hidden" name="action" value="buy">
                <input type="hidden" name="listing_id" value="<?= (int) $selectedListing['id'] ?>">
                <input type="hidden" name="csrf_token" value="<?= $escape($csrfToken) ?>">
                <label>Your name<input name="buyer_name" autocomplete="name" placeholder="Full name"></label>
                <label>Your email<input name="buyer_email" type="text" inputmode="email" autocomplete="email" placeholder="you@example.com"></label>
                <button class="store-button" type="submit">Reserve item <span>→</span></button>
                <small>Your reservation creates an order with “To process” status.</small>
              </form>
            <?php else: ?>
              <div class="unavailable-note">This item is no longer available.</div>
            <?php endif; ?>
          </div>
        </div>
      </section>
    <?php else: ?>
      <section class="store-hero">
        <div class="hero-copy">
          <span class="hero-kicker">MOVE · SHARE · REPEAT</span>
          <h1>Keep the sport going.<br><span>Give gear a new life.</span></h1>
          <p>Discover pre-loved sports equipment, selected by a community of enthusiasts.</p>
          <a class="store-button" href="#catalogue">Explore the store <span>↓</span></a>
        </div>
        <div class="hero-art" aria-hidden="true"><div class="hero-orbit"></div><span class="hero-equipment">🏋️</span><span class="hero-ball">⚽</span><span class="hero-spark">✳</span><span class="hero-caption">GEAR UP BETTER,<br>WASTE LESS.</span></div>
      </section>

      <section class="store-content" id="catalogue">
        <div class="section-heading">
          <div><span class="section-kicker">OUR SELECTION</span><h2>Ready for your next challenge?</h2><p>Quality equipment, ready for a new life.</p></div>
          <span class="product-count"><?= count($listings) ?> item<?= count($listings) === 1 ? '' : 's' ?></span>
        </div>

        <div class="catalogue-tools" id="categories">
          <nav class="category-pills" aria-label="Filter by category">
            <a class="<?= $activeCategory === '' ? 'selected' : '' ?>" href="index.php?page=store">All items</a>
            <?php foreach ($categories as $category): ?>
              <a class="<?= $activeCategory === $category['name'] ? 'selected' : '' ?>" href="index.php?page=store&amp;category=<?= rawurlencode($category['name']) ?>"><?= $escape($category['icon']) ?> <?= $escape($category['label']) ?></a>
            <?php endforeach; ?>
          </nav>
          <form class="store-search" action="index.php" method="get">
            <input type="hidden" name="page" value="store">
            <?php if ($activeCategory !== ''): ?><input type="hidden" name="category" value="<?= $escape($activeCategory) ?>"><?php endif; ?>
            <label for="store-search">⌕</label>
            <input id="store-search" type="search" name="q" value="<?= $escape($search) ?>" placeholder="Search equipment">
            <button type="submit">Search</button>
          </form>
        </div>

        <?php if ($listings === []): ?>
          <div class="store-empty"><span>⌕</span><h3>No items found</h3><p>Try another search or browse all categories.</p><a href="index.php?page=store">Reset filters</a></div>
        <?php else: ?>
          <div class="product-grid">
            <?php foreach ($listings as $index => $listing): ?>
              <article class="product-card">
                <a class="product-art product-art-<?= $index % 5 ?>" href="index.php?page=store&amp;listing=<?= (int) $listing['id'] ?>" aria-label="View <?= $escape($listing['title']) ?>">
                  <span class="product-art-icon"><?= $escape($listing['icon']) ?></span><span class="product-art-tag"><?= $escape($listing['condition']) ?></span>
                </a>
                <div class="product-card-body">
                  <span class="product-category"><?= $escape($listing['categoryLabel']) ?></span>
                  <h3><a href="index.php?page=store&amp;listing=<?= (int) $listing['id'] ?>"><?= $escape($listing['title']) ?></a></h3>
                  <div class="product-card-bottom"><strong>€<?= number_format($listing['price'], 2, '.', ',') ?></strong><a href="index.php?page=store&amp;listing=<?= (int) $listing['id'] ?>">View item <span>↗</span></a></div>
                  <div class="product-seller">Listed by <?= $escape($listing['seller']) ?></div>
                </div>
              </article>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </section>
    <?php endif; ?>
  </main>

  <footer class="store-footer"><a class="store-brand" href="index.php?page=store"><span class="store-brand-mark">a<span>.</span></span><span>atelier<small>sports marketplace</small></span></a><span>Give your equipment a second life. <a href="index.php?page=admin">Marketplace management ↗</a></span></footer>
</body>
</html>
