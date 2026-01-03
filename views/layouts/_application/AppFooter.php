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
                    <span>© <?php echo date('Y'); ?> Vani Social</span>
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
                    <input type="hidden" name="visibility" id="dialog-post-visibility" value="public">
                    <div class="flex items-start gap-4">
                        <img src="<?php echo htmlspecialchars($currentUser['avatar'] ?? 'https://placehold.co/200x200'); ?>" alt="Avatar" class="h-10 w-10 rounded-full object-cover">
                        <div class="flex-1">
                            <textarea name="content" placeholder="Bạn đang nghĩ gì?" class="w-full bg-transparent text-foreground placeholder:text-muted-foreground outline-none resize-none text-lg min-h-[120px]"></textarea>
                            <div id="dialog-post-media-previews" class="mt-2 grid grid-cols-2 md:grid-cols-4 gap-2"></div>
                        </div>
                    </div>
                    <div class="flex justify-between items-center mt-4 pt-4 border-t border-border">
                        <div class="flex items-center gap-2 text-muted-foreground">
                            <button type="button" class="h-9 w-9 rounded-lg hover:bg-accent hover:text-vanixjnk transition flex items-center justify-center" aria-label="Add media" onclick="$('#dialog-post-media-upload').click()">
                                <iconify-icon icon="solar:gallery-wide-linear" width="20"></iconify-icon>
                            </button>
                            <input type="file" id="dialog-post-media-upload" accept="image/*,video/*" class="hidden" multiple>
                            
                            <div class="relative" id="dialog-visibility-dropdown-container">
                                <button type="button" id="dialog-visibility-trigger" class="h-9 px-3 rounded-lg border border-input bg-background hover:bg-accent transition flex items-center gap-2 text-sm">
                                    <iconify-icon id="dialog-visibility-icon" icon="solar:earth-linear" width="16"></iconify-icon>
                                    <span id="dialog-visibility-text">Công khai</span>
                                    <iconify-icon icon="solar:alt-arrow-down-linear" width="14" class="text-muted-foreground"></iconify-icon>
                                </button>
                                <div id="dialog-visibility-dropdown" class="hidden absolute bottom-full left-0 mb-2 w-48 bg-card border border-border rounded-xl shadow-lg z-50">
                                    <ul class="py-1">
                                        <li>
                                            <button type="button" data-dialog-visibility="public" class="w-full text-left flex items-center gap-3 px-4 py-2 text-sm hover:bg-accent transition">
                                                <iconify-icon icon="solar:earth-linear" width="18"></iconify-icon>
                                                <div>
                                                    <span class="font-medium">Công khai</span>
                                                    <p class="text-xs text-muted-foreground">Mọi người có thể xem</p>
                                                </div>
                                            </button>
                                        </li>
                                        <li>
                                            <button type="button" data-dialog-visibility="followers" class="w-full text-left flex items-center gap-3 px-4 py-2 text-sm hover:bg-accent transition">
                                                <iconify-icon icon="solar:users-group-rounded-linear" width="18"></iconify-icon>
                                                <div>
                                                    <span class="font-medium">Người theo dõi</span>
                                                    <p class="text-xs text-muted-foreground">Chỉ người theo dõi</p>
                                                </div>
                                            </button>
                                        </li>
                                        <li>
                                            <button type="button" data-dialog-visibility="private" class="w-full text-left flex items-center gap-3 px-4 py-2 text-sm hover:bg-accent transition">
                                                <iconify-icon icon="solar:lock-linear" width="18"></iconify-icon>
                                                <div>
                                                    <span class="font-medium">Riêng tư</span>
                                                    <p class="text-xs text-muted-foreground">Chỉ mình tôi</p>
                                                </div>
                                            </button>
                                        </li>
                                    </ul>
                                </div>
                            </div>
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

            // Dialog visibility dropdown
            $('#dialog-visibility-trigger').on('click', function(e) {
                e.stopPropagation();
                $('#dialog-visibility-dropdown').toggleClass('hidden');
            });

            $('[data-dialog-visibility]').on('click', function() {
                const visibility = $(this).data('dialog-visibility');
                $('#dialog-post-visibility').val(visibility);
                
                const icons = {
                    'public': 'solar:earth-linear',
                    'followers': 'solar:users-group-rounded-linear',
                    'private': 'solar:lock-linear'
                };
                const texts = {
                    'public': 'Công khai',
                    'followers': 'Người theo dõi',
                    'private': 'Riêng tư'
                };
                
                $('#dialog-visibility-icon').attr('icon', icons[visibility]);
                $('#dialog-visibility-text').text(texts[visibility]);
                $('#dialog-visibility-dropdown').addClass('hidden');
            });

            $(document).on('click', function(e) {
                if (!$(e.target).closest('#dialog-visibility-dropdown-container').length) {
                    $('#dialog-visibility-dropdown').addClass('hidden');
                }
            });

            $(document).on('click', '[data-action="open-create-post-dialog"]', function() {
                if (createPostDialog) {
                    $('#dialog-create-post-form')[0].reset();
                    $('#dialog-post-media-previews').empty();
                    $('#dialog-post-visibility').val('public');
                    $('#dialog-visibility-icon').attr('icon', 'solar:earth-linear');
                    $('#dialog-visibility-text').text('Công khai');
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
                    let postDataArray = $form.serializeArray();
                    
                    let postData = {};
                    postDataArray.forEach(item => {
                        if (item.name.endsWith('[]')) {
                            const key = item.name.replace('[]', '');
                            if (!postData[key]) postData[key] = [];
                            postData[key].push(item.value);
                        } else {
                            postData[item.name] = item.value;
                        }
                    });
                    
                    if (mediaUrls.length > 0) {
                        postData.media = mediaUrls;
                    }
                    
                    const csrfToken = window.CSRF_TOKEN || '';
                    if (!csrfToken) {
                        toast.error('CSRF token không tồn tại. Vui lòng tải lại trang.');
                        $btn.prop('disabled', false).removeClass('opacity-70').text(originalBtnText);
                        return;
                    }
                    
                    postData.csrf_token = csrfToken;

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

        // Share post function
        window.sharePost = function(postId, authorName, content) {
            const url = `${window.location.origin}/post/${postId}`;
            const title = `Bài viết của ${authorName}`;
            const text = content || 'Xem bài viết này trên Vani Social';
            
            if (navigator.share) {
                navigator.share({
                    title: title,
                    text: text,
                    url: url
                }).then(() => {
                    toast.success('Đã chia sẻ thành công');
                }).catch((err) => {
                    if (err.name !== 'AbortError') {
                        copyToClipboard(url);
                    }
                });
            } else {
                copyToClipboard(url);
            }
        };

        function copyToClipboard(text) {
            if (navigator.clipboard) {
                navigator.clipboard.writeText(text).then(() => {
                    toast.success('Đã copy link bài viết');
                }).catch(() => {
                    fallbackCopyToClipboard(text);
                });
            } else {
                fallbackCopyToClipboard(text);
            }
        }

        function fallbackCopyToClipboard(text) {
            const textarea = document.createElement('textarea');
            textarea.value = text;
            textarea.style.position = 'fixed';
            textarea.style.opacity = '0';
            document.body.appendChild(textarea);
            textarea.select();
            try {
                document.execCommand('copy');
                toast.success('Đã copy link bài viết');
            } catch (err) {
                toast.error('Không thể copy link');
            }
            document.body.removeChild(textarea);
        }

        function loadUnreadMessagesCount() {
            <?php if (isset($_SESSION['email']) && !empty($_SESSION['email'])): ?>
            $.post('/api/controller/app', {
                type: 'GET_UNREAD_COUNT',
                csrf_token: window.CSRF_TOKEN || ''
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
            });
            <?php endif; ?>
        }

        function loadNotificationCount() {
            <?php if ($__isLoggedIn): ?>
            $.post('/api/controller/app', { type: 'GET_NOTIFICATIONS', limit: 1, csrf_token: window.CSRF_TOKEN || '' }, function(data) {
                if (data.status === 'success' && data.unread_count > 0) {
                    $('#notifications-badge').text(data.unread_count).removeClass('hidden');
                } else {
                    $('#notifications-badge').addClass('hidden');
                }
            }, 'json').fail(function() {
            });
            <?php endif; ?>
        }

        $(document).ready(function() {
            loadUnreadMessagesCount();
            loadNotificationCount();
            setInterval(loadUnreadMessagesCount, 10000);
            setInterval(loadNotificationCount, 10000);
        });
    </script>
</body>

</html>