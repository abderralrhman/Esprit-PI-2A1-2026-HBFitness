<?php
/**
 * Views/admin/dashboard.php
 * Marketplace Management Backoffice View (Unified HB FITNESS Layout)
 * Expects: $currentUser (?User)
 */
declare(strict_types=1);

if (!isset($currentUser) || $currentUser === null) {
    require_once __DIR__ . '/../../Controllers/AuthController.php';
    $auth = new AuthController();
    $currentUser = $auth->getCurrentUser() ?? $auth->findById(1);
}

$currentPage = 'market_admin';
$pageHeading = 'Marketplace Management';
$title       = 'Marketplace Admin — HB FITNESS';

ob_start();
?>
<link rel="stylesheet" href="Views/assets/css/market_admin.css?v=2">

<div class="market-admin-container">
    <!-- Sub-navigation & Actions Toolbar -->
    <div class="market-nav-bar">
        <div class="market-tabs main-nav" role="tablist">
            <button type="button" class="nav-link active" data-view="dashboard">
                <i class="fa-solid fa-chart-pie me-1"></i> Overview
            </button>
            <button type="button" class="nav-link" data-view="listings">
                <i class="fa-solid fa-boxes-stacked me-1"></i> Listings 
                <span class="nav-count" id="listing-nav-count">0</span>
            </button>
            <button type="button" class="nav-link" data-view="orders">
                <i class="fa-solid fa-cart-shopping me-1"></i> Orders 
                <span class="nav-count" id="order-nav-count">0</span>
            </button>
            <button type="button" class="nav-link" data-view="categories">
                <i class="fa-solid fa-tags me-1"></i> Categories
            </button>
        </div>

        <div class="d-flex align-items-center gap-2">
            <a href="index.php?action=store" class="btn btn-hb-outline btn-sm">
                <i class="fa-solid fa-store me-1"></i> View Storefront
            </a>
            <button type="button" class="btn btn-hb-primary btn-sm" id="add-listing-button">
                <i class="fa-solid fa-plus me-1"></i> New Listing
            </button>
        </div>
    </div>

    <!-- Hidden compatibility elements for app.js -->
    <div id="sidebar" class="d-none"></div>
    <div id="current-date" class="d-none"></div>
    <div id="page-title" class="d-none"></div>
    <div id="page-subtitle" class="d-none"></div>
    <div id="breadcrumb-current" class="d-none"></div>

    <!-- ================================================================= -->
    <!-- 1. OVERVIEW DASHBOARD VIEW -->
    <!-- ================================================================= -->
    <section id="dashboard-view" class="view-panel">
        <!-- KPI Stat Cards -->
        <div class="market-stats-grid">
            <!-- stat-card classes required by app.js renderStats() -->
            <article class="market-stat-card stat-card">
                <div class="market-stat-top">
                    <span>Revenue</span>
                    <div class="market-stat-icon"><i class="fa-solid fa-euro-sign"></i></div>
                </div>
                <div class="market-stat-value stat-value" id="revenue-stat">€0.00</div>
                <div class="market-stat-foot stat-foot">
                    <span class="trend">0 completed</span> <span>completed orders</span>
                </div>
            </article>

            <article class="market-stat-card stat-card">
                <div class="market-stat-top">
                    <span>Orders Received</span>
                    <div class="market-stat-icon"><i class="fa-solid fa-cart-arrow-down"></i></div>
                </div>
                <div class="market-stat-value stat-value" id="orders-stat">0</div>
                <div class="market-stat-foot stat-foot">
                    <span class="trend">0 orders</span> <span>in total</span>
                </div>
            </article>

            <article class="market-stat-card stat-card">
                <div class="market-stat-top">
                    <span>Active Listings</span>
                    <div class="market-stat-icon"><i class="fa-solid fa-boxes-packing"></i></div>
                </div>
                <div class="market-stat-value stat-value" id="active-stat">0</div>
                <div class="market-stat-foot stat-foot">
                    <span class="trend">0 live</span> <span>available to buy</span>
                </div>
            </article>

            <article class="market-stat-card stat-card">
                <div class="market-stat-top">
                    <span>To Process</span>
                    <div class="market-stat-icon"><i class="fa-solid fa-clock-rotate-left"></i></div>
                </div>
                <div class="market-stat-value stat-value" id="pending-stat">0</div>
                <div class="market-stat-foot stat-foot">
                    <span class="trend" style="color:#FBBF24;font-weight:600;">Needs attention</span> <span>orders to process</span>
                </div>
            </article>
        </div>

        <!-- Sales Chart & Categories Progress -->
        <div class="market-grid">
            <article class="market-panel">
                <div class="market-panel-heading">
                    <div>
                        <h2><i class="fa-solid fa-chart-line text-coral me-2"></i>Sales Performance</h2>
                        <p>Track your marketplace revenue over time</p>
                    </div>
                </div>
                <div class="chart-summary">
                    <strong>€0.00</strong>
                    <span class="trend">0 orders</span>
                    <small>over the last 7 days</small>
                </div>
                <div class="chart" role="img" aria-label="Sales chart for the last seven days">
                    <div class="chart-y-labels">
                        <span>1 000 €</span><span>750 €</span><span>500 €</span><span>250 €</span><span>0 €</span>
                    </div>
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

            <article class="market-panel">
                <div class="market-panel-heading">
                    <div>
                        <h2><i class="fa-solid fa-tags text-coral me-2"></i>Categories</h2>
                        <p>Listing distribution by category</p>
                    </div>
                    <button type="button" class="text-link" data-view="categories">
                        View all <i class="fa-solid fa-arrow-right ms-1"></i>
                    </button>
                </div>
                <div class="category-list" id="dashboard-categories"></div>
            </article>
        </div>

        <!-- Recent Listings Table -->
        <div class="market-table-panel">
            <div class="market-panel-heading">
                <div>
                    <h2><i class="fa-solid fa-clock me-2 text-coral"></i>Recent Listings</h2>
                    <p>The latest fitness equipment published on your marketplace</p>
                </div>
                <button type="button" class="btn btn-hb-outline btn-sm" data-view="listings">
                    All Listings <i class="fa-solid fa-arrow-right ms-1"></i>
                </button>
            </div>
            <div class="table-wrap">
                <table class="market-table">
                    <thead>
                        <tr>
                            <th>Listing</th>
                            <th>Seller</th>
                            <th>Category</th>
                            <th>Price</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody id="recent-listings"></tbody>
                </table>
            </div>
        </div>
    </section>

    <!-- ================================================================= -->
    <!-- 2. ALL LISTINGS VIEW -->
    <!-- ================================================================= -->
    <section id="listings-view" class="view-panel hidden">
        <div class="market-table-panel">
            <div class="toolbar">
                <div class="filter-tabs" id="listing-filters">
                    <button class="filter-tab active" data-filter="All">All <span id="all-count">0</span></button>
                    <button class="filter-tab" data-filter="Available">Available</button>
                    <button class="filter-tab" data-filter="Pending">Pending</button>
                    <button class="filter-tab" data-filter="Sold">Sold</button>
                </div>
                <div class="listing-search-actions">
                    <label class="listing-search">
                        <i class="fa-solid fa-magnifying-glass text-secondary"></i>
                        <input id="listing-search" type="search" placeholder="Search listings..." aria-label="Search listings">
                        <button type="button" id="listing-search-clear" aria-label="Clear search">×</button>
                    </label>
                    <span class="text-silver small" id="listing-search-count">0 results</span>
                    <label class="listing-sort">
                        <span>Sort:</span>
                        <select id="listing-sort" aria-label="Sort listings">
                            <option value="newest">Newest</option>
                            <option value="oldest">Oldest</option>
                            <option value="price-desc">Price: high to low</option>
                            <option value="price-asc">Price: low to high</option>
                            <option value="title-asc">Title: A to Z</option>
                            <option value="title-desc">Title: Z to A</option>
                            <option value="seller-asc">Seller: A to Z</option>
                        </select>
                    </label>
                </div>
            </div>

            <div class="table-wrap">
                <table class="market-table">
                    <thead>
                        <tr>
                            <th>Listing</th>
                            <th>Seller</th>
                            <th>Category</th>
                            <th>Price</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody id="all-listings"></tbody>
                </table>
                <div class="empty-state hidden" id="listing-empty">
                    <i class="fa-solid fa-magnifying-glass fa-2x mb-2 text-secondary"></i>
                    <strong>No listings found</strong>
                    <p>Update your search filters or publish a new listing.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- ================================================================= -->
    <!-- 3. ORDERS VIEW -->
    <!-- ================================================================= -->
    <section id="orders-view" class="view-panel hidden">
        <div class="market-table-panel">
            <div class="toolbar">
                <div class="filter-tabs" id="order-filters">
                    <button class="filter-tab active" data-filter="All">All <span id="all-orders-count">0</span></button>
                    <button class="filter-tab" data-filter="To process">To process</button>
                    <button class="filter-tab" data-filter="Shipped">Shipped</button>
                    <button class="filter-tab" data-filter="Completed">Completed</button>
                    <button class="filter-tab" data-filter="Cancelled">Cancelled</button>
                </div>
                <div class="order-search-actions">
                    <label class="order-search">
                        <i class="fa-solid fa-magnifying-glass text-secondary"></i>
                        <input id="order-search" type="search" placeholder="Search orders..." aria-label="Search orders">
                        <button type="button" id="order-search-clear" aria-label="Clear search">×</button>
                    </label>
                    <span class="text-silver small" id="order-search-count">0 results</span>
                    <label class="order-sort">
                        <span>Sort:</span>
                        <select id="order-sort" aria-label="Sort orders">
                            <option value="newest">Newest</option>
                            <option value="oldest">Oldest</option>
                            <option value="amount-desc">Amount: high to low</option>
                            <option value="amount-asc">Amount: low to high</option>
                            <option value="buyer-asc">Buyer: A to Z</option>
                            <option value="buyer-desc">Buyer: Z to A</option>
                        </select>
                    </label>
                    <button type="button" class="btn btn-hb-outline btn-sm" id="export-orders">
                        <i class="fa-solid fa-download me-1"></i> Export CSV
                    </button>
                </div>
            </div>

            <div class="table-wrap">
                <table class="market-table">
                    <thead>
                        <tr>
                            <th>Order</th>
                            <th>Item</th>
                            <th>Buyer</th>
                            <th>Date</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody id="all-orders"></tbody>
                </table>
                <div class="empty-state hidden" id="order-empty">
                    <i class="fa-solid fa-box-open fa-2x mb-2 text-secondary"></i>
                    <strong>No orders found</strong>
                    <p>Matching customer reservations will appear here.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- ================================================================= -->
    <!-- 4. CATEGORIES VIEW -->
    <!-- ================================================================= -->
    <section id="categories-view" class="view-panel hidden">
        <div class="market-table-panel">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
                <div>
                    <h2 class="h5 text-uppercase mb-1"><i class="fa-solid fa-tags text-coral me-2"></i>Catalog Categories</h2>
                    <p class="text-secondary small mb-0">Organize listings and help customers find the right equipment.</p>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <label class="category-search">
                        <i class="fa-solid fa-magnifying-glass text-secondary"></i>
                        <input id="category-search" type="search" placeholder="Search categories..." aria-label="Search categories">
                        <button type="button" id="category-search-clear" aria-label="Clear search">×</button>
                    </label>
                    <span class="text-silver small" id="category-total">0 categories</span>
                    <label class="category-sort">
                        <span>Sort:</span>
                        <select id="category-sort" aria-label="Sort categories">
                            <option value="name-asc">Name: A to Z</option>
                            <option value="name-desc">Name: Z to A</option>
                            <option value="count-desc">Listings: high to low</option>
                            <option value="count-asc">Listings: low to high</option>
                        </select>
                    </label>
                    <button type="button" class="btn btn-hb-primary btn-sm" id="add-category-button">
                        <i class="fa-solid fa-plus me-1"></i> Add Category
                    </button>
                </div>
            </div>
            <div class="category-cards" id="category-cards"></div>
        </div>
    </section>
