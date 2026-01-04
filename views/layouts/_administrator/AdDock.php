<?php
$__currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$__currentPath = rtrim($__currentPath, '/') ?: '/';

$__isDockDashboardActive = $__currentPath === '/admin';
$__isDockModerationActive = strpos($__currentPath, '/admin/moderation') === 0;
$__isDockReportsActive = strpos($__currentPath, '/admin/reports') === 0;
$__isDockUsersActive = strpos($__currentPath, '/admin/users') === 0;
$__isDockSettingsActive = strpos($__currentPath, '/admin/settings') === 0 || strpos($__currentPath, '/admin/blacklist') === 0;

if (!isset($pendingModerationCount)) {
    $pendingModerationCount = $Vani->num_rows("SELECT id FROM content_moderation_logs WHERE review_status IS NULL") ?: 0;
}
if (!isset($pendingReportsCount)) {
    $pendingReportsCount = $Vani->num_rows("SELECT id FROM reports WHERE status = 'open'") ?: 0;
}
?>
<nav class="lg:hidden fixed bottom-4 left-1/2 -translate-x-1/2 z-50 max-w-[95vw]">
    <div class="bg-background/95 backdrop-blur-lg border border-border shadow-2xl rounded-2xl px-1.5 py-1.5">
        <ul class="flex items-center gap-0.5">
            <li>
                <a href="/admin" class="group flex flex-col items-center justify-center w-[52px] h-11 rounded-xl <?php echo $__isDockDashboardActive ? 'text-red-500 bg-red-500/10' : 'text-muted-foreground hover:text-red-500 hover:bg-red-500/10'; ?> transition">
                    <iconify-icon icon="solar:chart-2-linear" width="20"></iconify-icon>
                    <span class="text-[9px] font-medium mt-0.5">Home</span>
                </a>
            </li>
            <li>
                <a href="/admin/moderation" class="group flex flex-col items-center justify-center w-[52px] h-11 rounded-xl <?php echo $__isDockModerationActive ? 'text-red-500 bg-red-500/10' : 'text-muted-foreground hover:text-red-500 hover:bg-red-500/10'; ?> transition relative">
                    <iconify-icon icon="solar:shield-check-linear" width="20"></iconify-icon>
                    <span class="text-[9px] font-medium mt-0.5">Mod</span>
                    <?php if ($pendingModerationCount > 0): ?>
                    <span class="absolute top-0.5 right-1.5 h-4 min-w-4 px-1 rounded-full bg-red-500 text-white text-[9px] flex items-center justify-center"><?php echo $pendingModerationCount > 99 ? '99+' : $pendingModerationCount; ?></span>
                    <?php endif; ?>
                </a>
            </li>
            <li>
                <a href="/admin/reports" class="group flex flex-col items-center justify-center w-[52px] h-11 rounded-xl <?php echo $__isDockReportsActive ? 'text-red-500 bg-red-500/10' : 'text-muted-foreground hover:text-red-500 hover:bg-red-500/10'; ?> transition relative">
                    <iconify-icon icon="solar:danger-triangle-linear" width="20"></iconify-icon>
                    <span class="text-[9px] font-medium mt-0.5">Reports</span>
                    <?php if ($pendingReportsCount > 0): ?>
                    <span class="absolute top-0.5 right-1.5 h-4 min-w-4 px-1 rounded-full bg-red-500 text-white text-[9px] flex items-center justify-center"><?php echo $pendingReportsCount > 99 ? '99+' : $pendingReportsCount; ?></span>
                    <?php endif; ?>
                </a>
            </li>
            <li>
                <a href="/admin/users" class="group flex flex-col items-center justify-center w-[52px] h-11 rounded-xl <?php echo $__isDockUsersActive ? 'text-red-500 bg-red-500/10' : 'text-muted-foreground hover:text-red-500 hover:bg-red-500/10'; ?> transition">
                    <iconify-icon icon="solar:users-group-two-rounded-linear" width="20"></iconify-icon>
                    <span class="text-[9px] font-medium mt-0.5">Users</span>
                </a>
            </li>
            <li>
                <a href="/admin/settings" class="group flex flex-col items-center justify-center w-[52px] h-11 rounded-xl <?php echo $__isDockSettingsActive ? 'text-red-500 bg-red-500/10' : 'text-muted-foreground hover:text-red-500 hover:bg-red-500/10'; ?> transition">
                    <iconify-icon icon="solar:settings-linear" width="20"></iconify-icon>
                    <span class="text-[9px] font-medium mt-0.5">Settings</span>
                </a>
            </li>
        </ul>
    </div>
</nav>
<div class="lg:hidden h-20"></div>

