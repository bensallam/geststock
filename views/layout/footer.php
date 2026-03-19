    </main>

    <footer class="text-center text-muted small py-3 border-top bg-light">
      <?= APP_NAME ?> v<?= APP_VERSION ?> &mdash; <?= date('Y') ?>
    </footer>
  </div><!-- /#page-content -->
</div><!-- /#wrapper -->

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<!-- App JS -->
<script src="<?= APP_URL ?>/public/js/app.js"></script>
<?php if (isset($extraJs)): echo $extraJs; endif; ?>
</body>
</html>
