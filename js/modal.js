let isPristine = true;

$(() => {
    const chargedState = $('form').serialize();
    $('#form').on('input', function () {
        const currentState = $('form').serialize();
        if (isPristine) {
            isPristine = false;
            console.log('DIRTY...')
        } else if (currentState === chargedState) {
            isPristine = true;
            console.log('PRISTINE')
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