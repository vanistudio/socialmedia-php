<?php
require $_SERVER['DOCUMENT_ROOT'] . '/config/database.php';
require $_SERVER['DOCUMENT_ROOT'] . '/config/function.php';
require $_SERVER['DOCUMENT_ROOT'] . '/views/layouts/_application/AppHeader.php';

$siteTitle = $Vani->get_row("SELECT value FROM settings WHERE `key` = 'siteTitle'");
$siteTitle = $siteTitle['value'] ?? 'Vani Social';
?>

<div class="w-full max-w-3xl mx-auto">
    <div class="bg-card border border-border rounded-2xl shadow-sm overflow-hidden">
        <div class="bg-gradient-to-r from-vanixjnk/10 to-blue-500/10 p-8 border-b border-border">
            <h1 class="text-3xl font-bold text-foreground mb-2">Chính sách quyền riêng tư</h1>
            <p class="text-muted-foreground">Cập nhật lần cuối: <?php echo date('d/m/Y'); ?></p>
        </div>

        <div class="p-8 prose prose-sm max-w-none">
            <div class="space-y-6 text-foreground">
                <section>
                    <h2 class="text-xl font-semibold text-foreground mb-3 flex items-center gap-2">
                        <iconify-icon icon="solar:info-circle-linear" width="24" class="text-vanixjnk"></iconify-icon>
                        1. Giới thiệu
                    </h2>
                    <p class="text-muted-foreground leading-relaxed">
                        <?php echo htmlspecialchars($siteTitle); ?> ("chúng tôi") cam kết bảo vệ quyền riêng tư của bạn. Chính sách này giải thích cách chúng tôi thu thập, sử dụng và bảo vệ thông tin cá nhân của bạn khi bạn sử dụng dịch vụ của chúng tôi.
                    </p>
                </section>

                <section>
                    <h2 class="text-xl font-semibold text-foreground mb-3 flex items-center gap-2">
                        <iconify-icon icon="solar:database-linear" width="24" class="text-vanixjnk"></iconify-icon>
                        2. Thông tin chúng tôi thu thập
                    </h2>
                    <p class="text-muted-foreground leading-relaxed mb-3">Chúng tôi thu thập các loại thông tin sau:</p>
                    <ul class="list-disc pl-6 space-y-2 text-muted-foreground">
                        <li><strong class="text-foreground">Thông tin tài khoản:</strong> Tên, email, username, mật khẩu (được mã hóa)</li>
                        <li><strong class="text-foreground">Thông tin hồ sơ:</strong> Avatar, banner, bio, vị trí, website, ngày sinh</li>
                        <li><strong class="text-foreground">Nội dung người dùng:</strong> Bài viết, bình luận, tin nhắn, media bạn tải lên</li>
                        <li><strong class="text-foreground">Dữ liệu sử dụng:</strong> Thời gian truy cập, tương tác, thiết bị</li>
                    </ul>
                </section>

                <section>
                    <h2 class="text-xl font-semibold text-foreground mb-3 flex items-center gap-2">
                        <iconify-icon icon="solar:settings-linear" width="24" class="text-vanixjnk"></iconify-icon>
                        3. Cách chúng tôi sử dụng thông tin
                    </h2>
                    <ul class="list-disc pl-6 space-y-2 text-muted-foreground">
                        <li>Cung cấp và cải thiện dịch vụ</li>
                        <li>Xác thực và bảo mật tài khoản</li>
                        <li>Gửi thông báo và cập nhật quan trọng</li>
                        <li>Phát hiện và ngăn chặn vi phạm, gian lận</li>
                        <li>Phân tích và nghiên cứu để cải thiện trải nghiệm người dùng</li>
                    </ul>
                </section>

                <section>
                    <h2 class="text-xl font-semibold text-foreground mb-3 flex items-center gap-2">
                        <iconify-icon icon="solar:share-linear" width="24" class="text-vanixjnk"></iconify-icon>
                        4. Chia sẻ thông tin
                    </h2>
                    <p class="text-muted-foreground leading-relaxed mb-3">
                        Chúng tôi không bán thông tin cá nhân của bạn. Chúng tôi chỉ chia sẻ thông tin trong các trường hợp sau:
                    </p>
                    <ul class="list-disc pl-6 space-y-2 text-muted-foreground">
                        <li>Với sự đồng ý của bạn</li>
                        <li>Để tuân thủ yêu cầu pháp lý</li>
                        <li>Với các nhà cung cấp dịch vụ đáng tin cậy giúp vận hành nền tảng</li>
                        <li>Để bảo vệ quyền lợi và an toàn của người dùng</li>
                    </ul>
                </section>

                <section>
                    <h2 class="text-xl font-semibold text-foreground mb-3 flex items-center gap-2">
                        <iconify-icon icon="solar:shield-check-linear" width="24" class="text-vanixjnk"></iconify-icon>
                        5. Bảo mật dữ liệu
                    </h2>
                    <p class="text-muted-foreground leading-relaxed">
                        Chúng tôi sử dụng các biện pháp bảo mật tiêu chuẩn ngành để bảo vệ thông tin của bạn, bao gồm mã hóa mật khẩu, kết nối HTTPS, và kiểm soát truy cập. Tuy nhiên, không có phương thức truyền tải qua Internet nào an toàn 100%.
                    </p>
                </section>

                <section>
                    <h2 class="text-xl font-semibold text-foreground mb-3 flex items-center gap-2">
                        <iconify-icon icon="solar:user-check-rounded-linear" width="24" class="text-vanixjnk"></iconify-icon>
                        6. Quyền của bạn
                    </h2>
                    <p class="text-muted-foreground leading-relaxed mb-3">Bạn có quyền:</p>
                    <ul class="list-disc pl-6 space-y-2 text-muted-foreground">
                        <li>Truy cập và xem thông tin cá nhân của bạn</li>
                        <li>Chỉnh sửa hoặc cập nhật thông tin</li>
                        <li>Xóa tài khoản và dữ liệu liên quan</li>
                        <li>Xuất dữ liệu của bạn</li>
                        <li>Từ chối nhận email marketing (nếu có)</li>
                    </ul>
                </section>

                <section>
                    <h2 class="text-xl font-semibold text-foreground mb-3 flex items-center gap-2">
                        <iconify-icon icon="solar:cookie-linear" width="24" class="text-vanixjnk"></iconify-icon>
                        7. Cookies
                    </h2>
                    <p class="text-muted-foreground leading-relaxed">
                        Chúng tôi sử dụng cookies để duy trì phiên đăng nhập, lưu tùy chọn của bạn, và cải thiện trải nghiệm sử dụng. Bạn có thể tắt cookies trong trình duyệt, nhưng điều này có thể ảnh hưởng đến một số chức năng của dịch vụ.
                    </p>
                </section>

                <section>
                    <h2 class="text-xl font-semibold text-foreground mb-3 flex items-center gap-2">
                        <iconify-icon icon="solar:shield-user-linear" width="24" class="text-vanixjnk"></iconify-icon>
                        8. Trẻ em
                    </h2>
                    <p class="text-muted-foreground leading-relaxed">
                        Dịch vụ của chúng tôi không dành cho người dưới 13 tuổi. Chúng tôi không cố ý thu thập thông tin từ trẻ em dưới 13 tuổi. Nếu bạn là phụ huynh và phát hiện con bạn đã đăng ký, vui lòng liên hệ với chúng tôi.
                    </p>
                </section>

                <section>
                    <h2 class="text-xl font-semibold text-foreground mb-3 flex items-center gap-2">
                        <iconify-icon icon="solar:refresh-linear" width="24" class="text-vanixjnk"></iconify-icon>
                        9. Thay đổi chính sách
                    </h2>
                    <p class="text-muted-foreground leading-relaxed">
                        Chúng tôi có thể cập nhật chính sách này theo thời gian. Những thay đổi quan trọng sẽ được thông báo qua email hoặc thông báo trên nền tảng.
                    </p>
                </section>

                <section>
                    <h2 class="text-xl font-semibold text-foreground mb-3 flex items-center gap-2">
                        <iconify-icon icon="solar:chat-round-dots-linear" width="24" class="text-vanixjnk"></iconify-icon>
                        10. Liên hệ
                    </h2>
                    <p class="text-muted-foreground leading-relaxed">
                        Nếu bạn có bất kỳ câu hỏi nào về chính sách quyền riêng tư, vui lòng liên hệ với chúng tôi qua trang <a href="/about" class="text-vanixjnk hover:underline">Giới thiệu</a>.
                    </p>
                </section>
            </div>
        </div>
    </div>

    <div class="mt-6 text-center">
        <a href="/" class="inline-flex items-center gap-2 text-sm text-muted-foreground hover:text-vanixjnk transition">
            <iconify-icon icon="solar:arrow-left-linear" width="18"></iconify-icon>
            <span>Quay về trang chủ</span>
        </a>
    </div>
</div>

<?php require $_SERVER['DOCUMENT_ROOT'] . '/views/layouts/_application/AppFooter.php'; ?>

