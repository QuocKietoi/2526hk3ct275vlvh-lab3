<?php
require_once __DIR__ . '/../src/bootstrap.php';

use CT275\Labs\Contact;

$contact = new Contact($PDO);
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $contactData = [
        'name' => $_POST['name'] ?? '',
        'phone' => $_POST['phone'] ?? '',
        'notes' => $_POST['notes'] ?? '',
    ];

    // Xử lý upload avatar nếu có
    $avatarPath = null;
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $tmpName = $_FILES['avatar']['tmp_name'];
        $ext = pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION);
        $avatarName = uniqid('avatar_', true) . '.' . $ext;
        $targetPath = $uploadDir . $avatarName;

        if (move_uploaded_file($tmpName, $targetPath)) {
            $avatarPath = 'uploads/' . $avatarName;
        }
    }

    $contactData['avatar'] = $avatarPath;

    $errors = $contact->validate($contactData);

    if (empty($errors)) {
        $contact->fill($contactData)->save();
        redirect('/');
    }
}

include_once __DIR__ . '/../src/partials/header.php';
?>

<body>
<?php include_once __DIR__ . '/../src/partials/navbar.php' ?>

<div class="container">
    <?php
    $subtitle = 'Thêm liên hệ ở đây.';
    include_once __DIR__ . '/../src/partials/heading.php';
    ?>

    <div class="row">
        <div class="col-12">
            <form method="post" enctype="multipart/form-data" class="col-md-6 offset-md-3">

                <!-- Tên -->
                <div class="mb-3">
                    <label for="name" class="form-label">Tên</label>
                    <input type="text" name="name" id="name"
                           class="form-control<?= isset($errors['name']) ? ' is-invalid' : '' ?>"
                           maxlength="255"
                           value="<?= html_escape($_POST['name'] ?? '') ?>"
                           placeholder="Enter Name" />
                    <?php if (isset($errors['name'])) : ?>
                        <span class="invalid-feedback"><strong><?= $errors['name'] ?></strong></span>
                    <?php endif ?>
                </div>

                <!-- Số điện thoại -->
                <div class="mb-3">
                    <label for="phone" class="form-label">Số điện thoại</label>
                    <input type="text" name="phone" id="phone"
                           class="form-control<?= isset($errors['phone']) ? ' is-invalid' : '' ?>"
                           maxlength="255"
                           value="<?= html_escape($_POST['phone'] ?? '') ?>"
                           placeholder="Enter Phone" />
                    <?php if (isset($errors['phone'])) : ?>
                        <span class="invalid-feedback"><strong><?= $errors['phone'] ?></strong></span>
                    <?php endif ?>
                </div>

                <!-- Ghi chú -->
                <div class="mb-3">
                    <label for="notes" class="form-label">Ghi chú</label>
                    <textarea name="notes" id="notes"
                              class="form-control<?= isset($errors['notes']) ? ' is-invalid' : '' ?>"
                              placeholder="Enter notes"><?= html_escape($_POST['notes'] ?? '') ?></textarea>
                    <?php if (isset($errors['notes'])) : ?>
                        <span class="invalid-feedback"><strong><?= $errors['notes'] ?></strong></span>
                    <?php endif ?>
                </div>

                <!-- Avatar -->
                <div class="mb-3">
                    <label for="avatar" class="form-label">Avatar</label>
                    <input type="file" name="avatar" id="avatar" class="form-control">
                    <img id="preview"
                         src="#" alt=""
                         class="img-thumbnail mt-2" style="max-width: 150px; display: none;">
                </div>

                <button type="submit" name="submit" class="btn btn-primary">Thêm liên hệ</button>
            </form>
        </div>
    </div>
</div>

<?php include_once __DIR__ . '/../src/partials/footer.php' ?>

<script>
document.getElementById('avatar').addEventListener('change', function (e) {
    const [file] = e.target.files;
    const preview = document.getElementById('preview');
    if (file) {
        preview.src = URL.createObjectURL(file);
        preview.style.display = 'block';
    }
});
</script>

</body>
</html>
