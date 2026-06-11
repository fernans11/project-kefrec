// =====================================================
// KeFrec - Customer Landing (DB Powered + REAL CHECKOUT)
// =====================================================

function formatIDR(amount) {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0
  }).format(Number(amount || 0));
}

function getCsrfToken() {
  const meta = document.querySelector('meta[name="csrf-token"]');
  return meta ? meta.getAttribute('content') : '';
}

const API_URL = (window.__KEFREC__ && window.__KEFREC__.productsApiUrl)
  ? window.__KEFREC__.productsApiUrl
  : "/api/products";

const CHECKOUT_URL = "/checkout";

const menuGridEl = document.getElementById('menu-grid');
const tabsEl = document.getElementById('menu-tabs');
const mainLayoutEl = document.getElementById('main-layout');

// cart desktop
const cartCountEl = document.getElementById('cart-count');
const cartItemsEl = document.getElementById('cart-items');
const cartTotalEl = document.getElementById('cart-total');

// cart mobile
const mobileCartBarEl = document.getElementById('mobile-cart-bar');
const mobileCartItemsEl = document.getElementById('mobile-cart-items');
const mobileCartPriceEl = document.getElementById('mobile-cart-price');

// checkout & modal elements
const checkoutItemsEl = document.getElementById('checkout-items');
const checkoutSubtotalEl = document.getElementById('checkout-subtotal');
const checkoutTaxEl = document.getElementById('checkout-tax');
const checkoutTotalEl = document.getElementById('checkout-total');

const checkoutOverlayEl = document.getElementById('checkout-overlay');
const qrisModalEl = document.getElementById('qris-modal');
const transferModalEl = document.getElementById('transfer-modal');
const successModalEl = document.getElementById('payment-success-modal');

const confirmPaymentBtn = document.getElementById('btn-confirm-payment');
const cancelCheckoutBtn = document.getElementById('btn-cancel-checkout');
const closeCheckoutBtn = document.getElementById('btn-close-checkout');
const qrisPaidBtn = document.getElementById('btn-qris-paid');
const transferPaidBtn = document.getElementById('btn-transfer-paid');
const closeQrisBtn = document.getElementById('btn-close-qris');
const closeTransferBtn = document.getElementById('btn-close-transfer');
const closeSuccessBtn = document.getElementById('btn-close-success');
const successBackBtn = document.getElementById('btn-success-back');
const successTrackBtn = document.getElementById('btn-success-track');

const qrisTotalTextEl = document.getElementById('qris-total-text');
const transferTotalTextEl = document.getElementById('transfer-total-text');
const successOrderIdEl = document.getElementById('success-order-id');
const successMethodEl = document.getElementById('success-method');
const successTotalEl = document.getElementById('success-total');

const paymentMethodButtons = document.querySelectorAll('.payment-method-btn');

let activeFilter = 'Semua';
let menuItems = []; // <-- from DB
let cart = [];
let selectedPaymentMethod = null;

let currentOrderNumber = '';
let currentOrderTotal = 0;
let currentOrderMethodLabel = '';
let currentOrderId = null;
let isPlacingOrder = false;

async function fetchMenu(category = 'Semua') {
  const url = new URL(API_URL, window.location.origin);
  if (category && category !== 'Semua') {
    url.searchParams.set('category', category);
  }
  const res = await fetch(url.toString(), { headers: { 'Accept': 'application/json' } });
  if (!res.ok) throw new Error('Gagal memuat menu dari server.');
  const json = await res.json();
  menuItems = Array.isArray(json.data) ? json.data : [];
}

