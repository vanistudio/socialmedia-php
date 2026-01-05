<?php
require $_SERVER['DOCUMENT_ROOT'] . '/views/layouts/_administrator/AdHeader.php';

$keywords = $Vani->get_list("SELECT * FROM blacklist_keywords ORDER BY created_at DESC");
?>

<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-foreground">Blacklist Keywords</h1>
            <p class="text-sm text-muted-foreground">Quản lý từ khóa bị cấm trong hệ thống</p>
        </div>
        <button type="button" onclick="openAddKeywordDialog()" class="h-10 px-4 rounded-xl bg-red-500 text-white hover:bg-red-600 transition text-sm font-medium flex items-center justify-center gap-2 w-full sm:w-auto">
            <iconify-icon icon="solar:add-circle-linear" width="18"></iconify-icon>
            <span>Thêm từ khóa</span>
        </button>
    </div>

    <div class="bg-card border border-border rounded-2xl overflow-hidden">
        <div class="p-4 border-b border-border">
            <div class="relative">
                <iconify-icon icon="solar:magnifer-linear" class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground" width="18"></iconify-icon>
                <input type="text" id="search-keyword" placeholder="Tìm kiếm từ khóa..." class="w-full h-10 pl-10 pr-4 rounded-lg border border-input bg-background text-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-red-500/30">
            </div>
        </div>

        <div class="divide-y divide-border" id="keywords-list">
            <?php if (empty($keywords)): ?>
            <div class="p-12 text-center">
                <iconify-icon icon="solar:forbidden-circle-linear" width="48" class="text-muted-foreground mx-auto mb-4"></iconify-icon>
                <p class="text-muted-foreground">Chưa có từ khóa nào</p>
            </div>
            <?php else: ?>
            <?php foreach ($keywords as $kw): ?>
            <div class="p-4 keyword-item" data-keyword="<?php echo htmlspecialchars(strtolower($kw['keyword'])); ?>">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div class="flex items-center gap-3 flex-wrap">
                        <span class="px-3 py-1.5 rounded-lg text-sm font-medium <?php echo $kw['active'] ? 'bg-red-500/15 text-red-500' : 'bg-gray-500/15 text-gray-500'; ?>">
                            <?php echo htmlspecialchars($kw['keyword']); ?>
                        </span>
                        <?php if (!$kw['active']): ?>
                        <span class="text-xs text-muted-foreground">(Đã tắt)</span>
                        <?php endif; ?>
                    </div>
                    <div class="flex items-center gap-1 shrink-0">
                        <button type="button" onclick="toggleKeyword(<?php echo $kw['id']; ?>, <?php echo $kw['active'] ? 0 : 1; ?>)" class="h-9 w-9 rounded-lg hover:bg-accent transition flex items-center justify-center text-muted-foreground" title="<?php echo $kw['active'] ? 'Tắt' : 'Bật'; ?>">
                            <iconify-icon icon="<?php echo $kw['active'] ? 'solar:eye-closed-linear' : 'solar:eye-linear'; ?>" width="18"></iconify-icon>
                        </button>
                        <button type="button" onclick="editKeyword(<?php echo $kw['id']; ?>, '<?php echo htmlspecialchars(addslashes($kw['keyword'])); ?>')" class="h-9 w-9 rounded-lg hover:bg-accent transition flex items-center justify-center text-muted-foreground" title="Sửa">
                            <iconify-icon icon="solar:pen-linear" width="18"></iconify-icon>
                        </button>
                        <button type="button" onclick="deleteKeyword(<?php echo $kw['id']; ?>)" class="h-9 w-9 rounded-lg hover:bg-red-500/10 transition flex items-center justify-center text-red-500" title="Xóa">
                            <iconify-icon icon="solar:trash-bin-trash-linear" width="18"></iconify-icon>
                        </button>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <p class="text-sm text-muted-foreground text-center">
        Tổng cộng: <span class="font-medium"><?php echo count($keywords); ?></span> từ khóa
    </p>
</div>

