<?php
require $_SERVER['DOCUMENT_ROOT'] . '/views/layouts/_administrator/AdHeader.php';

$page = intval($_GET['page'] ?? 1);
$status = $_GET['status'] ?? 'open';
$limit = 20;
$offset = ($page - 1) * $limit;

$statusFilter = "1=1";
if ($status === 'open') {
    $statusFilter = "r.status = 'open'";
} elseif ($status === 'resolved') {
    $statusFilter = "r.status = 'resolved'";
} elseif ($status === 'dismissed') {
    $statusFilter = "r.status = 'dismissed'";
}

$reports = $Vani->get_list("
    SELECT 
        r.*,
        u.username as reporter_username,
        u.full_name as reporter_name,
        u.avatar as reporter_avatar
    FROM reports r
    JOIN users u ON r.reporter_id = u.id
    WHERE $statusFilter
    ORDER BY r.created_at DESC
    LIMIT $limit OFFSET $offset
");

$total = $Vani->num_rows("SELECT id FROM reports WHERE " . str_replace('r.', '', $statusFilter)) ?: 0;
$totalPages = ceil($total / $limit);
?>

<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-foreground">Reports</h1>
            <p class="text-sm text-muted-foreground">Quản lý báo cáo vi phạm từ người dùng</p>
        </div>
    </div>

    <div class="overflow-x-auto -mx-4 px-4 sm:mx-0 sm:px-0">
        <div class="flex items-center gap-1 sm:gap-2 border-b border-border min-w-max">
            <a href="/admin/reports?status=open" class="px-3 sm:px-4 py-3 text-sm font-medium border-b-2 <?php echo $status === 'open' ? 'border-red-500 text-red-500' : 'border-transparent text-muted-foreground hover:text-foreground'; ?> transition flex items-center gap-1">
                <span>Open</span>
                <?php 
                $openCount = $Vani->num_rows("SELECT id FROM reports WHERE status = 'open'") ?: 0;
                if ($openCount > 0): ?>
                <span class="px-1.5 py-0.5 text-xs rounded-full bg-red-500 text-white"><?php echo $openCount; ?></span>
                <?php endif; ?>
            </a>
            <a href="/admin/reports?status=resolved" class="px-3 sm:px-4 py-3 text-sm font-medium border-b-2 <?php echo $status === 'resolved' ? 'border-red-500 text-red-500' : 'border-transparent text-muted-foreground hover:text-foreground'; ?> transition">
                Resolved
            </a>
            <a href="/admin/reports?status=dismissed" class="px-3 sm:px-4 py-3 text-sm font-medium border-b-2 <?php echo $status === 'dismissed' ? 'border-red-500 text-red-500' : 'border-transparent text-muted-foreground hover:text-foreground'; ?> transition">
                Dismissed
            </a>
            <a href="/admin/reports?status=all" class="px-3 sm:px-4 py-3 text-sm font-medium border-b-2 <?php echo $status === 'all' ? 'border-red-500 text-red-500' : 'border-transparent text-muted-foreground hover:text-foreground'; ?> transition">
                All
            </a>
        </div>
    </div>

    <?php if (empty($reports)): ?>
    <div class="bg-card border border-border rounded-2xl p-12 text-center">
        <iconify-icon icon="solar:check-circle-linear" width="48" class="text-muted-foreground mx-auto mb-4"></iconify-icon>
        <p class="text-muted-foreground">Không có báo cáo nào</p>
    </div>
    <?php else: ?>
    <div class="space-y-4">
        <?php foreach ($reports as $report): 
            $reportedContent = null;
            $reportedUser = null;
            
            if ($report['target_type'] === 'user') {
                $reportedUser = $Vani->get_row("SELECT * FROM users WHERE id = '" . intval($report['target_id']) . "'");
            } elseif ($report['target_type'] === 'post') {
                $reportedContent = $Vani->get_row("SELECT p.*, u.username, u.full_name, u.avatar FROM posts p JOIN users u ON p.user_id = u.id WHERE p.id = '" . intval($report['target_id']) . "'");
            } elseif ($report['target_type'] === 'comment') {
                $reportedContent = $Vani->get_row("SELECT c.*, u.username, u.full_name, u.avatar FROM post_comments c JOIN users u ON c.user_id = u.id WHERE c.id = '" . intval($report['target_id']) . "'");
            }
        ?>
        <div class="bg-card border border-border rounded-2xl overflow-hidden" id="report-<?php echo $report['id']; ?>">
            <div class="p-4 border-b border-border flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <img src="<?php echo htmlspecialchars($report['reporter_avatar'] ?? 'https://placehold.co/200x200'); ?>" alt="Avatar" class="h-10 w-10 rounded-full object-cover">
                    <div>
                        <p class="font-medium text-foreground"><?php echo htmlspecialchars($report['reporter_name']); ?> <span class="text-muted-foreground font-normal">đã báo cáo</span></p>
                        <p class="text-xs text-muted-foreground"><?php echo ucfirst($report['target_type']); ?> · <?php echo date('d/m/Y H:i', strtotime($report['created_at'])); ?></p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <?php if ($report['status'] === 'resolved'): ?>
                        <span class="px-3 py-1 rounded-full text-xs font-medium bg-green-500/15 text-green-500">Resolved</span>
                    <?php elseif ($report['status'] === 'dismissed'): ?>
                        <span class="px-3 py-1 rounded-full text-xs font-medium bg-gray-500/15 text-gray-500">Dismissed</span>
                    <?php else: ?>
                        <span class="px-3 py-1 rounded-full text-xs font-medium bg-orange-500/15 text-orange-500">Open</span>
                    <?php endif; ?>
                </div>
            </div>

            <div class="p-4">
                <div class="mb-4">
                    <p class="text-xs text-muted-foreground mb-1">Lý do báo cáo:</p>
                    <span class="px-3 py-1 rounded-lg text-sm font-medium bg-red-500/15 text-red-500"><?php echo htmlspecialchars($report['reason']); ?></span>
                    <?php if (!empty($report['detail'])): ?>
                    <p class="mt-2 text-sm text-foreground"><?php echo htmlspecialchars($report['detail']); ?></p>
                    <?php endif; ?>
                </div>
                <div class="bg-background rounded-xl p-4 mb-4">
                    <?php if ($report['target_type'] === 'user' && $reportedUser): ?>
                    <div class="flex items-center gap-3">
                        <img src="<?php echo htmlspecialchars($reportedUser['avatar'] ?? 'https://placehold.co/200x200'); ?>" alt="Avatar" class="h-12 w-12 rounded-full object-cover">
                        <div>
                            <p class="font-medium text-foreground"><?php echo htmlspecialchars($reportedUser['full_name']); ?></p>
                            <p class="text-sm text-muted-foreground">@<?php echo htmlspecialchars($reportedUser['username']); ?></p>
                        </div>
                        <a href="/u/<?php echo htmlspecialchars($reportedUser['username']); ?>" target="_blank" class="ml-auto h-8 px-3 rounded-lg border border-input bg-card hover:bg-accent transition text-sm flex items-center gap-1">
                            <iconify-icon icon="solar:eye-linear" width="16"></iconify-icon>
                            <span>Xem</span>
                        </a>
                    </div>
                    <?php elseif ($reportedContent): ?>
                    <div class="flex items-center gap-2 mb-2">
                        <img src="<?php echo htmlspecialchars($reportedContent['avatar'] ?? 'https://placehold.co/200x200'); ?>" alt="Avatar" class="h-6 w-6 rounded-full object-cover">
                        <span class="text-sm font-medium text-foreground"><?php echo htmlspecialchars($reportedContent['full_name']); ?></span>
                        <span class="text-xs text-muted-foreground">@<?php echo htmlspecialchars($reportedContent['username']); ?></span>
                    </div>
                    <p class="text-sm text-foreground whitespace-pre-wrap"><?php echo htmlspecialchars($reportedContent['content']); ?></p>
                    <?php if ($report['target_type'] === 'post'): ?>
                    <a href="/post/<?php echo $reportedContent['id']; ?>" target="_blank" class="inline-flex items-center gap-1 mt-2 text-sm text-vanixjnk hover:underline">
                        <iconify-icon icon="solar:eye-linear" width="16"></iconify-icon>
                        Xem bài viết
                    </a>
                    <?php endif; ?>
                    <?php else: ?>
                    <p class="text-sm text-muted-foreground italic">Nội dung đã bị xóa hoặc không tồn tại</p>
                    <?php endif; ?>
                </div>

                <?php if ($report['status'] === 'open'): ?>
                <div class="flex flex-wrap items-center gap-2">
                    <button type="button" onclick="resolveReport(<?php echo $report['id']; ?>, 'resolved')" class="h-9 px-3 sm:px-4 rounded-lg bg-green-500 text-white hover:bg-green-600 transition text-sm font-medium flex items-center gap-1.5 sm:gap-2">
                        <iconify-icon icon="solar:check-circle-linear" width="16"></iconify-icon>
                        <span>Resolve</span>
                    </button>
                    <button type="button" onclick="resolveReport(<?php echo $report['id']; ?>, 'dismissed')" class="h-9 px-3 sm:px-4 rounded-lg border border-input bg-card hover:bg-accent transition text-sm font-medium flex items-center gap-1.5 sm:gap-2">
                        <iconify-icon icon="solar:close-circle-linear" width="16"></iconify-icon>
                        <span>Dismiss</span>
                    </button>
                    <?php if ($reportedContent || $reportedUser): ?>
                    <button type="button" onclick="deleteReportedContent(<?php echo $report['id']; ?>, '<?php echo $report['target_type']; ?>', <?php echo $report['target_id']; ?>)" class="h-9 px-3 sm:px-4 rounded-lg bg-red-500 text-white hover:bg-red-600 transition text-sm font-medium flex items-center gap-1.5 sm:gap-2">
                        <iconify-icon icon="solar:trash-bin-trash-linear" width="16"></iconify-icon>
                        <span class="hidden xs:inline">Xóa nội dung</span>
                        <span class="xs:hidden">Xóa</span>
                    </button>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <?php if ($totalPages > 1): ?>
    <div class="flex items-center justify-center gap-2">
        <?php if ($page > 1): ?>
        <a href="/admin/reports?status=<?php echo $status; ?>&page=<?php echo $page - 1; ?>" class="h-10 px-4 rounded-lg border border-input bg-card hover:bg-accent transition text-sm font-medium">Trước</a>
        <?php endif; ?>
        <span class="text-sm text-muted-foreground">Trang <?php echo $page; ?> / <?php echo $totalPages; ?></span>
        <?php if ($page < $totalPages): ?>
        <a href="/admin/reports?status=<?php echo $status; ?>&page=<?php echo $page + 1; ?>" class="h-10 px-4 rounded-lg border border-input bg-card hover:bg-accent transition text-sm font-medium">Sau</a>
        <?php endif; ?>
    </div>
    <?php endif; ?>
    <?php endif; ?>
</div>

<script>
function resolveReport(reportId, status) {
    $.post('/api/controller/admin', {
        type: 'RESOLVE_REPORT',
        report_id: reportId,
        report_status: status,
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

function deleteReportedContent(reportId, entityType, entityId) {
    if (!confirm('Xác nhận xóa nội dung này? Hành động này không thể hoàn tác.')) {
        return;
    }

    $.post('/api/controller/admin', {
        type: 'DELETE_REPORTED_CONTENT',
        report_id: reportId,
        entity_type: entityType,
        entity_id: entityId,
        csrf_token: window.CSRF_TOKEN || ''
    }, function(data) {
        if (data.status === 'success') {
            toast.success('Đã xóa nội dung và giải quyết báo cáo');
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

