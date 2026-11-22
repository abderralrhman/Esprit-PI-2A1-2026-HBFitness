<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="theme-color" content="#111113">
  <title>Atelier Market — Marketplace Management</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <div class="app-shell">
    <aside class="sidebar" id="sidebar">
      <a class="brand" href="#" aria-label="Atelier Market home" data-view="dashboard">
        <span class="brand-mark">a<span>.</span></span>
        <span class="brand-copy">atelier<span>marketplace</span></span>
      </a>

      <div class="workspace-label">WORKSPACE</div>
      <nav class="main-nav" aria-label="Main navigation">
        <button class="nav-link active" data-view="dashboard"><span class="nav-icon">◫</span>Overview</button>
        <button class="nav-link" data-view="listings"><span class="nav-icon">▤</span>Listings <span class="nav-count" id="listing-nav-count">0</span></button>
        <button class="nav-link" data-view="orders"><span class="nav-icon">▧</span>Orders <span class="nav-count" id="order-nav-count">0</span></button>
        <button class="nav-link" data-view="categories"><span class="nav-icon">◈</span>Categories</button>
        <a class="nav-link storefront-link" href="index.php?page=store" aria-label="Switch to Front Office"><span class="nav-icon">↗</span>Front Office</a>
      </nav>

      <div class="sidebar-bottom">
        <div class="help-card">
          <div class="help-icon">✳</div>
          <strong>Need help?</strong>
          <p>Our team is here to help you.</p>
          <a href="mailto:support@atelier.market">Contact support <span>↗</span></a>
        </div>
        <button class="profile-button" aria-label="Sophie Martin profile">
          <span class="avatar avatar-photo">SM</span>
          <span class="profile-copy"><strong>Sophie Martin</strong><small>Administrator</small></span>
          <span class="profile-menu">···</span>
        </button>
      </div>
    </aside>

    <main class="main-content">
      <header class="topbar">
        <button class="mobile-menu" id="mobile-menu" aria-label="Open menu">☰</button>
        <div class="breadcrumbs">Workspace <span>/</span> <strong id="breadcrumb-current">Overview</strong></div>
        <div class="topbar-actions">
          <label class="global-search">
            <span aria-hidden="true">⌕</span>
            <input id="global-search" type="search" placeholder="Search..." aria-label="Search this page">
            <kbd>⌘ K</kbd>
          </label>
          <a class="office-switch" href="index.php?page=store" aria-label="Switch to Front Office" title="Switch to Front Office"><span class="office-switch-label">Front Office</span><span class="office-switch-short">Front</span><span aria-hidden="true">↗</span></a>
          <button class="icon-button notification-button" aria-label="Notifications">♧<i></i></button>
          <span class="avatar avatar-photo top-avatar">SM</span>
        </div>
      </header>

      <div class="page-container">
        <section class="page-heading">
          <div>
            <div class="eyebrow" id="current-date">MONDAY, NOVEMBER 16, 2026</div>
            <h1 id="page-title">Hello Sophie <span class="wave">✳</span></h1>
            <p class="page-subtitle" id="page-subtitle">Here’s what’s happening on your marketplace today.</p>
          </div>
          <button class="button button-primary" id="add-listing-button"><span class="plus">+</span> New listing</button>
        </section>

        <section id="dashboard-view" class="view-panel">
          <div class="stats-grid">
            <article class="stat-card">
              <div class="stat-top"><span>Revenue</span><span class="stat-icon mint">↗</span></div>
              <div class="stat-value">8 420 <small>€</small></div>
              <div class="stat-foot"><span class="trend">0 completed</span><span>completed orders</span></div>
            </article>
            <article class="stat-card">
              <div class="stat-top"><span>Orders received</span><span class="stat-icon peach">▧</span></div>
              <div class="stat-value" id="orders-stat">0</div>
              <div class="stat-foot"><span class="trend">0 orders</span><span>in total</span></div>
            </article>
            <article class="stat-card">
              <div class="stat-top"><span>Active listings</span><span class="stat-icon lilac">▤</span></div>
              <div class="stat-value" id="active-stat">0</div>
              <div class="stat-foot"><span class="trend">0 live</span><span>available to buy</span></div>
            </article>
            <article class="stat-card">
              <div class="stat-top"><span>To process</span><span class="stat-icon yellow">◷</span></div>
              <div class="stat-value" id="pending-stat">0</div>
              <div class="stat-foot"><span class="neutral">Needs attention</span><span>orders to process</span></div>
            </article>
          </div>

          <div class="dashboard-grid">
            <article class="panel revenue-panel">
              <div class="panel-heading">
                <div><h2>Sales performance</h2><p>Track your marketplace revenue</p></div>
                <button class="select-button" aria-label="Selected period: last 7 days">Last 7 days <span>⌄</span></button>
              </div>
              <div class="chart-summary"><strong>€0.00</strong><span class="trend">0 orders</span><small>over the last 7 days</small></div>
              <div class="chart" role="img" aria-label="Sales chart for the last seven days">
                <div class="chart-y-labels"><span>1 000 €</span><span>750 €</span><span>500 €</span><span>250 €</span><span>0 €</span></div>
                <div class="chart-content">
                  <div class="chart-gridlines"><i></i><i></i><i></i><i></i><i></i></div>
                  <div class="bars">
                    <div class="bar-item"><div class="bar" style="--height:38%"></div><span>Mon</span></div>
                    <div class="bar-item"><div class="bar" style="--height:57%"></div><span>Tue</span></div>
                    <div class="bar-item"><div class="bar" style="--height:46%"></div><span>Wed</span></div>
                    <div class="bar-item"><div class="bar" style="--height:77%"></div><span>Thu</span></div>
                    <div class="bar-item"><div class="bar" style="--height:61%"></div><span>Fri</span></div>
                    <div class="bar-item"><div class="bar" style="--height:90%"></div><span>Sat</span></div>
                    <div class="bar-item"><div class="bar bar-today" style="--height:68%"></div><span>Sun</span></div>
                  </div>
                </div>
              </div>
            </article>

            <article class="panel category-panel">
              <div class="panel-heading">
                <div><h2>Your categories</h2><p>Listing distribution</p></div>
                <button class="text-link" data-view="categories">View all <span>↗</span></button>
              </div>
              <div class="category-list" id="dashboard-categories"></div>
            </article>
          </div>

          <section class="panel table-panel">
            <div class="panel-heading table-heading">
              <div><h2>Recent listings</h2><p>The latest items published on your marketplace</p></div>
              <button class="button button-secondary" data-view="listings">View all listings <span>→</span></button>
            </div>
            <div class="table-wrap">
              <table>
                <thead><tr><th>LISTING</th><th>SELLER</th><th>CATEGORY</th><th>PRICE</th><th>STATUS</th><th></th></tr></thead>
                <tbody id="recent-listings"></tbody>
              </table>
            </div>
          </section>
        </section>

        <section id="listings-view" class="view-panel hidden">
          <div class="panel table-panel">
            <div class="toolbar">
              <div class="filter-tabs" id="listing-filters">
                <button class="filter-tab active" data-filter="All">All <span id="all-count">0</span></button>
                <button class="filter-tab" data-filter="Available">Available</button>
                <button class="filter-tab" data-filter="Pending">Pending</button>
                <button class="filter-tab" data-filter="Sold">Sold</button>
              </div>
              <button class="button button-secondary" id="listing-sort">Sort: newest <span>⌄</span></button>
            </div>
            <div class="table-wrap">
              <table>
                <thead><tr><th>LISTING</th><th>SELLER</th><th>CATEGORY</th><th>PRICE</th><th>STATUS</th><th></th></tr></thead>
                <tbody id="all-listings"></tbody>
              </table>
              <div class="empty-state hidden" id="listing-empty"><span>⌕</span><strong>No listings found</strong><p>Update your search or add a new listing.</p></div>
            </div>
          </div>
        </section>

        <section id="orders-view" class="view-panel hidden">
          <div class="panel table-panel">
            <div class="toolbar">
              <div class="filter-tabs" id="order-filters">
                <button class="filter-tab active" data-filter="All">All <span id="all-orders-count">0</span></button>
                <button class="filter-tab" data-filter="To process">To process</button>
                <button class="filter-tab" data-filter="Shipped">Shipped</button>
                <button class="filter-tab" data-filter="Completed">Completed</button>
              </div>
              <button class="button button-secondary" id="export-orders">↓ Export</button>
            </div>
            <div class="table-wrap">
              <table>
                <thead><tr><th>ORDER</th><th>ITEM</th><th>BUYER</th><th>DATE</th><th>AMOUNT</th><th>STATUS</th><th></th></tr></thead>
                <tbody id="all-orders"></tbody>
              </table>
              <div class="empty-state hidden" id="order-empty"><span>▧</span><strong>No orders found</strong><p>Matching orders will appear here.</p></div>
            </div>
          </div>
        </section>

        <section id="categories-view" class="view-panel hidden">
          <div class="category-page-heading"><div><h2>Catalog categories</h2><p>Organize listings to help customers find the right equipment.</p></div><button class="button button-secondary" id="add-category-button"><span class="plus">+</span> Add category</button></div>
          <div class="category-cards" id="category-cards"></div>
        </section>
        <footer class="page-footer"><span>© 2026 Atelier Market</span><span>A marketplace for sports enthusiasts <span class="footer-heart">♥</span></span></footer>
      </div>
    </main>
  </div>

  <div class="modal-backdrop hidden" id="listing-modal" role="presentation">
    <section class="modal" role="dialog" aria-modal="true" aria-labelledby="modal-title">
      <div class="modal-heading"><div><span class="eyebrow">CATALOG</span><h2 id="modal-title">Create a listing</h2><p>Add an item to the marketplace.</p></div><button class="close-button" data-close-modal aria-label="Close">×</button></div>
      <form id="listing-form">
        <input type="hidden" name="listing_id" value="">
        <label class="form-field">Item name<input name="title" placeholder="e.g. Adjustable dumbbells"></label>
        <div class="form-row">
          <label class="form-field">Category<select name="category"></select></label>
          <label class="form-field">Price (€)<input name="price" type="text" inputmode="decimal" placeholder="0.00"></label>
        </div>
        <div class="form-row">
          <label class="form-field">Condition<select name="condition"><option>Like new</option><option>Very good</option><option>Good</option><option>Needs repair</option></select></label>
          <label class="form-field">Seller<input name="seller" placeholder="Seller name"></label>
        </div>
        <div class="modal-actions"><button type="button" class="button button-secondary" data-close-modal>Cancel</button><button type="submit" class="button button-primary">Publish listing <span>→</span></button></div>
      </form>
    </section>
  </div>

  <div class="modal-backdrop hidden" id="category-modal" role="presentation">
    <section class="modal category-modal" role="dialog" aria-modal="true" aria-labelledby="category-modal-title">
      <div class="modal-heading"><div><span class="eyebrow">CATALOG</span><h2 id="category-modal-title">Create category</h2><p>Define a name for the category.</p></div><button type="button" class="close-button" data-close-category-modal aria-label="Close">×</button></div>
      <form id="category-form">
        <input type="hidden" name="category_id" value="">
        <label class="form-field">Category name<input name="name" placeholder="e.g. Boxing"></label>
        <div class="modal-actions"><button type="button" class="button button-secondary" data-close-category-modal>Cancel</button><button type="submit" class="button button-primary">Add category</button></div>
      </form>
    </section>
  </div>

  <div class="toast hidden" id="toast" role="status" aria-live="polite"></div>
  <script src="app.js"></script>
</body>
</html>
