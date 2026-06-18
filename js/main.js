// ================================================
// SS CRACKERS - MAIN JAVASCRIPT
// Cart System + Checkout Flow
// ================================================

// ---- CONFIG ----
const PHONE = "919876543210";
let currentCategory = 'all';
let PRODUCTS = [];

// ---- CART STATE ----
// cartItems: Map<productId, { product, qty }>
let cartItems = new Map();
let confirmedOrder = null; // holds last confirmed order data

// ================================================
// INIT
// ================================================
document.addEventListener('DOMContentLoaded', () => {
  initParticles();
  
  fetch('api/get_products.php')
    .then(res => res.json())
    .then(data => {
      PRODUCTS = data;
      renderProducts('all');
      renderPriceTable();
    })
    .catch(err => console.error("Error loading products:", err));

  startCountdown();
  startCounterAnimation();
  setupScrollEvents();
  setupSmoothNav();
  setupSearchInput();
  initRevealAnimations();
  updateCartUI();
  initHeroSlider();
});

// ================================================
// HERO SLIDER
// ================================================
let currentHeroSlide = 0;
let heroSlideInterval;

function initHeroSlider() {
  const slides = document.querySelectorAll('.hero-slide');
  if (slides.length <= 1) return;
  startHeroSlideInterval();
}

function showHeroSlide(index) {
  const slides = document.querySelectorAll('.hero-slide');
  const dots = document.querySelectorAll('.hero-dot');
  if (slides.length === 0) return;
  
  slides.forEach(s => s.classList.remove('active'));
  dots.forEach(d => d.classList.remove('active'));
  
  currentHeroSlide = (index + slides.length) % slides.length;
  slides[currentHeroSlide].classList.add('active');
  if (dots[currentHeroSlide]) dots[currentHeroSlide].classList.add('active');
}

function nextHeroSlide() { showHeroSlide(currentHeroSlide + 1); resetHeroSlideInterval(); }
function prevHeroSlide() { showHeroSlide(currentHeroSlide - 1); resetHeroSlideInterval(); }
function goToHeroSlide(index) { showHeroSlide(index); resetHeroSlideInterval(); }

function startHeroSlideInterval() {
  heroSlideInterval = setInterval(() => { showHeroSlide(currentHeroSlide + 1); }, 5000);
}
function resetHeroSlideInterval() {
  clearInterval(heroSlideInterval);
  startHeroSlideInterval();
}

// ================================================
// PARTICLES
// ================================================
function initParticles() {
  const container = document.getElementById('particles');
  if (!container) return;
  const colors = ['#FF4500', '#FF7A00', '#FFD600', '#FF9A00', '#FF6B35'];
  for (let i = 0; i < 18; i++) {
    const p = document.createElement('div');
    p.className = 'particle';
    const size = Math.random() * 10 + 5;
    const color = colors[Math.floor(Math.random() * colors.length)];
    p.style.cssText = `
      width:${size}px; height:${size}px;
      background:${color};
      left:${Math.random() * 100}%;
      top:${Math.random() * 100}%;
      animation-delay:${Math.random() * 4}s;
      animation-duration:${Math.random() * 3 + 3}s;
      opacity:${Math.random() * 0.4 + 0.1};
    `;
    container.appendChild(p);
  }
}

// ================================================
// CATEGORY ICON MAP (no emojis)
// ================================================
function getCategoryIcon(cat) {
  const icons = {
    bijili:  'fa-bolt',
    bombs:   'fa-bomb',
    flower:  'fa-fire',
    chakkra: 'fa-circle-notch',
    rocket:  'fa-arrow-up',
    aerial:  'fa-rocket',
    fancy:   'fa-magic',
    sparkler:'fa-star'
  };
  return icons[cat] || 'fa-box';
};

// ================================================
// RENDER PRODUCTS GRID
// ================================================
function renderProducts(cat) {
  currentCategory = cat;
  const grid = document.getElementById('productsGrid');
  if (!grid) return;

  const filtered = cat === 'all' ? PRODUCTS : PRODUCTS.filter(p => p.category === cat);
  const shown = filtered.slice(0, 24);

  grid.innerHTML = '';
  shown.forEach(p => {
    const discount = Math.round(((p.mrp - p.price) / p.mrp) * 100);
    const inCart = cartItems.has(p.id);
    const iconClass = getCategoryIcon(p.category);
    const imgHtml = p.image_url 
      ? `<img src="${p.image_url}" alt="${p.name}" style="width:100%; height:100%; object-fit:cover; border-radius:8px;">`
      : `<i class="fas ${iconClass}" style="font-size:3rem;color:var(--primary);opacity:0.75;"></i>`;
    const card = document.createElement('div');
    card.className = 'product-card reveal';
    card.id = `prod-card-${p.id}`;
    card.innerHTML = `
      <div class="product-badge">${discount}% OFF</div>
      <div class="product-img-wrap">
        ${imgHtml}
      </div>
      <div class="product-body">
        <div class="product-name">${p.name}</div>
        <div class="product-pricing">
          <span class="product-price">₹${p.price.toLocaleString('en-IN')}</span>
          <span class="product-mrp">₹${p.mrp.toLocaleString('en-IN')}</span>
          <span class="product-discount">${discount}% OFF</span>
        </div>
        <div class="product-actions" style="display: flex; flex-direction: column; gap: 10px;">
          <div class="qty-control-sm" style="width: 100%; justify-content: space-between; border-color: rgba(212,175,55,0.3);">
            <button type="button" onclick="const q=document.getElementById('grid-qty-${p.id}'); if(q.value>1) q.value--">-</button>
            <input type="number" id="grid-qty-${p.id}" value="1" min="1" readonly style="background:transparent; color:var(--text-dark);">
            <button type="button" onclick="document.getElementById('grid-qty-${p.id}').value++">+</button>
          </div>
          <button class="btn-add-cart ${inCart ? 'added' : ''}" style="width: 100%; justify-content: center;" id="add-btn-${p.id}" onclick="addToCart(${p.id}, this, null, parseInt(document.getElementById('grid-qty-${p.id}').value, 10) || 1)">
            <i class="fas ${inCart ? 'fa-check' : 'fa-plus'}"></i>
            ${inCart ? 'Added to Cart' : 'Add to Cart'}
          </button>
        </div>
      </div>
    `;
    grid.appendChild(card);
  });

  setTimeout(() => {
    grid.querySelectorAll('.reveal').forEach(el => el.classList.add('visible'));
  }, 50);
}

