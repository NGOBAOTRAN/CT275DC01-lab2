<?php
/* Đoạn mã xử lý PHP. */

define('TITLE', 'Sửa một Trích dẫn');

require_once __DIR__ . '/../partials/header.php';
require_once __DIR__ . '/../partials/footer.php';

$has_access = ensure_admin_access();
$error_message = null;
$success_message = null;
$form_data = [
    'id' => null,
    'quote' => '',
    'source' => '',
    'favorite' => false,
];

if (!$has_access) {
    $error_message = 'Bạn không có quyền truy cập trang này';
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $form_data['id'] = $_POST['id'] ?? null;
    $form_data['quote'] = trim($_POST['quote'] ?? '');
    $form_data['source'] = trim($_POST['source'] ?? '');
    $form_data['favorite'] = isset($_POST['favorite']);

    if (empty($form_data['id']) || $form_data['quote'] === '' || $form_data['source'] === '') {
        $error_message = 'Hãy đảm bảo rằng bạn cung cấp đầy đủ trích dẫn và nguồn!';
    } else {
        try {
            $pdo = get_database_connection();

            if ($pdo instanceof PDO) {
                $statement = $pdo->prepare(
                    'UPDATE quotes SET quote = :quote, source = :source, favorite = :favorite WHERE id = :id'
                );
                $statement->execute([
                    ':quote' => $form_data['quote'],
                    ':source' => $form_data['source'],
                    ':favorite' => $form_data['favorite'],
                    ':id' => $form_data['id'],
                ]);
                $success_message = 'Hiệu chỉnh trích dẫn thành công!';
            } else {
                $error_message = 'Không thể kết nối đến cơ sở dữ liệu';
            }
        } catch (PDOException $e) {
            $error_message = 'Không thể lưu trích dẫn';
        }
    }
} elseif (isset($_GET['id'])) {
    $form_data['id'] = $_GET['id'];

    try {
        $pdo = get_database_connection();

        if ($pdo instanceof PDO) {
            $statement = $pdo->prepare('SELECT id, quote, source, favorite FROM quotes WHERE id = :id');
            $statement->execute([':id' => $form_data['id']]);
            $row = $statement->fetch();

            if ($row) {
                $form_data['quote'] = $row['quote'];
                $form_data['source'] = $row['source'];
                $form_data['favorite'] = $row['favorite'];
            } else {
                $error_message = 'Không tìm thấy trích dẫn';
            }
        } else {
            $error_message = 'Không thể kết nối đến cơ sở dữ liệu';
        }
    } catch (PDOException $e) {
        $error_message = 'Không thể lấy dữ liệu';
    }
} else {
    $error_message = 'Thiếu id của trích dẫn cần sửa';
}

?>

<!--
    Đoạn mã HTML trình bày nội dung trang web.
-->
<?php render_page_header(); ?>

<h2>Sửa một Trích dẫn</h2>

<?php if (!empty($error_message)): ?>
    <?php include __DIR__ . '/../partials/show_error.php'; ?>
<?php endif; ?>

<?php if (!empty($success_message)): ?>
    <p><strong><?= html_escape($success_message) ?></strong></p>
<?php endif; ?>

<?php if ($has_access && !empty($form_data['id'])): ?>
    <form action="edit_quote.php" method="post">
        <input type="hidden" name="id" value="<?= html_escape($form_data['id']) ?>">
        <p>
            <label>Trích dẫn
                <textarea name="quote" rows="5" cols="30"><?= html_escape($form_data['quote']) ?></textarea>
            </label>
        </p>
        <p>
            <label>Nguồn
                <input type="text" name="source" value="<?= html_escape($form_data['source']) ?>">
            </label>
        </p>
        <p>
            <label>Đây là trích dẫn yêu thích?
                <input type="checkbox" name="favorite" value="yes" <?= !empty($form_data['favorite']) ? 'checked' : '' ?>>
            </label>
        </p>
        <p><input type="submit" name="submit" value="Lưu thay đổi!"></p>
    </form>
<?php endif; ?>

<?php render_page_footer(); ?>