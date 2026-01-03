<?php
require $_SERVER['DOCUMENT_ROOT'] . '/config/database.php';
require $_SERVER['DOCUMENT_ROOT'] . '/config/function.php';

if (!isset($_SESSION['email'])) {
    header('Location: /login');
    exit;
}

$user = $Vani->get_row("SELECT * FROM `users` WHERE `email` = '" . $_SESSION['email'] . "'");

require $_SERVER['DOCUMENT_ROOT'] . '/views/layouts/_application/AppHeader.php';
?>

<div class="w-full max-w-3xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-foreground">Cài đặt tài khoản</h1>
            <p class="text-sm text-muted-foreground">Quản lý thông tin cá nhân và bảo mật.</p>
        </div>
        <a href="/u/<?php echo htmlspecialchars($user['username']); ?>" class="h-10 px-4 rounded-lg border border-input bg-card hover:bg-accent transition text-sm font-medium flex items-center gap-2">
            <iconify-icon icon="solar:user-circle-linear" width="18"></iconify-icon>
            <span>Xem profile</span>
        </a>
    </div>
    <div class="border-b border-border">
        <nav class="-mb-px flex space-x-8" aria-label="Tabs">
            <button type="button" data-tab="profile" class="tab-button border-b-2 border-vanixjnk text-vanixjnk whitespace-nowrap py-4 px-1 text-sm font-medium">
                <iconify-icon icon="solar:user-linear" class="mr-2"></iconify-icon>
                Thông tin
            </button>
            <button type="button" data-tab="images" class="tab-button border-b-2 border-transparent text-muted-foreground hover:text-foreground hover:border-gray-300 whitespace-nowrap py-4 px-1 text-sm font-medium">
                <iconify-icon icon="solar:gallery-wide-linear" class="mr-2"></iconify-icon>
                Hình ảnh
            </button>
            <button type="button" data-tab="security" class="tab-button border-b-2 border-transparent text-muted-foreground hover:text-foreground hover:border-gray-300 whitespace-nowrap py-4 px-1 text-sm font-medium">
                <iconify-icon icon="solar:lock-password-linear" class="mr-2"></iconify-icon>
                Bảo mật
            </button>
        </nav>
    </div>
    <div class="space-y-6">
        <div id="profile-tab" class="tab-content">
            <div class="bg-card border border-border rounded-2xl p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-foreground mb-4">Thông tin cá nhân</h2>
                <form id="profile-form" class="space-y-5">
                    <input type="hidden" name="type" value="UPDATE_PROFILE">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <label for="full_name" class="text-sm font-medium text-foreground">Họ và tên</label>
                            <input type="text" id="full_name" name="full_name" value="<?php echo htmlspecialchars($user['full_name'] ?? ''); ?>" class="w-full h-10 rounded-lg border border-input bg-background px-3 text-sm shadow-sm transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-vanixjnk/30 focus-visible:border-vanixjnk/50 hover:border-vanixjnk/30" required>
                        </div>
                        <div class="space-y-2">
                            <label for="username" class="text-sm font-medium text-foreground">Username</label>
                            <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($user['username'] ?? ''); ?>" class="w-full h-10 rounded-lg border border-input bg-background px-3 text-sm shadow-sm transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-vanixjnk/30 focus-visible:border-vanixjnk/50 hover:border-vanixjnk/30" required>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label for="email" class="text-sm font-medium text-foreground">Email</label>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" class="w-full h-10 rounded-lg border border-input bg-background px-3 text-sm shadow-sm transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-vanixjnk/30 focus-visible:border-vanixjnk/50 hover:border-vanixjnk/30" required disabled>
                        <p class="text-xs text-muted-foreground">Email hiện tại không thể thay đổi trong phiên bản này.</p>
                    </div>

                    <div class="space-y-2">
                        <label for="bio" class="text-sm font-medium text-foreground">Bio</label>
                        <textarea id="bio" name="bio" rows="3" placeholder="Giới thiệu ngắn về bạn..." class="w-full rounded-lg border border-input bg-background px-3 py-2 text-sm shadow-sm transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-vanixjnk/30 focus-visible:border-vanixjnk/50 hover:border-vanixjnk/30"><?php echo htmlspecialchars($user['bio'] ?? ''); ?></textarea>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <label for="location" class="text-sm font-medium text-foreground">Vị trí</label>
                            <div class="relative">
                                <iconify-icon icon="solar:map-point-linear" class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground" width="18"></iconify-icon>
                                <input type="text" id="location" name="location" value="<?php echo htmlspecialchars($user['location'] ?? ''); ?>" placeholder="TP. Hồ Chí Minh, Việt Nam" class="w-full h-10 pl-10 pr-3 rounded-lg border border-input bg-background text-sm shadow-sm transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-vanixjnk/30 focus-visible:border-vanixjnk/50 hover:border-vanixjnk/30">
                            </div>
                        </div>
                        <div class="space-y-2">
                            <label for="website" class="text-sm font-medium text-foreground">Website</label>
                            <div class="relative">
                                <iconify-icon icon="solar:link-linear" class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground" width="18"></iconify-icon>
                                <input type="url" id="website" name="website" value="<?php echo htmlspecialchars($user['website'] ?? ''); ?>" placeholder="https://yourwebsite.com" class="w-full h-10 pl-10 pr-3 rounded-lg border border-input bg-background text-sm shadow-sm transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-vanixjnk/30 focus-visible:border-vanixjnk/50 hover:border-vanixjnk/30">
                            </div>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label for="birthday" class="text-sm font-medium text-foreground">Ngày sinh</label>
                        <div class="relative">
                            <iconify-icon icon="solar:calendar-linear" class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground" width="18"></iconify-icon>
                            <input type="date" id="birthday" name="birthday" value="<?php echo htmlspecialchars($user['birthday'] ?? ''); ?>" class="w-full h-10 pl-10 pr-3 rounded-lg border border-input bg-background text-sm shadow-sm transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-vanixjnk/30 focus-visible:border-vanixjnk/50 hover:border-vanixjnk/30">
                        </div>
                        <p class="text-xs text-muted-foreground">Ngày sinh của bạn sẽ được hiển thị trên trang cá nhân.</p>
                    </div>

                    <div class="flex items-center gap-3">
                        <button type="submit" class="h-10 px-5 rounded-lg bg-vanixjnk text-white hover:bg-vanixjnk/90 transition text-sm font-medium flex items-center gap-2">
                            <iconify-icon icon="solar:diskette-linear" width="18"></iconify-icon>
                            <span>Lưu thay đổi</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
        <div id="images-tab" class="tab-content hidden">
            <div class="bg-card border border-border rounded-2xl p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-foreground mb-4">Hình ảnh</h2>
                <form id="images-form" class="space-y-5">
                    <input type="hidden" name="type" value="UPDATE_PROFILE">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <div class="flex items-center justify-between">
                                <label for="avatar" class="text-sm font-medium text-foreground">Avatar URL</label>
                                <button type="button" data-upload-trigger="avatar" class="text-xs font-medium text-vanixjnk hover:underline">Upload</button>
                            </div>
                            <input type="url" id="avatar" name="avatar" value="<?php echo htmlspecialchars($user['avatar'] ?? ''); ?>" placeholder="https://..." class="w-full h-10 rounded-lg border border-input bg-background px-3 text-sm shadow-sm transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-vanixjnk/30 focus-visible:border-vanixjnk/50 hover:border-vanixjnk/30">
                            <div class="mt-2 flex items-center justify-center rounded-lg border border-dashed border-border p-4">
                                <div class="text-center">
                                    <div class="mt-1 flex justify-center">
                                        <div class="h-24 w-24 rounded-full bg-gray-100 overflow-hidden">
                                            <img id="avatar-preview" src="<?php echo !empty($user['avatar']) ? htmlspecialchars($user['avatar']) : 'https://i.pravatar.cc/150?u=' . urlencode($user['email'] ?? ''); ?>" alt="Avatar" class="h-full w-full object-cover">
                                        </div>
                                    </div>
                                    <p class="mt-2 text-xs text-muted-foreground">JPG, GIF or PNG. Max 2MB</p>
                                </div>
                            </div>
                        </div>
                        <div class="space-y-2">
                            <div class="flex items-center justify-between">
                                <label for="banner" class="text-sm font-medium text-foreground">Banner URL</label>
                                <button type="button" data-upload-trigger="banner" class="text-xs font-medium text-vanixjnk hover:underline">Upload</button>
                            </div>
                            <input type="url" id="banner" name="banner" value="<?php echo htmlspecialchars($user['banner'] ?? ''); ?>" placeholder="https://..." class="w-full h-10 rounded-lg border border-input bg-background px-3 text-sm shadow-sm transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-vanixjnk/30 focus-visible:border-vanixjnk/50 hover:border-vanixjnk/30">
                            <div class="mt-2 flex items-center justify-center rounded-lg border border-dashed border-border p-4">
                                <div class="text-center w-full">
                                    <div class="mt-1 flex justify-center">
                                        <div class="h-24 w-full bg-gray-100 rounded-md overflow-hidden">
                                            <img id="banner-preview" src="<?php echo !empty($user['banner']) ? htmlspecialchars($user['banner']) : 'https://images.unsplash.com/photo-1501854140801-50d01698950b?q=80&w=2070&auto=format&fit=crop'; ?>" alt="Banner" class="h-full w-full object-cover">
                                        </div>
                                    </div>
                                    <p class="mt-2 text-xs text-muted-foreground">JPG, GIF or PNG. Max 5MB</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        <button type="submit" class="h-10 px-5 rounded-lg bg-vanixjnk text-white hover:bg-vanixjnk/90 transition text-sm font-medium flex items-center gap-2">
                            <iconify-icon icon="solar:diskette-linear" width="18"></iconify-icon>
                            <span>Lưu thay đổi</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
        <div id="security-tab" class="tab-content hidden">
            <div class="bg-card border border-border rounded-2xl p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-foreground mb-4">Bảo mật</h2>
                <form id="password-form" class="space-y-4">
                    <input type="hidden" name="type" value="CHANGE_PASSWORD">
                    <div class="space-y-2">
                        <label for="current_password" class="text-sm font-medium text-foreground">Mật khẩu hiện tại</label>
                        <div class="relative">
                            <input type="password" id="current_password" name="current_password" class="w-full h-10 rounded-lg border border-input bg-background px-3 pr-10 text-sm shadow-sm transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-vanixjnk/30 focus-visible:border-vanixjnk/50 hover:border-vanixjnk/30">
                            <button type="button" class="absolute inset-y-0 right-0 pr-3 flex items-center" onclick="togglePassword('current_password')">
                                <iconify-icon icon="solar:eye-closed-linear" class="text-muted-foreground hover:text-foreground transition" width="18"></iconify-icon>
                            </button>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <label for="new_password" class="text-sm font-medium text-foreground">Mật khẩu mới</label>
                            <div class="relative">
                                <input type="password" id="new_password" name="new_password" class="w-full h-10 rounded-lg border border-input bg-background px-3 pr-10 text-sm shadow-sm transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-vanixjnk/30 focus-visible:border-vanixjnk/50 hover:border-vanixjnk/30">
                                <button type="button" class="absolute inset-y-0 right-0 pr-3 flex items-center" onclick="togglePassword('new_password')">
                                    <iconify-icon icon="solar:eye-closed-linear" class="text-muted-foreground hover:text-foreground transition" width="18"></iconify-icon>
                                </button>
                            </div>
                        </div>
                        <div class="space-y-2">
                            <label for="confirm_password" class="text-sm font-medium text-foreground">Nhập lại mật khẩu</label>
                            <div class="relative">
                                <input type="password" id="confirm_password" name="confirm_password" class="w-full h-10 rounded-lg border border-input bg-background px-3 pr-10 text-sm shadow-sm transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-vanixjnk/30 focus-visible:border-vanixjnk/50 hover:border-vanixjnk/30">
                                <button type="button" class="absolute inset-y-0 right-0 pr-3 flex items-center" onclick="togglePassword('confirm_password')">
                                    <iconify-icon icon="solar:eye-closed-linear" class="text-muted-foreground hover:text-foreground transition" width="18"></iconify-icon>
                                </button>
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="h-10 px-5 rounded-lg bg-vanixjnk text-white hover:bg-vanixjnk/90 transition text-sm font-medium flex items-center gap-2">
                        <iconify-icon icon="solar:lock-password-linear" width="18"></iconify-icon>
                        <span>Cập nhật mật khẩu</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