function escapeStr(str) {
  return str.replace(/'/g, "\\'");
}

// ================================================
// FILTER CATEGORY
// ================================================
function filterCategory(cat) {
  currentCategory = cat;
  document.querySelectorAll('.filter-btn').forEach(btn => btn.classList.remove('active'));
  const activeBtn = document.getElementById('filter-' + cat);
  if (activeBtn) activeBtn.classList.add('active');
  renderProducts(cat);
  const productsSection = document.getElementById('products');
  if (productsSection) {
    productsSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
  }
}

// ================================================
// RENDER PRICE TABLE
// ================================================
function renderPriceTable(data) {
  const tbody = document.getElementById('priceTableBody');
  if (!tbody) return;
  const items = data || PRODUCTS;
  tbody.innerHTML = '';
  items.forEach((p, i) => {
    const discount = Math.round(((p.mrp - p.price) / p.mrp) * 100);
    const inCart = cartItems.has(p.id);
    
    const iconClass = getCategoryIcon(p.category);
    const imgHtml = p.image_url 
      ? `<img src="${p.image_url}" alt="${p.name}" style="width:36px; height:36px; object-fit:cover; border-radius:6px; box-shadow:0 2px 4px rgba(0,0,0,0.1);">`
      : `<i class="fas ${iconClass}" style="font-size:20px;color:var(--emerald);opacity:0.8;"></i>`;

    const tr = document.createElement('tr');
    tr.id = `table-row-${p.id}`;
    tr.className = 'reveal';
    tr.innerHTML = `
      <td>${i + 1}</td>
      <td style="text-align:center;">${imgHtml}</td>
      <td style="text-align: center; font-weight:700; color:var(--text-dark);">${p.name}</td>
      <td style="text-align: center; text-decoration:line-through; color:var(--text-light);">₹${p.mrp.toLocaleString('en-IN')}</td>
      <td style="text-align: center; font-weight:800; color:#D4AF37; font-size:1.05rem;">₹${p.price.toLocaleString('en-IN')}</td>
      <td style="text-align: center;">
        <div class="qty-control-sm" style="margin: 0 auto;">
          <button type="button" onclick="const q=document.getElementById('table-qty-${p.id}'); if(q.value>1) q.value--">-</button>
          <input type="number" id="table-qty-${p.id}" value="1" min="1">
          <button type="button" onclick="document.getElementById('table-qty-${p.id}').value++">+</button>
        </div>
      </td>
      <td style="text-align: center;">
        <button class="table-add-cart-btn ${inCart ? 'added' : ''}" id="table-btn-${p.id}" onclick="addToCart(${p.id}, null, this, parseInt(document.getElementById('table-qty-${p.id}').value, 10) || 1)">
          <i class="fas ${inCart ? 'fa-check' : 'fa-plus'}"></i>
          ${inCart ? 'Added' : 'Add to Cart'}
        </button>
      </td>
    `;
    tbody.appendChild(tr);
  });
  
  setTimeout(() => {
    tbody.querySelectorAll('.reveal').forEach((el, index) => {
      setTimeout(() => el.classList.add('visible'), index * 15);
    });
  }, 50);
}

// ================================================
// SEARCH
// ================================================
function setupSearchInput() {
  const input = document.getElementById('searchInput');
  if (!input) return;
  input.addEventListener('keydown', (e) => { if (e.key === 'Enter') doSearch(); });
  input.addEventListener('input', debounce(doSearch, 300));
}

function doSearch() {
  const q = (document.getElementById('searchInput')?.value || '').toLowerCase().trim();
  if (!q) {
    renderProducts('all');
    renderPriceTable(PRODUCTS);
    return;
  }
  const results = PRODUCTS.filter(p =>
    p.name.toLowerCase().includes(q) || p.category.toLowerCase().includes(q)
  );
  renderProducts_filtered(results);
  renderPriceTable(results);
  document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
  const sec = document.getElementById('products');
  if (sec) sec.scrollIntoView({ behavior: 'smooth', block: 'start' });
}

function renderProducts_filtered(filtered) {
  const grid = document.getElementById('productsGrid');
  if (!grid) return;
  const shown = filtered.slice(0, 24);
  grid.innerHTML = '';
  if (shown.length === 0) {
    grid.innerHTML = `<div style="grid-column:1/-1;text-align:center;padding:40px;color:var(--text-light);">
      <i class="fas fa-search" style="font-size:3rem;margin-bottom:15px;display:block;opacity:0.3"></i>
      <p>No products found. Try a different search term.</p>
    </div>`;
    return;
  }
  shown.forEach(p => {
    const discount = Math.round(((p.mrp - p.price) / p.mrp) * 100);
    const inCart = cartItems.has(p.id);
    const iconClass = getCategoryIcon(p.category);
    const imgHtml = p.image_url 
      ? `<img src="${p.image_url}" alt="${p.name}" style="width:100%; height:100%; object-fit:cover; border-radius:8px;">`
      : `<i class="fas ${iconClass}" style="font-size:3rem;color:var(--primary);opacity:0.75;"></i>`;
    const card = document.createElement('div');
    card.className = 'product-card reveal';
    card.id = `prod-card-${p.id}`;
    card.innerHTML = `
      <div class="product-badge">${discount}% OFF</div>
      <div class="product-img-wrap">
        ${imgHtml}
      </div>
      <div class="product-body">
        <div class="product-name">${p.name}</div>
        <div class="product-pricing">
          <span class="product-price">₹${p.price.toLocaleString('en-IN')}</span>
          <span class="product-mrp">₹${p.mrp.toLocaleString('en-IN')}</span>
          <span class="product-discount">${discount}% OFF</span>
        </div>
        <div class="product-actions">
          <button class="btn-add-cart ${inCart ? 'added' : ''}" id="add-btn-${p.id}" onclick="addToCart(${p.id}, this)">
            <i class="fas ${inCart ? 'fa-check' : 'fa-plus'}"></i>
            ${inCart ? 'Added to Cart' : 'Add to Cart'}
          </button>
        </div>
      </div>
    `;
    grid.appendChild(card);
  });
  setTimeout(() => {
    grid.querySelectorAll('.reveal').forEach(el => el.classList.add('visible'));
  }, 50);
}

// ================================================
// CART — CORE LOGIC
// ================================================
function addToCart(productId, cardBtn, tableBtn, qtyToAdd = 1) {
  const p = PRODUCTS.find(x => x.id === productId);
  if (!p) return;

  if (cartItems.has(productId)) {
    // Already in cart — increment qty
    cartItems.get(productId).qty += qtyToAdd;
  } else {
    cartItems.set(productId, { product: p, qty: qtyToAdd });
  }

  // Update card button
  const cb = cardBtn || document.getElementById(`add-btn-${productId}`);
  if (cb) {
    cb.classList.add('added');
    cb.innerHTML = `<i class="fas fa-check"></i> Added to Cart`;
  }

  // Update table button
  const tb = tableBtn || document.getElementById(`table-btn-${productId}`);
  if (tb) {
    tb.classList.add('added');
    tb.innerHTML = `<i class="fas fa-check"></i> Added`;
  }

  updateCartUI();
  showToast(`${qtyToAdd}x ${p.name} added to cart`);
}

function removeFromCart(productId) {
  cartItems.delete(productId);

  // Reset card button
  const cb = document.getElementById(`add-btn-${productId}`);
  if (cb) {
    cb.classList.remove('added');
    cb.innerHTML = `<i class="fas fa-plus"></i> Add to Cart`;
  }
  // Reset table button
  const tb = document.getElementById(`table-btn-${productId}`);
  if (tb) {
    tb.classList.remove('added');
    tb.innerHTML = `<i class="fas fa-plus"></i> Add to Cart`;
  }

  updateCartUI();
  renderCartBody();
}

function changeCartQty(productId, delta) {
  if (!cartItems.has(productId)) return;
  const entry = cartItems.get(productId);
  entry.qty = Math.max(1, entry.qty + delta);
  updateCartUI();
  renderCartBody();
}

function clearCart() {
  cartItems.forEach((_, id) => {
    const cb = document.getElementById(`add-btn-${id}`);
    if (cb) { cb.classList.remove('added'); cb.innerHTML = `<i class="fas fa-plus"></i> Add to Cart`; }
    const tb = document.getElementById(`table-btn-${id}`);
    if (tb) { tb.classList.remove('added'); tb.innerHTML = `<i class="fas fa-plus"></i> Add to Cart`; }
  });
  cartItems.clear();
  updateCartUI();
  renderCartBody();
}

function getCartTotal() {
  let total = 0;
  cartItems.forEach(({ product, qty }) => { total += product.price * qty; });
  return total;
}

function getCartCount() {
  let count = 0;
  cartItems.forEach(({ qty }) => { count += qty; });
  return count;
}

// ================================================
// CART — UI UPDATE
// ================================================
function updateCartUI() {
  const count = getCartCount();
  const total = getCartTotal();
  const badge = document.getElementById('cartCountBadge');
  const cartFooter = document.getElementById('cartFooter');
  const cartTotalAmt = document.getElementById('cartTotalAmt');
  const cartItemCount = document.getElementById('cartItemCount');

  if (badge) {
    badge.textContent = count;
    badge.style.display = count > 0 ? 'flex' : 'none';
  }
  if (cartItemCount) {
    cartItemCount.textContent = `${count} item${count !== 1 ? 's' : ''}`;
  }
  if (cartTotalAmt) {
    cartTotalAmt.textContent = `₹${total.toLocaleString('en-IN')}`;
  }
  if (cartFooter) {
    cartFooter.style.display = count > 0 ? 'block' : 'none';
  }

  // Bottom Cart Summary Logic
  const bcs = document.getElementById('bottomCartSummary');
  if (bcs) {
    if (count > 0) {
      document.getElementById('bcsCount').textContent = count + (count === 1 ? ' Item' : ' Items');
      document.getElementById('bcsTotal').textContent = '₹' + total.toLocaleString('en-IN');
      bcs.classList.add('visible');
    } else {
      bcs.classList.remove('visible');
    }
  }
}

function renderCartBody() {
  const body = document.getElementById('cartBody');
  if (!body) return;

  if (cartItems.size === 0) {
    body.innerHTML = `
      <div class="cart-empty" style="text-align: center; display: flex; flex-direction: column; align-items: center; justify-content: center; height: 100%; min-height: 300px; gap: 15px;">
        <i class="fas fa-shopping-basket" style="font-size: 3rem; color: var(--gold); opacity: 0.5;"></i>
        <p style="color: var(--text-light); font-size: 1.1rem;">Your cart is empty.<br>Add crackers to get started!</p>
        <button class="btn-outline-warm" onclick="closeCart()" style="margin-top: 5px;">
          Browse Products
        </button>
      </div>`;
    return;
  }

  body.innerHTML = '';
  cartItems.forEach(({ product: p, qty }) => {
    const iconClass = getCategoryIcon(p.category);
    const item = document.createElement('div');
    item.className = 'cart-item';
    item.innerHTML = `
      <div class="cart-item-icon">
        <i class="fas ${iconClass}"></i>
      </div>
      <div class="cart-item-info">
        <div class="cart-item-name" title="${p.name}">${p.name}</div>
        <div class="cart-item-unit-price">₹${p.price.toLocaleString('en-IN')} per unit</div>
        <div class="cart-item-price">₹${(p.price * qty).toLocaleString('en-IN')}</div>
      </div>
      <div style="display: flex; align-items: center; gap: 12px;">
        <div class="cart-qty-ctrl">
          <button onclick="changeCartQty(${p.id}, -1)">−</button>
          <div class="cart-qty-val">${qty}</div>
          <button onclick="changeCartQty(${p.id}, +1)">+</button>
        </div>
        <button class="cart-remove-btn" onclick="removeFromCart(${p.id})" title="Remove">
          <i class="fas fa-trash-alt"></i>
        </button>
      </div>
    `;
    body.appendChild(item);
  });
}

// ================================================
// CART — OPEN / CLOSE
// ================================================
function openCart() {
  renderCartBody();
  document.getElementById('cartSidebar').classList.add('open');
  document.getElementById('cartOverlay').classList.add('active');
  document.body.style.overflow = 'hidden';
}

function closeCart() {
  document.getElementById('cartSidebar').classList.remove('open');
  document.getElementById('cartOverlay').classList.remove('active');
  document.body.style.overflow = '';
}

// ================================================
// CHECKOUT FLOW
// ================================================
function openCheckout() {
  if (cartItems.size === 0) {
    showToast('Your cart is empty!');
    return;
  }
  closeCart();
  showStep(1);
  document.getElementById('checkoutOverlay').classList.add('active');
  document.body.style.overflow = 'hidden';
}

function closeCheckout() {
  document.getElementById('checkoutOverlay').classList.remove('active');
  document.body.style.overflow = '';
}

function showStep(n) {
  [1, 2, 3].forEach(i => {
    const el = document.getElementById(`checkoutStep${i}`);
    const ind = document.getElementById(`step-ind-${i}`);
    if (el) el.style.display = (i === n) ? 'block' : 'none';
    if (ind) {
      ind.classList.remove('active', 'done');
      if (i < n) ind.classList.add('done');
      if (i === n) ind.classList.add('active');
    }
  });
  
  [1, 2].forEach(i => {
    const line = document.getElementById(`step-line-${i}`);
    if (line) {
      if (n > i) {
        line.classList.add('active');
      } else {
        line.classList.remove('active');
      }
    }
  });
}

function goToStep2(e) {
  e.preventDefault();
  // Build customer info display
  const name    = document.getElementById('co_name').value.trim();
  const phone   = document.getElementById('co_phone').value.trim();
  const email   = document.getElementById('co_email').value.trim();
  const address = document.getElementById('co_address').value.trim();
  const city    = document.getElementById('co_city').value.trim();
  const pin     = document.getElementById('co_pin').value.trim();

  // Customer info block
  const infoEl = document.getElementById('orderCustomerInfo');
  infoEl.innerHTML = `
    <p><strong>Name:</strong> ${name}</p>
    <p><strong>Phone:</strong> ${phone}</p>
    ${email ? `<p><strong>Email:</strong> ${email}</p>` : ''}
    <p><strong>Address:</strong> ${address}, ${city} – ${pin}</p>
  `;

  // Items table
  let rows = '';
  let grandTotal = 0;
  let sno = 1;
  cartItems.forEach(({ product: p, qty }) => {
    const lineTotal = p.price * qty;
    grandTotal += lineTotal;
    
    const iconClass = getCategoryIcon(p.category);
    const imgHtml = p.image_url 
      ? `<img src="${p.image_url}" alt="${p.name}" style="width:40px; height:40px; object-fit:cover; border-radius:6px; box-shadow:0 2px 4px rgba(0,0,0,0.1);">`
      : `<i class="fas ${iconClass}" style="font-size:24px;color:var(--primary);opacity:0.8;"></i>`;

    rows += `
      <tr>
        <td>${sno++}</td>
        <td style="text-align:center;">${imgHtml}</td>
        <td>${p.name}</td>
        <td>₹${p.price.toLocaleString('en-IN')}</td>
        <td>${qty}</td>
        <td><strong>₹${lineTotal.toLocaleString('en-IN')}</strong></td>
      </tr>`;
  });

  document.getElementById('orderItemsTable').innerHTML = `
    <table>
      <thead>
        <tr>
          <th>#</th>
          <th style="text-align:center;">Image</th>
          <th>Product</th>
          <th>Price</th>
          <th>Qty</th>
          <th>Total</th>
        </tr>
      </thead>
      <tbody>${rows}</tbody>
    </table>`;

  document.getElementById('orderGrandTotal').innerHTML = `
    <span>Grand Total</span>
    <span class="grand-amt">₹${grandTotal.toLocaleString('en-IN')}</span>`;

  showStep(2);
}

function goBackStep1() {
  showStep(1);
}

function confirmOrder() {
  const name    = document.getElementById('co_name').value.trim();
  const phone   = document.getElementById('co_phone').value.trim();
  const email   = document.getElementById('co_email').value.trim();
  const address = document.getElementById('co_address').value.trim();
  const city    = document.getElementById('co_city').value.trim();
  const pin     = document.getElementById('co_pin').value.trim();

  // Save order snapshot for download
  confirmedOrder = {
    customer: { name, phone, email, address, city, pin },
    items: Array.from(cartItems.values()).map(({ product: p, qty }) => ({
      id: p.id, name: p.name, category: p.category, image_url: p.image_url,
      price: p.price, mrp: p.mrp, qty
    })),
    total: getCartTotal(),
    date: new Date().toLocaleString('en-IN')
  };

  // Send to backend
  fetch('api/place_order.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(confirmedOrder)
  })
  .then(res => res.json())
  .then(data => {
    if(data.success) {
      console.log("Order saved to DB: ", data.order_number);
      confirmedOrder.order_number = data.order_number; // Save it so the download can use it
    } else {
      console.error("Order save failed: ", data.message);
    }
  })
  .catch(err => console.error("Error connecting to backend: ", err));

  document.getElementById('thankyouMsg').textContent =
    `Thank you, ${name}! Your order has been placed successfully. Our team will call you on ${phone} to confirm the delivery details.`;

  showStep(3);
}

