/**
 * Si-LazisMu UMS — App JS
 */
(function () {
    'use strict';

    // ── Sidebar toggle ──────────────────────────────────────
    const toggleBtn     = document.getElementById('sidebarToggle');
    const overlay       = document.getElementById('sidebar-overlay');
    const isMobile      = () => window.innerWidth < 992;

    function openSidebar() {
        document.body.classList.add('sidebar-open');
        document.body.classList.remove('sidebar-collapsed');
    }
    function closeSidebar() {
        if (isMobile()) {
            document.body.classList.remove('sidebar-open');
        } else {
            document.body.classList.toggle('sidebar-collapsed');
        }
    }

    if (toggleBtn) {
        toggleBtn.addEventListener('click', () => {
            if (isMobile()) {
                document.body.classList.contains('sidebar-open') ? closeSidebar() : openSidebar();
            } else {
                closeSidebar();
            }
        });
    }
    if (overlay) overlay.addEventListener('click', closeSidebar);

    // ── Keep active sub-menu open on load ──────────────────
    document.querySelectorAll('.sidebar-submenu .nav-link.active').forEach(link => {
        const collapse = link.closest('.collapse');
        if (collapse) {
            collapse.classList.add('show');
            const trigger = document.querySelector(`[data-bs-target="#${collapse.id}"]`);
            if (trigger) trigger.setAttribute('aria-expanded', 'true');
        }
    });

    // ── Format currency (rupiah) ────────────────────────────
    window.formatRupiah = function (angka, prefix = 'Rp ') {
        const number = parseFloat(angka) || 0;
        return prefix + number.toLocaleString('id-ID', { minimumFractionDigits: 0 });
    };

    // ── Auto-format input rupiah ────────────────────────────
    document.querySelectorAll('[data-rupiah]').forEach(input => {
        input.addEventListener('input', function () {
            let val = this.value.replace(/[^0-9]/g, '');
            this.value = parseInt(val || 0).toLocaleString('id-ID');
        });
    });

    // ── Tooltips ────────────────────────────────────────────
    const tooltipEls = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    tooltipEls.forEach(el => new bootstrap.Tooltip(el));

})();
