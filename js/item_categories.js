$(function () {
    const maxCategories = 3;
    const modalElement = document.getElementById('categoryLimitModal');
    const categoryLimitModal = modalElement ? new bootstrap.Modal(modalElement) : null;

    $('.category-checkbox').on('change', function () {
        const checkedCount = $('.category-checkbox:checked').length;

        if (checkedCount > maxCategories) {
            this.checked = false;

            if (categoryLimitModal) {
                categoryLimitModal.show();
            } else {
                alert('You can select up to 3 categories for one item.');
            }
        }
    });
});