<?php
require $_SERVER['DOCUMENT_ROOT'] . '/config/database.php';
require $_SERVER['DOCUMENT_ROOT'] . '/config/function.php';

if (!isset($_SESSION['email'])) {
    header('Location: /login');
    exit;
}

$currentUser = $Vani->get_row("SELECT * FROM `users` WHERE `email` = '" . addslashes($_SESSION['email']) . "'");
$currentUserId = intval($currentUser['id']);

require $_SERVER['DOCUMENT_ROOT'] . '/views/layouts/_application/AppHeader.php';
?>

<div class="w-full max-w-3xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-foreground">Thông báo</h1>
            <p class="text-sm text-muted-foreground">Tất cả thông báo của bạn</p>
        </div>
        <button type="button" id="mark-all-read-btn" class="h-10 px-4 rounded-lg border border-input bg-card hover:bg-accent transition text-sm font-medium flex items-center gap-2">
            <iconify-icon icon="solar:check-read-linear" width="18"></iconify-icon>
            <span>Đánh dấu tất cả đã đọc</span>
        </button>
    </div>

    <div id="notifications-container" class="space-y-2">
        <div class="text-center py-12">
            <p class="text-muted-foreground">Đang tải thông báo...</p>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    function loadNotifications() {
        $.post('/api/controller/app', { type: 'GET_NOTIFICATIONS', limit: 50, csrf_token: window.CSRF_TOKEN || '' }, function(data) {
            if (data.status === 'success') {
                const notifications = data.notifications || [];
                const $container = $('#notifications-container');
                
                if (notifications.length === 0) {
                    $container.html('<div class="text-center py-12"><p class="text-muted-foreground">Chưa có thông báo nào</p></div>');
                    return;
                }
                
                let html = '';
                notifications.forEach(function(notif) {
                    const avatar = notif.avatar || 'https://placehold.co/200x200/png';
                    const isRead = notif.is_read == 1;
                    const bgClass = isRead ? 'bg-card' : 'bg-vanixjnk/5';
                    
                    html += `
                        <div class="bg-card border border-border rounded-xl p-4 hover:bg-accent transition ${bgClass}" data-notification-id="${notif.id}">
                            <div class="flex items-start gap-3">
                                <a href="/u/${notif.username || ''}" class="shrink-0">
                                    <img src="${avatar}" alt="Avatar" class="h-10 w-10 rounded-full object-cover">
                                </a>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm text-foreground">${notif.message || 'Có thông báo mới'}</p>
                                    <p class="text-xs text-muted-foreground mt-1">${formatTime(notif.created_at)}</p>
                                </div>
                                ${!isRead ? '<div class="h-2 w-2 rounded-full bg-vanixjnk shrink-0 mt-2"></div>' : ''}
                            </div>
                        </div>
                    `;
                });
                
                $container.html(html);
                
                $container.find('[data-notification-id]').on('click', function() {
                    const notifId = $(this).data('notification-id');
                    if ($(this).hasClass('bg-vanixjnk/5')) {
                        $.post('/api/controller/app', { type: 'MARK_NOTIFICATION_READ', notification_id: notifId, csrf_token: window.CSRF_TOKEN || '' }, function(data) {
                            if (data.status === 'success') {
                                $(this).removeClass('bg-vanixjnk/5').addClass('bg-card');
                                $(this).find('.rounded-full').remove();
                            }
                        }.bind(this), 'json');
                    }
                });
            } else {
                $('#notifications-container').html('<div class="text-center py-12"><p class="text-red-500">Có lỗi xảy ra khi tải thông báo</p></div>');
            }
        }, 'json').fail(function() {
            $('#notifications-container').html('<div class="text-center py-12"><p class="text-red-500">Không thể kết nối tới máy chủ</p></div>');
        });
    }
    
    function formatTime(timeStr) {
        const now = new Date();
        const time = new Date(timeStr);
        const diff = Math.floor((now - time) / 1000);
        
        if (diff < 60) return 'Vừa xong';
        if (diff < 3600) return Math.floor(diff / 60) + ' phút trước';
        if (diff < 86400) return Math.floor(diff / 3600) + ' giờ trước';
        if (diff < 604800) return Math.floor(diff / 86400) + ' ngày trước';
        return time.toLocaleDateString('vi-VN');
    }
    
    $('#mark-all-read-btn').on('click', function() {
        $.post('/api/controller/app', { type: 'MARK_NOTIFICATION_READ', notification_id: 0, csrf_token: window.CSRF_TOKEN || '' }, function(data) {
            if (data.status === 'success') {
                toast.success('Đã đánh dấu tất cả đã đọc');
                loadNotifications();
            } else {
                toast.error(data.message || 'Có lỗi xảy ra');
            }
        }, 'json').fail(() => toast.error('Không thể kết nối tới máy chủ'));
    });
    
    loadNotifications();
    
    setInterval(loadNotifications, 30000);
});
</script>

<?php require $_SERVER['DOCUMENT_ROOT'] . '/views/layouts/_application/AppFooter.php'; ?>

