Ext.define('Plugin.rss-import.Properties', {
    extend: 'Ext.Window',
    closeAction: 'hide',
    title: '',
    width: 800,
    height: 600,
    layout: 'fit',
    modal: true,
    resizable: true,
    border: false,
    listId: 0,
    initComponent: function () {
        this.catsGrid = Ext.create('Ext.grid.GridPanel', {
            store: new Ext.data.JsonStore({
                autoDestroy: true,
                fields: ['id', 'name'],
                totalProperty: 'total',
                proxy: {
                    type: 'ajax',
                    extraParams: {'id': 0},
                    url: '/cms/plugins/rss-import/scripts/data_catalogs.php',
                    simpleSortMode: true,
                    reader: {
                        rootProperty: 'rows',
                        root: 'rows',
                        idProperty: 'id'
                    }
                }
            }),
            hideHeaders: true,
            selModel: {
                mode: 'SINGLE',
                listeners: {
                    'selectionchange': {
                        fn: function (sm) {
                            Ext.getCmp('tb_cat_remove').setDisabled(!sm.hasSelection());
                        }, scope: this
                    }
                }
            },
            loadMask: true,
            columns: [
                {
                    width: 20, renderer: function (v, m) {
                        m.css = 'icon-folder';
                    }
                },
                {flex: 1, dataIndex: 'name'}],
            tbar: [{
                iconCls: 'icon-plus',
                tooltip: _('Добавить раздел'),
                handler: function () {
                    if (!this.siteTree) {
                        this.siteTree = Ext.create('Cetera.window.SiteTree', {
                            title: _('Выберете раздел'),
                            norootselect: 1,
                            nolink: 1
                        });
                        this.siteTree.on('select', function (res) {
                            this.catsGrid.store.add({
                                id: res.id,
                                name: res.name_to
                            });
                        }, this);
                    }
                    this.siteTree.show();
                },
                scope: this
            }, {
                id: 'tb_cat_remove',
                iconCls: 'icon-minus',
                disabled: true,
                tooltip: _('Удалить раздел'),
                handler: function () {
                    this.catsGrid.getStore().remove(this.catsGrid.getSelectionModel().getSelection()[0]);
                },
                scope: this
            }]
        });

        this.tabs = new Ext.TabPanel({
            activeTab: 0,
            border: false,
            defaults: {bodyStyle: 'padding:5px'},
            items: [{
                title: _('Основные'),
                layout: 'form',
                defaults: {anchor: '0'},
                defaultType: 'textfield',
                items: [
                    {
                        fieldLabel: _('Путь до файла импорта'),
                        name: 'rss_url',
                        allowBlank: false
                    }
                ]
            }, {
                title: _('Разделы для импорта материалов'),
                layout: 'fit',
                items: this.catsGrid,
                listeners: {
                    'activate': {
                        fn: function () {
                            this.catsGrid.getView().refresh();
                        },
                        scope: this
                    }
                }
            }]
        });

        this.form = new Ext.FormPanel({
            labelWidth: 140,
            border: false,
            method: 'POST',
            waitMsgTarget: true,
            url: '/cms/plugins/rss-import/scripts/action.php',
            layout: 'fit',
            items: this.tabs
        });

        this.items = this.form;

        this.buttons = [{
            text: _('Ok'),
            scope: this,
            handler: this.submit
        }, {
            text: _('Отмена'),
            scope: this,
            handler: function () {
                this.hide();
            }
        }];

        this.callParent();
    },

    show: function (id) {
        this.form.getForm().reset();
        this.tabs.setActiveTab(0);

        this.callParent();

        this.listId = id;
        if (id > 0) {
            Ext.Ajax.request({
                url: '/cms/plugins/rss-import/scripts/action.php',
                params: {
                    action: 'get_list',
                    id: this.listId
                },
                scope: this,
                success: function (resp) {
                    const obj = Ext.decode(resp.responseText);
                    this.setTitle(_('Импорт материалов из RSS') + ': ' + obj.data.rss_url);
                    this.form.getForm().setValues(obj.data);
                }
            });
        } else {
            this.setTitle(_('Новый импорт RSS'));
        }
        this.catsGrid.getStore().proxy.extraParams['id'] = this.listId;
        this.catsGrid.getStore().reload();
    },

    submit: function () {

        var m = [];
        var i = 0;
        this.catsGrid.store.each(function (rec) {
            m[i++] = rec.get('id');
        }, this);

        var params = {
            action: 'save_list',
            id: this.listId,
            'catalogs[]': m
        };
        this.form.getForm().submit({
            params: params,
            scope: this,
            waitMsg: _('Сохранение...'),
            success: function (resp) {
                this.fireEvent('listChanged', this.listId, this.form.getForm().findField('rss_url').getValue());
                this.hide();
            },
            failure: function (form, action) {
                var msg = _('Ошибка сохранения');
                if (action.result && action.result.message) {
                    msg = action.result.message;
                }
                Ext.Msg.alert(_('Ошибка сохранения настроек импорта фида'), msg);
            }
        });
    },
});