</div>

<!-- ================================================================= -->
<!-- MODALS -->
<!-- ================================================================= -->
<!-- Order Modal -->
<div class="modal-backdrop hidden" id="order-modal" role="presentation">
    <section class="modal" role="dialog" aria-modal="true" aria-labelledby="order-modal-title">
        <div class="modal-heading">
            <div>
                <span class="eyebrow text-coral">ORDERS</span>
                <h2 id="order-modal-title">Create Order</h2>
                <p>Reserve an available listing for a buyer.</p>
            </div>
            <button type="button" class="close-button" data-close-order-modal aria-label="Close">×</button>
        </div>
        <form id="order-form">
            <input type="hidden" name="orderId" value="">
            <label class="form-field">
                Listing
                <select name="listingId"></select>
            </label>
            <div class="form-row">
                <label class="form-field">
                    Buyer Name
                    <input name="buyerName" placeholder="Full name">
                </label>
                <label class="form-field">
                    Status
                    <select name="orderStatus">
                        <option>To process</option>
                        <option>Shipped</option>
                        <option>Completed</option>
                        <option>Cancelled</option>
                    </select>
                </label>
            </div>
            <div class="form-row create-order-only">
                <label class="form-field" style="grid-column: 1 / -1;">
                    Buyer Email
                    <input name="buyerEmail" type="text" inputmode="email" placeholder="buyer@example.com">
                </label>
            </div>
            <div class="modal-actions">
                <button type="button" class="button-secondary" data-close-order-modal>Cancel</button>
                <button type="submit" class="button-primary" id="order-submit-button">Create Order</button>
            </div>
        </form>
    </section>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal-backdrop hidden" id="confirm-modal" role="presentation">
    <section class="modal confirm-modal" role="dialog" aria-modal="true" aria-labelledby="confirm-title">
        <div class="confirm-icon text-coral fs-1 mb-2" aria-hidden="true"><i class="fa-solid fa-triangle-exclamation"></i></div>
        <h2 id="confirm-title">Confirm Deletion</h2>
        <p id="confirm-message" class="text-silver">This action cannot be undone.</p>
        <div class="modal-actions">
            <button type="button" class="button-secondary" id="confirm-cancel">Cancel</button>
            <button type="button" class="button-danger" id="confirm-delete">Delete</button>
        </div>
    </section>