// ================================================
// DOWNLOAD CRACKER LIST
// ================================================
function downloadCrackerList() {
  if (!confirmedOrder) return;
  const { customer, items, total, date } = confirmedOrder;

  let itemRows = '';
  items.forEach((item, i) => {
    const iconFA = getCategoryIcon(item.category);
    const lineTotal = item.price * item.qty;
    itemRows += `
      <tr>
        <td style="text-align:center;font-weight:700;color:#006838;">${i + 1}</td>
        <td>
          <div style="display:flex;align-items:center;gap:12px;">
            <div style="width:44px;height:44px;background:linear-gradient(135deg,#F4F9F6,#D4AF37);border-radius:8px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
              <i class="fas ${iconFA}" style="color:#006838;font-size:1.2rem;"></i>
            </div>
            <span style="font-weight:600;color:#002814;">${item.name}</span>
          </div>
        </td>
        <td style="text-align:center;color:#557A68;text-decoration:line-through;">₹${item.mrp.toLocaleString('en-IN')}</td>
        <td style="text-align:center;color:#006838;font-weight:700;">₹${item.price.toLocaleString('en-IN')}</td>
        <td style="text-align:center;font-weight:700;color:#002814;">${item.qty}</td>
        <td style="text-align:right;font-weight:800;color:#006838;">₹${lineTotal.toLocaleString('en-IN')}</td>
      </tr>`;
  });

  const html = `<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>SS Crackers – Order List</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: 'Poppins', sans-serif; background: #FFFBF0; color: #002814; padding: 30px 40px; }
    .header-top { display: flex; align-items: center; justify-content: space-between; margin-bottom: 28px; padding-bottom: 20px; border-bottom: 3px solid #006838; }
    .brand { display: flex; align-items: center; gap: 14px; }
    .brand-name { font-size: 1.6rem; font-weight: 800; color: #006838; }
    .brand-sub { font-size: 0.78rem; color: #557A68; font-weight: 500; }
    .doc-title { text-align: right; }
    .doc-title h2 { font-size: 1.3rem; font-weight: 800; color: #006838; }
    .doc-title p { font-size: 0.8rem; color: #557A68; margin-top: 3px; }
    .customer-box { background: white; border-left: 4px solid #D4AF37; border-radius: 10px; padding: 16px 20px; margin-bottom: 24px; box-shadow: 0 2px 10px rgba(0,104,56,0.08); }
    .customer-box h3 { font-size: 0.85rem; font-weight: 700; color: #006838; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 10px; }
    .cust-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 6px 20px; }
    .cust-grid p { font-size: 0.85rem; }
    .cust-grid strong { color: #004d28; }
    table { width: 100%; border-collapse: collapse; background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 12px rgba(0,104,56,0.08); margin-bottom: 20px; }
    thead { background: linear-gradient(135deg, #006838, #008744); }
    thead th { padding: 13px 14px; text-align: left; color: white; font-size: 0.82rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; }
    tbody tr { border-bottom: 1px solid #D4AF37; }
    tbody tr:last-child { border-bottom: none; }
    tbody td { padding: 12px 14px; font-size: 0.88rem; }
    tbody tr:nth-child(even) { background: #F4F9F6; }
    .grand-total { background: linear-gradient(135deg, #006838, #008744); border-radius: 12px; padding: 18px 22px; display: flex; justify-content: space-between; align-items: center; color: white; margin-bottom: 20px; }
    .grand-total span { font-size: 1rem; font-weight: 600; }
    .grand-total .amt { font-size: 1.6rem; font-weight: 800; }
    .footer { text-align: center; padding-top: 16px; border-top: 2px solid #D4AF37; font-size: 0.8rem; color: #557A68; }
    @media print {
      body { background: white; padding: 20px; }
      .no-print { display: none !important; }
    }
  </style>
</head>
<body>
  <div class="header-top">
    <div class="brand">
      <div>
        <div class="brand-name">SS Crackers</div>
        <div class="brand-sub">Sivakasi Factory Price | Tamil Nadu</div>
      </div>
    </div>
    <div class="doc-title">
      <h2>Cracker Order List</h2>
      <p>Date: ${date}</p>
    </div>
  </div>

  <div class="customer-box">
    <h3>Customer Details</h3>
    <div class="cust-grid">
      <p><strong>Name:</strong> ${customer.name}</p>
      <p><strong>Phone:</strong> ${customer.phone}</p>
      ${customer.email ? `<p><strong>Email:</strong> ${customer.email}</p>` : '<p></p>'}
      <p><strong>City:</strong> ${customer.city} – ${customer.pin}</p>
      <p style="grid-column:1/-1"><strong>Address:</strong> ${customer.address}</p>
    </div>
  </div>

  <table>
    <thead>
      <tr>
        <th style="width:40px;">#</th>
        <th>Product Name</th>
        <th style="width:100px;">MRP</th>
        <th style="width:100px;">Price</th>
        <th style="width:60px;text-align:center;">Qty</th>
        <th style="width:110px;text-align:right;">Total</th>
      </tr>
    </thead>
    <tbody>
      ${itemRows}
    </tbody>
  </table>

  <div class="grand-total">
    <span>Grand Total (${items.length} product${items.length > 1 ? 's' : ''})</span>
    <span class="amt">₹${total.toLocaleString('en-IN')}</span>
  </div>

  <div class="footer">
    <p>Thank you for choosing SS Crackers! &nbsp;|&nbsp; +91 98765 43210 &nbsp;|&nbsp; sscrackers@gmail.com</p>
    <p style="margin-top:4px;">Sivakasi, Virudhunagar District, Tamil Nadu – 626189</p>
    <p style="margin-top:8px;font-size:0.72rem;color:#cc6600;">Please burst crackers responsibly as per government regulations.</p>
    <div class="no-print" style="margin-top:20px;">
      <button onclick="window.print()" style="background:linear-gradient(135deg,#006838,#008744);color:white;border:none;padding:12px 28px;border-radius:30px;font-size:0.95rem;font-weight:700;cursor:pointer;font-family:'Poppins',sans-serif;">
        <i class="fas fa-print"></i> Print / Save as PDF
      </button>
    </div>
  </div>
</body>
</html>`;

  const blob = new Blob([html], { type: 'text/html' });
  const url = URL.createObjectURL(blob);
  const a = document.createElement('a');
  a.href = url;
  a.download = `SS_Crackers_Order_${customer.name.replace(/\s+/g,'_')}.html`;
  a.click();
  URL.revokeObjectURL(url);
  showToast('Cracker list downloaded!');
}

