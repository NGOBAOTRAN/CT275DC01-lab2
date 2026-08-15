<?php
/* Đoạn mã xử lý PHP. */

define('TITLE', 'Tìm kiếm Trích dẫn');

require_once __DIR__ . '/../partials/header.php';
require_once __DIR__ . '/../partials/footer.php';

$error_message = null;
$sources = [];
$results = [];
$has_searched = false;

$keyword = trim($_GET['keyword'] ?? '');
$selected_source = trim($_GET['source'] ?? '');

try {
    $pdo = get_database_connection();

    if ($pdo instanceof PDO) {
        // Lấy danh sách nguồn/tác giả duy nhất cho combobox
        $statement = $pdo->query('SELECT DISTINCT source FROM quotes ORDER BY source');
        $sources = $statement->fetchAll(PDO::FETCH_COLUMN);

        // Nếu có gửi form (có tham số keyword hoặc source trên URL) thì tìm kiếm
        if (isset($_GET['keyword']) || isset($_GET['source'])) {
            $has_searched = true;

            $sql = 'SELECT id, quote, source, favorite FROM quotes WHERE quote ILIKE :keyword';
            $params = [':keyword' => '%' . $keyword . '%'];

            if ($selected_source !== '') {
                $sql .= ' AND source = :source';
                $params[':source'] = $selected_source;
            }

            $sql .= ' ORDER BY date_entered DESC';

            $statement = $pdo->prepare($sql);
            $statement->execute($params);
            $results = $statement->fetchAll();
        }
    } else {
        $error_message = 'Không thể kết nối đến cơ sở dữ liệu';
    }
} catch (PDOException $e) {
    $error_message = 'Không thể lấy dữ liệu';
}

?>

<!--
    Đoạn mã HTML trình bày nội dung trang web.
-->
<?php render_page_header(); ?>

<h2>Tìm kiếm Trích dẫn</h2>

<?php if (!empty($error_message)): ?>
    <?php include __DIR__ . '/../partials/show_error.php'; ?>
<?php endif; ?>

<form action="search.php" method="get">
    <p>
        <label>Từ khóa
            <input type="text" name="keyword" value="<?= html_escape($keyword) ?>">
        </label>
    </p>
    <p>
        <label>Nguồn/Tác giả
            <select name="source">
                <option value="">-- Tất cả --</option>
                <?php foreach ($sources as $source): ?>
                    <option value="<?= html_escape($source) ?>" <?= $selected_source === $source ? 'selected' : '' ?>>
                        <?= html_escape($source) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>
    </p>
    <p><input type="submit" name="submit" value="Tìm kiếm"></p>
</form>

<?php if ($has_searched): ?>
    <h3>Kết quả tìm kiếm</h3>

    <?php if (empty($results)): ?>
        <p>Không tìm thấy trích dẫn nào phù hợp.</p>
    <?php else: ?>
        <ul>
            <?php foreach ($results as $quote): ?>
                <li>
                    <blockquote><?= html_escape($quote['quote']) ?></blockquote>
                    <p>
                        - <?= html_escape($quote['source']) ?>
                        <?php if (!empty($quote['favorite'])): ?>
                            <strong> | Yêu thích!</strong>
                        <?php endif; ?>
                    </p>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
<?php endif; ?>

<?php render_page_footer(); ?>