// ---------- RENDER MENU ----------
function renderMenu() {
  if (!menuGridEl) return;

  menuGridEl.innerHTML = '';

  if (!menuItems.length) {
    menuGridEl.innerHTML = `
      <div style="padding:1rem;color:#e0e0e0;">
        Menu belum tersedia atau gagal dimuat.
      </div>
    `;
    return;
  }

  menuItems.forEach(item => {
    const card = document.createElement('article');
    card.className = 'menu-card';
    card.dataset.id = item.id;

    const img = item.image_url || 'https://images.unsplash.com/photo-1521017432531-fbd92d768814?auto=format&fit=crop&w=1200&q=80';

    card.innerHTML = `
      <div class="menu-image-wrapper">
        <img src="${img}" alt="${item.name}" class="menu-image">
        <div class="menu-badges"></div>
        <span class="badge badge-category">${item.category || '-'}</span>
        ${item.rating ? `
          <span class="badge badge-rating">
            <span class="star">★</span>
            <span>${Number(item.rating).toFixed(1)}</span>
          </span>` : ''}
      </div>
      <div class="menu-content">
        <h4 class="menu-title">${item.name}</h4>
        <p class="menu-desc">${item.description || ''}</p>
        <div class="menu-footer">
          <span class="menu-price">${formatIDR(item.price)}</span>
          <button class="btn btn-red btn-small btn-add" type="button">+ Tambah</button>
        </div>
      </div>
    `;

    menuGridEl.appendChild(card);
  });
}

// ---------- CART ----------
function getCartTotalItems() {
  return cart.reduce((sum, item) => sum + item.quantity, 0);
}
function getCartTotalPrice() {
  return cart.reduce((sum, item) => sum + item.price * item.quantity, 0);
}

function addToCart(itemId) {
  const menuItem = menuItems.find(m => Number(m.id) === Number(itemId));
  if (!menuItem) return;

  const existing = cart.find(c => Number(c.id) === Number(itemId));
  if (existing) {
    existing.quantity += 1;
  } else {
    cart.push({
      id: menuItem.id,
      name: menuItem.name,
      price: Number(menuItem.price || 0),
      image: menuItem.image_url,
      quantity: 1
    });
  }
  renderCart();
}

function updateCartQuantity(itemId, delta) {
  const item = cart.find(c => Number(c.id) === Number(itemId));
  if (!item) return;
  item.quantity += delta;
  if (item.quantity <= 0) {
    cart = cart.filter(c => Number(c.id) !== Number(itemId));
  }
  renderCart();
}

function removeCartItem(itemId) {
  cart = cart.filter(c => Number(c.id) !== Number(itemId));
  renderCart();
}

// ---------- RENDER CART ----------
function renderCart() {
  const totalItems = getCartTotalItems();
  const totalPrice = getCartTotalPrice();

  if (cartCountEl) cartCountEl.textContent = totalItems;
  if (cartTotalEl) cartTotalEl.textContent = formatIDR(totalPrice);
  if (cartItemsEl) cartItemsEl.innerHTML = '';

  if (!cart.length) {
    if (mainLayoutEl) mainLayoutEl.classList.remove('has-cart');
    if (cartItemsEl) cartItemsEl.innerHTML = '<p class="cart-empty">Keranjang masih kosong.</p>';

    if (mobileCartBarEl) mobileCartBarEl.classList.remove('show');
    if (mobileCartItemsEl) mobileCartItemsEl.textContent = '0 Item';
    if (mobileCartPriceEl) mobileCartPriceEl.textContent = 'Rp 0';
  } else {
    if (mainLayoutEl) mainLayoutEl.classList.add('has-cart');

    if (cartItemsEl) {
      cart.forEach(item => {
        const row = document.createElement('div');
        row.className = 'cart-item';
        row.dataset.id = item.id;

        row.innerHTML = `
          <div class="cart-item-thumb">
            <img src="${item.image || ''}" alt="${item.name}">
          </div>
          <div class="cart-item-main">
            <div class="cart-item-name">${item.name}</div>
            <div class="cart-item-price">${formatIDR(item.price)}</div>
            <div class="cart-item-actions-row">
              <div class="qty-control">
                <button class="qty-btn cart-minus" type="button">−</button>
                <span class="qty-value">${item.quantity}</span>
                <button class="qty-btn cart-plus" type="button">+</button>
              </div>
              <div class="cart-item-total">${formatIDR(item.price * item.quantity)}</div>
            </div>
          </div>
          <button class="cart-remove-btn cart-remove" type="button" title="Hapus">🗑️</button>
        `;

        cartItemsEl.appendChild(row);
      });
    }

    if (mobileCartItemsEl) mobileCartItemsEl.textContent = `${totalItems} Item`;
    if (mobileCartPriceEl) mobileCartPriceEl.textContent = formatIDR(totalPrice);
    if (mobileCartBarEl) mobileCartBarEl.classList.add('show');
  }

  renderCheckoutFromCart();
}

