Ext.define('Plugin.rss-import.imported_grid', {

  extend: 'Ext.grid.GridPanel',

  columns: [
    {header: "ID", width: 50, dataIndex: 'id'},
    {flex: 1, header: _('Материал'), width: 450, dataIndex: 'material'},
    {flex: 1, header: _('Раздел'), width: 450, dataIndex: 'catalog'},
    {flex: 1, header: _('Дата импорта'), width: 450, dataIndex: 'created_at'},
    {flex: 1, header: _('URL Статьи'), width: 450, dataIndex: 'url'},
    {flex: 1, header: _('GUID Статьи'), width: 450, dataIndex: 'guid'},
  ],

  selModel: {
    mode: 'SINGLE',
    listeners: {
      'selectionchange': {
        fn: function (sm) {
          return sm;
        },
        scope: this
      }
    }
  },

  initComponent: function () {

    this.store = new Ext.data.JsonStore({
      autoDestroy: true,
      remoteSort: true,
      fields: ['material', 'catalog', 'created_at', 'url', 'guid'],
      sortInfo: {field: "ID", direction: "ASC"},
      proxy: {
        type: 'ajax',
        url: '/plugins/rss-import/scripts/data_imported.php',
        simpleSortMode: true,
        reader: {
          root: 'rows',
          idProperty: 'id'
        }
      }
    });

    this.tbar = new Ext.Toolbar({
      items: []
    });

    this.callParent();
    this.reload();
  },

  border: false,
  loadMask: true,
  stripeRows: true,

  edit: function (id) {

  },

  delete_list: function () {

  },

  call: function (action) {
  },

  reload: function () {
    this.store.load();
  }
});
