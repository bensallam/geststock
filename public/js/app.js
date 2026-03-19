/**
 * GestStock — Main JavaScript
 */

document.addEventListener('DOMContentLoaded', function () {

  // ─── Sidebar toggle ────────────────────────────────────
  const sidebarToggle = document.getElementById('sidebarToggle');
  const sidebar       = document.getElementById('sidebar');

  if (sidebarToggle && sidebar) {
    sidebarToggle.addEventListener('click', function () {
      if (window.innerWidth < 768) {
        // Mobile: slide in/out
        sidebar.classList.toggle('mobile-open');
      } else {
        // Desktop: collapse width
        sidebar.classList.toggle('collapsed');
      }
    });

    // Close sidebar on mobile when clicking outside
    document.addEventListener('click', function (e) {
      if (
        window.innerWidth < 768 &&
        sidebar.classList.contains('mobile-open') &&
        !sidebar.contains(e.target) &&
        e.target !== sidebarToggle
      ) {
        sidebar.classList.remove('mobile-open');
      }
    });
  }

  // ─── Delete confirmation modal ──────────────────────────
  const deleteModal = document.getElementById('deleteModal');

  if (deleteModal) {
    const bsModal   = new bootstrap.Modal(deleteModal);
    const deleteName = document.getElementById('deleteName');
    const deleteId   = document.getElementById('deleteId');
    const deleteForm = document.getElementById('deleteForm');

    document.querySelectorAll('.btn-delete').forEach(function (btn) {
      btn.addEventListener('click', function () {
        const name   = this.dataset.name   || 'cet élément';
        const id     = this.dataset.id;
        const action = this.dataset.action;

        if (deleteName) deleteName.textContent = name;
        if (deleteId)   deleteId.value         = id;
        if (deleteForm) deleteForm.action       = action;

        bsModal.show();
      });
    });
  }

  // ─── Auto-dismiss alerts ────────────────────────────────
  document.querySelectorAll('.alert-dismissible').forEach(function (alert) {
    setTimeout(function () {
      const bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
      if (bsAlert) bsAlert.close();
    }, 5000);
  });

  // ─── Tooltips ───────────────────────────────────────────
  document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function (el) {
    new bootstrap.Tooltip(el);
  });

});
