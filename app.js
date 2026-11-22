let listings = [];
let orders = [];
let categories = [];
let currentView = "dashboard";
let listingFilter = "All";
let orderFilter = "All";
let searchTerm = "";
let orderSearchTerm = "";
let orderSort = "newest";
let listingSearchTerm = "";
let listingSort = "newest";
let toastTimer;
let confirmAction = null;
let categorySearchTerm = "";
let categorySort = "name-asc";

function openConfirm(message, action) {
  confirmAction = action;
  document.getElementById("confirm-message").textContent = message;
  document.getElementById("confirm-modal").classList.remove("hidden");
  document.getElementById("confirm-cancel").focus();
}

document.getElementById("confirm-cancel")?.addEventListener("click", () => {
  confirmAction = null;
  document.getElementById("confirm-modal")?.classList.add("hidden");
});
document.getElementById("confirm-delete")?.addEventListener("click", async () => {
  const action = confirmAction;
  confirmAction = null;
  document.getElementById("confirm-modal")?.classList.add("hidden");
  if (action) await action();
});

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
    cache: "no-store",
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

  const needle = categorySearchTerm.trim().toLocaleLowerCase("en");
  const filteredCategories = categories.filter((category) => !needle || [category.name, category.label, category.description].some((value) => String(value).toLocaleLowerCase("en").includes(needle)));
  filteredCategories.sort((first, second) => {
    const firstCount = listings.filter((listing) => listing.category === first.name).length;
    const secondCount = listings.filter((listing) => listing.category === second.name).length;
    if (categorySort === "name-desc") return String(second.label).localeCompare(String(first.label), "en", { sensitivity: "base" });
    if (categorySort === "count-desc") return secondCount - firstCount;
    if (categorySort === "count-asc") return firstCount - secondCount;
    return String(first.label).localeCompare(String(second.label), "en", { sensitivity: "base" });
  });
  const maxCategoryCount = Math.max(1, ...filteredCategories.map((category) => listings.filter((listing) => listing.category === category.name).length));
  document.getElementById("category-cards").innerHTML = filteredCategories.map((category) => {
    const count = listings.filter((listing) => listing.category === category.name).length;
    const percent = Math.max(8, count / maxCategoryCount * 100);
    return `<article class="category-card" data-category-id="${category.id}"><div class="category-card-top"><span class="category-emoji">${escapeHTML(category.icon)}</span><span class="category-card-count"><strong>${count}</strong><small>${count === 1 ? "listing" : "listings"}</small></span></div><h3>${escapeHTML(category.label)}</h3><p>${escapeHTML(category.description)}</p><div class="category-progress"><span style="width:${percent}%"></span></div><div class="category-actions"><button type="button" data-category-edit="${category.id}" class="category-edit-button">Edit</button><button type="button" data-category-delete="${category.id}" class="category-delete-button">Delete</button></div></article>`;
  }).join("") + `<div class="category-add-card"><button id="category-add-card-button"><span>+</span><strong>Add category</strong><small>Create a new catalog section</small></button></div>`;
  const total = document.getElementById("category-total");
  if (total) total.textContent = `${filteredCategories.length} ${filteredCategories.length === 1 ? "category" : "categories"}`;
  document.getElementById("category-add-card-button")?.addEventListener("click", addCategory);
}

function listingRow(listing, index) {
  return `<tr>
    <td><div class="listing-cell"><span class="product-thumb tone-${index % 6}">${escapeHTML(listing.icon || "📦")}</span><span><span class="listing-name">${escapeHTML(listing.title)}</span><span class="listing-meta">${escapeHTML(listing.condition || "Good")}</span></span></div></td>
    <td><span class="seller-cell"><span class="mini-avatar alt-${index % 4}">${escapeHTML(initials(listing.seller))}</span>${escapeHTML(listing.seller)}</span></td>
    <td>${escapeHTML(listing.categoryLabel)}</td><td class="price-cell">${money(listing.price)}</td>
    <td><span class="status ${safeStatusClass(listing.status)}">${escapeHTML(listing.status)}</span></td>
    <td><div class="listing-actions"><button type="button" class="listing-edit-button" data-listing-edit="${listing.id}">Edit</button><button type="button" class="listing-delete-button" data-listing-delete="${listing.id}">Delete</button><button class="row-menu" data-listing-menu="${listing.id}" aria-label="Actions for ${escapeHTML(listing.title)}">···</button></div></td>
  </tr>`;
}

