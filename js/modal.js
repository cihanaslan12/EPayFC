let isPristine = true;

$(() => {
    $('#form').on('input', function () {
        if (isPristine) {
            isPristine = false;
            console.log('DIRTY...')
        }
    });
});

$('#btn-back, #footer-href a').on('click', function(e) {
    if (!isPristine) {
        e.preventDefault();
        let urlSortie = $(this).attr('href');
        console.log('ESSAIE DE CAVALER.... vers ' + urlSortie);
        $('#confirmLeave').attr('href', urlSortie);
        $('#confirmationModal').modal('show');
    }
});