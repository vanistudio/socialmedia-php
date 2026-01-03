<?php
require $_SERVER['DOCUMENT_ROOT'] . '/views/layouts/_administrator/AdHeader.php';

// Get all settings
$settings = [];
$settingsRows = $Vani->get_list("SELECT * FROM settings ORDER BY `key` ASC");
foreach ($settingsRows as $row) {
    $settings[$row['key']] = $row['value'];
}
?>

<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-foreground">Settings</h1>
            <p class="text-sm text-muted-foreground">Cấu hình hệ thống Vani Social</p>
        </div>
    </div>

    <!-- Tabs -->
    <div class="border-b border-border overflow-x-auto -mx-4 px-4 sm:mx-0 sm:px-0">
        <nav class="-mb-px flex space-x-2 sm:space-x-6 min-w-max" aria-label="Tabs">
            <button type="button" data-tab="general" class="tab-button border-b-2 border-red-500 text-red-500 whitespace-nowrap py-3 sm:py-4 px-3 sm:px-1 text-sm font-medium flex items-center gap-1.5 sm:gap-2">
                <iconify-icon icon="solar:settings-linear" width="18"></iconify-icon>
                <span>General</span>
            </button>
            <button type="button" data-tab="appearance" class="tab-button border-b-2 border-transparent text-muted-foreground hover:text-foreground hover:border-gray-300 whitespace-nowrap py-3 sm:py-4 px-3 sm:px-1 text-sm font-medium flex items-center gap-1.5 sm:gap-2">
                <iconify-icon icon="solar:palette-linear" width="18"></iconify-icon>
                <span>Appearance</span>
            </button>
            <button type="button" data-tab="moderation" class="tab-button border-b-2 border-transparent text-muted-foreground hover:text-foreground hover:border-gray-300 whitespace-nowrap py-3 sm:py-4 px-3 sm:px-1 text-sm font-medium flex items-center gap-1.5 sm:gap-2">
                <iconify-icon icon="solar:shield-check-linear" width="18"></iconify-icon>
                <span>Moderation</span>
            </button>
            <button type="button" data-tab="api" class="tab-button border-b-2 border-transparent text-muted-foreground hover:text-foreground hover:border-gray-300 whitespace-nowrap py-3 sm:py-4 px-3 sm:px-1 text-sm font-medium flex items-center gap-1.5 sm:gap-2">
                <iconify-icon icon="solar:programming-linear" width="18"></iconify-icon>
                <span>API</span>
            </button>
        </nav>
    </div>

    <!-- General Tab -->
    <div id="general-tab" class="tab-content">
        <div class="bg-card border border-border rounded-2xl p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-foreground mb-4">General Settings</h2>
            <form id="general-form" class="space-y-5">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="space-y-2">
                        <label for="siteTitle" class="text-sm font-medium text-foreground">Site Title</label>
                        <input type="text" id="siteTitle" name="siteTitle" value="<?php echo htmlspecialchars($settings['siteTitle'] ?? 'Vani Social'); ?>" class="w-full h-10 rounded-lg border border-input bg-background px-3 text-sm shadow-sm transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-red-500/30">
                    </div>
                    <div class="space-y-2">
                        <label for="siteTagline" class="text-sm font-medium text-foreground">Tagline</label>
                        <input type="text" id="siteTagline" name="siteTagline" value="<?php echo htmlspecialchars($settings['siteTagline'] ?? 'Connect with the world'); ?>" class="w-full h-10 rounded-lg border border-input bg-background px-3 text-sm shadow-sm transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-red-500/30">
                    </div>
                </div>

                <div class="space-y-2">
                    <label for="siteDescription" class="text-sm font-medium text-foreground">Site Description</label>
                    <textarea id="siteDescription" name="siteDescription" rows="3" class="w-full rounded-lg border border-input bg-background px-3 py-2 text-sm shadow-sm transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-red-500/30"><?php echo htmlspecialchars($settings['siteDescription'] ?? ''); ?></textarea>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="space-y-2">
                        <label for="contactEmail" class="text-sm font-medium text-foreground">Contact Email</label>
                        <input type="email" id="contactEmail" name="contactEmail" value="<?php echo htmlspecialchars($settings['contactEmail'] ?? ''); ?>" class="w-full h-10 rounded-lg border border-input bg-background px-3 text-sm shadow-sm transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-red-500/30">
                    </div>
                    <div class="space-y-2">
                        <label for="supportUrl" class="text-sm font-medium text-foreground">Support URL</label>
                        <input type="url" id="supportUrl" name="supportUrl" value="<?php echo htmlspecialchars($settings['supportUrl'] ?? ''); ?>" class="w-full h-10 rounded-lg border border-input bg-background px-3 text-sm shadow-sm transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-red-500/30">
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <button type="submit" class="h-10 px-5 rounded-lg bg-red-500 text-white hover:bg-red-600 transition text-sm font-medium flex items-center gap-2">
                        <iconify-icon icon="solar:diskette-linear" width="18"></iconify-icon>
                        <span>Lưu thay đổi</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Appearance Tab -->
    <div id="appearance-tab" class="tab-content hidden">
        <div class="bg-card border border-border rounded-2xl p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-foreground mb-4">Appearance Settings</h2>
            <form id="appearance-form" class="space-y-5">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="space-y-2">
                        <label for="primaryColor" class="text-sm font-medium text-foreground">Primary Color</label>
                        <div class="flex gap-2">
                            <input type="color" id="primaryColorPicker" value="<?php echo htmlspecialchars($settings['primaryColor'] ?? '#6366f1'); ?>" class="h-10 w-14 rounded-lg border border-input">
                            <input type="text" id="primaryColor" name="primaryColor" value="<?php echo htmlspecialchars($settings['primaryColor'] ?? '#6366f1'); ?>" class="flex-1 h-10 rounded-lg border border-input bg-background px-3 text-sm shadow-sm transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-red-500/30">
                        </div>
                    </div>
                    <div class="space-y-2">
                        <label for="secondaryColor" class="text-sm font-medium text-foreground">Secondary Color</label>
                        <div class="flex gap-2">
                            <input type="color" id="secondaryColorPicker" value="<?php echo htmlspecialchars($settings['secondaryColor'] ?? '#f59e0b'); ?>" class="h-10 w-14 rounded-lg border border-input">
                            <input type="text" id="secondaryColor" name="secondaryColor" value="<?php echo htmlspecialchars($settings['secondaryColor'] ?? '#f59e0b'); ?>" class="flex-1 h-10 rounded-lg border border-input bg-background px-3 text-sm shadow-sm transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-red-500/30">
                        </div>
                    </div>
                </div>

                <div class="space-y-2">
                    <label for="logoUrl" class="text-sm font-medium text-foreground">Logo URL</label>
                    <input type="url" id="logoUrl" name="logoUrl" value="<?php echo htmlspecialchars($settings['logoUrl'] ?? ''); ?>" placeholder="https://..." class="w-full h-10 rounded-lg border border-input bg-background px-3 text-sm shadow-sm transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-red-500/30">
                </div>

                <div class="space-y-2">
                    <label for="faviconUrl" class="text-sm font-medium text-foreground">Favicon URL</label>
                    <input type="url" id="faviconUrl" name="faviconUrl" value="<?php echo htmlspecialchars($settings['faviconUrl'] ?? ''); ?>" placeholder="https://..." class="w-full h-10 rounded-lg border border-input bg-background px-3 text-sm shadow-sm transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-red-500/30">
                </div>

                <div class="flex items-center gap-3">
                    <button type="submit" class="h-10 px-5 rounded-lg bg-red-500 text-white hover:bg-red-600 transition text-sm font-medium flex items-center gap-2">
                        <iconify-icon icon="solar:diskette-linear" width="18"></iconify-icon>
                        <span>Lưu thay đổi</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Moderation Tab -->
    <div id="moderation-tab" class="tab-content hidden">
        <div class="bg-card border border-border rounded-2xl p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-foreground mb-4">Moderation Settings</h2>
            <form id="moderation-form" class="space-y-5">
                <div class="flex items-center justify-between p-4 rounded-xl bg-background">
                    <div>
                        <p class="font-medium text-foreground">Auto Moderation</p>
                        <p class="text-sm text-muted-foreground">Tự động kiểm duyệt nội dung bằng AI</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" id="autoModeration" name="autoModeration" <?php echo ($settings['autoModeration'] ?? '1') === '1' ? 'checked' : ''; ?> class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-red-500/30 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-red-500"></div>
                    </label>
                </div>

                <div class="flex items-center justify-between p-4 rounded-xl bg-background">
                    <div>
                        <p class="font-medium text-foreground">Blacklist Check</p>
                        <p class="text-sm text-muted-foreground">Kiểm tra từ khóa cấm trong nội dung</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" id="blacklistCheck" name="blacklistCheck" <?php echo ($settings['blacklistCheck'] ?? '1') === '1' ? 'checked' : ''; ?> class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-red-500/30 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-red-500"></div>
                    </label>
                </div>

                <div class="flex items-center justify-between p-4 rounded-xl bg-background">
                    <div>
                        <p class="font-medium text-foreground">User Registration</p>
                        <p class="text-sm text-muted-foreground">Cho phép người dùng mới đăng ký</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" id="allowRegistration" name="allowRegistration" <?php echo ($settings['allowRegistration'] ?? '1') === '1' ? 'checked' : ''; ?> class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-red-500/30 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-red-500"></div>
                    </label>
                </div>

                <div class="flex items-center gap-3">
                    <button type="submit" class="h-10 px-5 rounded-lg bg-red-500 text-white hover:bg-red-600 transition text-sm font-medium flex items-center gap-2">
                        <iconify-icon icon="solar:diskette-linear" width="18"></iconify-icon>
                        <span>Lưu thay đổi</span>
                    </button>
                </div>
            </form>
        </div>

        <div class="bg-card border border-border rounded-2xl p-6 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold text-foreground">Blacklist Keywords</h2>
                <a href="/admin/blacklist" class="h-9 px-4 rounded-lg bg-red-500 text-white hover:bg-red-600 transition text-sm font-medium flex items-center gap-2">
                    <iconify-icon icon="solar:forbidden-circle-linear" width="16"></iconify-icon>
                    <span>Quản lý Blacklist</span>
                </a>
            </div>
            <p class="text-sm text-muted-foreground">
                Hiện có <span class="font-medium"><?php echo $Vani->num_rows("SELECT id FROM blacklist_keywords WHERE active = 1") ?: 0; ?></span> từ khóa đang hoạt động.
            </p>
        </div>
    </div>

    <!-- API Keys Tab -->
    <div id="api-tab" class="tab-content hidden">
        <div class="bg-card border border-border rounded-2xl p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-foreground mb-4">API Keys</h2>
            <p class="text-sm text-muted-foreground mb-6">API keys được cấu hình trong file .env. Đây chỉ là thông tin hiển thị.</p>
            
            <div class="space-y-4">
                <div class="p-4 rounded-xl bg-background">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="h-10 w-10 rounded-lg bg-green-500/15 flex items-center justify-center">
                                <iconify-icon icon="simple-icons:openai" class="text-green-500" width="20"></iconify-icon>
                            </div>
                            <div>
                                <p class="font-medium text-foreground">OpenAI API</p>
                                <p class="text-xs text-muted-foreground">Content moderation</p>
                            </div>
                        </div>
                        <span class="px-3 py-1 rounded-lg text-xs font-medium <?php echo !empty($_ENV['OPENAI_API_KEY']) ? 'bg-green-500/15 text-green-500' : 'bg-gray-500/15 text-gray-500'; ?>">
                            <?php echo !empty($_ENV['OPENAI_API_KEY']) ? 'Configured' : 'Not Set'; ?>
                        </span>
                    </div>
                </div>

                <div class="p-4 rounded-xl bg-background">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="h-10 w-10 rounded-lg bg-purple-500/15 flex items-center justify-center">
                                <iconify-icon icon="simple-icons:pusher" class="text-purple-500" width="20"></iconify-icon>
                            </div>
                            <div>
                                <p class="font-medium text-foreground">Pusher</p>
                                <p class="text-xs text-muted-foreground">Real-time messaging</p>
                            </div>
                        </div>
                        <span class="px-3 py-1 rounded-lg text-xs font-medium <?php echo !empty($_ENV['PUSHER_APP_KEY']) ? 'bg-green-500/15 text-green-500' : 'bg-gray-500/15 text-gray-500'; ?>">
                            <?php echo !empty($_ENV['PUSHER_APP_KEY']) ? 'Configured' : 'Not Set'; ?>
                        </span>
                    </div>
                </div>

                <div class="p-4 rounded-xl bg-background">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="h-10 w-10 rounded-lg bg-blue-500/15 flex items-center justify-center">
                                <iconify-icon icon="solar:database-linear" class="text-blue-500" width="20"></iconify-icon>
                            </div>
                            <div>
                                <p class="font-medium text-foreground">Database</p>
                                <p class="text-xs text-muted-foreground">MySQL connection</p>
                            </div>
                        </div>
                        <span class="px-3 py-1 rounded-lg text-xs font-medium <?php echo !empty($_ENV['DB_HOST']) ? 'bg-green-500/15 text-green-500' : 'bg-gray-500/15 text-gray-500'; ?>">
                            <?php echo !empty($_ENV['DB_HOST']) ? 'Configured' : 'Not Set'; ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function switchTab(tabName) {
    $('.tab-content').addClass('hidden');
    $(`#${tabName}-tab`).removeClass('hidden');
    $('.tab-button').removeClass('border-red-500 text-red-500').addClass('border-transparent text-muted-foreground hover:text-foreground hover:border-gray-300');
    $(`[data-tab="${tabName}"]`).removeClass('text-muted-foreground hover:text-foreground hover:border-gray-300').addClass('border-red-500 text-red-500');
}

