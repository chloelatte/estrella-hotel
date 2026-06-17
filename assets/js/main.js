/**
 * Estrella Hotel – Main JavaScript
 * Handles: navbar scroll, form validation, DOM manipulation,
 *          booking flow, add-ons, payment, quantity controls
 */

'use strict';

// ─── Navbar scroll behaviour ────────────────────────────────
const navbar = document.getElementById('mainNavbar');
if (navbar) {
    const onScroll = () => {
        navbar.classList.toggle('scrolled', window.scrollY > 40);
    };
    window.addEventListener('scroll', onScroll, { passive: true });
}

// ─── Smooth reveal on scroll ────────────────────────────────
const observerOptions = { threshold: 0.12, rootMargin: '0px 0px -40px 0px' };
const revealObserver = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.classList.add('visible');
            revealObserver.unobserve(entry.target);
        }
    });
}, observerOptions);

document.querySelectorAll('.fade-in-up').forEach(el => revealObserver.observe(el));

// ─── Helper: show inline error ──────────────────────────────
function showFieldError(fieldId, message) {
    const field = document.getElementById(fieldId);
    if (!field) return;
    field.classList.add('is-invalid');
    let feedback = field.parentNode.querySelector('.invalid-feedback');
    if (!feedback) {
        feedback = document.createElement('div');
        feedback.className = 'invalid-feedback';
        field.parentNode.appendChild(feedback);
    }
    feedback.textContent = message;
}

function clearFieldError(fieldId) {
    const field = document.getElementById(fieldId);
    if (!field) return;
    field.classList.remove('is-invalid');
    const feedback = field.parentNode.querySelector('.invalid-feedback');
    if (feedback) feedback.textContent = '';
}

function addLiveValidation(fieldId, validator) {
    const field = document.getElementById(fieldId);
    if (!field) return;
    field.addEventListener('input', () => {
        const result = validator(field.value.trim());
        if (result) showFieldError(fieldId, result);
        else clearFieldError(fieldId);
    });
}

// ─── LOGIN FORM validation ──────────────────────────────────
const loginForm = document.getElementById('loginForm');
if (loginForm) {
    addLiveValidation('username', v => v.length < 3 ? 'Username minimal 3 karakter.' : null);
    addLiveValidation('password', v => v.length < 6 ? 'Password minimal 6 karakter.' : null);

    loginForm.addEventListener('submit', function (e) {
        let valid = true;
        const username = document.getElementById('username')?.value.trim() || '';
        const password = document.getElementById('password')?.value.trim() || '';

        if (username.length < 3) {
            showFieldError('username', 'Username minimal 3 karakter.'); valid = false;
        } else clearFieldError('username');

        if (password.length < 6) {
            showFieldError('password', 'Password minimal 6 karakter.'); valid = false;
        } else clearFieldError('password');

        if (!valid) e.preventDefault();
    });
}

// ─── REGISTER FORM validation ────────────────────────────────
const registerForm = document.getElementById('registerForm');
if (registerForm) {
    addLiveValidation('full_name', v => v.length < 3 ? 'Nama minimal 3 karakter.' : null);
    addLiveValidation('email', v => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v) ? null : 'Format email tidak valid.');
    addLiveValidation('reg_username', v => v.length < 3 ? 'Username minimal 3 karakter.' : null);
    addLiveValidation('reg_password', v => v.length < 6 ? 'Password minimal 6 karakter.' : null);

    registerForm.addEventListener('submit', function (e) {
        let valid = true;

        const fullName = document.getElementById('full_name')?.value.trim() || '';
        const email    = document.getElementById('email')?.value.trim() || '';
        const uname    = document.getElementById('reg_username')?.value.trim() || '';
        const pass     = document.getElementById('reg_password')?.value.trim() || '';
        const confirm  = document.getElementById('confirm_password')?.value.trim() || '';

        if (fullName.length < 3)  { showFieldError('full_name', 'Nama minimal 3 karakter.'); valid = false; }
        else clearFieldError('full_name');

        if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) { showFieldError('email', 'Format email tidak valid.'); valid = false; }
        else clearFieldError('email');

        if (uname.length < 3) { showFieldError('reg_username', 'Username minimal 3 karakter.'); valid = false; }
        else clearFieldError('reg_username');

        if (pass.length < 6) { showFieldError('reg_password', 'Password minimal 6 karakter.'); valid = false; }
        else clearFieldError('reg_password');

        if (pass !== confirm) { showFieldError('confirm_password', 'Password tidak cocok.'); valid = false; }
        else clearFieldError('confirm_password');

        if (!valid) e.preventDefault();
    });
}

