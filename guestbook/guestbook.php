<?php
// TODO 1: ПІДГОТОВКА СЕРЕДОВИЩА: 1) сесія 2) функції
session_start();

// Функція для відображення коментарів з пагінацією
function renderComments($page = 1, $perPage = 5) {
    $commentsFile = 'comments.csv';
    $comments = [];

    if (file_exists($commentsFile)) {
        $fileStream = fopen($commentsFile, "r");
        while (!feof($fileStream)) {
            $jsonString = fgets($fileStream);
            $comment = json_decode($jsonString, true);
            if (!empty($comment)) {
                $comments[] = $comment;
            }
        }
        fclose($fileStream);
    }

    // Сортуємо коментарі - нові зверху
    $comments = array_reverse($comments);

    // Розраховуємо пагінацію
    $totalComments = count($comments);
    $totalPages = ceil($totalComments / $perPage);
    $offset = ($page - 1) * $perPage;
    $commentsForPage = array_slice($comments, $offset, $perPage);

    return [
        'comments' => $commentsForPage,
        'totalComments' => $totalComments,
        'totalPages' => $totalPages,
        'currentPage' => $page,
        'perPage' => $perPage
    ];
}

// TODO 2: МАРШРУТИЗАЦІЯ
$errors = [];
$success = false;

// Отримуємо поточну сторінку для пагінації
$currentPage = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;

// TODO 3: КОД ЗА МЕТОДАМИ ЗАПИТУ (ДІЇ) GET, POST тощо
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Валідація даних
    if (empty($_POST['email'])) {
        $errors[] = "Email обов'язковий для заповнення";
    } elseif (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Некоректний формат email";
    }

    if (empty($_POST['name'])) {
        $errors[] = "Ім'я обов'язкове для заповнення";
    } elseif (strlen($_POST['name']) < 2) {
        $errors[] = "Ім'я повинно містити мінімум 2 символи";
    }

    if (empty($_POST['text'])) {
        $errors[] = "Текст коментаря обов'язковий";
    } elseif (strlen($_POST['text']) < 10) {
        $errors[] = "Коментар повинен містити мінімум 10 символів";
    }

    // Якщо немає помилок, зберігаємо коментар
    if (empty($errors)) {
        $comment = [
            'email' => htmlspecialchars(trim($_POST['email'])),
            'name' => htmlspecialchars(trim($_POST['name'])),
            'text' => htmlspecialchars(trim($_POST['text'])),
            'date' => date('Y-m-d H:i:s')
        ];

        $jsonString = json_encode($comment);
        $fileStream = fopen('comments.csv', 'a');
        fwrite($fileStream, $jsonString . "\n");
        fclose($fileStream);

        $success = true;
        // Очищуємо дані форми після успішного збереження
        $_POST = [];
        // Перенаправляємо на першу сторінку після додавання коментаря
        header("Location: guestbook.php?page=1&success=1");
        exit;
    }
}

// Перевіряємо чи є параметр успіху в URL
if (isset($_GET['success']) && $_GET['success'] == 1) {
    $success = true;
}

