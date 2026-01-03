<div class="bg-card border border-border rounded-2xl shadow-sm" id="post-<?php echo $post['id']; ?>">
    <div class="p-4 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <a href="/u/<?php echo htmlspecialchars($post['username']); ?>">
                <img src="<?php echo htmlspecialchars($post['avatar']); ?>" alt="Avatar" class="h-10 w-10 rounded-full object-cover">
            </a>
            <div>
                <a href="/u/<?php echo htmlspecialchars($post['username']); ?>" class="font-semibold text-foreground hover:underline"><?php echo htmlspecialchars($post['full_name']); ?></a>
                <p class="text-xs text-muted-foreground">@<?php echo htmlspecialchars($post['username']); ?> &middot; <?php echo htmlspecialchars($post['created_at']); ?></p>
            </div>
        </div>
        <div class="relative dropdown-container">
            <button type="button" onclick="toggleDropdown('post-menu-<?php echo $post['id']; ?>', this)" class="h-8 w-8 rounded-lg hover:bg-accent transition flex items-center justify-center text-muted-foreground" aria-label="More options">
                <iconify-icon icon="solar:menu-dots-bold" width="18"></iconify-icon>
            </button>
            <div id="post-menu-<?php echo $post['id']; ?>" class="dropdown-menu hidden fixed w-56 bg-card border border-border rounded-xl shadow-lg z-50" data-state="closed">
                <ul class="py-1">
                    <li><button type="button" data-action="copy-link" data-post-id="<?php echo $post['id']; ?>" class="w-full text-left flex items-center gap-3 px-4 py-2 text-sm text-foreground hover:bg-accent"><iconify-icon icon="solar:link-linear" width="16"></iconify-icon><span>Copy link</span></button></li>
                    <li><button type="button" data-action="save-post" data-post-id="<?php echo $post['id']; ?>" class="w-full text-left flex items-center gap-3 px-4 py-2 text-sm text-foreground hover:bg-accent"><iconify-icon icon="solar:bookmark-linear" width="16"></iconify-icon><span><?php echo ($post['has_saved'] ?? 0) > 0 ? 'Bỏ lưu' : 'Lưu bài viết'; ?></span></button></li>
                    <hr class="my-1 border-border">
                    <li><button type="button" data-action="report-post" data-post-id="<?php echo $post['id']; ?>" class="w-full text-left flex items-center gap-3 px-4 py-2 text-sm text-red-500 hover:bg-red-500/10"><iconify-icon icon="solar:danger-triangle-linear" width="16"></iconify-icon><span>Báo cáo</span></button></li>
                </ul>
            </div>
        </div>
    </div>
    <?php if (!empty($post['content'])): ?>
        <div class="px-4 pb-4">
            <p class="text-foreground whitespace-pre-wrap"><?php echo htmlspecialchars($post['content']); ?></p>
        </div>
    <?php endif; ?>
    <?php 
        $media = $Vani->get_list("SELECT * FROM `post_media` WHERE `post_id` = '{$post['id']}' ORDER BY `sort_order` ASC");
        if (!empty($media)):
    ?>
        <div class="grid grid-cols-<?php echo count($media) > 1 ? '2' : '1'; ?> gap-0.5 border-y border-border bg-border">
            <?php foreach ($media as $item): ?>
                <a href="<?php echo htmlspecialchars($item['media_url']); ?>" target="_blank" class="bg-background">
                    <img src="<?php echo htmlspecialchars($item['media_url']); ?>" alt="Post media" class="w-full h-auto max-h-[500px] object-cover">
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    <div class="p-2 flex justify-around">
        <button type="button" data-action="toggle-like" data-post-id="<?php echo $post['id']; ?>" class="flex-1 flex items-center justify-center gap-2 py-2 rounded-lg hover:bg-accent transition <?php echo ($post['has_liked'] ?? 0) > 0 ? 'text-vanixjnk' : 'text-muted-foreground'; ?>">
            <iconify-icon icon="<?php echo ($post['has_liked'] ?? 0) > 0 ? 'solar:heart-bold' : 'solar:heart-linear'; ?>" width="20"></iconify-icon>
            <span class="text-sm font-medium like-count"><?php echo $post['like_count']; ?></span>
        </button>
        <button type="button" data-action="toggle-comments" data-post-id="<?php echo $post['id']; ?>" class="flex-1 flex items-center justify-center gap-2 py-2 rounded-lg hover:bg-accent transition text-muted-foreground">
            <iconify-icon icon="solar:chat-dots-linear" width="20"></iconify-icon>
            <span class="text-sm font-medium comment-count"><?php echo $post['comment_count']; ?></span>
        </button>
        <button type="button" class="flex-1 flex items-center justify-center gap-2 py-2 rounded-lg hover:bg-accent transition text-muted-foreground" onclick="toast.info('Đang phát triển')">
            <iconify-icon icon="solar:share-linear" width="20"></iconify-icon>
            <span class="text-sm font-medium">Chia sẻ</span>
        </button>
    </div>
    <div class="hidden p-4 border-t border-border space-y-4" id="comments-<?php echo $post['id']; ?>">
        <?php if (isset($currentUser)): ?>
            <div class="flex items-start gap-3">
                <img src="<?php echo htmlspecialchars($currentUser['avatar']); ?>" alt="My Avatar" class="h-8 w-8 rounded-full object-cover">
                <form class="flex-1 relative" data-form="add-comment" data-post-id="<?php echo $post['id']; ?>">
                    <input type="hidden" name="type" value="ADD_COMMENT">
                    <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                    <input type="text" name="content" placeholder="Viết bình luận..." class="w-full h-9 rounded-lg border border-input bg-background px-3 pr-10 text-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-vanixjnk/30">
                    <button type="submit" class="absolute top-1/2 right-2 -translate-y-1/2 h-7 w-7 rounded-lg bg-vanixjnk text-white hover:bg-vanixjnk/90 flex items-center justify-center transition">
                        <iconify-icon icon="solar:arrow-right-linear" width="18"></iconify-icon>
                    </button>
                </form>
            </div>
        <?php endif; ?>
        <div class="space-y-3 comment-list"></div>
    </div>
</div>

