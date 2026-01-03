        </main>
        <?php if (isset($_SESSION['email']) && !empty($_SESSION['email'])):
            include 'AppDock.php';
        endif; ?>
        <footer class="border-t border-border bg-background">
            <div class="container mx-auto px-4 py-6 flex flex-col sm:flex-row items-center justify-between gap-4 text-sm text-muted-foreground">
                <div class="flex items-center gap-2">
                    <div class="h-7 w-7 rounded-lg bg-vanixjnk/15 flex items-center justify-center">
                        <iconify-icon icon="solar:chat-round-like-linear" class="text-vanixjnk" width="18"></iconify-icon>
                    </div>
                    <span>© <?php echo date('Y'); ?> Vanix Social</span>
                </div>

                <div class="flex items-center gap-4">
                    <a href="/about" class="hover:text-vanixjnk transition">Giới thiệu</a>
                    <a href="/privacy" class="hover:text-vanixjnk transition">Quyền riêng tư</a>
                    <a href="/terms" class="hover:text-vanixjnk transition">Điều khoản</a>
                </div>
            </div>
        </footer>
    </div>
    <?php if (isset($_SESSION['email']) && !empty($_SESSION['email'])): ?>
    <div id="create-post-dialog" class="hidden fixed inset-0 z-50 items-center justify-center" data-dialog>
        <div class="absolute inset-0 bg-black/50" data-dialog-backdrop></div>
        <div class="relative w-full max-w-xl mx-auto" data-dialog-content>
            <div class="bg-card border border-border rounded-2xl shadow-lg" data-dialog-inner>
                <div class="flex items-center justify-between p-4 border-b border-border">
                    <h3 class="text-lg font-semibold text-foreground">Tạo bài viết</h3>
                    <button type="button" class="h-9 w-9 rounded-lg hover:bg-accent transition flex items-center justify-center" data-dialog-close>
                        <iconify-icon icon="solar:close-circle-linear" width="22"></iconify-icon>
                    </button>
                </div>

                <form id="dialog-create-post-form" class="p-4">
                    <input type="hidden" name="type" value="CREATE_POST">
                    <div class="flex items-start gap-4">
                        <img src="<?php echo htmlspecialchars($currentUser['avatar'] ?? 'https://placehold.co/200x200'); ?>" alt="Avatar" class="h-10 w-10 rounded-full object-cover">
                        <div class="flex-1">
                            <textarea name="content" placeholder="Bạn đang nghĩ gì?" class="w-full bg-transparent text-foreground placeholder:text-muted-foreground outline-none resize-none text-lg min-h-[120px]"></textarea>
                            <div id="dialog-post-media-previews" class="mt-2 grid grid-cols-2 md:grid-cols-4 gap-2"></div>
                        </div>
                    </div>
                    <div class="flex justify-between items-center mt-4 pt-4 border-t border-border">
                        <div class="flex gap-1 text-muted-foreground">
                            <button type="button" class="h-9 w-9 rounded-lg hover:bg-accent hover:text-vanixjnk transition flex items-center justify-center" aria-label="Add media" onclick="$('#dialog-post-media-upload').click()">
                                <iconify-icon icon="solar:gallery-wide-linear" width="20"></iconify-icon>
                            </button>
                            <input type="file" id="dialog-post-media-upload" accept="image/*,video/*" class="hidden" multiple>
                        </div>
                        <button type="submit" class="h-9 px-4 rounded-lg bg-vanixjnk text-white hover:bg-vanixjnk/90 transition text-sm font-medium">Đăng bài</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>
    <script>
        $(document).ready(function() {
            const createPostDialog = window.initDialog ? window.initDialog('create-post-dialog') : null;
            let dialogPostMediaFiles = [];
            $(document).on('click', '[data-action="open-create-post-dialog"]', function() {
                if (createPostDialog) {
                    $('#dialog-create-post-form')[0].reset();
                    $('#dialog-post-media-previews').empty();
                    dialogPostMediaFiles = [];
                    createPostDialog.open();
                }
            });

            $('#dialog-post-media-upload').on('change', function(e) {
                dialogPostMediaFiles = Array.from(e.target.files);
                const previews = $('#dialog-post-media-previews');
                previews.empty();
                dialogPostMediaFiles.forEach(file => {
                    const url = URL.createObjectURL(file);
                    previews.append(`<div class="relative"><img src="${url}" class="h-24 w-full object-cover rounded-lg"></div>`);
                });
            });

            $('#dialog-create-post-form').on('submit', function(e) {
                e.preventDefault();
                const $form = $(this);
                const $btn = $form.find('button[type=submit]');
                const originalBtnText = $btn.text();
                const content = $form.find('textarea[name=content]').val();

                if (content.trim() === '' && dialogPostMediaFiles.length === 0) {
                    toast.error('Bài viết không được để trống');
                    return;
                }

                $btn.prop('disabled', true).addClass('opacity-70').text('Đang đăng...');

                let mediaUploadPromises = dialogPostMediaFiles.map(file => {
                    const formData = new FormData();
                    formData.append('file', file);
                    formData.append('csrf_token', window.CSRF_TOKEN || '');
                    return $.ajax({
                        url: '/api/controller/upload',
                        type: 'POST',
                        data: formData,
                        processData: false,
                        contentType: false,
                        dataType: 'json'
                    });
                });

                Promise.all(mediaUploadPromises).then(results => {
                    let mediaUrls = results.map(res => res.url);
                    let postData = $form.serializeArray();
                    mediaUrls.forEach(url => {
                        postData.push({ name: 'media[]', value: url });
                    });

                    $.post('/api/controller/app', postData, function(data) {
                        if (data.status === 'success') {
                            toast.success(data.message);
                            if (createPostDialog) createPostDialog.close();
                            setTimeout(() => window.location.reload(), 800);
                        } else {
                            toast.error(data.message || 'Có lỗi xảy ra');
                        }
                    }, 'json').fail(() => {
                        toast.error('Không thể kết nối');
                    }).always(() => {
                        $btn.prop('disabled', false).removeClass('opacity-70').text(originalBtnText);
                    });

                }).catch(() => {
                    toast.error('Upload media thất bại');
                    $btn.prop('disabled', false).removeClass('opacity-70').text(originalBtnText);
                });
            });
        });

        // Load unread messages count
        function loadUnreadMessagesCount() {
            <?php if (isset($_SESSION['email']) && !empty($_SESSION['email'])): ?>
            $.post('/api/controller/app', {
                type: 'GET_UNREAD_COUNT'
            }, function(data) {
                if (data && data.status === 'success' && data.unread_count !== undefined) {
                    const $badge = $('#unread-messages-badge');
                    if (data.unread_count > 0) {
                        $badge.text(data.unread_count > 99 ? '99+' : data.unread_count).removeClass('hidden');
                    } else {
                        $badge.addClass('hidden');
                    }
                }
            }, 'json').fail(function() {
                // Silent fail
            });
            <?php endif; ?>
        }

        // Load notification count
        function loadNotificationCount() {
            <?php if ($__isLoggedIn): ?>
            $.post('/api/controller/app', { type: 'GET_NOTIFICATIONS', limit: 1 }, function(data) {
                if (data.status === 'success' && data.unread_count > 0) {
                    $('#notifications-badge').text(data.unread_count).removeClass('hidden');
                } else {
                    $('#notifications-badge').addClass('hidden');
                }
            }, 'json').fail(function() {
                // Silent fail
            });
            <?php endif; ?>
        }

        // Load unread count on page load
        $(document).ready(function() {
            loadUnreadMessagesCount();
            loadNotificationCount();
            // Refresh every 10 seconds
            setInterval(loadUnreadMessagesCount, 10000);
            setInterval(loadNotificationCount, 10000);
        });
    </script>
</body>

</html>