// ================================================
// MOBILE MENU
// ================================================
function toggleMobileMenu() {
  const nav = document.getElementById('mainNav');
  const burger = document.getElementById('hamburger');
  const overlay = document.getElementById('mobileOverlay');
  nav.classList.toggle('open');
  burger.classList.toggle('active');
  overlay.classList.toggle('active');
  document.body.style.overflow = nav.classList.contains('open') ? 'hidden' : '';
}

// ================================================
// COUNTDOWN TIMER
// ================================================
function startCountdown() {
  const endDate = new Date('2026-10-20T23:59:59');
  function update() {
    const now = new Date();
    const diff = endDate - now;
    if (diff <= 0) {
      ['timerDays','timerHrs','timerMins','timerSecs'].forEach(id => {
        const el = document.getElementById(id);
        if (el) el.textContent = '00';
      });
      return;
    }
    const days = Math.floor(diff / (1000 * 60 * 60 * 24));
    const hrs  = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
    const mins = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
    const secs = Math.floor((diff % (1000 * 60)) / 1000);
    const set = (id, v) => { const el = document.getElementById(id); if(el) el.textContent = String(v).padStart(2,'0'); };
    set('timerDays', days); set('timerHrs', hrs); set('timerMins', mins); set('timerSecs', secs);
  }
  update();
  setInterval(update, 1000);
}

