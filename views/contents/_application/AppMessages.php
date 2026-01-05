<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/views/layouts/_application/AppHeader.php';

$isLoggedIn = isset($_SESSION['email']) && !empty($_SESSION['email']);
if (!$isLoggedIn) {
    die('<script>setTimeout(function(){ location.href = "/login"; },1000);</script>');
}

$currentUser = $Vani->get_row("SELECT * FROM `users` WHERE `email` = '" . addslashes($_SESSION['email']) . "'");
$currentUserId = intval($currentUser['id'] ?? 0);

$conversations = $Vani->get_list("
    SELECT 
        c.id,
        c.type,
        c.title,
        c.created_at,
        (SELECT content FROM messages WHERE conversation_id = c.id ORDER BY created_at DESC LIMIT 1) as last_message,
        (SELECT created_at FROM messages WHERE conversation_id = c.id ORDER BY created_at DESC LIMIT 1) as last_message_time,
        (SELECT sender_id FROM messages WHERE conversation_id = c.id ORDER BY created_at DESC LIMIT 1) as last_sender_id,
        (SELECT COUNT(*) FROM messages m 
         LEFT JOIN message_reads mr ON m.id = mr.message_id AND mr.user_id = '$currentUserId'
         WHERE m.conversation_id = c.id AND m.sender_id != '$currentUserId' AND mr.id IS NULL) as unread_count
    FROM conversations c
    INNER JOIN conversation_members cm ON c.id = cm.conversation_id
    WHERE cm.user_id = '$currentUserId'
    AND NOT EXISTS (
        SELECT 1 FROM conversation_members cm2
        INNER JOIN user_blocks ub ON (
            (ub.blocker_id = '$currentUserId' AND ub.blocked_id = cm2.user_id)
            OR (ub.blocker_id = cm2.user_id AND ub.blocked_id = '$currentUserId')
        )
        WHERE cm2.conversation_id = c.id AND cm2.user_id != '$currentUserId'
    )
    ORDER BY last_message_time DESC, c.created_at DESC
");

$conversationsData = [];
foreach ($conversations as $conv) {
    $convId = intval($conv['id']);
    $otherMembers = $Vani->get_list("
        SELECT u.id, u.username, u.full_name, u.avatar
        FROM conversation_members cm
        JOIN users u ON cm.user_id = u.id
        WHERE cm.conversation_id = '$convId' AND cm.user_id != '$currentUserId'
    ");
    
    $conversationsData[] = [
        'id' => $convId,
        'type' => $conv['type'],
        'title' => $conv['title'],
        'last_message' => $conv['last_message'],
        'last_message_time' => $conv['last_message_time'],
        'last_sender_id' => intval($conv['last_sender_id'] ?? 0),
        'unread_count' => intval($conv['unread_count'] ?? 0),
        'members' => $otherMembers,
    ];
}

$selectedConversationId = isset($_GET['id']) ? intval($_GET['id']) : 0;
$selectedConversation = null;
$selectedMessages = [];

if ($selectedConversationId > 0) {
    $member = $Vani->get_row("SELECT * FROM conversation_members WHERE conversation_id = '$selectedConversationId' AND user_id = '$currentUserId'");
    if ($member) {
        foreach ($conversationsData as $conv) {
            if ($conv['id'] == $selectedConversationId) {
                $selectedConversation = $conv;
                break;
            }
        }
        $selectedMessages = $Vani->get_list("
            SELECT 
                m.id,
                m.sender_id,
                m.content,
                m.media_url,
                m.created_at,
                u.username,
                u.full_name,
                u.avatar,
                (SELECT COUNT(*) FROM message_reads WHERE message_id = m.id AND user_id = '$currentUserId') as is_read
            FROM messages m
            JOIN users u ON m.sender_id = u.id
            WHERE m.conversation_id = '$selectedConversationId'
            ORDER BY m.created_at ASC
            LIMIT 100
        ");
        $Vani->query("
            INSERT INTO message_reads (message_id, user_id)
            SELECT m.id, '$currentUserId'
            FROM messages m
            WHERE m.conversation_id = '$selectedConversationId'
            AND m.sender_id != '$currentUserId'
            AND m.id NOT IN (SELECT message_id FROM message_reads WHERE user_id = '$currentUserId')
        ");
    }
}
?>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-4 h-[calc(100vh-12rem)]">
    <div class="lg:col-span-1 bg-card border border-border rounded-2xl shadow-sm overflow-hidden flex flex-col">
        <div class="p-4 border-b border-border flex items-center justify-between">
            <h2 class="text-xl font-bold text-foreground">Tin nhắn</h2>
            <button type="button" onclick="openNewChatDialog()" class="h-9 w-9 rounded-lg bg-vanixjnk text-white hover:bg-vanixjnk/90 transition flex items-center justify-center" aria-label="New chat">
                <iconify-icon icon="solar:add-circle-linear" width="20"></iconify-icon>
            </button>
        </div>
        <div class="flex-1 overflow-y-auto">
            <?php if (empty($conversationsData)): ?>
                <div class="p-8 text-center text-muted-foreground">
                    <iconify-icon icon="solar:chat-round-dots-linear" width="48" class="mx-auto mb-2 opacity-50"></iconify-icon>
                    <p>Chưa có cuộc trò chuyện nào</p>
                </div>
            <?php else: ?>
                <?php foreach ($conversationsData as $conv): ?>
                    <?php
                    $otherUser = !empty($conv['members']) ? $conv['members'][0] : null;
                    $displayName = $conv['type'] === 'direct' && $otherUser 
                        ? htmlspecialchars($otherUser['full_name'] ?? $otherUser['username'] ?? 'User')
                        : htmlspecialchars($conv['title'] ?? 'Nhóm chat');
                    $displayAvatar = $conv['type'] === 'direct' && $otherUser 
                        ? htmlspecialchars($otherUser['avatar'] ?? 'https://placehold.co/200x200')
                        : 'https://placehold.co/200x200';
                    $isActive = $selectedConversationId == $conv['id'];
                    $lastMessagePreview = $conv['last_message'] ? htmlspecialchars(mb_substr($conv['last_message'], 0, 50)) : '';
                    $lastTime = $conv['last_message_time'] ? date('H:i', strtotime($conv['last_message_time'])) : '';
                    ?>
                    <a href="/messages?id=<?php echo $conv['id']; ?>" 
                       class="block p-4 hover:bg-accent transition border-b border-border <?php echo $isActive ? 'bg-vanixjnk/10 border-l-4 border-l-vanixjnk' : ''; ?>">
                        <div class="flex items-start gap-3">
                            <img src="<?php echo $displayAvatar; ?>" alt="Avatar" class="h-12 w-12 rounded-full object-cover flex-shrink-0">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between mb-1">
                                    <h3 class="font-semibold text-foreground truncate"><?php echo $displayName; ?></h3>
                                    <?php if ($conv['unread_count'] > 0): ?>
                                        <span class="ml-2 h-5 w-5 rounded-full bg-vanixjnk text-white text-xs flex items-center justify-center flex-shrink-0">
                                            <?php echo $conv['unread_count']; ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                                <p class="text-sm text-muted-foreground truncate"><?php echo $lastMessagePreview; ?></p>
                                <p class="text-xs text-muted-foreground mt-1"><?php echo $lastTime; ?></p>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
    <div class="lg:col-span-2 bg-card border border-border rounded-2xl shadow-sm overflow-hidden flex flex-col">
        <?php if ($selectedConversation): ?>
            <?php
            $otherUser = !empty($selectedConversation['members']) ? $selectedConversation['members'][0] : null;
            $chatDisplayName = $selectedConversation['type'] === 'direct' && $otherUser 
                ? htmlspecialchars($otherUser['full_name'] ?? $otherUser['username'] ?? 'User')
                : htmlspecialchars($selectedConversation['title'] ?? 'Nhóm chat');
            $chatDisplayAvatar = $selectedConversation['type'] === 'direct' && $otherUser 
                ? htmlspecialchars($otherUser['avatar'] ?? 'https://placehold.co/200x200')
                : 'https://placehold.co/200x200';
            ?>
            <div class="p-4 border-b border-border flex items-center gap-3">
                <img src="<?php echo $chatDisplayAvatar; ?>" alt="Avatar" class="h-10 w-10 rounded-full object-cover">
                <div class="flex-1">
                    <h3 class="font-semibold text-foreground"><?php echo $chatDisplayName; ?></h3>
                    <p class="text-xs text-muted-foreground">Đang hoạt động</p>
                </div>
            </div>
            <div id="messages-container" class="flex-1 overflow-y-auto p-4 space-y-4">
                <?php if (empty($selectedMessages)): ?>
                    <div class="text-center text-muted-foreground py-8">
                        <p>Chưa có tin nhắn nào. Hãy bắt đầu cuộc trò chuyện!</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($selectedMessages as $msg): ?>
                        <?php
                        $isOwn = intval($msg['sender_id']) == $currentUserId;
                        $msgContent = htmlspecialchars($msg['content'] ?? '');
                        $msgMedia = $msg['media_url'] ? htmlspecialchars($msg['media_url']) : '';
                        $msgTime = date('H:i', strtotime($msg['created_at']));
                        ?>
                        <div class="flex items-start gap-3 <?php echo $isOwn ? 'flex-row-reverse' : ''; ?>">
                            <?php if (!$isOwn): ?>
                                <img src="<?php echo htmlspecialchars($msg['avatar'] ?? 'https://placehold.co/200x200'); ?>" alt="Avatar" class="h-8 w-8 rounded-full object-cover flex-shrink-0">
                            <?php endif; ?>
                            <div class="flex flex-col gap-1 max-w-[70%] <?php echo $isOwn ? 'items-end' : 'items-start'; ?>">
                                <?php if ($msgContent): ?>
                                    <div class="px-4 py-2 rounded-2xl <?php echo $isOwn ? 'bg-vanixjnk text-white' : 'bg-accent text-foreground'; ?>">
                                        <p class="text-sm"><?php echo nl2br($msgContent); ?></p>
                                    </div>
                                <?php endif; ?>
                                <?php if ($msgMedia): ?>
                                    <div class="rounded-2xl overflow-hidden max-w-xs">
                                        <?php if (preg_match('/\.(jpg|jpeg|png|gif|webp)(\?|$)/i', $msgMedia)): ?>
                                            <img src="<?php echo $msgMedia; ?>" alt="Media" class="max-w-full h-auto">
                                        <?php elseif (preg_match('/\.(mp4|webm|mov)(\?|$)/i', $msgMedia)): ?>
                                            <video src="<?php echo $msgMedia; ?>" controls class="max-w-full h-auto"></video>
                                        <?php else: ?>
                                            <a href="<?php echo $msgMedia; ?>" target="_blank" class="block px-4 py-2 bg-accent text-foreground hover:bg-accent/80 transition">
                                                <iconify-icon icon="solar:file-linear" width="20"></iconify-icon>
                                                <span class="ml-2">Xem file</span>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                                <p class="text-xs text-muted-foreground"><?php echo $msgTime; ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            <div class="p-4 border-t border-border">
                <form id="send-message-form" class="flex items-end gap-2">
                    <input type="hidden" name="type" value="SEND_MESSAGE">
                    <input type="hidden" name="conversation_id" value="<?php echo $selectedConversationId; ?>">
                    <button type="button" class="h-10 w-10 rounded-lg border border-input bg-card hover:bg-accent transition flex items-center justify-center flex-shrink-0" onclick="$('#message-media-upload').click()" aria-label="Add media">
                        <iconify-icon icon="solar:gallery-wide-linear" width="20"></iconify-icon>
                    </button>
                    <input type="file" id="message-media-upload" accept="image/*,video/*" class="hidden">
                    <textarea name="content" rows="1" placeholder="Nhập tin nhắn..." class="flex-1 min-h-[40px] max-h-32 px-4 py-2 rounded-lg border border-input bg-background text-foreground placeholder:text-muted-foreground text-sm resize-none focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-vanixjnk/30 focus-visible:border-vanixjnk/50"></textarea>
                    <button type="submit" class="h-10 px-4 rounded-lg bg-vanixjnk text-white hover:bg-vanixjnk/90 transition font-medium flex items-center gap-2 flex-shrink-0">
                        <iconify-icon icon="solar:plain-2-linear" width="18"></iconify-icon>
                        <span>Gửi</span>
                    </button>
                </form>
                <div id="message-media-preview" class="mt-2 hidden">
                    <div class="relative inline-block">
                        <img id="message-media-preview-img" src="" alt="Preview" class="max-w-xs rounded-lg">
                        <button type="button" class="absolute -top-2 -right-2 h-6 w-6 rounded-full bg-destructive text-white flex items-center justify-center" onclick="clearMessageMedia()">
                            <iconify-icon icon="solar:close-circle-linear" width="16"></iconify-icon>
                        </button>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="flex-1 flex items-center justify-center text-center text-muted-foreground">
                <div>
                    <iconify-icon icon="solar:chat-round-dots-linear" width="64" class="mx-auto mb-4 opacity-50"></iconify-icon>
                    <p class="text-lg">Chọn một cuộc trò chuyện để bắt đầu</p>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
let selectedConversationId = <?php echo $selectedConversationId; ?>;
let messageMediaUrl = '';
const PUSHER_KEY = window.PUSHER_KEY || '';
const PUSHER_CLUSTER = window.PUSHER_CLUSTER || 'ap1';
let pusher = null;
let pusherChannel = null;

function scrollToBottom() {
    const container = document.getElementById('messages-container');
    if (container) {
        container.scrollTop = container.scrollHeight;
    }
}

function initRealtime() {
    if (!PUSHER_KEY || !selectedConversationId) return;
    
    try {
        pusher = new Pusher(PUSHER_KEY, {
            cluster: PUSHER_CLUSTER,
            forceTLS: true,
        });
        
        const channelName = 'conversation.' + selectedConversationId;
        pusherChannel = pusher.subscribe(channelName);
        
        pusherChannel.bind('message:new', function(payload) {
            if (!payload || !payload.message) return;
            
            const currentUserId = <?php echo $currentUserId; ?>;
            const msgSenderId = parseInt(payload.message.sender_id);
            
            if (msgSenderId === currentUserId) {
                return;
            }
            
            appendRealtimeMessage(payload.message);
        });
    } catch (e) {
        console.error('Pusher initialization error:', e);
    }
}

function appendRealtimeMessage(msg) {
    const container = document.getElementById('messages-container');
    if (!container) return;
    
    const currentUserId = <?php echo $currentUserId; ?>;
    const isOwn = parseInt(msg.sender_id) === currentUserId;
    const msgContent = (msg.content || '').trim();
    const msgMedia = (msg.media_url || '').trim();
    const msgTime = new Date(msg.created_at).toLocaleTimeString('vi-VN', { hour: '2-digit', minute: '2-digit' });
    const avatar = msg.avatar || 'https://placehold.co/200x200';
    
    if (!msgContent && !msgMedia) return;
    
    let html = '';
    html += '<div class="flex items-start gap-3 ' + (isOwn ? 'flex-row-reverse' : '') + '">';
    if (!isOwn) {
        html += '<img src="' + escapeHtml(avatar) + '" alt="Avatar" class="h-8 w-8 rounded-full object-cover flex-shrink-0">';
    }
    html += '<div class="flex flex-col gap-1 max-w-[70%] ' + (isOwn ? 'items-end' : 'items-start') + '">';
    if (msgContent) {
        html += '<div class="px-4 py-2 rounded-2xl ' + (isOwn ? 'bg-vanixjnk text-white' : 'bg-accent text-foreground') + '">';
        html += '<p class="text-sm">' + escapeHtml(msgContent).replace(/\n/g, '<br>') + '</p>';
        html += '</div>';
    }
    if (msgMedia) {
        html += '<div class="rounded-2xl overflow-hidden max-w-xs">';
        if (/\.(jpg|jpeg|png|gif|webp)(\?|$)/i.test(msgMedia)) {
            html += '<img src="' + escapeHtml(msgMedia) + '" alt="Media" class="max-w-full h-auto">';
        } else if (/\.(mp4|webm|mov)(\?|$)/i.test(msgMedia)) {
            html += '<video src="' + escapeHtml(msgMedia) + '" controls class="max-w-full h-auto"></video>';
        } else {
            html += '<a href="' + escapeHtml(msgMedia) + '" target="_blank" class="block px-4 py-2 bg-accent text-foreground hover:bg-accent/80 transition">';
            html += '<iconify-icon icon="solar:file-linear" width="20"></iconify-icon>';
            html += '<span class="ml-2">Xem file</span>';
            html += '</a>';
        }
        html += '</div>';
    }
    html += '<p class="text-xs text-muted-foreground">' + escapeHtml(msgTime) + '</p>';
    html += '</div>';
    html += '</div>';
    
    container.insertAdjacentHTML('beforeend', html);
    scrollToBottom();
}

function escapeHtml(str) {
    if (!str) return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}
function loadMessages() {
    if (!selectedConversationId) return;
    
    $.post('/api/controller/app', {
        type: 'GET_MESSAGES',
        conversation_id: selectedConversationId,
        limit: 100,
        offset: 0,
        csrf_token: window.CSRF_TOKEN || ''
    }, function(data) {
        if (data && data.status === 'success' && data.messages) {
            renderMessages(data.messages);
            scrollToBottom();
        }
    }, 'json').fail(function() {
        console.error('Không thể tải tin nhắn');
    });
}
function renderMessages(messages) {
    const container = document.getElementById('messages-container');
    if (!container) return;
    const currentUserId = <?php echo $currentUserId; ?>;
    if (messages.length === 0) {
        container.innerHTML = '<div class="text-center text-muted-foreground py-8"><p>Chưa có tin nhắn nào. Hãy bắt đầu cuộc trò chuyện!</p></div>';
        return;
    }
    
    let html = '';
    messages.forEach(function(msg) {
        const isOwn = parseInt(msg.sender_id) === currentUserId;
        const msgContent = msg.content || '';
        const msgMedia = msg.media_url || '';
        const msgTime = new Date(msg.created_at).toLocaleTimeString('vi-VN', { hour: '2-digit', minute: '2-digit' });
        const avatar = msg.avatar || 'https://placehold.co/200x200';
        
        html += '<div class="flex items-start gap-3 ' + (isOwn ? 'flex-row-reverse' : '') + '">';
        if (!isOwn) {
            html += '<img src="' + avatar + '" alt="Avatar" class="h-8 w-8 rounded-full object-cover flex-shrink-0">';
        }
        html += '<div class="flex flex-col gap-1 max-w-[70%] ' + (isOwn ? 'items-end' : 'items-start') + '">';
        if (msgContent) {
            html += '<div class="px-4 py-2 rounded-2xl ' + (isOwn ? 'bg-vanixjnk text-white' : 'bg-accent text-foreground') + '">';
            html += '<p class="text-sm">' + msgContent.replace(/\n/g, '<br>') + '</p>';
            html += '</div>';
        }
        if (msgMedia) {
            html += '<div class="rounded-2xl overflow-hidden max-w-xs">';
            if (/\.(jpg|jpeg|png|gif|webp)(\?|$)/i.test(msgMedia)) {
                html += '<img src="' + msgMedia + '" alt="Media" class="max-w-full h-auto">';
            } else if (/\.(mp4|webm|mov)(\?|$)/i.test(msgMedia)) {
                html += '<video src="' + msgMedia + '" controls class="max-w-full h-auto"></video>';
            } else {
                html += '<a href="' + msgMedia + '" target="_blank" class="block px-4 py-2 bg-accent text-foreground hover:bg-accent/80 transition">';
                html += '<iconify-icon icon="solar:file-linear" width="20"></iconify-icon>';
                html += '<span class="ml-2">Xem file</span>';
                html += '</a>';
            }
            html += '</div>';
        }
        html += '<p class="text-xs text-muted-foreground">' + msgTime + '</p>';
        html += '</div>';
        html += '</div>';
    });
    
    container.innerHTML = html;
    scrollToBottom();
}

$('#send-message-form').on('submit', function(e) {
    e.preventDefault();
    
    const $form = $(this);
    const $btn = $form.find('button[type=submit]');
    const originalBtnHtml = $btn.html();
    $btn.prop('disabled', true);
    $btn.html('<span>Đang gửi...</span>');
    
    const formData = {
        type: 'SEND_MESSAGE',
        conversation_id: selectedConversationId,
        content: $form.find('textarea[name=content]').val().trim(),
        media_url: messageMediaUrl,
        csrf_token: window.CSRF_TOKEN || ''
    };
    
    if (!formData.content && !formData.media_url) {
        toast.error('Vui lòng nhập nội dung hoặc chọn media');
        $btn.prop('disabled', false);
        $btn.html(originalBtnHtml);
        return;
    }
    
    $.post('/api/controller/app', formData, function(data) {
        $btn.prop('disabled', false);
        $btn.html(originalBtnHtml);
        
        if (data && data.status === 'error') {
            toast.error(data.message);
            return;
        }
        
        $form.find('textarea[name=content]').val('');
        clearMessageMedia();
        
        if (data && data.message) {
            appendRealtimeMessage(data.message);
        } else {
            loadMessages();
        }
        
        toast.success('Đã gửi tin nhắn');
    }, 'json').fail(function() {
        $btn.prop('disabled', false);
        $btn.html(originalBtnHtml);
        toast.error('Có lỗi xảy ra', { description: 'Không thể kết nối tới máy chủ.' });
    });
});

$('#message-media-upload').on('change', function(e) {
    const file = e.target.files[0];
    if (!file) return;
    
    const formData = new FormData();
    formData.append('file', file);
    formData.append('csrf_token', window.CSRF_TOKEN || '');
    
    $.ajax({
        url: '/api/controller/upload',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(data) {
            if (data && data.status === 'success' && data.url) {
                messageMediaUrl = data.url;
                $('#message-media-preview-img').attr('src', data.url);
                $('#message-media-preview').removeClass('hidden');
            } else {
                toast.error(data.message || 'Không thể upload file');
            }
        },
        error: function() {
            toast.error('Có lỗi xảy ra khi upload file');
        }
    });
});

function clearMessageMedia() {
    messageMediaUrl = '';
    $('#message-media-upload').val('');
    $('#message-media-preview').addClass('hidden');
}

if (selectedConversationId) {
    initRealtime();
    
    setTimeout(scrollToBottom, 100);
    
    setInterval(function() {
        loadMessages();
    }, 20000);
}

$('textarea[name=content]').on('input', function() {
    this.style.height = 'auto';
    this.style.height = (this.scrollHeight) + 'px';
});

let newChatDialog = null;

$(document).ready(function() {
    setTimeout(function() {
        if (window.initDialog) {
            newChatDialog = window.initDialog('new-chat-dialog');
        }
    }, 100);
});

function openNewChatDialog() {
    if (!newChatDialog && window.initDialog) {
        newChatDialog = window.initDialog('new-chat-dialog');
    }
    
    if (newChatDialog) {
        newChatDialog.open();
        setTimeout(function() {
            $('#new-chat-search').focus();
        }, 200);
    } else {
        const $dialog = $('#new-chat-dialog');
        $dialog.removeClass('hidden').addClass('flex');
        setTimeout(function() {
            $dialog.attr('data-state', 'open');
            $('#new-chat-search').focus();
        }, 10);
    }
}

function closeNewChatDialog() {
    if (newChatDialog) {
        newChatDialog.close();
    } else {
        const $dialog = $('#new-chat-dialog');
        $dialog.attr('data-state', 'closed');
        setTimeout(function() {
            $dialog.addClass('hidden').removeClass('flex');
        }, 200);
    }
    $('#new-chat-search').val('').prop('disabled', false);
    $('#new-chat-results').empty();
    isSearching = false;
}

function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

let isSearching = false;
function performSearch(query) {
    if (isSearching) return;
    
    const $results = $('#new-chat-results');
    const $searchInput = $('#new-chat-search');
    
    if (query.length < 2) {
        $results.empty();
        return;
    }
    
    isSearching = true;
    $results.html('<div class="p-4 text-center"><p class="text-sm text-muted-foreground">Đang tìm kiếm...</p></div>');
    $searchInput.prop('disabled', true);
    
    $.post('/api/controller/app', {
        type: 'SEARCH_USERS',
        query: query,
        csrf_token: window.CSRF_TOKEN || ''
    }, function(data) {
        isSearching = false;
        $searchInput.prop('disabled', false);
        
        if (data && data.status === 'success' && data.users) {
            renderUserSearchResults(data.users);
        } else {
            $results.html('<p class="text-sm text-muted-foreground p-4">Không tìm thấy người dùng</p>');
        }
    }, 'json').fail(function() {
        isSearching = false;
        $searchInput.prop('disabled', false);
        $results.html('<p class="text-sm text-muted-foreground p-4">Không thể tìm kiếm. Vui lòng thử lại.</p>');
    });
}

const debouncedSearch = debounce(performSearch, 300);

$(document).ready(function() {
    $(document).on('input', '#new-chat-search', function() {
        const query = $(this).val().trim();
        debouncedSearch(query);
    });
});

function renderUserSearchResults(users) {
    const $results = $('#new-chat-results');
    $results.empty();
    
    if (users.length === 0) {
        $results.html('<p class="text-sm text-muted-foreground p-4">Không tìm thấy người dùng</p>');
        return;
    }
    
    users.forEach(function(user) {
        const $item = $('<a href="#" class="block p-3 hover:bg-accent transition border-b border-border last:border-b-0"></a>');
        $item.html(`
            <div class="flex items-center gap-3">
                <img src="${user.avatar || 'https://placehold.co/200x200'}" alt="Avatar" class="h-10 w-10 rounded-full object-cover">
                <div class="flex-1">
                    <h4 class="font-semibold text-foreground">${user.full_name || user.username}</h4>
                    <p class="text-sm text-muted-foreground">@${user.username}</p>
                </div>
            </div>
        `);
        $item.on('click', function(e) {
            e.preventDefault();
            startConversation(user.id);
        });
        $results.append($item);
    });
}

function startConversation(userId) {
    $.post('/api/controller/app', {
        type: 'CREATE_CONVERSATION',
        target_user_id: userId,
        csrf_token: window.CSRF_TOKEN || ''
    }, function(data) {
        if (data && data.status === 'success') {
            closeNewChatDialog();
            window.location.href = '/messages?id=' + data.conversation_id;
        } else {
            toast.error(data.message || 'Không thể tạo cuộc trò chuyện');
        }
    }, 'json').fail(function() {
        toast.error('Có lỗi xảy ra', { description: 'Không thể kết nối tới máy chủ.' });
    });
}

</script>

<div id="new-chat-dialog" class="hidden fixed inset-0 z-50 items-center justify-center" data-dialog data-state="closed">
    <div class="absolute inset-0 bg-black/50" data-dialog-backdrop></div>
    <div class="relative w-full max-w-md mx-auto" data-dialog-content>
        <div class="bg-card border border-border rounded-2xl shadow-lg" data-dialog-inner>
            <div class="flex items-center justify-between p-4 border-b border-border">
                <h3 class="text-lg font-semibold text-foreground">Tìm người dùng</h3>
                <button type="button" class="h-9 w-9 rounded-lg hover:bg-accent transition flex items-center justify-center" data-dialog-close onclick="closeNewChatDialog()">
                    <iconify-icon icon="solar:close-circle-linear" width="22"></iconify-icon>
                </button>
            </div>
            <div class="p-4">
                <div class="relative mb-4">
                    <iconify-icon icon="solar:magnifer-linear" class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground" width="18"></iconify-icon>
                    <input type="text" id="new-chat-search" placeholder="Tìm kiếm theo tên hoặc username..." class="w-full h-10 pl-10 pr-4 rounded-lg border border-input bg-background text-foreground placeholder:text-muted-foreground text-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-vanixjnk/30 focus-visible:border-vanixjnk/50">
                </div>
                <div id="new-chat-results" class="max-h-96 overflow-y-auto"></div>
            </div>
        </div>
    </div>
</div>

<?php require $_SERVER['DOCUMENT_ROOT'] . '/views/layouts/_application/AppFooter.php'; ?>

