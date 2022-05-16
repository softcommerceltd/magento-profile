/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

define([
    'Magento_Ui/js/form/element/abstract',
    'mageUtils',
], function (Component, utils) {
    'use strict';

    return Component.extend({
        defaults: {
            isUseDefault: true,
            listens: {
                'isUseDefault': 'toggleElementUseDefault'
            }
        },

        /**
         * @inheritDoc
         */
        initObservable: function () {
            return this
                ._super()
                .observe('isUseDefault');
        },

        /**
         * @inheritDoc
         */
        initConfig: function () {
            var uid = utils.uniqueid(),
                name,
                valueUpdate,
                scope;

            this._super();

            scope = this.dataScope.split('.');
            name = scope.length > 1 ? scope.slice(1) : scope;
            let inputNameUseDefault = [...name];
            inputNameUseDefault.unshift('use_default');
            valueUpdate = this.showFallbackReset ? 'afterkeydown' : this.valueUpdate;

            _.extend(this, {
                uid: uid,
                noticeId: 'notice-' + uid,
                errorId: 'error-' + uid,
                inputName: utils.serializeName(name.join('.')),
                valueUpdate: valueUpdate,
                inputNameUseDefault: utils.serializeName(inputNameUseDefault.join('.'))
            });

            return this;
        },

        /**
         * Toggle useDefault element
         */
        toggleElementUseDefault: function () {
            this.disabled(this.isUseDefault());

            if (this.source && this.hasService()) {
                this.source.set('data.' + this.inputNameUseDefault, Number(this.isUseDefault()));
            }
        }
    });
});
