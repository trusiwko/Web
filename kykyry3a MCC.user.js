// ==UserScript==
// @name        kykyry3a MCC
// @namespace   http://usbo.info/
// @version     0.6
// @description Show MCC for operation in Kykyruza new web site
// @author      usbo
// @author      alezhu (adaptation to new version of https://mybank.oplata.kykyryza.ru/)
// @match       https://mybank.oplata.kykyryza.ru/
// @grant       none
// @run-at      document-start
// @source      https://raw.githubusercontent.com/trusiwko/Web/master/kykyry3a%20MCC.user.js
// @updateURL   https://raw.githubusercontent.com/trusiwko/Web/master/kykyry3a%20MCC.user.js
// @downloadURL https://raw.githubusercontent.com/trusiwko/Web/master/kykyry3a%20MCC.user.js
// ==/UserScript==


(function(window) {
    'use strict';

    var AppView;
    var log = 0;

    if (typeof HistoryView !== "undefined") {
        HistoryView.OperationView = React.createClass({
            displayName: "HistoryView.OperationView",
            shouldComponentUpdate: function(e) {
                return this.props.operation.id !== e.operation.id
            },
            render: function() {

                return React.DOM.a({
                    href: "/?popup=%2Fhistory%2Foperation%2F" + this.props.operation.id,
                    className: "history_operations_day_operation"
                }, [React.DOM.div({
                    className: "history_operations_day_operation_icon history_operations_day_operation_icon__" +
                        this.props.operation.type,
                    key: "icon"
                }), React.DOM.div({
                    className: "history_operations_day_operation_info",
                    key: "info"
                }, [React.DOM.div({
                        className: "history_operations_day_operation_info_title",
                        key: "title",
                        dangerouslySetInnerHTML: {
                            __html: this.props.operation.title + ((this.props.operation.mcc &&
                                    this.props.operation.mcc.code) ? " / " + this.props.operation.mcc
                                .code : "")
                        }
                    }), consumer.getMergedCards()
                    .length > 1 && React.DOM.div({
                            className: "history_operations_day_operation_info_card",
                            key: "card"
                        }, consumer.contractIdCardMap[this.props.operation.contractId].name +
                        " *" + consumer.contractIdCardMap[this.props.operation.contractId].panTail
                    ), (this.props.operation.tag || this.props.operation.subtitle) &&
                    React.DOM.div({
                        className: "history_operations_day_operation_info_tag",
                        key: "tag",
                        dangerouslySetInnerHTML: {
                            __html: (this.props.operation.tag || "") + " " + (this.props.operation
                                .subtitle || "")
                        }
                    })
                ]), React.DOM.div({
                    className: "history_operations_day_operation_amount",
                    key: "amount"
                }, [this.props.operation.money && React.DOM.div({
                    className: "history_operations_day_operation_amount_money",
                    key: "money",
                    dangerouslySetInnerHTML: {
                        __html: formatCurrency(this.props.operation.money.amount, this.props
                            .operation.money.currency, this.props.operation.money.income, !0
                        )
                    }
                }), this.props.operation.bonus && React.DOM.div({
                    className: "history_operations_day_operation_amount_bonus",
                    key: "bonus",
                    dangerouslySetInnerHTML: {
                        __html: formatCurrency(this.props.operation.bonus.amount, this.props
                            .operation.bonus.currency, this.props.operation.bonus.income, !0
                        )
                    }
                })])])
            }
        })
    } else {
        if (log) console.log("alezhu case");

        if (!window.__REACT_DEVTOOLS_GLOBAL_HOOK__) {
            window.__REACT_DEVTOOLS_GLOBAL_HOOK__ = {
                inject: hook
            };
        } else if (!window.__REACT_DEVTOOLS_GLOBAL_HOOK__.inject) {
            window.__REACT_DEVTOOLS_GLOBAL_HOOK__.inject = hook;
        } else if (window.__REACT_DEVTOOLS_GLOBAL_HOOK__._reactRuntime) {
            hook(window.__REACT_DEVTOOLS_GLOBAL_HOOK__._reactRuntime);
        } else {
            var inject = window.__REACT_DEVTOOLS_GLOBAL_HOOK__.inject;
            window.__REACT_DEVTOOLS_GLOBAL_HOOK__.inject = function() {
                hook.apply(this, arguments);
                inject.apply(this, arguments);
            };
        }

        function decorate(obj, attr, fn) {
            var old = obj[attr];
            obj[attr] = function() {
                var result = old.apply(this, arguments);
                fn(attr, result, arguments);
                return result;
            };
            return old;
        }


        function hook(ReactInternals) {
            if (log) console.log("hooked");
            //decorate(ReactInternals.Mount,"render",console_log);
            decorate(ReactInternals.Mount, "_renderNewRootComponent", hookAppView);
            decorate(ReactInternals.Mount, "renderComponent", hookAppView);
        }

        function hookAppView(attr, res, obj) {
            if (log) console.log(attr);
            if (log) console.log(res);

            AppView = AppView || obj[0];

            if (typeof AppView !== "undefined") {
                var timer_fn = function() {
                    var model = null;
                    var obj = AppView.props;
                    for (var i = 0; i < 100; i++) {
                        if (typeof obj.model !== 'undefined') {
                            model = obj.model;
                            break;
                        } else if (typeof obj.props !== 'undefined') {
                            obj = obj.props;
                        } else {
                            break;
                        }
                    }
                    if (model != null) {
                        var viewHistoryOperation = null;
                        for (var index = model.view.length - 1; index >= 0; index--) {
                            var view = model.view[index];
                            var viewClass = view.viewClass;
                            if (viewClass.displayName == "HistoryView" && typeof viewClass.OperationView !==
                                'undefined') {
                                viewHistoryOperation = viewClass.OperationView;
                                break;
                            }
                        }
                    }
                    if (viewHistoryOperation !== null) {
                        var render = viewHistoryOperation.prototype.render;
                        viewHistoryOperation.prototype.render = function() {
                            if (typeof this._alezhu_processed === 'undefined') {
                                this._alezhu_processed = true;
                                if (typeof this.props.operation.mcc !== 'undefined') {
                                    var mcc = this.props.operation.mcc;
                                    this.props.operation.title += ' / MCC:' + mcc.code + ' "' + mcc.description +
                                        '"';
                                }
                            }
                            var result = render.apply(this, arguments);
                            return result;
                        }
                    } else {
                        setTimeout(timer_fn, '500');
                    }
                }
                timer_fn();
            }
        }
    }
})(window);