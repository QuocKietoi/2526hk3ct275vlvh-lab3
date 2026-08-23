<?php

require_once __DIR__ . '/../src/bootstrap.php';
use CT275\Labs\Contact;

$contact = new Contact($PDO);
$id = isset($_REQUEST['id']) ? filter_var($_REQUEST['id'], FILTER_VALIDATE_INT) : false;

if (!$id || !$contact->find($id)) {
    redirect('/');
}

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $contactData = [
        'name' => $_POST['name'] ?? '',
        'phone' => $_POST['phone'] ?? '',
        'notes' => $_POST['notes'] ?? '',
    ];

    // Xử lý upload avatar
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
            $contactData['avatar'] = 'uploads/' . $avatarName;
        }
    }

    $errors = $contact->validate($contactData);
    if (empty($errors)) {
        $contact->fill($contactData);
        $contact->save();
        redirect('/');
    }
}

include_once __DIR__ . '/../src/partials/header.php';
?>

<body>
<?php include_once __DIR__ . '/../src/partials/navbar.php'; ?>

<div class="container">
  <?php
  $subtitle = 'Edit Contact';
  include_once __DIR__ . '/../src/partials/heading.php';
  ?>

  <div class="row">
    <div class="col-md-6 offset-md-3">

      <?php if ($errors): ?>
        <div class="alert alert-danger">
          <ul class="mb-0">
            <?php foreach ($errors as $error): ?>
              <li><?= html_escape($error) ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
      <?php endif; ?>

      <form method="post" enctype="multipart/form-data">
        <div class="mb-3">
          <label for="name" class="form-label">Name</label>
          <input value="<?= html_escape($contact->name) ?>" type="text" name="name" id="name" class="form-control">
        </div>

        <div class="mb-3">
          <label for="phone" class="form-label">Phone</label>
          <input value="<?= html_escape($contact->phone) ?>" type="text" name="phone" id="phone" class="form-control">
        </div>

        <div class="mb-3">
          <label for="notes" class="form-label">Notes</label>
          <textarea name="notes" id="notes" class="form-control"><?= html_escape($contact->notes) ?></textarea>
        </div>

        <div class="mb-3">
          <label for="avatar" class="form-label">Avatar</label>
          <input type="file" name="avatar" id="avatar" class="form-control">
          <?php if ($contact->avatar): ?>
            <img src="/<?= html_escape($contact->avatar) ?>" alt="Avatar"
                 class="img-thumbnail mt-2" style="max-width: 150px;">
          <?php endif ?>
        </div>

        <button class="btn btn-primary" type="submit">
          <i class="fa fa-save"></i> Update Contact
        </button>
      </form>
    </div>
  </div>
</div>

<?php include_once __DIR__ . '/../src/partials/footer.php'; ?>

<script>
document.getElementById('avatar').addEventListener('change', function(e) {
  const [file] = e.target.files;
  if (file) {
    const preview = document.querySelector('img.img-thumbnail');
    if (preview) {
      preview.src = URL.createObjectURL(file);
    }
  }
});
</script>

</body>
</html>