function renderCheckoutFromCart() {
  if (!checkoutItemsEl) return;

  checkoutItemsEl.innerHTML = '';

  if (!cart.length) {
    checkoutItemsEl.innerHTML =
      '<p style="font-size:0.8rem;color:var(--text-muted);">Tidak ada item di keranjang.</p>';
  } else {
    cart.forEach(item => {
      const row = document.createElement('div');
      row.className = 'checkout-item-row';
      row.dataset.id = item.id;

      row.innerHTML = `
        <div class="checkout-item-main">
          <div class="checkout-item-name">${item.name}</div>
          <div class="checkout-item-price">${formatIDR(item.price)} per item</div>
        </div>
        <div class="checkout-item-controls">
          <div class="checkout-qty-control">
            <button class="checkout-qty-btn checkout-minus" type="button">−</button>
            <span class="checkout-qty-value">${item.quantity}</span>
            <button class="checkout-qty-btn checkout-plus" type="button">+</button>
          </div>
          <div class="checkout-item-total">${formatIDR(item.price * item.quantity)}</div>
          <button class="checkout-remove-btn checkout-remove" type="button" title="Hapus">🗑️</button>
        </div>
      `;

      checkoutItemsEl.appendChild(row);
    });
  }

  const subtotal = getCartTotalPrice();
  const tax = Math.floor(subtotal * 0.1);
  const total = subtotal + tax;

  if (checkoutSubtotalEl) checkoutSubtotalEl.textContent = formatIDR(subtotal);
  if (checkoutTaxEl) checkoutTaxEl.textContent = formatIDR(tax);
  if (checkoutTotalEl) checkoutTotalEl.textContent = formatIDR(total);
}

// ---------- EVENTS ----------
if (tabsEl) {
  tabsEl.addEventListener('click', async (event) => {
    const btn = event.target.closest('.tab-btn');
    if (!btn) return;

    activeFilter = btn.dataset.filter;

    [...tabsEl.querySelectorAll('.tab-btn')].forEach(b => b.classList.remove('active'));
    btn.classList.add('active');

    try {
      await fetchMenu(activeFilter);
      renderMenu();
    } catch (e) {
      console.error(e);
      renderMenu();
      alert('Gagal memuat menu. Periksa API /api/products.');
    }
  });
}

if (menuGridEl) {
  menuGridEl.addEventListener('click', (event) => {
    const btn = event.target.closest('.btn-add');
    if (!btn) return;
    const card = btn.closest('.menu-card');
    const id = card ? Number(card.dataset.id) : null;
    if (!id) return;
    addToCart(id);
  });
}

if (cartItemsEl) {
  cartItemsEl.addEventListener('click', (event) => {
    const minusBtn = event.target.closest('.cart-minus');
    const plusBtn = event.target.closest('.cart-plus');
    const removeBtn = event.target.closest('.cart-remove');
    const itemRow = event.target.closest('.cart-item');
    if (!itemRow) return;

    const id = Number(itemRow.dataset.id);

    if (minusBtn) updateCartQuantity(id, -1);
    else if (plusBtn) updateCartQuantity(id, 1);
    else if (removeBtn) removeCartItem(id);
  });
}

if (checkoutItemsEl) {
  checkoutItemsEl.addEventListener('click', (event) => {
    const minusBtn = event.target.closest('.checkout-minus');
    const plusBtn = event.target.closest('.checkout-plus');
    const removeBtn = event.target.closest('.checkout-remove');
    const itemRow = event.target.closest('.checkout-item-row');
    if (!itemRow) return;

    const id = Number(itemRow.dataset.id);

    if (minusBtn) updateCartQuantity(id, -1);
    else if (plusBtn) updateCartQuantity(id, 1);
    else if (removeBtn) removeCartItem(id);
  });
}

