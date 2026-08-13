</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="<?= $base_url ?>assets/js/app.js"></script>
<?php if (!empty($_SESSION['login'])) { ?>
<script>
  window.addEventListener('pageshow', function (event) {
    const navigation = performance.getEntriesByType('navigation')[0];
    const fromHistory = event.persisted || (navigation && navigation.type === 'back_forward');
    if (!fromHistory) {
      return;
    }

    fetch('<?= $base_url ?>logout.php', { credentials: 'same-origin', cache: 'no-store' })
      .finally(function () {
        window.location.replace('<?= $base_url ?>login.php');
      });
  });
</script>
<?php } ?>
</body>

</html>
