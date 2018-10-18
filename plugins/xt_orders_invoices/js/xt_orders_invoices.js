var gridPanel = Ext.ComponentMgr.get('xt_orders_invoicesgridForm');
if (typeof gridPanel !== "undefined") {
    gridPanel.getStore().on('load', invoiceOverdue);
    gridPanel.getColumnModel().on('columnmoved', invoiceOverdue);
    gridPanel.on('sortchange', invoiceOverdue);
    invoiceOverdue();
}

function invoiceOverdue()
{
    var col = gridPanel.getColumnModel().findColumnIndex('invoice_due_date');
    var r = gridPanel.getStore().getRange();
    for (i = 0; i < r.length; i++) {
        if (r[i].json.invoice_overdue) {
            Ext.fly(gridPanel.getView().getCell(i, col)).addClass('xt_orders_invoices-overdue');
        }
    }
}