<div id="upload-dialog" class="hidden fixed inset-0 z-50 items-center justify-center" data-dialog>
    <div class="absolute inset-0 bg-black/50" data-dialog-backdrop></div>
    <div class="relative w-full max-w-lg mx-auto" data-dialog-content>
        <div class="bg-card border border-border rounded-2xl shadow-lg" data-dialog-inner>
            <div class="flex items-center justify-between p-4 border-b border-border">
                <div>
                    <h3 class="text-lg font-semibold text-foreground">Upload media</h3>
                    <p class="text-sm text-muted-foreground">Chọn ảnh để đặt Avatar/Banner. (JPG/PNG/WebP)</p>
                </div>
                <button type="button" class="h-9 w-9 rounded-lg hover:bg-accent transition flex items-center justify-center" data-dialog-close>
                    <iconify-icon icon="solar:close-circle-linear" width="22"></iconify-icon>
                </button>
            </div>

            <div class="p-4 space-y-4">
                <input type="hidden" id="upload-target" value="avatar">

                <div class="rounded-xl border border-dashed border-border bg-background/50 p-6 text-center">
                    <div class="mx-auto h-12 w-12 rounded-xl bg-vanixjnk/15 flex items-center justify-center mb-3">
                        <iconify-icon icon="solar:upload-linear" class="text-vanixjnk" width="24"></iconify-icon>
                    </div>
                    <p class="text-sm text-foreground font-medium">Kéo thả file vào đây</p>
                    <p class="text-xs text-muted-foreground mt-1">hoặc bấm để chọn file</p>

                    <input id="upload-file" type="file" accept="image/*" class="mt-4 block w-full text-sm text-muted-foreground file:mr-4 file:rounded-lg file:border-0 file:bg-vanixjnk file:px-4 file:py-2 file:text-sm file:font-medium file:text-white hover:file:bg-vanixjnk/90 cursor-pointer">
                </div>

                <div id="upload-preview" class="hidden rounded-xl border border-border overflow-hidden">
                    <img id="upload-preview-img" src="" alt="Preview" class="w-full h-56 object-cover">
                </div>

                <div class="flex items-center justify-end gap-2">
                    <button type="button" class="h-10 px-4 rounded-lg border border-input bg-card hover:bg-accent transition text-sm font-medium" data-dialog-close>Hủy</button>
                    <button id="btn-upload" type="button" class="h-10 px-4 rounded-lg bg-vanixjnk text-white hover:bg-vanixjnk/90 transition text-sm font-medium">Tải lên</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function switchTab(tabName) {
    $('.tab-content').addClass('hidden');
    $(`#${tabName}-tab`).removeClass('hidden');
    $('.tab-button').removeClass('border-vanixjnk text-vanixjnk').addClass('border-transparent text-muted-foreground hover:text-foreground hover:border-gray-300');
    $(`[data-tab="${tabName}"]`).removeClass('text-muted-foreground hover:text-foreground hover:border-gray-300').addClass('border-vanixjnk text-vanixjnk');
}
function togglePassword(inputId) {
    const input = document.getElementById(inputId);
    const icon = $(`#${inputId}`).siblings('button').find('iconify-icon');
    if (input.type === 'password') {
        input.type = 'text';
        icon.attr('icon', 'solar:eye-linear');
    } else {
        input.type = 'password';
        icon.attr('icon', 'solar:eye-closed-linear');
    }
}
$(document).ready(function() {
    $('[data-tab]').on('click', function() {
        const tabName = $(this).data('tab');
        switchTab(tabName);
    });
    switchTab('profile');
    const uploadDialog = window.initDialog ? window.initDialog('upload-dialog') : null;
    $('[data-upload-trigger]').on('click', function () {
        const target = $(this).data('upload-trigger');
        $('#upload-target').val(target);
        $('#upload-file').val('');
        $('#upload-preview').addClass('hidden');
        $('#upload-preview-img').attr('src', '');
        if (uploadDialog) uploadDialog.open();
        else $('#upload-dialog').removeClass('hidden').addClass('flex').attr('data-state', 'open');
    });
    $('#upload-file').on('change', function () {
        const file = this.files && this.files[0];
        if (!file) return;
        const url = URL.createObjectURL(file);
        $('#upload-preview-img').attr('src', url);
        $('#upload-preview').removeClass('hidden');
    });
    $('#btn-upload').on('click', function () {
        const $btn = $(this);
        const original = $btn.text();
        const fileInput = document.getElementById('upload-file');
        const file = fileInput.files && fileInput.files[0];
        const target = $('#upload-target').val();
        if (!file) {
            toast.error('Vui lòng chọn file');
            return;
        }
        const formData = new FormData();
        formData.append('file', file);
        formData.append('target', target);
        formData.append('csrf_token', window.CSRF_TOKEN || '');
        $btn.prop('disabled', true).addClass('opacity-70 cursor-not-allowed').text('Đang tải...');
        $.ajax({
            url: '/api/controller/upload',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function (data) {
                if (data && data.status === 'success') {
                    const url = data.url;
                    if (target === 'avatar') {
                        $('#avatar').val(url);
                        $('#avatar-preview').attr('src', url);
                    } else {
                        $('#banner').val(url);
                        $('#banner-preview').attr('src', url);
                    }
                    toast.success(data.message || 'Upload thành công');

                    if (uploadDialog) uploadDialog.close();
                    else $('#upload-dialog').attr('data-state', 'closed').addClass('hidden').removeClass('flex');
                } else {
                    toast.error(data?.message || 'Upload thất bại');
                }
            },
            error: function () {
                toast.error('Không thể kết nối tới máy chủ');
            },
            complete: function () {
                $btn.prop('disabled', false).removeClass('opacity-70 cursor-not-allowed').text(original);
            }
        });
    });
    $('#profile-form').on('submit', function(e) {
        e.preventDefault();
        const $btn = $(this).find('button[type="submit"]');
        const originalBtnHtml = $btn.html();

        $btn.prop('disabled', true).addClass('opacity-70 cursor-not-allowed')
            .html('<span>Đang lưu...</span>');

        const $form = $(this);
        if ($form.find('input[name="csrf_token"]').length === 0) {
            $form.append('<input type="hidden" name="csrf_token" value="' + (window.CSRF_TOKEN || '') + '">');
        }

        $.post(
            '/api/controller/app',
            $form.serialize(),
            function (data) {
                $btn.prop('disabled', false).removeClass('opacity-70 cursor-not-allowed')
                    .html(originalBtnHtml);

                if (data && data.status === 'error') {
                    toast.error(data.message);
                    return;
                }
                toast.success(data.message || 'Lưu thành công');
            },
            'json'
        ).fail(function () {
            $btn.prop('disabled', false).removeClass('opacity-70 cursor-not-allowed')
                .html(originalBtnHtml);
            toast.error('Không thể kết nối tới máy chủ');
        });
    });
    $('#images-form').on('submit', function(e) {
        e.preventDefault();

        const $btn = $(this).find('button[type=submit]');
        const originalBtnHtml = $btn.html();

        $btn.prop('disabled', true);
        $btn.addClass('opacity-70 cursor-not-allowed');
        $btn.html('<span>Đang lưu ...</span>');

        const $form = $(this);
        if ($form.find('input[name="csrf_token"]').length === 0) {
            $form.append('<input type="hidden" name="csrf_token" value="' + (window.CSRF_TOKEN || '') + '">');
        }

        $.post(
            '/api/controller/app',
            $form.serialize(),
            function (data) {
                $btn.prop('disabled', false);
                $btn.removeClass('opacity-70 cursor-not-allowed');
                $btn.html(originalBtnHtml);

                if (data && data.status == 'error') {
                    toast.error(data.message);
                    return;
                }

                const avatarUrl = $('#avatar').val();
                const bannerUrl = $('#banner').val();
                if (avatarUrl) $('#avatar-preview').attr('src', avatarUrl);
                if (bannerUrl) $('#banner-preview').attr('src', bannerUrl);

                toast.success(data.message || 'Lưu thành công');
            },
            'json'
        ).fail(function () {
            $btn.prop('disabled', false);
            $btn.removeClass('opacity-70 cursor-not-allowed');
            $btn.html(originalBtnHtml);

            toast.error('Không thể kết nối tới máy chủ');
        });
    });
    $('#password-form').on('submit', function(e) {
        e.preventDefault();

        const $btn = $(this).find('button[type=submit]');
        const originalBtnHtml = $btn.html();

        $btn.prop('disabled', true);
        $btn.addClass('opacity-70 cursor-not-allowed');
        $btn.html('<span>Đang cập nhật ...</span>');

        const $form = $(this);
        if ($form.find('input[name="csrf_token"]').length === 0) {
            $form.append('<input type="hidden" name="csrf_token" value="' + (window.CSRF_TOKEN || '') + '">');
        }

        $.post(
            '/api/controller/app',
            $form.serialize(),
            function (data) {
                $btn.prop('disabled', false);
                $btn.removeClass('opacity-70 cursor-not-allowed');
                $btn.html(originalBtnHtml);

                if (data && data.status == 'error') {
                    toast.error(data.message);
                    return;
                }

                $('#current_password, #new_password, #confirm_password').val('');
                toast.success(data.message || 'Cập nhật thành công');
            },
            'json'
        ).fail(function () {
            $btn.prop('disabled', false);
            $btn.removeClass('opacity-70 cursor-not-allowed');
            $btn.html(originalBtnHtml);

            toast.error('Không thể kết nối tới máy chủ');
        });
    });
    $('#avatar, #banner').on('change', function() {
        const url = $(this).val();
        if (url) {
            const img = new Image();
            img.onload = function() {
                if (this.width > 0) {
                    if ($(this).attr('id') === 'avatar') {
                        $('#avatar-preview').attr('src', url);
                    } else {
                        $('#banner-preview').attr('src', url);
                    }
                }
            };
            img.onerror = function() {
                toast.error('Lỗi', { description: 'Không thể tải ảnh từ URL này' });
            };
            img.src = url;
        }
    });
});
</script>

<?php require $_SERVER['DOCUMENT_ROOT'] . '/views/layouts/_application/AppFooter.php'; ?>