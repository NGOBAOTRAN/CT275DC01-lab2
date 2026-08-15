<?php
/* Đoạn mã xử lý PHP. */

define('TITLE', 'Xóa một Trích dẫn');

require_once __DIR__ . '/../partials/header.php';
require_once __DIR__ . '/../partials/footer.php';

$has_access = ensure_admin_access();
$error_message = null;
$success_message = null;
$quote_details = null;
$id = null;

if (!$has_access) {
    $error_message = 'Bạn không có quyền truy cập trang này';
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;

    if (empty($id)) {
        $error_message = 'Thiếu id của trích dẫn cần xóa';
    } else {
        try {
            $pdo = get_database_connection();

            if ($pdo instanceof PDO) {
                $statement = $pdo->prepare('DELETE FROM quotes WHERE id = :id');
                $statement->execute([':id' => $id]);
                $success_message = 'Xóa trích dẫn thành công!';
            } else {
                $error_message = 'Không thể kết nối đến cơ sở dữ liệu';
            }
        } catch (PDOException $e) {
            $error_message = 'Không thể xóa trích dẫn';
        }
    }
} elseif (isset($_GET['id'])) {
    $id = $_GET['id'];

    try {
        $pdo = get_database_connection();

        if ($pdo instanceof PDO) {
            $statement = $pdo->prepare('SELECT id, quote, source, favorite FROM quotes WHERE id = :id');
            $statement->execute([':id' => $id]);
            $quote_details = $statement->fetch();

            if (!$quote_details) {
                $error_message = 'Không tìm thấy trích dẫn';
            }
        } else {
            $error_message = 'Không thể kết nối đến cơ sở dữ liệu';
        }
    } catch (PDOException $e) {
        $error_message = 'Không thể lấy dữ liệu';
    }
} else {
    $error_message = 'Thiếu id của trích dẫn cần xóa';
}

?>

<!--
    Đoạn mã HTML trình bày nội dung trang web.
-->
<?php render_page_header(); ?>

<h2>Xóa một Trích dẫn</h2>

<?php if (!empty($error_message)): ?>
    <?php include __DIR__ . '/../partials/show_error.php'; ?>
<?php endif; ?>

<?php if (!empty($success_message)): ?>
    <p><strong><?= html_escape($success_message) ?></strong></p>
<?php endif; ?>

<?php if ($has_access && !empty($quote_details)): ?>
    <p>Bạn có chắc chắn muốn xóa trích dẫn sau đây không?</p>
    <blockquote><?= html_escape($quote_details['quote']) ?></blockquote>
    <p>- <?= html_escape($quote_details['source']) ?></p>

    <form action="delete_quote.php" method="post">
        <input type="hidden" name="id" value="<?= html_escape($quote_details['id']) ?>">
        <p><input type="submit" name="submit" value="Xác nhận Xóa"></p>
    </form>
<?php endif; ?>

<?php render_page_footer(); ?>