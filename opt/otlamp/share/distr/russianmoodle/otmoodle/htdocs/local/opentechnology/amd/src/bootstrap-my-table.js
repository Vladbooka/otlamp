import 'jquery';
import 'tableExport';
import 'bootstrap-table';
import 'bootstrap-table-locale-all';
import 'bootstrap-table-toolbar';
import 'bootstrap-table-page-changed';
import 'bootstrap-table-export';

export const init = (element, args) => {
    element.bootstrapTable(...args);
};