function renderListings() {
  const filtered = listings.filter((listing) => {
    const needle = listingSearchTerm.trim().toLocaleLowerCase("en");
    const matchesListingSearch = !needle || [
      listing.id,
      listing.title,
      listing.seller,
      listing.category,
      listing.categoryLabel,
      listing.price,
      listing.condition,
      listing.status,
      listing.date
    ].some((value) => String(value).toLocaleLowerCase("en").includes(needle));

    return (listingFilter === "All" || listing.status === listingFilter) &&
      matchesListingSearch &&
      matchesSearch([listing.title, listing.seller, listing.categoryLabel, listing.status]);
  });
  filtered.sort((first, second) => {
    if (listingSort === "oldest") return String(first.date).localeCompare(String(second.date));
    if (listingSort === "price-desc") return Number(second.price) - Number(first.price);
    if (listingSort === "price-asc") return Number(first.price) - Number(second.price);
    if (listingSort === "title-asc") return String(first.title).localeCompare(String(second.title), "en", { sensitivity: "base" });
    if (listingSort === "title-desc") return String(second.title).localeCompare(String(first.title), "en", { sensitivity: "base" });
    if (listingSort === "seller-asc") return String(first.seller).localeCompare(String(second.seller), "en", { sensitivity: "base" });
    return String(second.date).localeCompare(String(first.date));
  });
  document.getElementById("all-listings").innerHTML = filtered.map(listingRow).join("");
  const recent = [...listings].sort((a, b) => b.date.localeCompare(a.date)).slice(0, 4);
  document.getElementById("recent-listings").innerHTML = recent.map(listingRow).join("");
  document.getElementById("listing-empty").classList.toggle("hidden", filtered.length > 0);
  const searchCount = document.getElementById("listing-search-count");
  if (searchCount) searchCount.textContent = `${filtered.length} ${filtered.length === 1 ? "result" : "results"}`;
  const clearButton = document.getElementById("listing-search-clear");
  if (clearButton) clearButton.classList.toggle("visible", listingSearchTerm !== "");
  attachListingActions();
}

