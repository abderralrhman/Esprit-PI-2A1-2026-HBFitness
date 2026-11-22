let listings = [];
let orders = [];
let categories = [];
let currentView = "dashboard";
let listingFilter = "All";
let orderFilter = "All";
let searchTerm = "";
let oldestFirst = false;
let toastTimer;

const viewTitles = {
  dashboard: ["Overview", "Hello Sophie", "Here’s what’s happening on your marketplace today."],
  listings: ["Listings", "Listing management", "Browse and manage items published on your marketplace."],
  orders: ["Orders", "Order tracking", "Track purchases and update their shipping status."],
  categories: ["Categories", "Catalog categories", "Organize your marketplace catalog."]
};

const escapeHTML = (value) => String(value).replace(/[&<>"']/g, (character) => ({
  "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;"
}[character]));

const money = (value) => `€${Number(value).toLocaleString("en-GB", { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
const initials = (name) => name.split(/\s+/).slice(0, 2).map((part) => part.charAt(0)).join("").toUpperCase();
const safeStatusClass = (status) => ({
  "Available": "available", "Pending": "waiting", "Sold": "sold",
  "To process": "pending", "Shipped": "shipped", "Completed": "done", "Cancelled": "cancelled"
}[status] || "waiting");

async function apiRequest(resource, options = {}) {
  const [resourceName, ...queryParts] = resource.split("&");
  const query = new URLSearchParams({ resource: resourceName });
  queryParts.forEach((part) => {
    const [key, value] = part.split("=");
    query.set(key, value);
  });
  const response = await fetch(`api.php?${query.toString()}`, {
    method: options.method || "GET",
    headers: options.body
      ? { "Content-Type": "application/json", "Accept": "application/json" }
      : { "Accept": "application/json" },
    body: options.body ? JSON.stringify(options.body) : undefined
  });
  let data;
  try {
    data = await response.json();
  } catch (error) {
    throw new Error("Invalid PHP response. Check the API configuration.");
  }
  if (!response.ok) throw new Error(data.error || `HTTP error ${response.status}.`);
  return data;
}

async function loadDashboard() {
  try {
    const data = await apiRequest("dashboard");
    listings = data.listings;
    orders = data.orders;
    categories = data.categories;
    render();
  } catch (error) {
    console.error("Unable to load marketplace data.", error);
    showToast(error.message);
  }
}

function showToast(message) {
  const toast = document.getElementById("toast");
  toast.textContent = message;
  toast.classList.remove("hidden");
  window.clearTimeout(toastTimer);
  toastTimer = window.setTimeout(() => toast.classList.add("hidden"), 3000);
}

function matchesSearch(values) {
  const needle = searchTerm.trim().toLocaleLowerCase("en");
  return !needle || values.some((value) => String(value).toLocaleLowerCase("en").includes(needle));
}

function renderStats() {
  const pendingOrders = orders.filter((order) => order.status === "To process").length;
  const activeListings = listings.filter((listing) => listing.status === "Available").length;
  const completedOrders = orders.filter((order) => order.status === "Completed");
  const revenue = completedOrders.reduce((total, order) => total + Number(order.amount), 0);
  const statCards = document.querySelectorAll(".stat-card");
  statCards[0].querySelector(".stat-value").textContent = money(revenue);
  statCards[0].querySelector(".stat-foot .trend").textContent = `${completedOrders.length} completed`;
  statCards[0].querySelector(".stat-foot span:last-child").textContent = "completed orders";
  statCards[1].querySelector(".stat-foot .trend").textContent = `${orders.length} orders`;
  statCards[1].querySelector(".stat-foot span:last-child").textContent = "in total";
  statCards[2].querySelector(".stat-foot .trend").textContent = `${activeListings} live`;
  statCards[2].querySelector(".stat-foot span:last-child").textContent = "available to buy";
  document.getElementById("orders-stat").textContent = orders.length;
  document.getElementById("active-stat").textContent = activeListings;
  document.getElementById("pending-stat").textContent = pendingOrders;
  document.getElementById("listing-nav-count").textContent = activeListings;
  document.getElementById("order-nav-count").textContent = pendingOrders;
  document.getElementById("all-count").textContent = listings.length;
  document.getElementById("all-orders-count").textContent = orders.length;
}

function renderSalesChart() {
  const today = new Date();
  const days = Array.from({ length: 7 }, (_, index) => {
    const date = new Date(today);
    date.setDate(today.getDate() - (6 - index));
    const dateIso = `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, "0")}-${String(date.getDate()).padStart(2, "0")}`;
    return { date, dateIso, amount: 0 };
  });
  const totals = new Map(days.map((day) => [day.dateIso, day]));
  orders.filter((order) => order.status !== "Cancelled").forEach((order) => {
    const day = totals.get(order.dateIso);
    if (day) day.amount += Number(order.amount);
  });
  const maxAmount = Math.max(1, ...days.map((day) => day.amount));
  const sevenDayRevenue = days.reduce((total, day) => total + day.amount, 0);
  document.querySelector(".chart-summary strong").textContent = money(sevenDayRevenue);
  document.querySelector(".chart-summary .trend").textContent = `${orders.filter((order) => totals.has(order.dateIso)).length} orders`;
  document.querySelectorAll(".bar-item").forEach((barItem, index) => {
    const day = days[index];
    barItem.querySelector(".bar").style.setProperty("--height", `${Math.max(4, day.amount / maxAmount * 90)}%`);
    barItem.querySelector("span").textContent = new Intl.DateTimeFormat("en-GB", { weekday: "short" }).format(day.date);
    barItem.setAttribute("aria-label", `${barItem.querySelector("span").textContent}: ${money(day.amount)}`);
  });
  document.querySelectorAll(".chart-y-labels span").forEach((label, index) => {
    label.textContent = money(maxAmount * (4 - index) / 4);
  });
}

function renderCategoryRows() {
  const listingCounts = categories.map((category) => ({
    ...category,
    count: listings.filter((listing) => listing.category === category.name).length
  }));
  const maxCount = Math.max(1, ...listingCounts.map((category) => category.count));
  document.getElementById("dashboard-categories").innerHTML = listingCounts.slice(0, 4).map((category) => `
    <div class="category-row">
      <span class="category-emoji">${escapeHTML(category.icon)}</span>
      <div class="category-info"><strong>${escapeHTML(category.label)}</strong><div class="progress"><span style="width:${Math.max(4, category.count / maxCount * 100)}%"></span></div></div>
      <span class="category-amount">${category.count}</span>
    </div>`).join("");

  document.getElementById("category-cards").innerHTML = categories.map((category) => {
    const count = listings.filter((listing) => listing.category === category.name).length;
    return `<article class="category-card" data-category-id="${category.id}"><div class="category-card-top"><span class="category-emoji">${escapeHTML(category.icon)}</span><span class="category-card-count">${count}</span></div><h3>${escapeHTML(category.label)}</h3><p>${escapeHTML(category.description)}</p><div class="category-actions"><button type="button" data-category-edit="${category.id}" class="button button-secondary">Edit</button><button type="button" data-category-delete="${category.id}" class="button button-secondary">Delete</button></div></article>`;
  }).join("") + `<div class="category-add-card"><button id="category-add-card-button"><span>+</span>Add a category</button></div>`;
  document.getElementById("category-add-card-button")?.addEventListener("click", addCategory);
}

function listingRow(listing, index) {
  return `<tr>
    <td><div class="listing-cell"><span class="product-thumb tone-${index % 6}">${escapeHTML(listing.icon || "📦")}</span><span><span class="listing-name">${escapeHTML(listing.title)}</span><span class="listing-meta">${escapeHTML(listing.condition || "Good")}</span></span></div></td>
    <td><span class="seller-cell"><span class="mini-avatar alt-${index % 4}">${escapeHTML(initials(listing.seller))}</span>${escapeHTML(listing.seller)}</span></td>
    <td>${escapeHTML(listing.categoryLabel)}</td><td class="price-cell">${money(listing.price)}</td>
    <td><span class="status ${safeStatusClass(listing.status)}">${escapeHTML(listing.status)}</span></td>
    <td><div class="action-wrap"><button class="row-menu" data-listing-menu="${listing.id}" aria-label="Actions for ${escapeHTML(listing.title)}">···</button></div></td>
  </tr>`;
}

function renderListings() {
  const filtered = listings.filter((listing) =>
    (listingFilter === "All" || listing.status === listingFilter) &&
    matchesSearch([listing.title, listing.seller, listing.categoryLabel, listing.status])
  );
  filtered.sort((a, b) => oldestFirst ? a.date.localeCompare(b.date) : b.date.localeCompare(a.date));
  document.getElementById("all-listings").innerHTML = filtered.map(listingRow).join("");
  const recent = [...listings].sort((a, b) => b.date.localeCompare(a.date)).slice(0, 4);
  document.getElementById("recent-listings").innerHTML = recent.map(listingRow).join("");
  document.getElementById("listing-empty").classList.toggle("hidden", filtered.length > 0);
  attachListingActions();
}

function renderOrders() {
  const filtered = orders.filter((order) =>
    (orderFilter === "All" || order.status === orderFilter) &&
    matchesSearch([order.id, order.item, order.buyer, order.status])
  );
  document.getElementById("all-orders").innerHTML = filtered.map((order, index) => `
    <tr>
      <td><span class="order-reference">#${escapeHTML(order.reference)}</span></td>
      <td><span class="order-article"><span class="product-thumb tone-${index % 6}">${escapeHTML(order.icon || "📦")}</span>${escapeHTML(order.item)}</span></td>
      <td><span class="seller-cell"><span class="mini-avatar alt-${index % 4}">${escapeHTML(initials(order.buyer))}</span>${escapeHTML(order.buyer)}</span></td>
      <td>${escapeHTML(order.date)}</td><td class="price-cell">${money(order.amount)}</td>
      <td><span class="status ${safeStatusClass(order.status)}">${escapeHTML(order.status)}</span></td>
      <td><button class="row-menu" data-order-action="${escapeHTML(order.id)}" aria-label="Update order ${escapeHTML(order.id)}">···</button></td>
    </tr>`).join("");
  document.getElementById("order-empty").classList.toggle("hidden", filtered.length > 0);
}

function render() {
  renderStats();
  renderSalesChart();
  renderCategoryRows();
  renderListings();
  renderOrders();
}

function changeView(view) {
  if (!viewTitles[view]) return;
  currentView = view;
  document.querySelectorAll(".view-panel").forEach((panel) => panel.classList.add("hidden"));
  document.getElementById(`${view}-view`).classList.remove("hidden");
  document.querySelectorAll(".nav-link").forEach((button) => button.classList.toggle("active", button.dataset.view === view));
  document.getElementById("breadcrumb-current").textContent = viewTitles[view][0];
  document.getElementById("page-title").textContent = viewTitles[view][1];
  document.getElementById("page-subtitle").textContent = viewTitles[view][2];
  document.getElementById("add-listing-button").classList.toggle("hidden", view === "categories");
  document.getElementById("sidebar").classList.remove("open");
  render();
}

function openListingModal() {
  const categorySelect = document.querySelector('#listing-form select[name="category"]');
  categorySelect.innerHTML = categories.map((category) => `<option value="${escapeHTML(category.name)}">${escapeHTML(category.label)}</option>`).join("");
  document.getElementById("listing-form").reset();
  document.getElementById("listing-modal").classList.remove("hidden");
  document.querySelector('#listing-form input[name="title"]').focus();
}

function closeListingModal() {
  document.getElementById("listing-modal").classList.add("hidden");
  document.querySelector('#listing-form input[name="listing_id"]').value = "";
  document.getElementById("modal-title").textContent = "Create a listing";
}

function openListingEdit(listing) {
  const form = document.getElementById("listing-form");
  const categorySelect = form.querySelector('select[name="category"]');
  categorySelect.innerHTML = categories.map((category) => `<option value="${escapeHTML(category.name)}">${escapeHTML(category.label)}</option>`).join("");
  form.elements.listing_id.value = String(listing.id);
  form.elements.title.value = listing.title;
  form.elements.category.value = listing.category;
  form.elements.price.value = String(listing.price);
  form.elements.condition.value = listing.condition;
  form.elements.seller.value = listing.seller;
  document.getElementById("modal-title").textContent = "Edit listing";
  document.getElementById("listing-modal").classList.remove("hidden");
  form.elements.title.focus();
}

function attachListingActions() {
  document.querySelectorAll("[data-listing-menu]").forEach((button) => {
    button.addEventListener("click", () => {
      const listing = listings.find((item) => item.id === Number(button.dataset.listingMenu));
      if (!listing) return;
      const options = listing.status === "Available" ? ["Edit", "Mark as sold", "Put on hold", "Delete"] : ["Edit", "Relist", "Delete"];
      const menu = document.createElement("div");
      menu.className = "row-action-menu";
      menu.innerHTML = options.map((option) => `<button type="button">${option}</button>`).join("");
      button.parentElement.querySelector(".row-action-menu")?.remove();
      button.parentElement.appendChild(menu);
      menu.querySelectorAll("button").forEach((optionButton) => optionButton.addEventListener("click", async () => {
        const action = optionButton.textContent;
        try {
          if (action === "Edit") {
            openListingEdit(listing);
            return;
          }
          if (action === "Delete") {
            if (!window.confirm(`Delete "${listing.title}"?`)) return;
            await apiRequest(`listings&id=${listing.id}`, { method: "DELETE" });
          } else {
            const status = action === "Mark as sold" ? "Sold" : action === "Put on hold" ? "Pending" : "Available";
            await apiRequest(`listings&id=${listing.id}`, { method: "PATCH", body: { status } });
          }
          await loadDashboard();
          showToast(action === "Delete" ? "Listing deleted." : "Listing status updated.");
        } catch (error) {
          console.error("Unable to update listing.", error);
          showToast(error.message);
        }
      }));
      const dismiss = (event) => {
        if (!menu.contains(event.target) && event.target !== button) {
          menu.remove();
          document.removeEventListener("click", dismiss);
        }
      };
      setTimeout(() => document.addEventListener("click", dismiss), 0);
    });
  });
}

async function editCategory(category) {
  const name = window.prompt("Category name:", category.name);
  if (!name || !name.trim()) return;
  const trimmed = name.trim();
  if (trimmed.length > 60) {
    showToast("Category name must contain at most 60 characters.");
    return;
  }
  try {
    await apiRequest(`categories&id=${category.id}`, { method: "PATCH", body: { name: trimmed } });
    await loadDashboard();
    showToast("Category updated.");
  } catch (error) {
    showToast(error.message);
  }
}

async function deleteCategory(category) {
  if (!window.confirm(`Delete the category "${category.name}"?`)) return;
  try {
    await apiRequest(`categories&id=${category.id}`, { method: "DELETE" });
    await loadDashboard();
    showToast("Category deleted.");
  } catch (error) {
    showToast(error.message);
  }
}

async function addCategory() {
  const name = window.prompt("New category name:");
  if (!name || !name.trim()) return;
  if (categories.some((category) => category.name.toLocaleLowerCase("en") === name.trim().toLocaleLowerCase("en"))) {
    showToast("This category already exists.");
    return;
  }
  try {
    await apiRequest("categories", { method: "POST", body: { name: name.trim() } });
    await loadDashboard();
    showToast("Category added to the catalog.");
  } catch (error) {
    console.error("Unable to add category.", error);
    showToast(error.message);
  }
}

document.querySelectorAll("[data-view]").forEach((button) => button.addEventListener("click", () => changeView(button.dataset.view)));
document.getElementById("add-listing-button").addEventListener("click", openListingModal);
document.querySelectorAll("[data-close-modal]").forEach((button) => button.addEventListener("click", closeListingModal));
document.getElementById("listing-modal").addEventListener("click", (event) => {
  if (event.target.id === "listing-modal") closeListingModal();
});
document.addEventListener("keydown", (event) => {
  if (event.key === "Escape") closeListingModal();
  if ((event.metaKey || event.ctrlKey) && event.key.toLowerCase() === "k") {
    event.preventDefault();
    document.getElementById("global-search").focus();
  }
});

document.getElementById("category-cards").addEventListener("click", (event) => {
  const editButton = event.target.closest("[data-category-edit]");
  const deleteButton = event.target.closest("[data-category-delete]");
  if (editButton) {
    const category = categories.find((item) => item.id === Number(editButton.dataset.categoryEdit));
    if (category) editCategory(category);
  }
  if (deleteButton) {
    const category = categories.find((item) => item.id === Number(deleteButton.dataset.categoryDelete));
    if (category) deleteCategory(category);
  }
});

document.getElementById("listing-form").addEventListener("submit", async (event) => {
  event.preventDefault();
  const formData = new FormData(event.currentTarget);
  const listing = {
    title: String(formData.get("title")).trim(),
    category: String(formData.get("category")),
    price: Number(formData.get("price")),
    seller: String(formData.get("seller")).trim(),
    condition: String(formData.get("condition")),
    status: "Available"
  };
  if (!listing.title || listing.title.length > 120 || !listing.seller || listing.seller.length > 100 || !Number.isFinite(listing.price) || listing.price <= 0 || listing.price > 999999.99) {
    showToast("Check the listing details: title, seller and valid price are required.");
    return;
  }
  try {
    const listingId = String(formData.get("listing_id") || "");
    if (listingId) {
      await apiRequest(`listings&id=${listingId}`, { method: "PATCH", body: listing });
      closeListingModal();
      await loadDashboard();
      changeView("listings");
      showToast("Listing updated.");
      return;
    }
    await apiRequest("listings", { method: "POST", body: listing });
    closeListingModal();
    await loadDashboard();
    changeView("listings");
    showToast("Your listing has been published.");
  } catch (error) {
    console.error("Unable to publish listing.", error);
    showToast(error.message);
  }
});

document.querySelectorAll("#listing-filters .filter-tab").forEach((button) => button.addEventListener("click", () => {
  listingFilter = button.dataset.filter;
  document.querySelectorAll("#listing-filters .filter-tab").forEach((tab) => tab.classList.toggle("active", tab === button));
  renderListings();
}));
document.querySelectorAll("#order-filters .filter-tab").forEach((button) => button.addEventListener("click", () => {
  orderFilter = button.dataset.filter;
  document.querySelectorAll("#order-filters .filter-tab").forEach((tab) => tab.classList.toggle("active", tab === button));
  renderOrders();
}));

document.getElementById("all-orders").addEventListener("click", async (event) => {
  const button = event.target.closest("[data-order-action]");
  if (!button) return;
  const order = orders.find((item) => item.id === Number(button.dataset.orderAction));
  if (!order) return;
  const status = order.status === "To process" ? "Shipped" : order.status === "Shipped" ? "Completed" : null;
  if (status) {
    try {
      await apiRequest(`orders&id=${order.id}`, { method: "PATCH", body: { status } });
      await loadDashboard();
      showToast(`Order ${order.reference} updated: ${status.toLowerCase()}.`);
    } catch (error) {
      console.error("Unable to update order.", error);
      showToast(error.message);
    }
    return;
  }
  if (window.confirm(`Delete order ${order.reference}?`)) {
    try {
      await apiRequest(`orders&id=${order.id}`, { method: "DELETE" });
      await loadDashboard();
      showToast("Order deleted.");
    } catch (error) {
      showToast(error.message);
    }
  }
});

document.getElementById("global-search").addEventListener("input", (event) => {
  searchTerm = event.target.value;
  renderListings();
  renderOrders();
});
document.getElementById("mobile-menu").addEventListener("click", () => document.getElementById("sidebar").classList.toggle("open"));
document.getElementById("add-category-button").addEventListener("click", addCategory);
document.getElementById("listing-sort").addEventListener("click", (event) => {
  oldestFirst = !oldestFirst;
  event.currentTarget.innerHTML = `Sort: ${oldestFirst ? "oldest" : "newest"} <span>⌄</span>`;
  renderListings();
});
document.getElementById("export-orders").addEventListener("click", () => {
  const rows = [["Order", "Item", "Buyer", "Date", "Amount", "Status"], ...orders.map((order) => [order.id, order.item, order.buyer, order.date, order.amount, order.status])];
  const csv = rows.map((row) => row.map((value) => `"${String(value).replace(/"/g, '""')}"`).join(",")).join("\r\n");
  const link = document.createElement("a");
  link.href = URL.createObjectURL(new Blob(["\uFEFF" + csv], { type: "text/csv;charset=utf-8" }));
  link.download = "atelier-market-orders.csv";
  link.click();
  URL.revokeObjectURL(link.href);
  showToast("Orders exported.");
});

const today = new Date();
document.getElementById("current-date").textContent = new Intl.DateTimeFormat("en-GB", { weekday: "long", day: "numeric", month: "long", year: "numeric" }).format(today).toLocaleUpperCase("en");
loadDashboard();