// ================================================
// COUNTER ANIMATION
// ================================================
function startCounterAnimation() {
  const counters = document.querySelectorAll('.stat-num');
  const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) { animateCounter(entry.target); observer.unobserve(entry.target); }
    });
  }, { threshold: 0.5 });
  counters.forEach(c => observer.observe(c));
}

function animateCounter(el) {
  const target = parseInt(el.dataset.target) || 0;
  const duration = 1800;
  const step = target / (duration / 16);
  let current = 0;
  const timer = setInterval(() => {
    current += step;
    if (current >= target) { current = target; clearInterval(timer); }
    el.textContent = Math.floor(current).toLocaleString('en-IN');
  }, 16);
}

// ================================================
// SCROLL EVENTS
// ================================================
let isScrolling = false;
function setupScrollEvents() {
  const backTop = document.getElementById('backToTop');
  const header = document.getElementById('mainHeader');
  window.addEventListener('scroll', () => {
    if (!isScrolling) {
      window.requestAnimationFrame(() => {
        const scrollY = window.scrollY;
        if (backTop) backTop.classList.toggle('visible', scrollY > 400);
        if (header) {
          header.style.boxShadow = scrollY > 10
            ? '0 4px 20px rgba(255,69,0,0.18)'
            : '0 2px 8px rgba(255,69,0,0.10)';
        }
        updateActiveNav();
        isScrolling = false;
      });
      isScrolling = true;
    }
  }, { passive: true });
}

