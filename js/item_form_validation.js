(function ($) {
    let validationConfig = null;
    let titleRequestTimer = null;
    let latestTitleRequest = 0;

    const titleState = {
        touched: false,
        valid: false,
        asyncPending: false
    };

    function getConfig() {
        return $('#item-form-config');
    }

    function getSaveButton() {
        return $('button[name="save"]');
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

    function escapeHtml(value) {
        return $('<div>').text(value ?? '').html();
    }

    function updateSaveButtonState() {
        const $save = getSaveButton();
        const hasError = !titleState.valid || titleState.asyncPending;
        $save.prop('disabled', hasError);
    }

    function validateTitleSync() {
        const $input = $('#title');
        const $group = $('#title-group');
        const $error = $('#title-error');

        const value = $input.val().trim();
        const errors = [];

        if (!titleState.touched) {
            titleState.valid = false;
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
            titleState.valid = false;
            titleState.asyncPending = false;
            setFieldInvalid($input, $group, $error, errors);
            updateSaveButtonState();
            return { ok: false, value };
        }

        titleState.valid = false;
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

        titleState.asyncPending = true;
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

            titleState.asyncPending = false;

            const errors = Object.values(response.errors || {});
            if (response.valid) {
                titleState.valid = true;
                setFieldValid($input, $group, $error);
            } else {
                titleState.valid = false;
                setFieldInvalid($input, $group, $error, errors);
            }

            updateSaveButtonState();
        }).fail(function () {
            if (requestId !== latestTitleRequest) {
                return;
            }

            titleState.asyncPending = false;
            titleState.valid = false;
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
        $('#title').on('input', function () {
            titleState.touched = true;
            triggerTitleValidation();
        });

        $('#title').on('blur', function () {
            titleState.touched = true;
            triggerTitleValidation();
        });

        $('#form').on('submit', function (e) {
            titleState.touched = true;
            const syncResult = validateTitleSync();

            if (!syncResult.ok || titleState.asyncPending || !titleState.valid) {
                e.preventDefault();
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