(function ($) {
    let deleteTargetRow = null;
    let deleteModal = null;

    function getConfig() {
        const $config = $('#manage-categories-config');

        return {
            addUrl: $config.data('addUrl'),
            updateUrl: $config.data('updateUrl'),
            deleteUrl: $config.data('deleteUrl'),
            reorderUrl: $config.data('reorderUrl'),
            nameMin: parseInt($config.data('nameMin'), 10),
            nameMax: parseInt($config.data('nameMax'), 10)
        };
    }

    function escapeHtml(value) {
        return $('<div>').text(value ?? '').html();
    }

    function validateNameSynchronously(name) {
        const config = getConfig();
        const errors = [];

        name = (name || '').trim();

        if (name === '') {
            errors.push('Category name is required.');
        } else if (name.length < config.nameMin || name.length > config.nameMax) {
            errors.push(`Category name must contain between ${config.nameMin} and ${config.nameMax} characters.`);
        }

        return errors;
    }

    function showErrors($row, errors) {
        $row.find('.category-error').html(
            (errors || []).map(function (error) {
                return `<div>${escapeHtml(error)}</div>`;
            }).join('')
        );
    }

    function clearErrors($row) {
        $row.find('.category-error').empty();
    }

    function startEdit($row) {
        const $display = $row.find('.category-name-display');
        const $input = $row.find('.category-name-input');

        clearErrors($row);

        $input.val($display.text().trim());
        $display.addClass('d-none');
        $input.removeClass('d-none').focus().select();
    }

    function cancelEdit($row) {
        const $display = $row.find('.category-name-display');
        const $input = $row.find('.category-name-input');

        $input.addClass('d-none');
        $display.removeClass('d-none');
        clearErrors($row);
    }

    function saveEdit($row) {
        const config = getConfig();
        const id = parseInt($row.data('categoryId'), 10);
        const $display = $row.find('.category-name-display');
        const $input = $row.find('.category-name-input');
        const newName = $input.val().trim();
        const oldName = $display.text().trim();

        if (newName === oldName) {
            cancelEdit($row);
            return;
        }

        const syncErrors = validateNameSynchronously(newName);

        if (syncErrors.length > 0) {
            showErrors($row, syncErrors);
            return;
        }

        $.ajax({
            url: config.updateUrl,
            method: 'POST',
            dataType: 'json',
            data: {
                id: id,
                name: newName
            }
        })
            .done(function (response) {
                if (response.success) {
                    $display.text(response.category.name);
                    cancelEdit($row);
                }
            })
            .fail(function (xhr) {
                const response = xhr.responseJSON || {};
                showErrors($row, response.errors || ['Update failed.']);
            });
    }

    function showAddRow() {
        const $row = $('#add-category-row');
        const $input = $('#new-category-name');

        clearErrors($row);
        $input.val('');
        $row.removeClass('d-none');
        $('#show-add-category').prop('disabled', true);
        $input.focus();
    }

    function hideAddRow() {
        const $row = $('#add-category-row');

        clearErrors($row);
        $('#new-category-name').val('');
        $row.addClass('d-none');
        $('#show-add-category').prop('disabled', false);
    }

    function buildCategoryRow(category) {
        const deleteButton = category.item_count === 0
            ? `<button type="button" class="btn btn-sm btn-danger delete-category-btn">
                   <i class="bi bi-trash"></i>
               </button>`
            : `<button type="button" class="btn btn-sm btn-danger" disabled title="Category contains items">
                   <i class="bi bi-trash"></i>
               </button>`;

        return `
            <div class="category-js-row"
                 data-category-id="${category.id}"
                 data-item-count="${category.item_count}">
                <div class="category-drag-handle">
                    <i class="bi bi-grip-vertical"></i>
                </div>

                <div class="category-main">
                    <div class="category-name-display">${escapeHtml(category.name)}</div>

                    <input type="text"
                           class="form-control category-name-input d-none"
                           value="${escapeHtml(category.name)}">

                    <div class="category-error text-danger small mt-1"></div>
                </div>

                <div class="category-count">
                    <span class="badge text-bg-secondary">${category.item_count}</span>
                </div>

                <div class="category-actions">
                    <button type="button" class="btn btn-sm btn-primary edit-category-btn">
                        <i class="bi bi-pencil"></i>
                    </button>

                    ${deleteButton}
                </div>
            </div>
        `;
    }

    function saveNewCategory() {
        const config = getConfig();
        const $row = $('#add-category-row');
        const $input = $('#new-category-name');
        const name = $input.val().trim();

        const syncErrors = validateNameSynchronously(name);

        if (syncErrors.length > 0) {
            showErrors($row, syncErrors);
            return;
        }

        $.ajax({
            url: config.addUrl,
            method: 'POST',
            dataType: 'json',
            data: {
                name: name
            }
        })
            .done(function (response) {
                if (response.success) {
                    $('#categories-list').append(buildCategoryRow(response.category));
                    hideAddRow();
                    refreshSortable();
                }
            })
            .fail(function (xhr) {
                const response = xhr.responseJSON || {};
                showErrors($row, response.errors || ['Creation failed.']);
            });
    }

    function openDeleteModal($row) {
        deleteTargetRow = $row;

        const name = $row.find('.category-name-display').text().trim();
        $('#delete-category-name').text(`"${name}"`);

        deleteModal.show();
    }

    function confirmDelete() {
        if (!deleteTargetRow) {
            return;
        }

        const config = getConfig();
        const id = parseInt(deleteTargetRow.data('categoryId'), 10);

        $.ajax({
            url: config.deleteUrl,
            method: 'POST',
            dataType: 'json',
            data: {
                id: id
            }
        })
            .done(function (response) {
                if (response.success) {
                    deleteTargetRow.remove();
                    deleteTargetRow = null;
                    deleteModal.hide();
                    saveOrder();
                }
            })
            .fail(function (xhr) {
                const response = xhr.responseJSON || {};
                alert((response.errors || ['Delete failed.']).join('\n'));
                deleteModal.hide();
            });
    }

    function getCurrentOrder() {
        return $('#categories-list .category-js-row').map(function () {
            return $(this).data('categoryId');
        }).get();
    }

    function saveOrder() {
        const config = getConfig();

        $.ajax({
            url: config.reorderUrl,
            method: 'POST',
            dataType: 'json',
            data: {
                order: getCurrentOrder()
            }
        })
            .fail(function () {
                alert('Could not save category order.');
                window.location.reload();
            });
    }

    function refreshSortable() {
        const $list = $('#categories-list');

        if ($list.data('ui-sortable')) {
            $list.sortable('destroy');
        }

        $list.sortable({
            handle: '.category-drag-handle',
            placeholder: 'category-sort-placeholder',
            update: saveOrder
        });
    }

    function initManageCategories() {
        if ($('#manage-categories-config').length === 0) {
            return;
        }

        $('#categories-nojs').addClass('d-none');
        $('#categories-js').removeClass('d-none');

        const modalElement = document.getElementById('deleteCategoryModal');
        deleteModal = modalElement ? new bootstrap.Modal(modalElement) : null;

        refreshSortable();

        $('#show-add-category').on('click', showAddRow);
        $('#cancel-add-category').on('click', hideAddRow);

        $('#new-category-name').on('blur', function () {
            const value = $(this).val().trim();

            if (value !== '') {
                saveNewCategory();
            }
        });

        $('#new-category-name').on('keydown', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                saveNewCategory();
            }

            if (e.key === 'Escape') {
                hideAddRow();
            }
        });

        $(document).on('click', '.edit-category-btn', function () {
            startEdit($(this).closest('.category-js-row'));
        });

        $(document).on('blur', '.category-name-input', function () {
            saveEdit($(this).closest('.category-js-row'));
        });

        $(document).on('keydown', '.category-name-input', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                saveEdit($(this).closest('.category-js-row'));
            }

            if (e.key === 'Escape') {
                cancelEdit($(this).closest('.category-js-row'));
            }
        });

        $(document).on('click', '.delete-category-btn', function () {
            openDeleteModal($(this).closest('.category-js-row'));
        });

        $('#confirm-delete-category').on('click', confirmDelete);
    }

    $(initManageCategories);
})(jQuery);