function scrollToTop() {
  window.scrollTo({ top: 0, behavior: 'smooth' });
}

function updateActiveNav() {
  const sections = ['home', 'categories', 'products', 'pricelist', 'offers', 'safety', 'contact'];
  const navLinks = document.querySelectorAll('.nav-link');
  let active = '';
  sections.forEach(id => {
    const el = document.getElementById(id);
    if (el) { const rect = el.getBoundingClientRect(); if (rect.top <= 150) active = id; }
  });
  navLinks.forEach(link => {
    const href = link.getAttribute('href')?.replace('#', '');
    link.classList.toggle('active', href === active);
  });
}

// ================================================
// SMOOTH NAV
// ================================================
function setupSmoothNav() {
  document.querySelectorAll('a[href^="#"]').forEach(link => {
    link.addEventListener('click', function(e) {
      const target = document.querySelector(this.getAttribute('href'));
      if (target) {
        e.preventDefault();
        target.scrollIntoView({ behavior: 'smooth', block: 'start' });
        const nav = document.getElementById('mainNav');
        if (nav.classList.contains('open')) toggleMobileMenu();
      }
    });
  });
}

// ================================================
// SCROLL REVEAL ANIMATIONS
// ================================================
function initRevealAnimations() {
  const revealObserver = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) { entry.target.classList.add('visible'); revealObserver.unobserve(entry.target); }
    });
  }, { threshold: 0.1, rootMargin: '0px 0px -50px 0px' });
  document.querySelectorAll('.cat-card, .why-card, .stat-card, .safety-card').forEach(el => {
    el.classList.add('reveal');
    revealObserver.observe(el);
  });
}

