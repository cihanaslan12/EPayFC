$(async () => {
    console.log("Démarrage du script AJAX..."); // Si tu ne vois pas ça en console, le JS ne tourne pas
    const itemId = $('#item-container').data('id');

    if (itemId) {
        const pictures = await $.getJSON(`item/get_pictures_service/${itemId}`);
        console.log("Données reçues :", pictures); // Si tu ne vois pas ça, l'appel n'est pas fait
        rebuild_all(pictures);
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
            <a href="#" data-bs-target="#carousel" data-bs-slide-to="${index}">
                <img src="${picture_path}" 
                    class="border rounded p-1 bg-light"
                    alt="thumbnail ${index}"
                    style="width:90px;height:90px;object-fit:cover;display:block;">
            </a>
        `);
    });
}