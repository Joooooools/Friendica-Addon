(function () {
    'use strict';

    function initAlertBanner() {
        const banner = document.getElementById('alertbanner-root');
        if (!banner) {
            return;
        }

        const bannerId = banner.dataset.bannerId;
        if (!bannerId) {
            return;
        }

        // Check if this banner version was already dismissed in this browser
        try {
            if (localStorage.getItem('alertbanner_dismissed_' + bannerId) === 'true') {
                banner.style.display = 'none';
                return;
            }
        } catch (e) {
            // Ignore localStorage security exceptions
        }

        const closeBtn = banner.querySelector('.alertbanner-close');
        if (closeBtn) {
            closeBtn.addEventListener('click', function () {
                banner.classList.add('alertbanner-dismissed');
                
                try {
                    localStorage.setItem('alertbanner_dismissed_' + bannerId, 'true');
                } catch (e) {
                    // Ignore localStorage quota/security exceptions
                }
                
                // Allow transition to finish before hiding completely
                banner.addEventListener('transitionend', function () {
                    banner.style.display = 'none';
                }, { once: true });
            });
        }
    }

    // Initialize on DOMContentLoaded
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initAlertBanner);
    } else {
        initAlertBanner();
    }
})();
