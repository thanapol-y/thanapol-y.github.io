</div> <!-- End Container -->
    
    <!-- Footer -->
    <footer class="bg-dark text-white text-center py-4 mt-5">
        <div class="container">
            <p class="mb-0">
                &copy; <?php echo date('Y'); ?> ระบบลงทะเบียนกิจกรรมนักศึกษา | 
                พัฒนาด้วย <i class="bi bi-heart-fill text-danger"></i>
            </p>
            <p class="mb-0 small">
                <a href="#" class="text-white text-decoration-none">นโยบายความเป็นส่วนตัว</a> | 
                <a href="#" class="text-white text-decoration-none">เงื่อนไขการใช้งาน</a> | 
                <a href="#" class="text-white text-decoration-none">ติดต่อเรา</a>
            </p>
        </div>
    </footer>
    
    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JavaScript -->
    <script>
        // Auto hide alerts after 5 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);
        
        // DataTable initialization (if needed)
        if (typeof jQuery !== 'undefined' && $.fn.DataTable) {
            $(document).ready(function() {
                $('#eventsTable').DataTable({
                    language: {
                        url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/th.json'
                    }
                });
            });
        }
    </script>
</body>
</html>