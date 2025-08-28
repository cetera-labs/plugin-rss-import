Ext.define('Plugin.rss-import.Panel', {
  extend: 'Ext.tab.Panel',
  requires: ['Plugin.rss-import.rss_grid'],

  bodyCls: 'x-window-body-default',
  cls: 'x-window-body-default',
  style: 'border: none',
  border: false,
  layout: 'border',
  items: [
    Ext.create('Plugin.rss-import.rss_grid', {
      'title': _('Настойки RSS'),
    }),
    Ext.create('Plugin.rss-import.imported_grid', {
      'title': _('Импортированные статьи'),
    })
  ]
});