function renderOrders() {
  const filtered = orders.filter((order) => {
    const needle = orderSearchTerm.trim().toLocaleLowerCase("en");
    const matchesOrderSearch = !needle || [
      order.reference,
      order.id,
      order.item,
      order.buyer,
      order.date,
      order.dateIso,
      order.amount,
      order.status
    ].some((value) => String(value).toLocaleLowerCase("en").includes(needle));

    return (orderFilter === "All" || order.status === orderFilter) &&
      matchesOrderSearch &&
      matchesSearch([order.id, order.item, order.buyer, order.status]);
  });
  filtered.sort((first, second) => {
    if (orderSort === "oldest") return String(first.dateIso).localeCompare(String(second.dateIso));
    if (orderSort === "amount-desc") return Number(second.amount) - Number(first.amount);
    if (orderSort === "amount-asc") return Number(first.amount) - Number(second.amount);
    if (orderSort === "buyer-asc") return String(first.buyer).localeCompare(String(second.buyer), "en", { sensitivity: "base" });
    if (orderSort === "buyer-desc") return String(second.buyer).localeCompare(String(first.buyer), "en", { sensitivity: "base" });
    return String(second.dateIso).localeCompare(String(first.dateIso));
  });

  document.getElementById("all-orders").innerHTML = filtered.map((order, index) => `
    <tr>
      <td><span class="order-reference">#${escapeHTML(order.reference)}</span></td>
      <td><span class="order-article"><span class="product-thumb tone-${index % 6}">${escapeHTML(order.icon || "📦")}</span>${escapeHTML(order.item)}</span></td>
      <td><span class="seller-cell"><span class="mini-avatar alt-${index % 4}">${escapeHTML(initials(order.buyer))}</span>${escapeHTML(order.buyer)}</span></td>
      <td>${escapeHTML(order.date)}</td><td class="price-cell">${money(order.amount)}</td>
      <td><span class="status ${safeStatusClass(order.status)}">${escapeHTML(order.status)}</span></td>
      <td><div class="order-actions"><button type="button" class="order-update-button" data-order-edit="${escapeHTML(order.id)}">Edit</button>${order.status === "Completed" || order.status === "Cancelled" ? "" : `<button type="button" class="order-update-button" data-order-update="${escapeHTML(order.id)}">${order.status === "To process" ? "Ship" : "Complete"}</button><button type="button" class="order-delete-button" data-order-cancel="${escapeHTML(order.id)}">Cancel</button>`}<button type="button" class="order-delete-button" data-order-delete="${escapeHTML(order.id)}">Delete</button></div></td>
    </tr>`).join("");
  document.getElementById("order-empty").classList.toggle("hidden", filtered.length > 0);
  const searchCount = document.getElementById("order-search-count");
  if (searchCount) searchCount.textContent = `${filtered.length} ${filtered.length === 1 ? "result" : "results"}`;
  const clearButton = document.getElementById("order-search-clear");
  if (clearButton) clearButton.classList.toggle("visible", orderSearchTerm !== "");
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
  const addButton = document.getElementById("add-listing-button");
  addButton.classList.toggle("hidden", view === "categories");
  addButton.innerHTML = view === "orders" ? '<span class="plus">+</span> New order' : '<span class="plus">+</span> New listing';
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

function openOrderModal(order = null) {
  const form = document.getElementById("order-form");
  const availableListings = listings.filter((listing) => listing.status === "Available");
  const editableListings = order
    ? [...availableListings, ...listings.filter((listing) => listing.id === order.listingId)].filter((listing, index, all) => all.findIndex((item) => item.id === listing.id) === index)
    : availableListings;
  const listingSelect = form.elements.listingId;
  listingSelect.innerHTML = editableListings.length
    ? editableListings.map((listing) => `<option value="${listing.id}">${escapeHTML(listing.title)} - ${money(listing.price)}</option>`).join("")
    : '<option value="">No available listing</option>';
  form.reset();
  form.elements.orderId.value = order ? String(order.id) : "";
  document.querySelector(".create-order-only").classList.toggle("hidden", Boolean(order));
  document.getElementById("order-modal-title").textContent = order ? "Edit order" : "Create order";
  document.getElementById("order-submit-button").textContent = order ? "Save changes" : "Create order";
  if (order) {
    const matchingListing = [...availableListings, ...listings.filter((listing) => listing.id === order.listingId)].find((listing, index, all) => all.findIndex((item) => item.id === listing.id) === index);
    if (matchingListing) listingSelect.value = String(matchingListing.id);
    form.elements.buyerName.value = order.buyer;
    form.elements.orderStatus.value = order.status;
  } else if (listingSelect.options.length) {
    listingSelect.selectedIndex = 0;
  }
  document.getElementById("order-modal").classList.remove("hidden");
  form.elements.buyerName.focus();
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
  document.querySelectorAll("[data-listing-edit]").forEach((button) => {
    button.addEventListener("click", () => {
      const listing = listings.find((item) => item.id === Number(button.dataset.listingEdit));
      if (listing) openListingEdit(listing);
    });
  });

  document.querySelectorAll("[data-listing-delete]").forEach((button) => {
    button.addEventListener("click", async () => {
      const listing = listings.find((item) => item.id === Number(button.dataset.listingDelete));
      if (!listing) return;
      openConfirm(`Delete "${listing.title}"?`, async () => {
        try {
        await apiRequest(`listings&id=${listing.id}`, { method: "DELETE" });
        await loadDashboard();
          showToast("Listing deleted.");
        } catch (error) {
          showToast(error.message);
        }
      });
    });
  });

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
            openConfirm(`Delete "${listing.title}"?`, async () => {
              await apiRequest(`listings&id=${listing.id}`, { method: "DELETE" });
              await loadDashboard();
              showToast("Listing deleted.");
            });
            return;
          } else {
            const status = action === "Mark as sold" ? "Sold" : action === "Put on hold" ? "Pending" : "Available";
            await apiRequest(`listings&id=${listing.id}`, { method: "PATCH", body: { status } });
          }
          await loadDashboard();
          if (action !== "Delete") showToast("Listing status updated.");
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

function openCategoryModal(category = null) {
  const modal = document.getElementById("category-modal");
  const form = document.getElementById("category-form");
  form.elements.category_id.value = category ? String(category.id) : "";
  form.elements.name.value = category ? category.name : "";
  document.getElementById("category-modal-title").textContent = category ? "Edit category" : "Create category";
  document.querySelector("#category-form button[type='submit']").textContent = category ? "Save changes" : "Add category";
  modal.classList.remove("hidden");
  form.elements.name.focus();
}

function editCategory(category) {
  openCategoryModal(category);
}

async function deleteCategory(category) {
  openConfirm(`Delete the category "${category.name}"?`, async () => {
  try {
    await apiRequest(`categories&id=${category.id}`, { method: "DELETE" });
    await loadDashboard();
    showToast("Category deleted.");
  } catch (error) {
    showToast(error.message);
  }
  });
}

async function addCategory() {
  openCategoryModal();
}

document.querySelectorAll("[data-view]").forEach((button) => button.addEventListener("click", () => changeView(button.dataset.view)));
document.getElementById("add-listing-button").addEventListener("click", () => {
  if (currentView === "orders") openOrderModal();
  else openListingModal();
});
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

document.getElementById("order-form").addEventListener("submit", async (event) => {
  event.preventDefault();
  const formData = new FormData(event.currentTarget);
  const listingId = Number(formData.get("listingId"));
  const buyerName = String(formData.get("buyerName") || "").trim();
  const buyerEmail = String(formData.get("buyerEmail") || "").trim();
  const orderId = String(formData.get("orderId") || "");
  if (!Number.isInteger(listingId) || listingId < 1 || !buyerName || buyerName.length > 100 || (!orderId && (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(buyerEmail) || buyerEmail.length > 120))) {
    showToast("Select an available listing and enter a valid buyer name and email.");
    return;
  }
  try {
    if (orderId) {
      await apiRequest(`orders&id=${orderId}`, { method: "PATCH", body: { listingId, buyerName, status: String(formData.get("orderStatus") || "") } });
      showToast("Order updated.");
    } else {
      await apiRequest("orders", { method: "POST", body: { listingId, buyerName, buyerEmail } });
      showToast("Order created.");
    }
    document.getElementById("order-modal").classList.add("hidden");
    await loadDashboard();
    changeView("orders");
  } catch (error) {
    showToast(error.message);
  }
});

document.querySelectorAll("[data-close-order-modal]").forEach((button) => {
  button.addEventListener("click", () => document.getElementById("order-modal").classList.add("hidden"));
});

document.getElementById("category-search").addEventListener("input", (event) => {
  categorySearchTerm = event.target.value;
  document.getElementById("category-search-clear").classList.toggle("visible", categorySearchTerm !== "");
  renderCategoryRows();
});
document.getElementById("category-sort").addEventListener("change", (event) => {
  categorySort = event.target.value;
  renderCategoryRows();
});

document.getElementById("category-search-clear").addEventListener("click", () => {
  categorySearchTerm = "";
  document.getElementById("category-search").value = "";
  document.getElementById("category-search").focus();
  renderCategoryRows();
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

document.getElementById("category-form").addEventListener("submit", async (event) => {
  event.preventDefault();
  const formData = new FormData(event.currentTarget);
  const categoryId = String(formData.get("category_id") || "");
  const name = String(formData.get("name") || "").trim();
  if (!name || name.length > 60 || /[<>]/.test(name)) {
    showToast("Category name must contain 1 to 60 valid characters.");
    return;
  }
  if (!categoryId && categories.some((category) => category.name.toLocaleLowerCase("en") === name.toLocaleLowerCase("en"))) {
    showToast("This category already exists.");
    return;
  }
  try {
    await apiRequest(categoryId ? `categories&id=${categoryId}` : "categories", {
      method: categoryId ? "PATCH" : "POST",
      body: { name }
    });
    document.getElementById("category-modal").classList.add("hidden");
    await loadDashboard();
    showToast(categoryId ? "Category updated." : "Category added to the catalog.");
  } catch (error) {
    showToast(error.message);
  }
});

document.querySelectorAll("[data-close-category-modal]").forEach((button) => {
  button.addEventListener("click", () => document.getElementById("category-modal").classList.add("hidden"));
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
  if (!listing.title || listing.title.length > 120 || /[<>]/.test(listing.title) || !listing.category || !listing.seller || listing.seller.length > 100 || /[<>]/.test(listing.seller) || !Number.isFinite(listing.price) || listing.price <= 0 || listing.price > 999999.99) {
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

document.getElementById("listing-search").addEventListener("input", (event) => {
  listingSearchTerm = event.target.value;
  renderListings();
});
document.getElementById("listing-search-clear").addEventListener("click", () => {
  listingSearchTerm = "";
  document.getElementById("listing-search").value = "";
  document.getElementById("listing-search").focus();
  renderListings();
});
document.getElementById("listing-sort").addEventListener("change", (event) => {
  listingSort = event.target.value;
  renderListings();
});

document.querySelectorAll("#listing-filters .filter-tab").forEach((button) => button.addEventListener("click", () => {
  listingFilter = button.dataset.filter;
  document.querySelectorAll("#listing-filters .filter-tab").forEach((tab) => tab.classList.toggle("active", tab === button));
  renderListings();
}));
document.getElementById("order-search").addEventListener("input", (event) => {
  orderSearchTerm = event.target.value;
  renderOrders();
});
document.getElementById("order-sort").addEventListener("change", (event) => {
  orderSort = event.target.value;
  renderOrders();
});

document.getElementById("order-search-clear").addEventListener("click", () => {
  orderSearchTerm = "";
  document.getElementById("order-search").value = "";
  document.getElementById("order-search").focus();
  renderOrders();
});

document.querySelectorAll("#order-filters .filter-tab").forEach((button) => button.addEventListener("click", () => {
  orderFilter = button.dataset.filter;
  document.querySelectorAll("#order-filters .filter-tab").forEach((tab) => tab.classList.toggle("active", tab === button));
  renderOrders();
}));

document.getElementById("all-orders").addEventListener("click", async (event) => {
  const editButton = event.target.closest("[data-order-edit]");
  if (editButton) {
    const order = orders.find((item) => item.id === Number(editButton.dataset.orderEdit));
    if (order) openOrderModal(order);
    return;
  }

  const cancelButton = event.target.closest("[data-order-cancel]");
  if (cancelButton) {
    const order = orders.find((item) => item.id === Number(cancelButton.dataset.orderCancel));
    if (!order) return;
    openConfirm(`Cancel order ${order.reference}?`, async () => {
      try {
        await apiRequest(`orders&id=${order.id}`, { method: "PATCH", body: { status: "Cancelled" } });
        await loadDashboard();
        showToast("Order cancelled.");
      } catch (error) {
        showToast(error.message);
      }
    });
    return;
  }

  const deleteButton = event.target.closest("[data-order-delete]");
  if (deleteButton) {
    const order = orders.find((item) => item.id === Number(deleteButton.dataset.orderDelete));
    if (!order) return;
    openConfirm(`Delete order ${order.reference}?`, async () => {
    try {
      await apiRequest(`orders&id=${order.id}`, { method: "DELETE" });
      await loadDashboard();
      showToast("Order deleted.");
    } catch (error) {
      showToast(error.message);
    }
    });
    return;
  }

  const updateButton = event.target.closest("[data-order-update]");
  if (updateButton) {
    const order = orders.find((item) => item.id === Number(updateButton.dataset.orderUpdate));
    if (!order) return;
    const status = order.status === "To process" ? "Shipped" : "Completed";
    try {
      await apiRequest(`orders&id=${order.id}`, { method: "PATCH", body: { status } });
      await loadDashboard();
      showToast(`Order ${order.reference} updated: ${status.toLowerCase()}.`);
    } catch (error) {
      showToast(error.message);
    }
    return;
  }

  /* Compatibility with legacy menu markup. */
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
  openConfirm(`Delete order ${order.reference}?`, async () => {
    try {
      await apiRequest(`orders&id=${order.id}`, { method: "DELETE" });
      await loadDashboard();
      showToast("Order deleted.");
    } catch (error) {
      showToast(error.message);
    }
  });
});

document.getElementById("global-search")?.addEventListener("input", (event) => {
  searchTerm = event.target.value;
  renderListings();
  renderOrders();
});
document.getElementById("mobile-menu")?.addEventListener("click", () => document.getElementById("sidebar")?.classList.toggle("open"));
document.getElementById("add-category-button")?.addEventListener("click", addCategory);
document.getElementById("export-orders")?.addEventListener("click", () => {
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
const currentDateElement = document.getElementById("current-date");
if (currentDateElement) {
  currentDateElement.textContent = new Intl.DateTimeFormat("en-GB", { weekday: "long", day: "numeric", month: "long", year: "numeric" }).format(today).toLocaleUpperCase("en");
}
loadDashboard();