// TODO 4: РЕНДЕРИНГ
$paginationData = renderComments($currentPage, 5);
?>
<!DOCTYPE html>
<html>
<?php require_once 'sectionHead.php'?>
<body>
<div class="container">
    <!-- навігаційне меню -->
    <?php require_once 'sectionNavbar.php'?>
    <br>

    <!-- Повідомлення про успіх або помилки -->
    <?php if ($success): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <strong>Успіх!</strong> Ваш коментар було успішно додано.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong>Помилки:</strong>
            <ul class="mb-0">
                <?php foreach ($errors as $error): ?>
                    <li><?php echo $error; ?></li>
                <?php endforeach; ?>
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- розділ гостьової книги -->
    <div class="card card-primary">
        <div class="card-header bg-primary text-light">
            <i class="fas fa-book"></i> Форма гостьової книги
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-sm-8">
                    <!-- Форма гостьової книги -->
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label for="email" class="form-label">
                                <i class="fas fa-envelope"></i> Email *
                            </label>
                            <input type="email"
                                   class="form-control <?php echo (!empty($errors) && empty($_POST['email'])) ? 'is-invalid' : ''; ?>"
                                   id="email"
                                   name="email"
                                   value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                                   placeholder="Введіть ваш email"
                                   required>
                        </div>

                        <div class="mb-3">
                            <label for="name" class="form-label">
                                <i class="fas fa-user"></i> Ім'я *
                            </label>
                            <input type="text"
                                   class="form-control <?php echo (!empty($errors) && empty($_POST['name'])) ? 'is-invalid' : ''; ?>"
                                   id="name"
                                   name="name"
                                   value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>"
                                   placeholder="Введіть ваше ім'я"
                                   required>
                        </div>

                        <div class="mb-3">
                            <label for="text" class="form-label">
                                <i class="fas fa-comment"></i> Коментар *
                            </label>
                            <textarea class="form-control <?php echo (!empty($errors) && empty($_POST['text'])) ? 'is-invalid' : ''; ?>"
                                      id="text"
                                      name="text"
                                      rows="4"
                                      placeholder="Напишіть ваш коментар (мінімум 10 символів)"
                                      required><?php echo isset($_POST['text']) ? htmlspecialchars($_POST['text']) : ''; ?></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-paper-plane"></i> Відправити коментар
                        </button>
                    </form>
                </div>
                <div class="col-sm-4">
                    <div class="card bg-light">
                        <div class="card-body">
                            <h6 class="card-title"><i class="fas fa-info-circle"></i> Інформація</h6>
                            <p class="card-text small">
                                Поля позначені зірочкою (*) обов'язкові для заповнення.
                                <br><br>
                                Ваш коментар буде опублікований одразу після відправки.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <br>

    <div class="card card-primary">
        <div class="card-header bg-body-secondary text-dark">
            <i class="fas fa-comments"></i> Коментарі (<?php echo $paginationData['totalComments']; ?>)
            <?php if ($paginationData['totalPages'] > 1): ?>
                <small class="text-muted">
                    - Сторінка <?php echo $paginationData['currentPage']; ?> з <?php echo $paginationData['totalPages']; ?>
                </small>
            <?php endif; ?>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-sm-12">
                    <!-- Відображення коментарів -->
                    <?php if (empty($paginationData['comments'])): ?>
                        <div class="text-center text-muted p-4">
                            <i class="fas fa-comment-slash fa-3x mb-3"></i>
                            <?php if ($paginationData['totalComments'] == 0): ?>
                                <p>Поки немає коментарів. Будьте першим!</p>
                            <?php else: ?>
                                <p>На цій сторінці немає коментарів.</p>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <?php foreach ($paginationData['comments'] as $comment): ?>
                            <div class="card mb-3 border-left-primary">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h6 class="card-title mb-1">
                                                <i class="fas fa-user-circle"></i>
                                                <?php echo htmlspecialchars($comment['name']); ?>
                                            </h6>
                                            <small class="text-muted">
                                                <i class="fas fa-envelope"></i>
                                                <?php echo htmlspecialchars($comment['email']); ?>
                                            </small>
                                        </div>
                                        <small class="text-muted">
                                            <i class="fas fa-clock"></i>
                                            <?php echo date('d.m.Y H:i', strtotime($comment['date'])); ?>
                                        </small>
                                    </div>
                                    <hr>
                                    <p class="card-text"><?php echo nl2br(htmlspecialchars($comment['text'])); ?></p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>

                    <!-- Пагінація Bootstrap -->
                    <?php if ($paginationData['totalPages'] > 1): ?>
                        <nav aria-label="Навігація по коментарях">
                            <ul class="pagination justify-content-center">
                                <!-- Попередня сторінка -->
                                <?php if ($paginationData['currentPage'] > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?php echo $paginationData['currentPage'] - 1; ?>" aria-label="Попередня">
                                            <span aria-hidden="true">&laquo;</span>
                                        </a>
                                    </li>
                                <?php else: ?>
                                    <li class="page-item disabled">
                                        <span class="page-link" aria-label="Попередня">
                                            <span aria-hidden="true">&laquo;</span>
                                        </span>
                                    </li>
                                <?php endif; ?>

                                <!-- Номери сторінок -->
                                <?php
                                $startPage = max(1, $paginationData['currentPage'] - 2);
                                $endPage = min($paginationData['totalPages'], $paginationData['currentPage'] + 2);

                                // Показуємо першу сторінку якщо вона не в діапазоні
                                if ($startPage > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=1">1</a>
                                    </li>
                                    <?php if ($startPage > 2): ?>
                                        <li class="page-item disabled">
                                            <span class="page-link">...</span>
                                        </li>
                                    <?php endif; ?>
                                <?php endif; ?>

                                <!-- Основні сторінки -->
                                <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                                    <?php if ($i == $paginationData['currentPage']): ?>
                                        <li class="page-item active" aria-current="page">
                                            <span class="page-link"><?php echo $i; ?></span>
                                        </li>
                                    <?php else: ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                        </li>
                                    <?php endif; ?>
                                <?php endfor; ?>

                                <!-- Показуємо останню сторінку якщо вона не в діапазоні -->
                                <?php if ($endPage < $paginationData['totalPages']): ?>
                                    <?php if ($endPage < $paginationData['totalPages'] - 1): ?>
                                        <li class="page-item disabled">
                                            <span class="page-link">...</span>
                                        </li>
                                    <?php endif; ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?php echo $paginationData['totalPages']; ?>"><?php echo $paginationData['totalPages']; ?></a>
                                    </li>
                                <?php endif; ?>

                                <!-- Наступна сторінка -->
                                <?php if ($paginationData['currentPage'] < $paginationData['totalPages']): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?php echo $paginationData['currentPage'] + 1; ?>" aria-label="Наступна">
                                            <span aria-hidden="true">&raquo;</span>
                                        </a>
                                    </li>
                                <?php else: ?>
                                    <li class="page-item disabled">
                                        <span class="page-link" aria-label="Наступна">
                                            <span aria-hidden="true">&raquo;</span>
                                        </span>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </nav>

                        <!-- Інформація про пагінацію -->
                        <div class="text-center text-muted small mt-3">
                            Показано <?php echo count($paginationData['comments']); ?> з <?php echo $paginationData['totalComments']; ?> коментарів
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>