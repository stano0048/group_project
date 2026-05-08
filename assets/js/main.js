document.addEventListener('DOMContentLoaded', function () {
    const toggle = document.getElementById('navToggle');
    const links = document.getElementById('navLinks');

    if (toggle && links) {
        toggle.addEventListener('click', function () {
            links.classList.toggle('open');
        });
    }

    const previewInputs = document.querySelectorAll('.upload-input');
    previewInputs.forEach(function (input) {
        input.addEventListener('change', function () {
            const container = document.getElementById(input.dataset.preview);
            if (!container) return;
            container.innerHTML = '';
            Array.from(input.files).forEach(function (file) {
                if (!file.type.startsWith('image/')) return;
                const reader = new FileReader();
                reader.onload = function (e) {
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    container.appendChild(img);
                };
                reader.readAsDataURL(file);
            });
        });
    });

    const negotiable = document.getElementById('is_negotiable');
    const priceRange = document.getElementById('price_range_fields');
    if (negotiable && priceRange) {
        negotiable.addEventListener('change', function () {
            priceRange.style.display = this.checked ? 'block' : 'none';
        });
    }

    const offerInput = document.getElementById('offer_price');
    if (offerInput) {
        offerInput.addEventListener('input', function () {
            const min = parseFloat(this.dataset.min);
            const max = parseFloat(this.dataset.max);
            const val = parseFloat(this.value);
            const msg = document.getElementById('offer_msg');
            if (!msg) return;
            if (isNaN(val) || this.value === '') { msg.textContent = ''; return; }
            if (val < min) {
                msg.textContent = 'Your offer is below the seller\'s allowed price range.';
                msg.className = 'form-hint' ;
                msg.style.color = '#dc2626';
            } else if (val > max) {
                msg.textContent = 'Your offer is above the seller\'s allowed price range.';
                msg.style.color = '#dc2626';
            } else {
                msg.textContent = 'Your offer is within the acceptable range.';
                msg.style.color = '#16a34a';
            }
        });
    }

    const dismissAlerts = document.querySelectorAll('.alert-dismiss');
    dismissAlerts.forEach(function (btn) {
        btn.addEventListener('click', function () {
            this.closest('.alert').remove();
        });
    });

    document.querySelectorAll('.confirm-action').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            if (!confirm(this.dataset.confirm || 'Are you sure?')) {
                e.preventDefault();
            }
        });
    });

    const mainImg = document.getElementById('mainProductImg');
    document.querySelectorAll('.product-thumb').forEach(function (thumb) {
        thumb.addEventListener('click', function () {
            if (mainImg) mainImg.src = this.src;
            document.querySelectorAll('.product-thumb').forEach(t => t.classList.remove('active'));
            this.classList.add('active');
        });
    });

    function cartCount() {
        const badge = document.getElementById('cartCount');
        if (!badge) return;
        fetch('/ajax/cart-count.php')
            .then(r => r.json())
            .then(data => { badge.textContent = data.count; });
    }

    document.querySelectorAll('.add-to-cart-btn').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            const productId = this.dataset.product;
            const offerPrice = document.getElementById('offer_price') ? document.getElementById('offer_price').value : '';
            fetch('/ajax/add-to-cart.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'product_id=' + productId + '&offer_price=' + offerPrice
            })
            .then(r => r.json())
            .then(data => {
                const msg = document.getElementById('cart_msg');
                if (msg) {
                    msg.textContent = data.message;
                    msg.className = 'alert ' + (data.success ? 'alert-success' : 'alert-danger');
                    msg.style.display = 'block';
                }
                cartCount();
            });
        });
    });
});
