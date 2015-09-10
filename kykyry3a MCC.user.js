// ==UserScript==
// @name         kykyry3a MCC
// @namespace    http://usbo.info/
// @version      0.2
// @description  Show MCC
// @author       usbo
// @match        https://the-future.ru/
// @grant        none
// ==/UserScript==

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
            className: "history_operations_day_operation_icon history_operations_day_operation_icon__" + this.props.operation.type,
            key: "icon"
        }), React.DOM.div({
            className: "history_operations_day_operation_info",
            key: "info"
        }, [React.DOM.div({
            className: "history_operations_day_operation_info_title",
            key: "title",
            dangerouslySetInnerHTML: {
                __html: this.props.operation.title + " / " + ((this.props.operation.mcc && this.props.operation.mcc.code))
            }
        }), consumer.getMergedCards().length > 1 && React.DOM.div({
            className: "history_operations_day_operation_info_card",
            key: "card"
        }, consumer.contractIdCardMap[this.props.operation.contractId].name + " *" + consumer.contractIdCardMap[this.props.operation.contractId].panTail), (this.props.operation.tag || this.props.operation.subtitle) && React.DOM.div({
            className: "history_operations_day_operation_info_tag",
            key: "tag",
            dangerouslySetInnerHTML: {
                __html: (this.props.operation.tag || "") + " " + (this.props.operation.subtitle || "")
            }
        })]), React.DOM.div({
            className: "history_operations_day_operation_amount",
            key: "amount"
        }, [this.props.operation.money && React.DOM.div({
            className: "history_operations_day_operation_amount_money",
            key: "money",
            dangerouslySetInnerHTML: {
                __html: formatCurrency(this.props.operation.money.amount, this.props.operation.money.currency, this.props.operation.money.income, !0)
            }
        }), this.props.operation.bonus && React.DOM.div({
            className: "history_operations_day_operation_amount_bonus",
            key: "bonus",
            dangerouslySetInnerHTML: {
                __html: formatCurrency(this.props.operation.bonus.amount, this.props.operation.bonus.currency, this.props.operation.bonus.income, !0)
            }
        })])])
    }
});