// ---------- MODAL HELPERS ----------
function openOverlay(el) { if (el) el.classList.add('is-open'); }
function closeOverlay(el) { if (el) el.classList.remove('is-open'); }

function openCheckout() {
  if (!cart.length) {
    alert('Keranjang masih kosong.');
    return;
  }

  selectedPaymentMethod = null;
  paymentMethodButtons.forEach(b => b.classList.remove('active'));
  if (confirmPaymentBtn) confirmPaymentBtn.disabled = true;

  renderCheckoutFromCart();
  openOverlay(checkoutOverlayEl);
}

const btnCheckout = document.getElementById('btn-checkout');
const btnMobileCheckout = document.getElementById('mobile-cart-checkout');
if (btnCheckout) btnCheckout.addEventListener('click', openCheckout);
if (btnMobileCheckout) btnMobileCheckout.addEventListener('click', openCheckout);

if (cancelCheckoutBtn) cancelCheckoutBtn.addEventListener('click', () => closeOverlay(checkoutOverlayEl));
if (closeCheckoutBtn) closeCheckoutBtn.addEventListener('click', () => closeOverlay(checkoutOverlayEl));

// payment method
paymentMethodButtons.forEach(btn => {
  btn.addEventListener('click', () => {
    if (btn.classList.contains('is-disabled')) {
      return;
    }
    selectedPaymentMethod = btn.dataset.method;
    paymentMethodButtons.forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    if (confirmPaymentBtn) confirmPaymentBtn.disabled = false;
  });
});

async function placeOrder(paymentMethod) {
  const payload = {
    payment_method: paymentMethod,
    items: cart.map(i => ({
      product_id: Number(i.id),
      qty: Number(i.quantity)
    }))
  };

  const res = await fetch(CHECKOUT_URL, {
    method: 'POST',
    headers: {
      'Accept': 'application/json',
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': getCsrfToken(),
    },
    body: JSON.stringify(payload)
  });

  const ct = (res.headers.get('content-type') || '').toLowerCase();

  if (!res.ok) {
    let msg = 'Checkout gagal.';
    try {
      if (ct.includes('application/json')) {
        const j = await res.json();
        msg = j.message || msg;
      } else {
        msg = await res.text();
      }
    } catch (_) {}
    throw new Error(msg);
  }

  if (!ct.includes('application/json')) {
    throw new Error('Kamu harus login terlebih dahulu sebelum checkout.');
  }

  return await res.json();
}

async function syncCurrentPayment() {
  if (!currentOrderId) return null;

  const res = await fetch(`/orders/${currentOrderId}/sync-payment`, {
    method: 'POST',
    headers: {
      'Accept': 'application/json',
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': getCsrfToken(),
    },
    body: JSON.stringify({})
  });

  return res.ok ? await res.json() : null;
}

