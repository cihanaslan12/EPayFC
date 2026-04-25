(function ($) {
    let validationConfig = null;
    let titleRequestTimer = null;
    let latestTitleRequest = 0;

    const fieldState = {
        title: { touched: false, valid: false, asyncPending: false, lastAsyncValue: null },
        description: { touched: false, valid: true },
        duration: { touched: false, valid: false },
        startingBid: { touched: false, valid: true },
        instantPurchase: { touched: false, valid: true },
        directSale: { touched: false, valid: true },
        saleType: { touched: false, valid: false }
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

    function setRuleError(messages, show) {
        const $error = $('#sale-type-rule-error');
        if (!show || messages.length === 0) {
            $error.html('');
            return;
        }
        $error.html(messages.map(msg => `<div>${escapeHtml(msg)}</div>`).join(''));
    }

    function updateSaveButtonState() {
        const hasInvalidField =
            !fieldState.title.valid ||
            fieldState.title.asyncPending ||
            !fieldState.description.valid ||
            !fieldState.duration.valid ||
            !fieldState.startingBid.valid ||
            !fieldState.instantPurchase.valid ||
            !fieldState.directSale.valid ||
            !fieldState.saleType.valid;

        getSaveButton().prop('disabled', hasInvalidField);
    }

    function parsePositiveMoney(raw) {
        const value = raw.trim();

        if (value === '') {
            return { empty: true, valid: true, value: null, errors: [] };
        }

        if (!/^\d+(\.\d{1,2})?$/.test(value)) {
            return {
                empty: false,
                valid: false,
                value: null,
                errors: ['Le montant doit contenir au maximum 2 décimales.']
            };
        }

        const amount = parseFloat(value);
        if (Number.isNaN(amount) || amount < validationConfig.price.min) {
            return {
                empty: false,
                valid: false,
                value: null,
                errors: [`Le montant doit être supérieur ou égal à ${validationConfig.price.min.toFixed(2)}.`]
            };
        }

        return { empty: false, valid: true, value: amount, errors: [] };
    }

    function validateTitleSync(forceFeedback = false) {
        const $input = $('#title');
        const $group = $('#title-group');
        const $error = $('#title-error');

        const value = $input.val().trim();
        const showFeedback = fieldState.title.touched || forceFeedback;
        const errors = [];

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
            fieldState.title.lastAsyncValue = null;

            if (showFeedback) {
                setFieldInvalid($input, $group, $error, errors);
            } else {
                setFieldNeutral($input, $group, $error);
            }

            updateSaveButtonState();
            return { ok: false, value, alreadyValidated: false };
        }

        const alreadyValidated =
            fieldState.title.lastAsyncValue === value &&
            !fieldState.title.asyncPending &&
            fieldState.title.valid;

        if (!showFeedback) {
            fieldState.title.valid = true;
            setFieldNeutral($input, $group, $error);
            updateSaveButtonState();
            return { ok: true, value, alreadyValidated: true };
        }

        if (alreadyValidated) {
            setFieldValid($input, $group, $error);
            updateSaveButtonState();
            return { ok: true, value, alreadyValidated: true };
        }

        fieldState.title.valid = false;
        setFieldNeutral($input, $group, $error);
        updateSaveButtonState();
        return { ok: true, value, alreadyValidated: false };
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
                fieldState.title.lastAsyncValue = value;
                setFieldValid($input, $group, $error);
            } else {
                fieldState.title.valid = false;
                fieldState.title.lastAsyncValue = null;
                setFieldInvalid($input, $group, $error, errors);
            }

            updateSaveButtonState();
        }).fail(function () {
            if (requestId !== latestTitleRequest) {
                return;
            }

            fieldState.title.asyncPending = false;
            fieldState.title.valid = false;
            fieldState.title.lastAsyncValue = null;
            setFieldInvalid($input, $group, $error, ['Erreur lors de la validation du titre.']);
            updateSaveButtonState();
        });
    }

    function triggerTitleValidation() {
        const syncResult = validateTitleSync(false);
        if (!syncResult.ok || syncResult.alreadyValidated) {
            return;
        }

        clearTimeout(titleRequestTimer);
        fieldState.title.asyncPending = true;
        updateSaveButtonState();

        titleRequestTimer = setTimeout(function () {
            validateTitleAsync(syncResult.value);
        }, 250);
    }

    function validateDescriptionSync(forceFeedback = false) {
        const $input = $('#description');
        const $group = $('#description-group');
        const $error = $('#description-error');

        const value = $input.val().trim();
        const showFeedback = fieldState.description.touched || forceFeedback;
        const errors = [];

        if (value !== '' && value.length < validationConfig.description.min) {
            errors.push(`La description doit contenir au moins ${validationConfig.description.min} caractères.`);
        }

        fieldState.description.valid = errors.length === 0;

        if (!showFeedback) {
            setFieldNeutral($input, $group, $error);
        } else if (errors.length > 0) {
            setFieldInvalid($input, $group, $error, errors);
        } else if (value === '') {
            setFieldNeutral($input, $group, $error);
        } else {
            setFieldValid($input, $group, $error);
        }

        updateSaveButtonState();
    }

    function validateDurationSync(forceFeedback = false) {
        const $input = $('#duration');
        const $group = $('#duration-group');
        const $error = $('#duration-error');

        const rawValue = $input.val().trim();
        const showFeedback = fieldState.duration.touched || forceFeedback;
        const errors = [];

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

        fieldState.duration.valid = errors.length === 0;

        if (!showFeedback) {
            setFieldNeutral($input, $group, $error);
        } else if (errors.length > 0) {
            setFieldInvalid($input, $group, $error, errors);
        } else {
            setFieldValid($input, $group, $error);
        }

        updateSaveButtonState();
    }

    function validateMoneyField(stateKey, inputSelector, groupSelector, errorSelector, forceFeedback = false) {
        const $input = $(inputSelector);
        const $group = $(groupSelector);
        const $error = $(errorSelector);

        const showFeedback = fieldState[stateKey].touched || forceFeedback;
        const parsed = parsePositiveMoney($input.val());

        fieldState[stateKey].valid = parsed.valid;

        if (!showFeedback || parsed.empty) {
            setFieldNeutral($input, $group, $error);
        } else if (parsed.valid) {
            setFieldValid($input, $group, $error);
        } else {
            setFieldInvalid($input, $group, $error, parsed.errors);
        }

        updateSaveButtonState();
        return parsed;
    }

    function getCurrentPriceValues() {
        return {
            startingBid: parsePositiveMoney($('#starting_bid').val()),
            instantPurchase: parsePositiveMoney($('#instant_purchase_price').val()),
            directSale: parsePositiveMoney($('#direct_sale_price').val())
        };
    }

    function validateSaleTypeRules(forceFeedback = false) {
        const showFeedback = fieldState.saleType.touched || forceFeedback;
        const prices = getCurrentPriceValues();
        const errors = [];

        const individualFieldsValid =
            prices.startingBid.valid &&
            prices.instantPurchase.valid &&
            prices.directSale.valid;

        const hasStartingBid = individualFieldsValid && !prices.startingBid.empty;
        const hasInstantPurchase = individualFieldsValid && !prices.instantPurchase.empty;
        const hasDirectSale = individualFieldsValid && !prices.directSale.empty;

        if (individualFieldsValid) {
            if (!hasStartingBid && !hasDirectSale) {
                errors.push('Renseigne soit un starting bid, soit un sale price.');
            }

            if (hasDirectSale && (hasStartingBid || hasInstantPurchase)) {
                errors.push('Tu ne peux pas combiner enchère et vente directe.');
            }

            if (hasInstantPurchase && !hasStartingBid) {
                errors.push('Le prix d’achat immédiat de l’enchère nécessite un starting bid.');
            }

            if (hasStartingBid && hasInstantPurchase && prices.instantPurchase.value <= prices.startingBid.value) {
                errors.push('Le prix d’achat immédiat doit être supérieur au starting bid.');
            }
        }

        fieldState.saleType.valid = individualFieldsValid && errors.length === 0;
        setRuleError(errors, showFeedback);
        updateSaveButtonState();
    }

    function validatePriceSection(forceFeedback = false) {
        validateMoneyField('startingBid', '#starting_bid', '#starting-bid-group', '#starting-bid-error', forceFeedback);
        validateMoneyField('instantPurchase', '#instant_purchase_price', '#instant-purchase-group', '#instant-purchase-error', forceFeedback);
        validateMoneyField('directSale', '#direct_sale_price', '#direct-sale-group', '#direct-sale-error', forceFeedback);
        validateSaleTypeRules(forceFeedback);
    }

    function validateAllSyncFields(forceFeedback = false) {
        const titleSyncResult = validateTitleSync(forceFeedback);
        validateDescriptionSync(forceFeedback);
        validateDurationSync(forceFeedback);
        validatePriceSection(forceFeedback);
        return titleSyncResult;
    }

    function formIsValid() {
        return (
            fieldState.title.valid &&
            !fieldState.title.asyncPending &&
            fieldState.description.valid &&
            fieldState.duration.valid &&
            fieldState.startingBid.valid &&
            fieldState.instantPurchase.valid &&
            fieldState.directSale.valid &&
            fieldState.saleType.valid
        );
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
            validateDescriptionSync(false);
        });

        $('#duration').on('input blur', function () {
            fieldState.duration.touched = true;
            validateDurationSync(false);
        });

        $('#starting_bid').on('input blur', function () {
            fieldState.startingBid.touched = true;
            fieldState.saleType.touched = true;
            validatePriceSection(false);
        });

        $('#instant_purchase_price').on('input blur', function () {
            fieldState.instantPurchase.touched = true;
            fieldState.saleType.touched = true;
            validatePriceSection(false);
        });

        $('#direct_sale_price').on('input blur', function () {
            fieldState.directSale.touched = true;
            fieldState.saleType.touched = true;
            validatePriceSection(false);
        });

        $('#form').on('submit', function (e) {
            fieldState.title.touched = true;
            fieldState.description.touched = true;
            fieldState.duration.touched = true;
            fieldState.startingBid.touched = true;
            fieldState.instantPurchase.touched = true;
            fieldState.directSale.touched = true;
            fieldState.saleType.touched = true;

            const titleSyncResult = validateAllSyncFields(true);

            if (titleSyncResult.ok && !fieldState.title.valid && !fieldState.title.asyncPending) {
                validateTitleAsync($('#title').val().trim());
            }

            if (!formIsValid()) {
                e.preventDefault();
            }
        });
    }

    $(function () {
        if ($('#item-form-config').length === 0) {
            return;
        }

        loadValidationConfig().done(function () {
            validateAllSyncFields(false);
            bindEvents();
            updateSaveButtonState();
        });
    });
})(jQuery);