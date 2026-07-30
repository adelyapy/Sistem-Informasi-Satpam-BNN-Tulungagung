<?php
require_once "../../config/admin_auth.php";
require_once "../../config/database.php";
require_once "../../config/function.php";

if (!isset($_GET['id'])) {
    header("Location:index.php");
    exit;
}

$id = (int)$_GET['id'];

$q = mysqli_query($conn,"
SELECT *
FROM materi_buku_saku
WHERE id_materi='$id'
");

if(mysqli_num_rows($q)==0){

    echo "
    <script>

    alert('Data tidak ditemukan');

    location='index.php';

    </script>";

    exit;

}

$data = mysqli_fetch_assoc($q);

if(isset($_POST['update'])){

    $judul = mysqli_real_escape_string($conn,$_POST['judul']);
    $kategori = (int)$_POST['kategori'];
    $isi = mysqli_real_escape_string($conn,$_POST['isi']);

    $icon = $data['icon'];

    if(isset($_FILES['icon']) && $_FILES['icon']['error']==0){

        $folder="../../uploads/icon_buku_saku/";

        if(!is_dir($folder)){
            mkdir($folder,0777,true);
        }

        $ext = strtolower(pathinfo($_FILES['icon']['name'],PATHINFO_EXTENSION));

        $allow = ['png','jpg','jpeg','svg','webp'];

        if(in_array($ext,$allow)){

            $namaBaru = uniqid().".".$ext;

            move_uploaded_file(
                $_FILES['icon']['tmp_name'],
                $folder.$namaBaru
            );

            if($icon!="" && file_exists($folder.$icon)){
                unlink($folder.$icon);
            }

            $icon = $namaBaru;

        }

    }

    $update = mysqli_query($conn,"
    UPDATE materi_buku_saku
    SET

    judul='$judul',

    id_kategori='$kategori',

    isi='$isi',

    icon='$icon'

    WHERE

    id_materi='$id'

    ");

    if($update){

        echo "

        <script>

        alert('Materi berhasil diperbarui');

        location='index.php';

        </script>";

        exit;

    }else{

        echo "

        <script>

        alert('Gagal memperbarui materi');

        </script>";

    }

}

$kategori = mysqli_query($conn,"
SELECT *
FROM kategori_buku_saku
ORDER BY nama_kategori
ASC
");
?>

<!DOCTYPE html>
<html>

<?php include "../../includes/header.php"; ?>

<body>

<?php include "../../includes/navbar.php"; ?>

<div class="container-fluid">

<div class="row">

<?php include "../../includes/admin_sidebar.php"; ?>

<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
  <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">

    <h3>
        <i class="bi bi-pencil-square"></i>
        Edit Materi Buku Saku
    </h3>

    <a href="index.php" class="btn btn-secondary">

        <i class="bi bi-arrow-left"></i>

        Kembali

    </a>

  </div>

  <div class="card shadow">

    <div class="card-header bg-primary text-white">

        <h5 class="mb-0">

            Form Edit Materi

        </h5>

    </div>

    <div class="card-body">
      <form
      method="POST"
      enctype="multipart/form-data"

      onsubmit="return confirm('Simpan perubahan materi?')">

      <div class="mb-3">

      <label>Judul</label>

      <input
      type="text"
      name="judul"
      class="form-control"
      maxlength="255"
      value="<?= htmlspecialchars($data['judul']); ?>"
      required>

      </div>

      <div class="mb-3">

      <label>Kategori</label>

      <select
      name="kategori"
      class="form-select">

      <?php while($k=mysqli_fetch_assoc($kategori)): ?>

      <option
      value="<?= $k['id_kategori']; ?>"

      <?= $k['id_kategori']==$data['id_kategori']?'selected':''; ?>

      >

      <?= $k['nama_kategori']; ?>

      </option>

      <?php endwhile; ?>

      </select>

      </div>

      <div class="mb-3">

      <label>Icon</label>

      <?php if($data['icon']!=""): ?>

      <div class="mb-3">

      <label>Icon Saat Ini</label>

      <br>

      <img
      src="../../uploads/icon_buku_saku/<?= htmlspecialchars($data['icon']); ?>"
      class="img-thumbnail"
      style="max-height:100px">

      </div>

      <?php endif; ?>

      <input
      type="file"
      name="icon"
      class="form-control">

      </div>

      <div class="mb-3">

      <label>Isi Materi</label>

      <textarea
        id="editor"
        name="isi">

        <?= htmlspecialchars($data['isi']); ?>

      </textarea>

      </div>

      <div class="text-end mt-4">
      <button class="btn btn-success" name="update">
          <i class="bi bi-save"></i> Update
      </button>

      <a href="index.php" class="btn btn-secondary">
          <i class="bi bi-arrow-left"></i> Kembali
      </a>
      </div>

      </form>

    </div>

  </div>

</main>

</div>

</div>

<?php include "../../includes/footer.php"; ?>

<!-- DI SINI LETAK SCRIPT -->

<script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>

<script>
ClassicEditor
.create(document.querySelector('#editor'))
.then(editor => {

    editor.editing.view.change(writer => {

        writer.setStyle(
            'min-height',
            '450px',
            editor.editing.view.document.getRoot()
        );

    });

})
.catch(error => {
    console.error(error);
});
</script>

</body>
</html>