// ─── BOOKING DETAILS FORM validation ────────────────────────
const bookingForm = document.getElementById('bookingDetailsForm');
if (bookingForm) {
    addLiveValidation('guest_name',  v => v.length < 3 ? 'Nama minimal 3 karakter.' : null);
    addLiveValidation('guest_email', v => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v) ? null : 'Format email tidak valid.');
    addLiveValidation('guest_phone', v => v.length < 8 ? 'Nomor telepon tidak valid.' : null);
    addLiveValidation('checkin_date', v => v ? null : 'Pilih tanggal check-in.');
    addLiveValidation('checkout_date', v => v ? null : 'Pilih tanggal check-out.');

    bookingForm.addEventListener('submit', function (e) {
        let valid = true;
        const fields = [
            ['guest_name',     v => v.length < 3   ? 'Nama minimal 3 karakter.' : null],
            ['guest_email',    v => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v) ? null : 'Format email tidak valid.'],
            ['guest_phone',    v => v.length < 8   ? 'Nomor telepon tidak valid.' : null],
            ['checkin_date',   v => v ? null : 'Pilih tanggal check-in.'],
            ['checkout_date',  v => v ? null : 'Pilih tanggal check-out.'],
        ];
        fields.forEach(([id, fn]) => {
            const el = document.getElementById(id);
            if (!el) return;
            const err = fn(el.value.trim());
            if (err) { showFieldError(id, err); valid = false; }
            else clearFieldError(id);
        });

        // check-out must be after check-in
        const ci = document.getElementById('checkin_date')?.value;
        const co = document.getElementById('checkout_date')?.value;
        if (ci && co && co <= ci) {
            showFieldError('checkout_date', 'Check-out harus setelah check-in.'); valid = false;
        }

        if (!valid) e.preventDefault();
    });

    // Auto-calculate nights
    ['checkin_date', 'checkout_date'].forEach(id => {
        document.getElementById(id)?.addEventListener('change', updateNightCount);
    });

    function updateNightCount() {
        const ci = document.getElementById('checkin_date')?.value;
        const co = document.getElementById('checkout_date')?.value;
        if (ci && co) {
            const diff = (new Date(co) - new Date(ci)) / 86400000;
            const nightEl = document.getElementById('nightCount');
            if (nightEl) nightEl.textContent = diff > 0 ? diff : 0;
            // trigger summary update if function exists
            if (typeof updateSummary === 'function') updateSummary();
        }
    }
}

// ─── ADD-ONS quantity controls ───────────────────────────────
document.querySelectorAll('.qty-btn').forEach(btn => {
    btn.addEventListener('click', function () {
        const addonId  = this.dataset.addon;
        const action   = this.dataset.action; // 'inc' or 'dec'
        const qtyEl    = document.getElementById('qty_' + addonId);
        const priceEl  = document.getElementById('price_' + addonId);
        if (!qtyEl) return;

        let qty   = parseInt(qtyEl.value || 0);
        const max = parseInt(qtyEl.dataset.max || 10);

        if (action === 'inc' && qty < max) qty++;
        if (action === 'dec' && qty > 0)   qty--;

        qtyEl.value = qty;

        // Update display
        const displayEl = document.getElementById('display_qty_' + addonId);
        if (displayEl) displayEl.textContent = qty;

        updateAddonTotal();
    });
});

