<?php
/* Đoạn mã xử lý PHP. */

define('TITLE', 'Xem tất cả các Trích dẫn');

require_once __DIR__ . '/../partials/header.php';
require_once __DIR__ . '/../partials/footer.php';

$has_access = ensure_admin_access();
$error_message = null;
$reason = null;
$quotes = [];

if (!$has_access) {
    $error_message = 'Bạn không có quyền truy cập trang này';
} else {
    try {
        $pdo = get_database_connection();

        if ($pdo instanceof PDO) {
            $statement = $pdo->prepare('SELECT id, quote, source, favorite FROM quotes ORDER BY date_entered DESC');
            $statement->execute();
            $quotes = $statement->fetchAll();
        } else {
            $error_message = 'Không thể kết nối đến cơ sở dữ liệu';
        }
    } catch (PDOException $e) {
        $error_message = 'Không thể lấy dữ liệu';
        $reason = $e->getMessage();
    }
}

?>

<!--
    Đoạn mã HTML trình bày nội dung trang web.
-->
<?php render_page_header(); ?>

<h2>Tất cả các Trích dẫn</h2>

<?php if (!empty($error_message)): ?>
    <?php include __DIR__ . '/../partials/show_error.php'; ?>
<?php endif; ?>

<?php if ($has_access): ?>
    <?php if (empty($quotes)): ?>
        <p>Không có trích dẫn nào để hiển thị.</p>
    <?php else: ?>
        <ul>
            <?php foreach ($quotes as $quote): ?>
                <li>
                    <blockquote><?= html_escape($quote['quote']) ?></blockquote>
                    <p>
                        - <?= html_escape($quote['source']) ?>
                        <?php if (!empty($quote['favorite'])): ?>
                            <strong> | Yêu thích!</strong>
                        <?php endif; ?>
                    </p>
                    <p>
                        <a href="edit_quote.php?id=<?= urlencode($quote['id']) ?>">Sửa</a> <->
                            <a href="delete_quote.php?id=<?= urlencode($quote['id']) ?>">Xóa</a>
                    </p>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
<?php endif; ?>

<?php render_page_footer(); ?>