(function ($) {
        'use strict';

        var modal = $('#bhg-prize-modal');
        var form = $('#bhg-prize-form');
        var titleField = $('#bhg_prize_title');
        var descriptionField = $('#bhg_prize_description');
        var categoryField = $('#bhg_prize_category');
        var activeField = $('#bhg_prize_active');
        var linkField = $('#bhg_prize_link_url');
        var clickActionField = $('#bhg_prize_click_action');
        var linkTargetField = $('#bhg_prize_link_target');
        var categoryLinkField = $('#bhg_prize_category_link');
        var categoryTargetField = $('#bhg_prize_category_target');
        var showTitleField = $('#bhg_prize_show_title');
        var showDescriptionField = $('#bhg_prize_show_description');
        var showCategoryField = $('#bhg_prize_show_category');
        var showImageField = $('#bhg_prize_show_image');
        var submitButton = $('#bhg-prize-submit');
        var spinner = $('#bhg-prize-spinner');
        var errorNotice = $('#bhg-prize-error');
        var heading = $('#bhg-prize-modal-title');
        var idField = $('#bhg_prize_id');
        var cssFields = {
                border: $('#bhg_css_border'),
                border_color: $('#bhg_css_border_color'),
                padding: $('#bhg_css_padding'),
                margin: $('#bhg_css_margin'),
                background: $('#bhg_css_background')
        };

        var state = {
                mode: 'add'
        };

        function setSpinner(active) {
                if (!spinner.length) {
                        return;
                }

                spinner.toggleClass('is-active', Boolean(active));
        }

        function clearError() {
                if (!errorNotice.length) {
                        return;
                }

                errorNotice.addClass('hidden');
                errorNotice.find('p').text('');
        }

        function showError(message) {
                if (!errorNotice.length) {
                        window.alert(message);
                        return;
                }

                errorNotice.removeClass('hidden');
                errorNotice.find('p').text(message);
        }

        function updateSubmitLabel(mode) {
                if (!submitButton.length) {
                        return;
                }

                var label = BHGPrizesL10n.strings.saveLabel;

                if ('edit' === mode) {
                        label = BHGPrizesL10n.strings.updateLabel;
                }

                submitButton.text(label);
        }

        function updateHeading(mode) {
                if (!heading.length) {
                        return;
                }

                var text = BHGPrizesL10n.strings.modalAddTitle;
                if ('edit' === mode) {
                        text = BHGPrizesL10n.strings.modalEditTitle;
                }

                heading.text(text);
        }

        function resetMediaPreviews() {
                        form.find('.bhg-media-control input[type="hidden"]').each(function () {
                                var input = $(this);
                                setFieldValue(input.attr('id'), '', '');
                        });
        }

        function resetForm() {
                if (!form.length) {
                        return;
                }

                if (form[0]) {
                        form[0].reset();
                }

                idField.val('0');
                resetSelect(clickActionField);
                resetSelect(linkTargetField);
                resetSelect(categoryTargetField);
                clearError();
                updateSubmitLabel('add');
                updateHeading('add');
                resetMediaPreviews();
                state.mode = 'add';
        }

        function resetSelect(select) {
                if (!select || !select.length) {
                        return;
                }

                var defaults = select.data('default');
                if (typeof defaults !== 'undefined') {
                        select.val(defaults);
                }
        }

        function openModal(mode) {
                state.mode = mode || 'add';
                updateHeading(state.mode);
                updateSubmitLabel(state.mode);
                clearError();
                setSpinner(false);

                modal.removeClass('hidden');
                $('body').addClass('modal-open');

                window.setTimeout(function () {
                        titleField.trigger('focus');
                }, 50);
        }

        function closeModal() {
                modal.addClass('hidden');
                $('body').removeClass('modal-open');
                setSpinner(false);
        }

        function populateCss(css) {
                if (!css) {
                        css = {};
                }

                Object.keys(cssFields).forEach(function (key) {
                        var field = cssFields[key];
                        if (!field.length) {
                                return;
                        }

                        if (Object.prototype.hasOwnProperty.call(css, key)) {
                                field.val(css[key] || '');
                        } else if (BHGPrizesL10n.cssDefaults && Object.prototype.hasOwnProperty.call(BHGPrizesL10n.cssDefaults, key)) {
                                field.val(BHGPrizesL10n.cssDefaults[key] || '');
                        }
                });
        }

        function populateImages(images) {
                if (!images) {
                        images = {};
                }

                ['small', 'medium', 'big'].forEach(function (size) {
                        var item = images[size] || {};
                        var fieldId = 'bhg_image_' + size;
                        setFieldValue(fieldId, item.id || '', item.url || '');
                });
        }

        function populateForm(data) {
                if (!data) {
                        return;
                }

                titleField.val(data.title || '');
                descriptionField.val(data.description || '');
                categoryField.val(data.category || 'various');
                activeField.prop('checked', !!data.active);
                idField.val(data.id || '0');
                linkField.val(data.link_url || '');
                clickActionField.val(data.click_action || clickActionField.data('default') || 'link');
                linkTargetField.val(data.link_target || linkTargetField.data('default') || '_self');
                categoryLinkField.val(data.category_link_url || '');
                categoryTargetField.val(data.category_link_target || categoryTargetField.data('default') || '_self');
                showTitleField.prop('checked', data.show_title ? true : false);
                showDescriptionField.prop('checked', data.show_description ? true : false);
                showCategoryField.prop('checked', data.show_category ? true : false);
                showImageField.prop('checked', data.show_image ? true : false);

                populateCss(data.css);
                populateImages(data.images);
        }

        function fetchPrize(id) {
                if (!id) {
                        return;
                }

                setSpinner(true);
                clearError();

                $.ajax({
                        url: BHGPrizesL10n.ajaxUrl,
                        method: 'POST',
                        dataType: 'json',
                        data: {
                                action: 'bhg_get_prize',
                                nonce: BHGPrizesL10n.fetchNonce,
                                id: id
                        }
                })
                        .done(function (response) {
                                if (response && response.success && response.data) {
                                        populateForm(response.data);
                                } else {
                                        var message = (response && response.data && response.data.message) ? response.data.message : BHGPrizesL10n.strings.errorLoading;
                                        showError(message);
                                }
                        })
                        .fail(function () {
                                showError(BHGPrizesL10n.strings.errorLoading);
                        })
                        .always(function () {
                                setSpinner(false);
                        });
        }

        function openMediaFrame(targetField) {
                var frame = wp.media({
                        title: BHGPrizesL10n.chooseImage,
                        button: { text: BHGPrizesL10n.chooseImage },
                        multiple: false
                });

                frame.on('select', function () {
                        var attachment = frame.state().get('selection').first().toJSON();
                        var previewUrl = attachment.sizes && attachment.sizes.thumbnail ? attachment.sizes.thumbnail.url : attachment.url;
                        setFieldValue(targetField, attachment.id, previewUrl);
                });

                frame.open();
        }

        function setFieldValue(fieldId, attachmentId, previewUrl) {
                var input = $('#' + fieldId);
                if (!input.length) {
                        return;
                }

                input.val(attachmentId || '');
                var container = input.closest('.bhg-media-control');
                var preview = container.find('.bhg-media-preview');
                if (!preview.length) {
                        return;
                }

                if (previewUrl) {
                        preview.html('<img src="' + previewUrl + '" alt="" />');
                } else {
                        preview.html('<span class="bhg-media-placeholder">' + BHGPrizesL10n.noImage + '</span>');
                }
        }

        $(document).on('click', '#bhg-add-prize', function (event) {
                event.preventDefault();
                resetForm();
                openModal('add');
        });

        $(document).on('click', '.bhg-edit-prize', function (event) {
                event.preventDefault();
                var id = $(this).data('id');
                if (!id) {
                        return;
                }

                resetForm();
                openModal('edit');
                setSpinner(true);
                fetchPrize(id);
        });

        $(document).on('click', '.bhg-prize-modal__close, .bhg-prize-modal__backdrop', function (event) {
                event.preventDefault();
                closeModal();
        });

        $(document).on('keydown', function (event) {
                if ('Escape' === event.key && !modal.hasClass('hidden')) {
                        closeModal();
                }
        });

        $(document).on('click', '.bhg-select-media', function (event) {
                event.preventDefault();
                var target = $(this).data('target');
                if (!target) {
                        return;
                }
                openMediaFrame(target);
        });

        $(document).on('click', '.bhg-clear-media', function (event) {
                event.preventDefault();
                var target = $(this).data('target');
                if (!target) {
                        return;
                }
                setFieldValue(target, '', '');
        });

        // Close the modal if the form is submitted successfully (traditional submit).
        form.on('submit', function () {
                closeModal();
        });
})(jQuery);