function updateAddonTotal() {
    let total = 0;
    document.querySelectorAll('.addon-qty-input').forEach(input => {
        const qty   = parseInt(input.value || 0);
        const price = parseFloat(input.dataset.price || 0);
        const mult  = parseFloat(input.dataset.multiplier || 1); // e.g. num_nights for breakfast
        total += qty * price * mult;
    });

    const addonTotalEl = document.getElementById('addonTotal');
    if (addonTotalEl) addonTotalEl.textContent = formatRupiah(total);

    const addonHiddenEl = document.getElementById('addon_total_hidden');
    if (addonHiddenEl) addonHiddenEl.value = total;

    updateGrandTotal();
}

// ─── GRAND TOTAL calculator ──────────────────────────────────
function updateGrandTotal() {
    const roomPrice = parseFloat(document.getElementById('room_price_val')?.value || 0);
    const nights    = parseInt(document.getElementById('num_nights_val')?.value || 1);
    const rooms     = parseInt(document.getElementById('num_rooms_val')?.value || 1);
    const addonAmt  = parseFloat(document.getElementById('addon_total_hidden')?.value || 0);

    const roomTotal  = roomPrice * nights * rooms;
    const service    = roomTotal * 0.10;
    const tax        = roomTotal * 0.10;
    const grand      = roomTotal + service + tax + addonAmt;

    setIfExists('display_room_total', formatRupiah(roomTotal));
    setIfExists('display_service',    formatRupiah(service));
    setIfExists('display_tax',        formatRupiah(tax));
    setIfExists('display_addon',      formatRupiah(addonAmt));
    setIfExists('display_grand',      formatRupiah(grand));

    const hiddenGrand = document.getElementById('grand_total_hidden');
    if (hiddenGrand) hiddenGrand.value = grand;
}

function setIfExists(id, value) {
    const el = document.getElementById(id);
    if (el) el.textContent = value;
}

function formatRupiah(amount) {
    return 'IDR ' + new Intl.NumberFormat('id-ID').format(Math.round(amount));
}

// ─── PAYMENT method toggle ───────────────────────────────────
document.querySelectorAll('.payment-option').forEach(option => {
    option.addEventListener('click', function () {
        document.querySelectorAll('.payment-option').forEach(o => o.classList.remove('selected'));
        this.classList.add('selected');
        const radio = this.querySelector('input[type="radio"]');
        if (radio) radio.checked = true;

        // Show/hide credit card fields
        const ccFields = document.getElementById('creditCardFields');
        if (ccFields) {
            ccFields.style.display = (this.dataset.method === 'credit_card') ? 'block' : 'none';
        }
    });
});

// ─── PAYMENT FORM validation ──────────────────────────────────
const paymentForm = document.getElementById('paymentForm');
if (paymentForm) {
    paymentForm.addEventListener('submit', function (e) {
        const method = document.querySelector('input[name="payment_method"]:checked')?.value;
        if (!method) {
            e.preventDefault();
            alert('Pilih metode pembayaran terlebih dahulu.');
            return;
        }
        if (method === 'credit_card') {
            let valid = true;
            const cardNum  = document.getElementById('card_number')?.value.replace(/\s/g,'') || '';
            const cardName = document.getElementById('card_name')?.value.trim() || '';
            const expiry   = document.getElementById('card_expiry')?.value.trim() || '';
            const cvv      = document.getElementById('card_cvv')?.value.trim() || '';

            if (cardNum.length < 16) { showFieldError('card_number', 'Nomor kartu tidak valid.'); valid = false; }
            else clearFieldError('card_number');
            if (cardName.length < 3) { showFieldError('card_name', 'Nama pemegang kartu diperlukan.'); valid = false; }
            else clearFieldError('card_name');
            if (!/^\d{2}\/\d{2}$/.test(expiry)) { showFieldError('card_expiry', 'Format MM/YY.'); valid = false; }
            else clearFieldError('card_expiry');
            if (cvv.length < 3) { showFieldError('card_cvv', 'CVV tidak valid.'); valid = false; }
            else clearFieldError('card_cvv');

            if (!valid) { e.preventDefault(); return; }
        }

        if (!confirm('Konfirmasi pembayaran sebesar ' + (document.getElementById('display_grand')?.textContent || '') + '?')) {
            e.preventDefault();
        }
    });

    // Card number formatting
    const cardNumInput = document.getElementById('card_number');
    if (cardNumInput) {
        cardNumInput.addEventListener('input', function () {
            let v = this.value.replace(/\D/g, '').substring(0, 16);
            this.value = v.replace(/(.{4})/g, '$1 ').trim();
        });
    }

    // Expiry formatting
    const expiryInput = document.getElementById('card_expiry');
    if (expiryInput) {
        expiryInput.addEventListener('input', function () {
            let v = this.value.replace(/\D/g, '');
            if (v.length >= 2) v = v.substring(0,2) + '/' + v.substring(2,4);
            this.value = v;
        });
    }
}

