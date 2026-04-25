// récupération des images du carousel au chargement de la page
$(async () => {
    const itemId = $('#item-container').data('id');

    if (itemId) {
        const pictures = await $.getJSON(`item/get_pictures_service/${itemId}`);
        rebuild_all(pictures);
        $('.thumb-link').first().find('img').addClass('border border-info-subtle border-4'); // bordure
    }
});

function rebuild_all(pictures) {
    const $carousel = $('.carousel-inner');
    const $thumbnails = $('.thumbnails-container');

    $carousel.empty();
    $thumbnails.empty();

    pictures.forEach((picture_path, index) => {
        const isActive = (index === 0) ? "active" : "";

        $carousel.append(`
            <div class="carousel-item ${isActive}">
                <img src="${picture_path}" class="d-block w-100" alt="Main picture">
            </div>
        `);
        $thumbnails.append(`
            <a href="#" data-bs-target="#carousel" data-bs-slide-to="${index}" class="thumb-link">
                <img src="${picture_path}" 
                    class="border rounded p-1 bg-light"
                    alt="thumbnail ${index}"
                    style="width:90px;height:90px;object-fit:cover;display:block;">
            </a>
        `);
    });

    // affichage de la bordure au clique de la vignette
    $('.thumb-link').on('click', function(e) {
        e.preventDefault()
        $('.thumb-link img').removeClass('border-info-subtle border-4');
        $(this).find('img').addClass('border border-info-subtle border-4');
    });
}