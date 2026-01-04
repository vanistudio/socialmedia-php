<?php
require $_SERVER['DOCUMENT_ROOT'] . '/config/database.php';
require $_SERVER['DOCUMENT_ROOT'] . '/config/function.php';
require $_SERVER['DOCUMENT_ROOT'] . '/views/layouts/_application/AppHeader.php';

$siteTitle = $Vani->get_row("SELECT value FROM settings WHERE `key` = 'siteTitle'");
$siteTitle = $siteTitle['value'] ?? 'Vani Social';
?>

<div class="w-full max-w-3xl mx-auto">
    <div class="bg-card border border-border rounded-2xl shadow-sm overflow-hidden">
        <div class="bg-gradient-to-r from-vanixjnk/10 to-purple-500/10 p-8 border-b border-border">
            <h1 class="text-3xl font-bold text-foreground mb-2">Điều khoản sử dụng</h1>
            <p class="text-muted-foreground">Cập nhật lần cuối: <?php echo date('d/m/Y'); ?></p>
        </div>

        <div class="p-8 prose prose-sm max-w-none">
            <div class="space-y-6 text-foreground">
                <section>
                    <h2 class="text-xl font-semibold text-foreground mb-3 flex items-center gap-2">
                        <iconify-icon icon="solar:document-text-linear" width="24" class="text-vanixjnk"></iconify-icon>
                        1. Chấp nhận điều khoản
                    </h2>
                    <p class="text-muted-foreground leading-relaxed">
                        Bằng việc truy cập và sử dụng <?php echo htmlspecialchars($siteTitle); ?>, bạn đồng ý tuân thủ và bị ràng buộc bởi các điều khoản và điều kiện này. Nếu bạn không đồng ý với bất kỳ phần nào của điều khoản, bạn không nên sử dụng dịch vụ của chúng tôi.
                    </p>
                </section>

                <section>
                    <h2 class="text-xl font-semibold text-foreground mb-3 flex items-center gap-2">
                        <iconify-icon icon="solar:user-check-linear" width="24" class="text-vanixjnk"></iconify-icon>
                        2. Tài khoản người dùng
                    </h2>
                    <ul class="list-disc pl-6 space-y-2 text-muted-foreground">
                        <li>Bạn phải cung cấp thông tin chính xác và đầy đủ khi đăng ký.</li>
                        <li>Bạn chịu trách nhiệm bảo mật tài khoản và mật khẩu của mình.</li>
                        <li>Bạn phải thông báo ngay cho chúng tôi nếu phát hiện bất kỳ vi phạm bảo mật nào.</li>
                        <li>Bạn phải từ 13 tuổi trở lên để sử dụng dịch vụ.</li>
                    </ul>
                </section>

                <section>
                    <h2 class="text-xl font-semibold text-foreground mb-3 flex items-center gap-2">
                        <iconify-icon icon="solar:gallery-wide-linear" width="24" class="text-vanixjnk"></iconify-icon>
                        3. Nội dung người dùng
                    </h2>
                    <p class="text-muted-foreground leading-relaxed mb-3">
                        Bạn giữ toàn bộ quyền sở hữu đối với nội dung mà bạn đăng tải. Tuy nhiên, khi đăng nội dung lên nền tảng, bạn đồng ý cấp cho chúng tôi quyền sử dụng không độc quyền, miễn phí bản quyền, có thể chuyển nhượng để hiển thị, sao chép, lưu trữ và phân phối nội dung đó nhằm mục đích vận hành và cải thiện dịch vụ.
                    </p>
                    <p class="text-muted-foreground leading-relaxed">Bạn không được đăng nội dung:</p>
                    <ul class="list-disc pl-6 space-y-2 text-muted-foreground mt-2">
                        <li>Vi phạm pháp luật hoặc quyền của người khác</li>
                        <li>Mang tính thù địch, quấy rối, đe dọa hoặc phân biệt đối xử</li>
                        <li>Chứa thông tin sai lệch hoặc lừa đảo</li>
                        <li>Spam hoặc quảng cáo không được phép</li>
                        <li>Nội dung khiêu dâm hoặc bạo lực</li>
                    </ul>
                </section>

                <section>
                    <h2 class="text-xl font-semibold text-foreground mb-3 flex items-center gap-2">
                        <iconify-icon icon="solar:shield-check-linear" width="24" class="text-vanixjnk"></iconify-icon>
                        4. Quyền và trách nhiệm
                    </h2>
                    <p class="text-muted-foreground leading-relaxed">
                        Chúng tôi có quyền xóa bất kỳ nội dung nào vi phạm điều khoản, tạm ngưng hoặc chấm dứt tài khoản của bạn mà không cần thông báo trước nếu phát hiện vi phạm.
                    </p>
                </section>

                <section>
                    <h2 class="text-xl font-semibold text-foreground mb-3 flex items-center gap-2">
                        <iconify-icon icon="solar:danger-triangle-linear" width="24" class="text-vanixjnk"></iconify-icon>
                        5. Giới hạn trách nhiệm
                    </h2>
                    <p class="text-muted-foreground leading-relaxed">
                        Dịch vụ được cung cấp "nguyên trạng" và "như có sẵn". Chúng tôi không đảm bảo dịch vụ sẽ hoạt động liên tục hoặc không có lỗi. Chúng tôi không chịu trách nhiệm về bất kỳ thiệt hại trực tiếp, gián tiếp, ngẫu nhiên hoặc do hậu quả nào phát sinh từ việc sử dụng dịch vụ.
                    </p>
                </section>

                <section>
                    <h2 class="text-xl font-semibold text-foreground mb-3 flex items-center gap-2">
                        <iconify-icon icon="solar:refresh-linear" width="24" class="text-vanixjnk"></iconify-icon>
                        6. Thay đổi điều khoản
                    </h2>
                    <p class="text-muted-foreground leading-relaxed">
                        Chúng tôi có quyền cập nhật điều khoản này bất kỳ lúc nào. Các thay đổi sẽ có hiệu lực ngay khi được đăng tải. Việc tiếp tục sử dụng dịch vụ sau khi thay đổi được đăng tải đồng nghĩa với việc bạn chấp nhận điều khoản mới.
                    </p>
                </section>

                <section>
                    <h2 class="text-xl font-semibold text-foreground mb-3 flex items-center gap-2">
                        <iconify-icon icon="solar:chat-round-dots-linear" width="24" class="text-vanixjnk"></iconify-icon>
                        7. Liên hệ
                    </h2>
                    <p class="text-muted-foreground leading-relaxed">
                        Nếu bạn có bất kỳ câu hỏi nào về điều khoản này, vui lòng liên hệ với chúng tôi qua trang <a href="/about" class="text-vanixjnk hover:underline">Giới thiệu</a>.
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

