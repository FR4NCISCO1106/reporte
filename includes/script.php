<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
<script src="js/scripts.js"></script>
<script src="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/umd/simple-datatables.min.js" crossorigin="anonymous"></script>
<script src="js/datatables-simple-demo.js"></script>

<script>
// Delegación de eventos para que funcione incluso con tablas dinámicas
document.addEventListener('click', function (e) {
    const btn = e.target.closest('.view-pdf');
    if (btn) {
        const ruta = btn.getAttribute('data-archivo');
        const viewer = document.getElementById('pdfViewer');
        if (viewer) {
            viewer.setAttribute('src', ruta);
        }
    }
});

// Limpiar el src cuando se cierra el modal para liberar memoria
const myModalEl = document.getElementById('pdfModal');
if (myModalEl) {
    myModalEl.addEventListener('hidden.bs.modal', function () {
        document.getElementById('pdfViewer').setAttribute('src', '');
    });
}
</script>