// ─── DELETE confirmation ─────────────────────────────────────
document.querySelectorAll('.btn-delete').forEach(btn => {
    btn.addEventListener('click', function (e) {
        if (!confirm('Yakin ingin menghapus data ini? Tindakan tidak dapat dibatalkan.')) {
            e.preventDefault();
        }
    });
});

// ─── Room selector (booking step 1) ─────────────────────────
document.querySelectorAll('.room-selector-card').forEach(card => {
    card.addEventListener('click', function () {
        document.querySelectorAll('.room-selector-card').forEach(c => c.classList.remove('selected'));
        this.classList.add('selected');
        const roomId    = this.dataset.roomId;
        const roomPrice = this.dataset.roomPrice;
        const roomName  = this.dataset.roomName;

        const hiddenRoomId    = document.getElementById('selected_room_id');
        const hiddenRoomPrice = document.getElementById('room_price_val');
        if (hiddenRoomId)    hiddenRoomId.value    = roomId;
        if (hiddenRoomPrice) hiddenRoomPrice.value = roomPrice;

        // Update summary panel
        setIfExists('summary_room_name', roomName);
        setIfExists('summary_room_price', formatRupiah(parseFloat(roomPrice)));
        updateGrandTotal();
    });
});

// ─── Search/filter with live DOM ─────────────────────────────
const searchInput = document.getElementById('searchInput');
if (searchInput) {
    searchInput.addEventListener('input', function () {
        const q = this.value.toLowerCase();
        document.querySelectorAll('.searchable-row').forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(q) ? '' : 'none';
        });
        const noResult = document.getElementById('noSearchResult');
        const visible  = document.querySelectorAll('.searchable-row:not([style*="none"])').length;
        if (noResult) noResult.style.display = visible === 0 ? 'block' : 'none';
    });
}

// ─── Toggle password visibility ───────────────────────────────
document.querySelectorAll('.toggle-password').forEach(btn => {
    btn.addEventListener('click', function () {
        const targetId = this.dataset.target;
        const input    = document.getElementById(targetId);
        if (!input) return;
        const isPassword = input.type === 'password';
        input.type = isPassword ? 'text' : 'password';
        this.innerHTML = isPassword ? '<i class="bi bi-eye-slash"></i>' : '<i class="bi bi-eye"></i>';
    });
});

// ─── Rooms filter (home check-availability bar) ──────────────
const checkAvailBtn = document.getElementById('checkAvailBtn');
if (checkAvailBtn) {
    checkAvailBtn.addEventListener('click', function () {
        const checkin  = document.getElementById('bar_checkin')?.value;
        const checkout = document.getElementById('bar_checkout')?.value;
        const guests   = document.getElementById('bar_guests')?.value || 2;
        const roomType = document.getElementById('bar_room_type')?.value || '';

        if (!checkin || !checkout) {
            alert('Pilih tanggal check-in dan check-out.');
            return;
        }
        if (checkout <= checkin) {
            alert('Tanggal check-out harus setelah check-in.');
            return;
        }

        let url = 'pages/rooms.php?checkin=' + encodeURIComponent(checkin) +
                  '&checkout=' + encodeURIComponent(checkout) +
                  '&guests=' + encodeURIComponent(guests);
        if (roomType) url += '&type=' + encodeURIComponent(roomType);
        window.location.href = url;
    });
}

// ─── Init grand total on page load ──────────────────────────
updateAddonTotal();
updateGrandTotal();
