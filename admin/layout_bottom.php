                </main><!-- /adm-content -->
        </div><!-- /adm-main -->
</div><!-- /adm-shell -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI"
        crossorigin="anonymous"></script>
<script>
(function () {
        var toggleBtn = document.getElementById('admSidebarToggle');
        var backdrop = document.getElementById('admBackdrop');
        var desktopBreakpoint = 992;
        var storageKey = 'mellatron_admin_sidebar_collapsed';

        function isMobile() {
                return window.innerWidth < desktopBreakpoint;
        }

        function setCollapsed(collapsed) {
                document.body.classList.toggle('adm-sidebar-collapsed', !!collapsed);
                try {
                        localStorage.setItem(storageKey, collapsed ? '1' : '0');
                } catch (e) {}
        }

        function closeMobileMenu() {
                document.body.classList.remove('adm-sidebar-open-mobile');
        }

        function initState() {
                var stored = null;
                try {
                        stored = localStorage.getItem(storageKey);
                } catch (e) {}
                if (!isMobile() && stored === '1') {
                        document.body.classList.add('adm-sidebar-collapsed');
                }
        }

        if (toggleBtn) {
                toggleBtn.addEventListener('click', function () {
                        if (isMobile()) {
                                document.body.classList.toggle('adm-sidebar-open-mobile');
                        } else {
                                setCollapsed(!document.body.classList.contains('adm-sidebar-collapsed'));
                        }
                });
        }

        if (backdrop) {
                backdrop.addEventListener('click', closeMobileMenu);
        }

        window.addEventListener('resize', function () {
                if (!isMobile()) {
                        closeMobileMenu();
                }
        });

        document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape') {
                        closeMobileMenu();
                }
        });

        initState();
})();
</script>
</body>
</html>
