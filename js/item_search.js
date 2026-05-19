(function ($) {
    function escapeHtml(value) {
        return $('<div>').text(value ?? '').html();
    }

    function buildBidderBadge(item) {
        if (item.highest_bidder) {
            return `
                <span class="position-absolute top-0 start-0 m-2 badge rounded-pill text-bg-success text-white">
                    <i class="bi bi-trophy"></i> Highest Bidder
                </span>
            `;
        }

        if (item.bidder) {
            return `
                <span class="position-absolute top-0 start-0 m-2 badge rounded-pill text-bg-secondary text-white">
                    <i class="bi bi-hammer"></i> Bidder
                </span>
            `;
        }

        return '';
    }

    function buildSaleTypeBadges(item) {
        if (item.is_auction) {
            let html = `
                <span class="position-absolute top-0 end-0 m-2 badge rounded-pill text-bg-warning text-white">
                    <i class="bi bi-hammer"></i> Auction
                </span>
            `;

            if (item.buy_now_price !== null && item.buy_now_price !== '') {
                html += `
                    <span class="position-absolute end-0 me-2 mt-5 badge rounded-pill text-bg-primary">
                        <i class="bi bi-bag"></i> Buy Now
                    </span>
                `;
            }

            return html;
        }

        if (item.buy_now_price !== null && item.buy_now_price !== '') {
            return `
                <span class="position-absolute top-0 end-0 m-2 badge rounded-pill text-bg-primary">
                    <i class="bi bi-bag"></i> Buy Now
                </span>
            `;
        }

        return '';
    }

    function buildPictureCountBadge(item) {
        if (item.picture_count > 1) {
            return `
                <span class="position-absolute bottom-0 end-0 m-2 badge rounded-pill text-bg-dark opacity-75">
                    <i class="bi bi-images"></i> ${item.picture_count} images
                </span>
            `;
        }

        return '';
    }

    function buildPriceBlock(item) {
        if (item.buy_now_price !== null && item.buy_now_price !== '') {
            return `<p class="get-buy-now-price">€ ${escapeHtml(item.buy_now_price)}</p>`;
        }

        return `<p class="starting-bid">€ ${escapeHtml(item.starting_bid ?? '')}</p>`;
    }

    function buildCurrentBidBlock(item) {
        if (!item.has_bids) {
            return `<div></div>`;
        }

        return `
            <div>
                <label class="current-bid-label" for="current-bid-${item.id}">Current bid</label>
                <p class="card-price" id="current-bid-${item.id}">€ ${escapeHtml(item.max_bid ?? '')}</p>
            </div>
        `;
    }

    function buildItemCard(item, openFrom) {
        const thumbnail = item.thumbnail || 'img/item_placeholder/item_placeholder.jpg';

        return `
            <div class="col">
                <a href="item/open/${item.id}/${encodeURIComponent(openFrom)}" class="text-decoration-none item-card-link">
                    <div class="card">
                        <div class="position-relative">
                            <img src="${escapeHtml(thumbnail)}"
                                 alt="item_thumbnail"
                                 class="card-img-top w-100">

                            ${buildBidderBadge(item)}
                            ${buildSaleTypeBadges(item)}
                            ${buildPictureCountBadge(item)}
                        </div>

                        <div class="card-body">
                            <h6 class="card-title">${escapeHtml(item.title)}</h6>
                            <p class="card-owner">by ${escapeHtml(item.owner_pseudo ?? '')}</p>

                            <div class="row row-cols-md-2">
                                ${buildPriceBlock(item)}
                                ${buildCurrentBidBlock(item)}
                            </div>

                            <p class="card-left_time">
                                <i class="bi bi-clock"></i> ${escapeHtml(item.time_left ?? '')}
                            </p>
                        </div>
                    </div>
                </a>
            </div>
        `;
    }

    function renderSections(sections, openFrom) {
        const $sectionsContainer = $('#item-search-sections');
        const $emptyMessage = $('#item-search-empty');

        let html = '';
        let hasItems = false;
        let renderedSectionIndex = 0;

        sections.forEach(function (section) {
            if (!section.items || section.items.length === 0) {
                return;
            }

            hasItems = true;

            const cards = section.items.map(function (item) {
                return buildItemCard(item, openFrom);
            }).join('');

            html += `
                <section>
                    <h2 class="${renderedSectionIndex === 0 ? 'pt-5' : 'pt-3'}">${escapeHtml(section.title)}</h2>
                    <div class="row row-cols-md-4 g-4 ${renderedSectionIndex === sections.length - 1 ? 'pb-5' : ''}">
                        ${cards}
                    </div>
                </section>
            `;

            renderedSectionIndex++;
        });

        $sectionsContainer.html(html);
        $emptyMessage.toggleClass('d-none', hasItems);
    }

    function initItemSearch() {
        const $config = $('#item-search-config');

        if ($config.length === 0) {
            return;
        }

        const searchUrl = $config.data('searchUrl');
        const openFrom = $config.data('openFrom');
        const $searchBox = $('#item-search-box');
        const $searchInput = $('#item-search-input');

        $searchBox.removeClass('d-none');

        let debounceTimer = null;

        $searchInput.on('input', function () {
            const query = $(this).val();

            clearTimeout(debounceTimer);

            debounceTimer = setTimeout(function () {
                $.ajax({
                    url: searchUrl,
                    method: 'POST',
                    dataType: 'json',
                    data: {query: query}
                })
                    .done(function (response) {
                        renderSections(response.sections || [], openFrom);
                    })
                    .fail(function () {
                        console.error('Item search request failed.');
                    });
            }, 250);
        });

        // propagation filtre url en bas à gauche lors du survol de l'item dans browse & my_items
            $(document).on('mouseenter', '.item-card-link', function (e) {
                const $link = $(this);
                const filterValue = ($searchInput.length > 0) ? $searchInput.val().trim() : '';

                if (filterValue === '') {
                    $link.attr('href', baseUrl);
                } else {
                    $.post('item/encode_filter_service', {filter: filterValue}, function (response) {
                        if (response && response.encoded) {
                            const baseUrl = $link.attr('href');
                            $link.attr('href', baseUrl + '/' + response.encoded);
                            $link.data('filtered', true);
                        }
                    }, 'json');
                }
            });

        // propagation du filtre sur la page suivante
            $(document).on('click', '.item-card-link', function (e) {
                e.preventDefault();

                const currentUrl = $(this).attr('href');
                const filterValue = ($searchInput.length > 0) ? $searchInput.val().trim() : '';

                if (filterValue === '') {
                    window.location.href = currentUrl;
                } else {
                    $.post('item/encode_filter_service', {filter: filterValue}, function (response) {
                        if (response && response.encoded) {
                            window.location.href = currentUrl + '/' + response.encoded;
                        } else {
                            window.location.href = currentUrl;
                        }
                    }, 'json');
                }
            });

            // propagation inverse (btn back)
            const parts = window.location.pathname.split('/');
            const lastPart = parts[parts.length - 1];
            if (lastPart !== openFrom && lastPart !== '') {
                $.post('item/decode_filter_service', {encoded_filter: lastPart}, function (response) {
                    if (response && response.decoded && $searchInput.length) {
                        $searchInput.val(response.decoded.trim());
                        setTimeout(() => {
                            $searchInput.trigger('input');
                        }, 50);
                    }
                }, 'json');
            }
    }

    $(initItemSearch);
})(jQuery);