<div id="keyword-dialog" class="hidden fixed inset-0 z-50 items-center justify-center" data-dialog>
    <div class="absolute inset-0 bg-black/50" data-dialog-backdrop></div>
    <div class="relative w-full max-w-md mx-auto" data-dialog-content>
        <div class="bg-card border border-border rounded-2xl shadow-lg" data-dialog-inner>
            <div class="flex items-center justify-between p-4 border-b border-border">
                <h3 class="text-lg font-semibold text-foreground" id="dialog-title">Thêm từ khóa</h3>
                <button type="button" class="h-9 w-9 rounded-lg hover:bg-accent transition flex items-center justify-center" onclick="closeKeywordDialog()">
                    <iconify-icon icon="solar:close-circle-linear" width="22"></iconify-icon>
                </button>
            </div>
            <form id="keyword-form" class="p-4 space-y-4">
                <input type="hidden" id="keyword-id" value="">
                <div class="space-y-2">
                    <label for="keyword-input" class="text-sm font-medium text-foreground">Từ khóa</label>
                    <input type="text" id="keyword-input" class="w-full h-10 rounded-lg border border-input bg-background px-3 text-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-red-500/30" placeholder="Nhập từ khóa..." required>
                </div>
                <div class="flex items-center justify-end gap-2">
                    <button type="button" onclick="closeKeywordDialog()" class="h-10 px-4 rounded-lg border border-input bg-card hover:bg-accent transition text-sm font-medium">Hủy</button>
                    <button type="submit" class="h-10 px-4 rounded-lg bg-red-500 text-white hover:bg-red-600 transition text-sm font-medium">Lưu</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let keywordDialog = null;

$(document).ready(function() {
    if (window.initDialog) {
        keywordDialog = window.initDialog('keyword-dialog');
    }

    $('#search-keyword').on('input', function() {
        const query = $(this).val().toLowerCase().trim();
        $('.keyword-item').each(function() {
            const keyword = $(this).data('keyword');
            if (keyword.includes(query)) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    });

    $('#keyword-form').on('submit', function(e) {
        e.preventDefault();
        const id = $('#keyword-id').val();
        const keyword = $('#keyword-input').val().trim();

        if (!keyword) {
            toast.error('Vui lòng nhập từ khóa');
            return;
        }

        const type = id ? 'UPDATE_BLACKLIST_KEYWORD' : 'ADD_BLACKLIST_KEYWORD';
        const data = {
            type: type,
            keyword: keyword,
            csrf_token: window.CSRF_TOKEN || ''
        };
        if (id) {
            data.id = id;
            data.active = 1;
        }

        $.post('/api/controller/admin', data, function(res) {
            if (res.status === 'success') {
                toast.success(res.message || 'Thành công');
                closeKeywordDialog();
                setTimeout(() => window.location.reload(), 500);
            } else {
                toast.error(res.message || 'Có lỗi xảy ra');
            }
        }, 'json').fail(function() {
            toast.error('Không thể kết nối tới máy chủ');
        });
    });
});

function openAddKeywordDialog() {
    $('#dialog-title').text('Thêm từ khóa');
    $('#keyword-id').val('');
    $('#keyword-input').val('');
    if (keywordDialog) {
        keywordDialog.open();
    } else {
        $('#keyword-dialog').removeClass('hidden').addClass('flex');
    }
}

function editKeyword(id, keyword) {
    $('#dialog-title').text('Sửa từ khóa');
    $('#keyword-id').val(id);
    $('#keyword-input').val(keyword);
    if (keywordDialog) {
        keywordDialog.open();
    } else {
        $('#keyword-dialog').removeClass('hidden').addClass('flex');
    }
}

function closeKeywordDialog() {
    if (keywordDialog) {
        keywordDialog.close();
    } else {
        $('#keyword-dialog').addClass('hidden').removeClass('flex');
    }
}

function toggleKeyword(id, active) {
    $.post('/api/controller/admin', {
        type: 'UPDATE_BLACKLIST_KEYWORD',
        id: id,
        keyword: $('.keyword-item').filter(function() { return $(this).find('button[onclick*="toggleKeyword(' + id + '"]').length; }).find('span').first().text().trim(),
        active: active,
        csrf_token: window.CSRF_TOKEN || ''
    }, function(res) {
        if (res.status === 'success') {
            toast.success(active ? 'Đã bật từ khóa' : 'Đã tắt từ khóa');
            setTimeout(() => window.location.reload(), 500);
        } else {
            toast.error(res.message || 'Có lỗi xảy ra');
        }
    }, 'json');
}

function deleteKeyword(id) {
    if (!confirm('Bạn có chắc chắn muốn xóa từ khóa này?')) return;

    $.post('/api/controller/admin', {
        type: 'DELETE_BLACKLIST_KEYWORD',
        id: id,
        csrf_token: window.CSRF_TOKEN || ''
    }, function(res) {
        if (res.status === 'success') {
            toast.success('Đã xóa từ khóa');
            setTimeout(() => window.location.reload(), 500);
        } else {
            toast.error(res.message || 'Có lỗi xảy ra');
        }
    }, 'json');
}
</script>

<?php require $_SERVER['DOCUMENT_ROOT'] . '/views/layouts/_administrator/AdFooter.php'; ?>

