        </main>
        <?php include 'AdDock.php'; ?>
        <footer class="border-t border-border bg-background">
            <div class="container mx-auto px-4 py-6 flex flex-col sm:flex-row items-center justify-between gap-4 text-sm text-muted-foreground">
                <div class="flex items-center gap-2">
                    <div class="h-7 w-7 rounded-lg bg-red-500/15 flex items-center justify-center">
                        <iconify-icon icon="solar:shield-user-linear" class="text-red-500" width="18"></iconify-icon>
                    </div>
                    <span>Admin Panel - <?php echo htmlspecialchars($siteTitle ?? 'Vani Social'); ?> © <?php echo date('Y'); ?></span>
                </div>

                <div class="flex items-center gap-4">
                    <a href="/" class="hover:text-red-500 transition">Trang chủ</a>
                    <span class="text-xs text-muted-foreground">Version 1.0.0</span>
                </div>
            </div>
        </footer>
    </div>
    <script>
        $(document).ready(function() {
            $('#admin-theme-toggle').on('click', function() {
                const $html = $('html');
                const $toggle = $(this).find('.h-8');
                const $icon = $(this).find('iconify-icon');
                const isDark = $html.hasClass('dark');
                
                if (isDark) {
                    $html.removeClass('dark').addClass('light');
                    localStorage.setItem('theme', 'light');
                    $icon.attr('icon', 'solar:moon-linear');
                    $toggle.removeClass('translate-x-6').addClass('translate-x-0');
                } else {
                    $html.removeClass('light').addClass('dark');
                    localStorage.setItem('theme', 'dark');
                    $icon.attr('icon', 'solar:sun-linear');
                    $toggle.removeClass('translate-x-0').addClass('translate-x-6');
                }
            });

            const savedTheme = localStorage.getItem('theme') || 'light';
            $('html').removeClass('light dark').addClass(savedTheme);
            if (savedTheme === 'dark') {
                $('#admin-theme-toggle').find('iconify-icon').attr('icon', 'solar:sun-linear');
                $('#admin-theme-toggle').find('.h-8').removeClass('translate-x-0').addClass('translate-x-6');
            }
        });
    </script>
</body>
</html>
