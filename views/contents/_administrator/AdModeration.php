<?php
require $_SERVER['DOCUMENT_ROOT'] . '/views/layouts/_administrator/AdHeader.php';

$page = intval($_GET['page'] ?? 1);
$status = $_GET['status'] ?? 'pending';
$limit = 20;
$offset = ($page - 1) * $limit;

$statusFilter = "1=1";
if ($status === 'pending') {
    $statusFilter = "review_status IS NULL";
} elseif ($status === 'approved') {
    $statusFilter = "review_status = 'approved'";
} elseif ($status === 'rejected') {
    $statusFilter = "review_status = 'rejected'";
}

$logs = $Vani->get_list("
    SELECT 
        m.*,
        u.username,
        u.full_name,
        u.avatar,
        r.username as reviewer_username,
        r.full_name as reviewer_full_name
    FROM content_moderation_logs m
    JOIN users u ON m.user_id = u.id
    LEFT JOIN users r ON m.reviewed_by = r.id
    WHERE $statusFilter
    ORDER BY m.created_at DESC
    LIMIT $limit OFFSET $offset
");

$total = $Vani->num_rows("SELECT id FROM content_moderation_logs WHERE $statusFilter") ?: 0;
$totalPages = ceil($total / $limit);
?>

<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-foreground">Content Moderation</h1>
            <p class="text-sm text-muted-foreground">Quản lý nội dung bị flag bởi hệ thống</p>
        </div>
    </div>

    <!-- Tabs -->
    <div class="overflow-x-auto -mx-4 px-4 sm:mx-0 sm:px-0">
        <div class="flex items-center gap-1 sm:gap-2 border-b border-border min-w-max">
            <a href="/admin/moderation?status=pending" class="px-3 sm:px-4 py-3 text-sm font-medium border-b-2 <?php echo $status === 'pending' ? 'border-red-500 text-red-500' : 'border-transparent text-muted-foreground hover:text-foreground'; ?> transition flex items-center gap-1">
                <span>Pending</span>
                <?php 
                $pendingCount = $Vani->num_rows("SELECT id FROM content_moderation_logs WHERE review_status IS NULL") ?: 0;
                if ($pendingCount > 0): ?>
                <span class="px-1.5 py-0.5 text-xs rounded-full bg-red-500 text-white"><?php echo $pendingCount; ?></span>
                <?php endif; ?>
            </a>
            <a href="/admin/moderation?status=approved" class="px-3 sm:px-4 py-3 text-sm font-medium border-b-2 <?php echo $status === 'approved' ? 'border-red-500 text-red-500' : 'border-transparent text-muted-foreground hover:text-foreground'; ?> transition">
                Approved
            </a>
            <a href="/admin/moderation?status=rejected" class="px-3 sm:px-4 py-3 text-sm font-medium border-b-2 <?php echo $status === 'rejected' ? 'border-red-500 text-red-500' : 'border-transparent text-muted-foreground hover:text-foreground'; ?> transition">
                Rejected
            </a>
            <a href="/admin/moderation?status=all" class="px-3 sm:px-4 py-3 text-sm font-medium border-b-2 <?php echo $status === 'all' ? 'border-red-500 text-red-500' : 'border-transparent text-muted-foreground hover:text-foreground'; ?> transition">
                All
            </a>
        </div>
    </div>

    <?php if (empty($logs)): ?>
    <div class="bg-card border border-border rounded-2xl p-12 text-center">
        <iconify-icon icon="solar:shield-check-linear" width="48" class="text-muted-foreground mx-auto mb-4"></iconify-icon>
        <p class="text-muted-foreground">Không có nội dung nào cần review</p>
    </div>
    <?php else: ?>
    <div class="space-y-4">
        <?php foreach ($logs as $log): 
            $violations = json_decode($log['violations'] ?? '[]', true) ?: [];
            $blacklistKeywords = json_decode($log['blacklist_keywords'] ?? '[]', true) ?: [];
        ?>
        <div class="bg-card border border-border rounded-2xl overflow-hidden" id="log-<?php echo $log['id']; ?>">
            <div class="p-4 border-b border-border flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <img src="<?php echo htmlspecialchars($log['avatar'] ?? 'https://placehold.co/200x200'); ?>" alt="Avatar" class="h-10 w-10 rounded-full object-cover">
                    <div>
                        <p class="font-medium text-foreground"><?php echo htmlspecialchars($log['full_name']); ?></p>
                        <p class="text-xs text-muted-foreground">@<?php echo htmlspecialchars($log['username']); ?> · <?php echo $log['content_type']; ?> · <?php echo date('d/m/Y H:i', strtotime($log['created_at'])); ?></p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <?php if ($log['review_status'] === 'approved'): ?>
                        <span class="px-3 py-1 rounded-full text-xs font-medium bg-green-500/15 text-green-500">Approved</span>
                    <?php elseif ($log['review_status'] === 'rejected'): ?>
                        <span class="px-3 py-1 rounded-full text-xs font-medium bg-blue-500/15 text-blue-500">Rejected</span>
                    <?php else: ?>
                        <span class="px-3 py-1 rounded-full text-xs font-medium bg-orange-500/15 text-orange-500">Pending</span>
                    <?php endif; ?>
                </div>
            </div>

            <div class="p-4">
                <div class="bg-background rounded-xl p-4 mb-4">
                    <p class="text-foreground whitespace-pre-wrap"><?php echo htmlspecialchars($log['content']); ?></p>
                </div>

                <div class="flex flex-wrap gap-2 mb-4">
                    <?php foreach ($violations as $v): ?>
                    <span class="px-2 py-1 rounded-lg text-xs font-medium bg-red-500/15 text-red-500"><?php echo htmlspecialchars($v); ?></span>
                    <?php endforeach; ?>
                    <?php foreach ($blacklistKeywords as $k): ?>
                    <span class="px-2 py-1 rounded-lg text-xs font-medium bg-orange-500/15 text-orange-500">Blacklist: <?php echo htmlspecialchars($k); ?></span>
                    <?php endforeach; ?>
                    <span class="px-2 py-1 rounded-lg text-xs font-medium bg-gray-500/15 text-gray-500">Source: <?php echo htmlspecialchars($log['source']); ?></span>
                </div>

                <?php if ($log['review_status'] === null): ?>
                <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2">
                    <button type="button" onclick="reviewModeration(<?php echo $log['id']; ?>, 'approved')" class="h-10 sm:h-9 px-4 rounded-lg bg-green-500 text-white hover:bg-green-600 transition text-sm font-medium flex items-center justify-center gap-2">
                        <iconify-icon icon="solar:check-circle-linear" width="16"></iconify-icon>
                        <span class="hidden sm:inline">Approve (Xóa nội dung)</span>
                        <span class="sm:hidden">Approve & Xóa</span>
                    </button>
                    <button type="button" onclick="reviewModeration(<?php echo $log['id']; ?>, 'rejected')" class="h-10 sm:h-9 px-4 rounded-lg bg-blue-500 text-white hover:bg-blue-600 transition text-sm font-medium flex items-center justify-center gap-2">
                        <iconify-icon icon="solar:close-circle-linear" width="16"></iconify-icon>
                        <span class="hidden sm:inline">Reject (Giữ nội dung)</span>
                        <span class="sm:hidden">Reject & Giữ</span>
                    </button>
                </div>
                <?php else: ?>
                <p class="text-sm text-muted-foreground">
                    Reviewed by <span class="font-medium"><?php echo htmlspecialchars($log['reviewer_full_name'] ?? 'Unknown'); ?></span>
                    vào <?php echo date('d/m/Y H:i', strtotime($log['reviewed_at'])); ?>
                </p>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
    <div class="flex items-center justify-center gap-2">
        <?php if ($page > 1): ?>
        <a href="/admin/moderation?status=<?php echo $status; ?>&page=<?php echo $page - 1; ?>" class="h-10 px-4 rounded-lg border border-input bg-card hover:bg-accent transition text-sm font-medium">Trước</a>
        <?php endif; ?>
        <span class="text-sm text-muted-foreground">Trang <?php echo $page; ?> / <?php echo $totalPages; ?></span>
        <?php if ($page < $totalPages): ?>
        <a href="/admin/moderation?status=<?php echo $status; ?>&page=<?php echo $page + 1; ?>" class="h-10 px-4 rounded-lg border border-input bg-card hover:bg-accent transition text-sm font-medium">Sau</a>
        <?php endif; ?>
    </div>
    <?php endif; ?>
    <?php endif; ?>
</div>

<script>
function reviewModeration(logId, status) {
    if (!confirm(status === 'approved' ? 'Xác nhận xóa nội dung vi phạm?' : 'Xác nhận giữ lại nội dung?')) {
        return;
    }

    $.post('/api/controller/admin', {
        type: 'REVIEW_MODERATION',
        log_id: logId,
        review_status: status,
        csrf_token: window.CSRF_TOKEN || ''
    }, function(data) {
        if (data.status === 'success') {
            toast.success('Đã cập nhật');
            setTimeout(() => window.location.reload(), 500);
        } else {
            toast.error(data.message || 'Có lỗi xảy ra');
        }
    }, 'json').fail(function() {
        toast.error('Không thể kết nối tới máy chủ');
    });
}
</script>

<?php require $_SERVER['DOCUMENT_ROOT'] . '/views/layouts/_administrator/AdFooter.php'; ?>