</div>

<!-- Listing Modal -->
<div class="modal-backdrop hidden" id="listing-modal" role="presentation">
    <section class="modal" role="dialog" aria-modal="true" aria-labelledby="modal-title">
        <div class="modal-heading">
            <div>
                <span class="eyebrow text-coral">CATALOG</span>
                <h2 id="modal-title">Create a Listing</h2>
                <p>Add a new sports equipment item to the marketplace.</p>
            </div>
            <button type="button" class="close-button" data-close-modal aria-label="Close">×</button>
        </div>
        <form id="listing-form">
            <input type="hidden" name="listing_id" value="">
            <label class="form-field">
                Item Title
                <input name="title" placeholder="e.g. Adjustable dumbbells">
            </label>
            <div class="form-row">
                <label class="form-field">
                    Category
                    <select name="category"></select>
                </label>
                <label class="form-field">
                    Price (€)
                    <input name="price" type="text" inputmode="decimal" placeholder="0.00">
                </label>
            </div>
            <div class="form-row">
                <label class="form-field">
                    Condition
                    <select name="condition">
                        <option>Like new</option>
                        <option>Very good</option>
                        <option>Good</option>
                        <option>Needs repair</option>
                    </select>
                </label>
                <label class="form-field">
                    Seller
                    <input name="seller" placeholder="Seller name">
                </label>
            </div>
            <div class="modal-actions">
                <button type="button" class="button-secondary" data-close-modal>Cancel</button>
                <button type="submit" class="button-primary">Publish Listing</button>
            </div>
        </form>
    </section>
</div>

<!-- Category Modal -->
<div class="modal-backdrop hidden" id="category-modal" role="presentation">
    <section class="modal category-modal" role="dialog" aria-modal="true" aria-labelledby="category-modal-title">
        <div class="modal-heading">
            <div>
                <span class="eyebrow text-coral">CATALOG</span>
                <h2 id="category-modal-title">Create Category</h2>
                <p>Define a name for the new category.</p>
            </div>
            <button type="button" class="close-button" data-close-category-modal aria-label="Close">×</button>
        </div>
        <form id="category-form">
            <input type="hidden" name="category_id" value="">
            <label class="form-field">
                Category Name
                <input name="name" placeholder="e.g. Boxing & Combat">
            </label>
            <div class="modal-actions">
                <button type="button" class="button-secondary" data-close-category-modal>Cancel</button>
                <button type="submit" class="button-primary">Add Category</button>
            </div>
        </form>
    </section>
</div>

<!-- Toast notification -->
<div class="toast hidden" id="toast" role="status" aria-live="polite"></div>

<script src="app.js?v=3"></script>

<?php
$content = (string)ob_get_clean();
require __DIR__ . '/../layout_admin.php';