if (confirmPaymentBtn) {
  confirmPaymentBtn.addEventListener('click', async () => {
    if (!selectedPaymentMethod) return;
    if (!cart.length) return;
    if (isPlacingOrder) return;

    isPlacingOrder = true;
    confirmPaymentBtn.disabled = true;
    confirmPaymentBtn.textContent = 'Memproses...';

    try {
      const methodLabelMap = { cash: 'Tunai', qris: 'QRIS', transfer: 'Transfer Bank' };
      currentOrderMethodLabel = methodLabelMap[selectedPaymentMethod] || 'Tunai';

      const result = await placeOrder(selectedPaymentMethod);

      currentOrderId = result.transaction_id || null;
      currentOrderNumber = result.invoice_no || ('TRX-' + String(result.transaction_id || ''));
      currentOrderTotal = Number(result.total || 0);

      if (successOrderIdEl) successOrderIdEl.textContent = currentOrderNumber;
      if (successMethodEl) successMethodEl.textContent = currentOrderMethodLabel;
      if (successTotalEl) successTotalEl.textContent = formatIDR(currentOrderTotal);

      closeOverlay(checkoutOverlayEl);

      if (selectedPaymentMethod === 'cash') {
        openOverlay(successModalEl);
      } else {
        if (!result.snap_token || !window.snap) {
          throw new Error('Snap Midtrans belum siap. Pastikan client key dan koneksi internet benar.');
        }

        window.snap.pay(result.snap_token, {
          onSuccess: async function () {
            await syncCurrentPayment();
            openOverlay(successModalEl);
          },
          onPending: async function () {
            await syncCurrentPayment();
            alert('Pembayaran masih tertunda. Jika sudah melakukan simulasi pembayaran, buka detail pesanan lalu klik Cek Status Pembayaran.');
            window.location.href = `/orders/${currentOrderId}`;
          },
          onError: function () {
            alert('Pembayaran gagal atau ditolak oleh Midtrans.');
          },
          onClose: function () {
            alert('Pembayaran belum selesai. Anda bisa melanjutkan pembayaran atau membatalkan pesanan dari halaman detail pesanan.');
            window.location.href = `/orders/${currentOrderId}`;
          }
        });
      }
    } catch (e) {
      console.error(e);
      alert(e.message || 'Checkout gagal.');
    } finally {
      isPlacingOrder = false;
      confirmPaymentBtn.disabled = false;
      confirmPaymentBtn.textContent = 'Konfirmasi Pembayaran';
    }
  });
}

if (qrisPaidBtn) qrisPaidBtn.addEventListener('click', () => { closeOverlay(qrisModalEl); openOverlay(successModalEl); });
if (closeQrisBtn) closeQrisBtn.addEventListener('click', () => { closeOverlay(qrisModalEl); openOverlay(checkoutOverlayEl); });

if (transferPaidBtn) transferPaidBtn.addEventListener('click', () => { closeOverlay(transferModalEl); openOverlay(successModalEl); });
if (closeTransferBtn) closeTransferBtn.addEventListener('click', () => { closeOverlay(transferModalEl); openOverlay(checkoutOverlayEl); });

function finishOrderAndReset() {
  cart = [];
  renderCart();
  closeOverlay(successModalEl);
}
if (successBackBtn) successBackBtn.addEventListener('click', finishOrderAndReset);
if (closeSuccessBtn) closeSuccessBtn.addEventListener('click', finishOrderAndReset);
if (successTrackBtn) successTrackBtn.addEventListener('click', () => {
  if (!currentOrderId) {
    finishOrderAndReset();
    return;
  }
  window.location.href = `/orders/${currentOrderId}`;
});

// ---------- PROFILE DROPDOWN (NEW, SAFE) ----------
(function initProfileDropdown() {
  const toggle = document.getElementById('profileToggle');
  const menu = document.getElementById('profileMenu');
  const wrap = document.getElementById('profileWrap');

  if (!toggle || !menu || !wrap) return;

  function open() {
    menu.classList.add('is-open');
    toggle.setAttribute('aria-expanded', 'true');
  }
  function close() {
    menu.classList.remove('is-open');
    toggle.setAttribute('aria-expanded', 'false');
  }
  function isOpen() {
    return menu.classList.contains('is-open');
  }

  toggle.addEventListener('click', (e) => {
    e.preventDefault();
    e.stopPropagation();
    if (isOpen()) close();
    else open();
  });

  document.addEventListener('click', (e) => {
    // klik di luar -> tutup
    if (!wrap.contains(e.target)) close();
  });

  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') close();
  });
})();

// ---------- INIT ----------
(async function init() {
  try {
    await fetchMenu('Semua');
    renderMenu();
  } catch (e) {
    console.error(e);
    renderMenu();
    alert('Gagal memuat menu. Pastikan API /api/products bisa diakses.');
  }
  renderCart();
  renderCheckoutFromCart();
})();