// ================================================
// CONTACT FORM
// ================================================
function submitContactForm(e) {
  e.preventDefault();
  const name    = document.getElementById('fname').value;
  const phone   = document.getElementById('fphone').value;
  const city    = document.getElementById('fcity').value;
  const message = document.getElementById('fmessage').value;
  showToast('Message received! We will contact you shortly.');
  e.target.reset();
}

// ================================================
// PRINT PRICE LIST
// ================================================
function printPriceList() {
  window.print();
}

// ================================================
// TOAST
// ================================================
function showToast(msg, duration = 3000) {
  const toast = document.getElementById('toast');
  if (!toast) return;
  toast.textContent = msg;
  toast.classList.add('show');
  setTimeout(() => toast.classList.remove('show'), duration);
}

// ================================================
// DEBOUNCE
// ================================================
function debounce(fn, delay) {
  let timer;
  return function(...args) {
    clearTimeout(timer);
    timer = setTimeout(() => fn.apply(this, args), delay);
  };
}

// ================================================
// PDF EXPORT
// ================================================
function downloadEstimate() {
  if (!window.html2pdf) {
    showToast('PDF Library not loaded. Please try again.');
    return;
  }
  if (!confirmedOrder) {
    showToast('No active order to download!');
    return;
  }

  const { customer, items, total, date } = confirmedOrder;

  let itemRows = '';
  items.forEach((item, i) => {
    const iconFA = getCategoryIcon(item.category);
    const lineTotal = item.price * item.qty;
    const imgHtml = item.image_url 
      ? `<img src="${item.image_url}" style="width:36px; height:36px; object-fit:cover; border-radius:6px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">`
      : `<i class="fas ${iconFA}" style="color:#006838;font-size:1.2rem;"></i>`;

    itemRows += `
      <tr>
        <td style="padding: 12px 14px; font-size: 0.88rem; text-align:center;font-weight:700;color:#006838; border-bottom: 1px solid #D4AF37;">${i + 1}</td>
        <td style="padding: 12px 14px; font-size: 0.88rem; border-bottom: 1px solid #D4AF37;">
          <div style="display:flex;align-items:center;gap:12px;">
            <div style="width:44px;height:44px;background:linear-gradient(135deg,#F4F9F6,#D4AF37);border-radius:8px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
              ${imgHtml}
            </div>
            <span style="font-weight:600;color:#002814;">${item.name}</span>
          </div>
        </td>
        <td style="padding: 12px 14px; font-size: 0.88rem; text-align:center;color:#557A68;text-decoration:line-through; border-bottom: 1px solid #D4AF37;">₹${item.mrp.toLocaleString('en-IN')}</td>
        <td style="padding: 12px 14px; font-size: 0.88rem; text-align:center;color:#006838;font-weight:700; border-bottom: 1px solid #D4AF37;">₹${item.price.toLocaleString('en-IN')}</td>
        <td style="padding: 12px 14px; font-size: 0.88rem; text-align:center;font-weight:700;color:#002814; border-bottom: 1px solid #D4AF37;">${item.qty}</td>
        <td style="padding: 12px 14px; font-size: 0.88rem; text-align:right;font-weight:800;color:#006838; border-bottom: 1px solid #D4AF37;">₹${lineTotal.toLocaleString('en-IN')}</td>
      </tr>`;
  });

  const html = `
    <div style="font-family: 'Poppins', sans-serif; background: #FFFBF0; color: #002814; padding: 30px 40px; box-sizing: border-box; width: 100%;">
      <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 28px; padding-bottom: 20px; border-bottom: 3px solid #006838;">
        <div style="display: flex; align-items: center; gap: 14px;">
          <div>
            <div style="font-size: 1.6rem; font-weight: 800; color: #006838;">SS Crackers</div>
            <div style="font-size: 0.78rem; color: #557A68; font-weight: 500;">Sivakasi Factory Price | Tamil Nadu</div>
          </div>
        </div>
        <div style="text-align: right;">
          <h2 style="font-size: 1.3rem; font-weight: 800; color: #006838; margin: 0;">Estimate</h2>
          <p style="font-size: 0.8rem; color: #557A68; margin-top: 3px;">Date: ${date}</p>
        </div>
      </div>

      <div style="background: white; border-left: 4px solid #D4AF37; border-radius: 10px; padding: 16px 20px; margin-bottom: 24px; box-shadow: 0 2px 10px rgba(0,104,56,0.08);">
        <h3 style="font-size: 0.85rem; font-weight: 700; color: #006838; text-transform: uppercase; letter-spacing: 1px; margin: 0 0 10px 0;">Customer Details</h3>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 6px 20px;">
          <p style="margin: 0; font-size: 0.85rem;"><strong>Name:</strong> ${customer.name}</p>
          <p style="margin: 0; font-size: 0.85rem;"><strong>Phone:</strong> ${customer.phone}</p>
          ${customer.email ? `<p style="margin: 0; font-size: 0.85rem;"><strong>Email:</strong> ${customer.email}</p>` : ''}
          <p style="margin: 0; font-size: 0.85rem;"><strong>City:</strong> ${customer.city} – ${customer.pin}</p>
          <p style="grid-column: 1/-1; margin: 0; font-size: 0.85rem;"><strong>Address:</strong> ${customer.address}</p>
        </div>
      </div>

      <table style="width: 100%; border-collapse: collapse; background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 12px rgba(0,104,56,0.08); margin-bottom: 20px;">
        <thead style="background: linear-gradient(135deg, #006838, #008744);">
          <tr>
            <th style="padding: 13px 14px; text-align: center; color: white; font-size: 0.82rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; width:40px;">#</th>
            <th style="padding: 13px 14px; text-align: left; color: white; font-size: 0.82rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;">Product Name</th>
            <th style="padding: 13px 14px; text-align: center; color: white; font-size: 0.82rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; width:100px;">MRP</th>
            <th style="padding: 13px 14px; text-align: center; color: white; font-size: 0.82rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; width:100px;">Price</th>
            <th style="padding: 13px 14px; text-align: center; color: white; font-size: 0.82rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; width:60px;">Qty</th>
            <th style="padding: 13px 14px; text-align: right; color: white; font-size: 0.82rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; width:110px;">Total</th>
          </tr>
        </thead>
        <tbody>
          ${itemRows}
        </tbody>
      </table>

      <div style="background: linear-gradient(135deg, #006838, #008744); border-radius: 12px; padding: 18px 22px; display: flex; justify-content: space-between; align-items: center; color: white; margin-bottom: 20px;">
        <span style="font-size: 1rem; font-weight: 600;">Grand Total (${items.length} product${items.length > 1 ? 's' : ''})</span>
        <span style="font-size: 1.6rem; font-weight: 800;">₹${total.toLocaleString('en-IN')}</span>
      </div>

      <div style="text-align: center; padding-top: 16px; border-top: 2px solid #D4AF37; font-size: 0.8rem; color: #557A68;">
        <p style="margin: 0;">Thank you for choosing SS Crackers! &nbsp;|&nbsp; +91 98765 43210 &nbsp;|&nbsp; sscrackers@gmail.com</p>
        <p style="margin: 4px 0 0 0;">Sivakasi, Virudhunagar District, Tamil Nadu – 626189</p>
      </div>
    </div>
  `;

  const container = document.createElement('div');
  container.style.position = 'absolute';
  container.style.left = '-9999px';
  container.style.top = '0';
  container.style.width = '800px';
  container.innerHTML = html;
  
  document.body.appendChild(container);
  
  const opt = {
    margin:       [0.3, 0.3, 0.3, 0.3],
    filename:     'SS_Crackers_Estimate.pdf',
    image:        { type: 'jpeg', quality: 0.98 },
    html2canvas:  { scale: 2, useCORS: true, windowWidth: 800 },
    jsPDF:        { unit: 'in', format: 'letter', orientation: 'portrait' }
  };

  showToast('Generating your PDF... Please wait.', 4000);
  
  html2pdf().set(opt).from(container.firstElementChild).save().then(() => {
    document.body.removeChild(container);
  });
}

// ================================================
// MOBILE MENU
// ================================================
function toggleMobileMenu() {
  const nav = document.getElementById('mainNav');
  const overlay = document.getElementById('mobileOverlay');
  const hamburger = document.getElementById('hamburger');
  
  if (nav) nav.classList.toggle('active');
  if (overlay) overlay.classList.toggle('active');
  if (hamburger) hamburger.classList.toggle('active');
}
