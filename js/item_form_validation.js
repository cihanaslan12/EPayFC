(function ($) {
    let validationConfig = null;
    let titleRequestTimer = null;
    let latestTitleRequest = 0;

    const fieldState = {
        title: {
            touched: false,
            valid: false,
            asyncPending: false
        },
        description: {
            touched: false,
            valid: true
        },
        duration: {
            touched: false,
            valid: false
        }
    };

    function getConfig() {
        return $('#item-form-config');
    }

    function getSaveButton() {
        return $('button[name="save"]');
    }

    function escapeHtml(value) {
        return $('<div>').text(value ?? '').html();
    }

    function setFieldNeutral($input, $group, $error) {
        $input.removeClass('is-valid is-invalid');
        $group.removeClass('has-valid has-invalid');
        $error.html('');
    }

    function setFieldValid($input, $group, $error) {
        $input.removeClass('is-invalid').addClass('is-valid');
        $group.removeClass('has-invalid').addClass('has-valid');
        $error.html('');
    }

    function setFieldInvalid($input, $group, $error, messages) {
        const html = messages.map(msg => `<div>${escapeHtml(msg)}</div>`).join('');
        $input.removeClass('is-valid').addClass('is-invalid');
        $group.removeClass('has-valid').addClass('has-invalid');
        $error.html(html);
    }

    function updateSaveButtonState() {
        const $save = getSaveButton();

        const hasInvalidField =
            !fieldState.title.valid ||
            !fieldState.description.valid ||
            !fieldState.duration.valid ||
            fieldState.title.asyncPending;

        $save.prop('disabled', hasInvalidField);
    }

    function validateTitleSync() {
        const $input = $('#title');
        const $group = $('#title-group');
        const $error = $('#title-error');

        const value = $input.val().trim();
        const errors = [];

        if (!fieldState.title.touched) {
            fieldState.title.valid = false;
            setFieldNeutral($input, $group, $error);
            updateSaveButtonState();
            return { ok: false, value };
        }

        if (!value) {
            errors.push('Le titre est requis.');
        } else {
            if (value.length < validationConfig.title.min) {
                errors.push(`Le titre doit contenir au moins ${validationConfig.title.min} caractères.`);
            }
            if (value.length > validationConfig.title.max) {
                errors.push(`Le titre ne peut pas dépasser ${validationConfig.title.max} caractères.`);
            }
        }

        if (errors.length > 0) {
            fieldState.title.valid = false;
            fieldState.title.asyncPending = false;
            setFieldInvalid($input, $group, $error, errors);
            updateSaveButtonState();
            return { ok: false, value };
        }

        fieldState.title.valid = false;
        setFieldNeutral($input, $group, $error);
        updateSaveButtonState();
        return { ok: true, value };
    }

    function validateTitleAsync(value) {
        const $input = $('#title');
        const $group = $('#title-group');
        const $error = $('#title-error');
        const config = getConfig();
        const itemId = config.data('itemId');

        fieldState.title.asyncPending = true;
        updateSaveButtonState();

        const requestId = ++latestTitleRequest;

        $.ajax({
            url: config.data('validateTitleUrl'),
            method: 'POST',
            dataType: 'json',
            data: {
                title: value,
                item_id: itemId
            }
        }).done(function (response) {
            if (requestId !== latestTitleRequest) {
                return;
            }

            fieldState.title.asyncPending = false;

            const errors = Object.values(response.errors || {});
            if (response.valid) {
                fieldState.title.valid = true;
                setFieldValid($input, $group, $error);
            } else {
                fieldState.title.valid = false;
                setFieldInvalid($input, $group, $error, errors);
            }

            updateSaveButtonState();
        }).fail(function () {
            if (requestId !== latestTitleRequest) {
                return;
            }

            fieldState.title.asyncPending = false;
            fieldState.title.valid = false;
            setFieldInvalid($input, $group, $error, ['Erreur lors de la validation du titre.']);
            updateSaveButtonState();
        });
    }

    function triggerTitleValidation() {
        const syncResult = validateTitleSync();
        if (!syncResult.ok) {
            return;
        }

        clearTimeout(titleRequestTimer);
        titleRequestTimer = setTimeout(function () {
            validateTitleAsync(syncResult.value);
        }, 250);
    }

    function validateDescriptionSync() {
        const $input = $('#description');
        const $group = $('#description-group');
        const $error = $('#description-error');

        const value = $input.val().trim();
        const errors = [];

        if (!fieldState.description.touched) {
            fieldState.description.valid = true;
            setFieldNeutral($input, $group, $error);
            updateSaveButtonState();
            return true;
        }

        if (value !== '' && value.length < validationConfig.description.min) {
            errors.push(`La description doit contenir au moins ${validationConfig.description.min} caractères.`);
        }

        if (errors.length > 0) {
            fieldState.description.valid = false;
            setFieldInvalid($input, $group, $error, errors);
            updateSaveButtonState();
            return false;
        }

        fieldState.description.valid = true;

        if (value === '') {
            setFieldNeutral($input, $group, $error);
        } else {
            setFieldValid($input, $group, $error);
        }

        updateSaveButtonState();
        return true;
    }

    function validateDurationSync() {
        const $input = $('#duration');
        const $group = $('#duration-group');
        const $error = $('#duration-error');

        const rawValue = $input.val().trim();
        const errors = [];

        if (!fieldState.duration.touched) {
            fieldState.duration.valid = false;
            setFieldNeutral($input, $group, $error);
            updateSaveButtonState();
            return false;
        }

        if (rawValue === '') {
            errors.push('La durée est requise.');
        } else if (!/^\d+$/.test(rawValue)) {
            errors.push('La durée doit être un entier.');
        } else {
            const value = parseInt(rawValue, 10);

            if (value < validationConfig.duration.min || value > validationConfig.duration.max) {
                errors.push(`La durée doit être comprise entre ${validationConfig.duration.min} et ${validationConfig.duration.max} jours.`);
            }
        }

        if (errors.length > 0) {
            fieldState.duration.valid = false;
            setFieldInvalid($input, $group, $error, errors);
            updateSaveButtonState();
            return false;
        }

        fieldState.duration.valid = true;
        setFieldValid($input, $group, $error);
        updateSaveButtonState();
        return true;
    }

    function validateAllSyncFields() {
        const titleSyncOk = validateTitleSync();
        validateDescriptionSync();
        validateDurationSync();
        return titleSyncOk.ok;
    }

    function loadValidationConfig() {
        const config = getConfig();

        return $.ajax({
            url: config.data('validationConfigUrl'),
            method: 'GET',
            dataType: 'json'
        }).done(function (response) {
            validationConfig = response;
        });
    }

    function bindEvents() {
        $('#title').on('input blur', function () {
            fieldState.title.touched = true;
            triggerTitleValidation();
        });

        $('#description').on('input blur', function () {
            fieldState.description.touched = true;
            validateDescriptionSync();
        });

        $('#duration').on('input blur', function () {
            fieldState.duration.touched = true;
            validateDurationSync();
        });

        $('#form').on('submit', function (e) {
            fieldState.title.touched = true;
            fieldState.description.touched = true;
            fieldState.duration.touched = true;

            const titleSyncOk = validateAllSyncFields();

            if (!titleSyncOk || fieldState.title.asyncPending || !fieldState.title.valid || !fieldState.description.valid || !fieldState.duration.valid) {
                e.preventDefault();

                if (titleSyncOk && !fieldState.title.asyncPending) {
                    validateTitleAsync($('#title').val().trim());
                }
            }
        });
    }

    $(function () {
        if ($('#item-form-config').length === 0) {
            return;
        }

        loadValidationConfig().done(function () {
            bindEvents();
            updateSaveButtonState();
        });
    });
})(jQuery);