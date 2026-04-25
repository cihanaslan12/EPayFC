(function ($) {
    function getConfig() {
        return $('#manage-images-config');
    }

    function getOrderedPaths() {
        const paths = [];
        $('#sortable-images .sortable-image-card').each(function () {
            paths.push($(this).data('picturePath'));
        });
        return paths;
    }

    function initSortableImages() {
        const $sortable = $('#sortable-images');
        const $config = getConfig();

        if ($sortable.length === 0 || $config.length === 0) {
            return;
        }

        $sortable.sortable({
            items: '.sortable-image-card',
            cursor: 'move',
            opacity: 0.85,
            tolerance: 'pointer',
            placeholder: 'sortable-placeholder',
            forcePlaceholderSize: true,
            update: function () {
                const orderedPaths = getOrderedPaths();

                $.ajax({
                    url: $config.data('reorderUrl'),
                    method: 'POST',
                    dataType: 'json',
                    data: {
                        item_id: $config.data('itemId'),
                        ordered_paths: orderedPaths
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