<?php
/* Đoạn mã xử lý PHP. */

define('TITLE', 'Thêm một Trích dẫn');

require_once __DIR__ . '/../partials/header.php';
require_once __DIR__ . '/../partials/footer.php';

$has_access = ensure_admin_access();
$error_message = null;
$success_message = null;
$form_data = [
    'quote' => '',
    'source' => '',
    'favorite' => false,
];

if (!$has_access) {
    $error_message = 'Bạn không có quyền truy cập trang này';
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $form_data['quote'] = trim($_POST['quote'] ?? '');
    $form_data['source'] = trim($_POST['source'] ?? '');
    $form_data['favorite'] = isset($_POST['favorite']);

    if ($form_data['quote'] === '' || $form_data['source'] === '') {
        $error_message = 'Hãy đảm bảo rằng bạn cung cấp đầy đủ trích dẫn và nguồn!';
    } else {
        try {
            $pdo = get_database_connection();

            if ($pdo instanceof PDO) {
                $statement = $pdo->prepare(
                    'INSERT INTO quotes (quote, source, favorite) VALUES (:quote, :source, :favorite)'
                );
                $statement->execute([
                    ':quote' => $form_data['quote'],
                    ':source' => $form_data['source'],
                    ':favorite' => $form_data['favorite'],
                ]);
                $success_message = 'Thêm trích dẫn thành công!';
                $form_data = ['quote' => '', 'source' => '', 'favorite' => false];
            } else {
                $error_message = 'Không thể kết nối đến cơ sở dữ liệu';
            }
        } catch (PDOException $e) {
            $error_message = 'Không thể lưu trích dẫn';
        }
    }
}

?>

<!--
    Đoạn mã HTML trình bày nội dung trang web.
-->
<?php render_page_header(); ?>

<h2>Thêm một Trích dẫn</h2>

<?php if (!empty($error_message)): ?>
    <?php include __DIR__ . '/../partials/show_error.php'; ?>
<?php endif; ?>

<?php if (!empty($success_message)): ?>
    <p><strong><?= html_escape($success_message) ?></strong></p>
<?php endif; ?>

<?php if ($has_access): ?>
    <form action="add_quote.php" method="post">
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
        <p><input type="submit" name="submit" value="Thêm Trích dẫn này!"></p>
    </form>
<?php endif; ?>

<?php render_page_footer(); ?>