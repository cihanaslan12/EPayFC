(function ($) {
    function getConfig() {
        return $('#manage-images-config');
    }

    function getCards() {
        return $('#sortable-images .sortable-image-card');
    }

    function getOrderedPaths() {
        const paths = [];
        getCards().each(function () {
            paths.push($(this).data('picturePath'));
        });
        return paths;
    }

    function refreshCardControls() {
        const $cards = getCards();
        const lastIndex = $cards.length - 1;

        $cards.each(function (index) {
            const newPriority = index + 1;
            const $card = $(this);

            // Après un drag & drop, les valeurs générées par PHP
            // ne changent pas toutes seules. On resynchronise donc le DOM.
            $card.find('input[name="priority"]').val(newPriority);
            $card.find('button[name="btn-left"]').prop('disabled', index === 0);
            $card.find('button[name="btn-right"]').prop('disabled', index === lastIndex);
        });
    }

    function initSortableImages() {
        const $sortable = $('#sortable-images');
        const $config = getConfig();

        if ($sortable.length === 0 || $config.length === 0) {
            return;
        }

        // État correct dès le chargement.
        refreshCardControls();

        $sortable.sortable({
            items: '.sortable-image-card',
            cursor: 'move',
            opacity: 0.85,
            tolerance: 'pointer',
            placeholder: 'sortable-placeholder',
            forcePlaceholderSize: true,

            update: function () {
                // État correct immédiatement après le drop, sans refresh.
                refreshCardControls();

                $.ajax({
                    url: $config.data('reorderUrl'),
                    method: 'POST',
                    dataType: 'json',
                    data: {
                        item_id: $config.data('itemId'),
                        ordered_paths: getOrderedPaths()
                    }
                }).fail(function () {
                    alert('Unable to save image order.');
                    window.location.reload();
                });
            }
        });
    }

    $(initSortableImages);
})(jQuery);