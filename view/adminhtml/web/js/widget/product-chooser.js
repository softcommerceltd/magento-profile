/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
define([
    'jquery',
    'Magento_Ui/js/modal/modal'
], function($) {
    'use strict';
    return function (options) {
        let gridContent = options.html,
            gridUrl = options.url,
            gridPopupBtn = options.trigger_btn,
            massActionObj = options.massaction_obj,
            productExportGridObj = options.product_export_grid_obj;

        let productChooserWidget = $('<div>').html(gridContent).modal({
            modalClass: 'product-chooser-widget',
            responsive: true,
            innerScroll: true,
            title: $.mage.__('Select products to export.'),
            type: 'slide',
            buttons: [{
                text: 'Add Products',
                class: 'action-primary',
                click: function () {
                    (function ($) {
                        $.ajax({
                            url: gridUrl,
                            method: 'POST',
                            data: {product_ids: getSelectedIds()},
                            showLoader: true
                        }).done(function (data) {
                            createMessage(null, null);
                            if (data.error) {
                                createMessage(data.error, 'error');
                            } else {
                                getProductExportGridJsObj().resetFilter();
                                createMessage(data.success, 'success');
                                productChooserWidget.modal('closeModal');
                            }

                        }).fail(function (jqXHR, textStatus) {
                            if (window.console) {
                                console.log(textStatus);
                            }
                            location.reload();
                        });
                    })(jQuery);
                }
            }]
        });

        $('#'+gridPopupBtn).on('click',  function() {
            productChooserWidget.modal('openModal');
        });

        function getSelectedIds() {
            return getProductGridMassActionJsObj().getCheckedValues();
        }

        function getProductExportGridJsObj() {
            return window[productExportGridObj];
        }

        function getProductGridMassActionJsObj() {
            return window[massActionObj];
        }

        function createMessage(txt, type) {
            if (!txt && !type) {
                $('.page-main-actions').next('.messages').remove();
                $('.page-main-actions').next('#messages').remove();
            } else {
                let html = '<div id="messages">' +
                    '<div class="messages">' +
                    '<div class="message message-' + type + type + '">' +
                    '<div data-ui-id="messages-message-' + type + '">' +
                    txt +
                    '</div></div></div>';
                $('.page-main-actions').after(html);
            }
        }
    };
});