$(document).ready(function() {
    $('[data-tab]').on('click', function() {
        const tabName = $(this).data('tab');
        switchTab(tabName);
    });

    // Color picker sync
    $('#primaryColorPicker').on('input', function() {
        $('#primaryColor').val($(this).val());
    });
    $('#primaryColor').on('input', function() {
        $('#primaryColorPicker').val($(this).val());
    });
    $('#secondaryColorPicker').on('input', function() {
        $('#secondaryColor').val($(this).val());
    });
    $('#secondaryColor').on('input', function() {
        $('#secondaryColorPicker').val($(this).val());
    });

    // Form submissions
    $('#general-form, #appearance-form, #moderation-form').on('submit', function(e) {
        e.preventDefault();
        const $form = $(this);
        const $btn = $form.find('button[type="submit"]');
        const originalBtnHtml = $btn.html();

        $btn.prop('disabled', true).addClass('opacity-70').html('<span>Đang lưu...</span>');

        const formData = {};
        $form.find('input, textarea, select').each(function() {
            const name = $(this).attr('name');
            if (!name) return;
            
            if ($(this).attr('type') === 'checkbox') {
                formData[name] = $(this).is(':checked') ? '1' : '0';
            } else {
                formData[name] = $(this).val();
            }
        });

        $.post('/api/controller/admin', {
            type: 'ADMIN_UPDATE_SETTINGS',
            settings: JSON.stringify(formData),
            csrf_token: window.CSRF_TOKEN || ''
        }, function(data) {
            if (data.status === 'success') {
                toast.success('Đã lưu cài đặt');
            } else {
                toast.error(data.message || 'Có lỗi xảy ra');
            }
        }, 'json').fail(function() {
            toast.error('Không thể kết nối tới máy chủ');
        }).always(function() {
            $btn.prop('disabled', false).removeClass('opacity-70').html(originalBtnHtml);
        });
    });
});
</script>

<?php require $_SERVER['DOCUMENT_ROOT'] . '/views/layouts/_administrator/AdFooter.php'; ?>

