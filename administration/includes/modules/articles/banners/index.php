new Ext.Panel({
    border: false,
	anchor: '100% 100%',
	layout: 'anchor',
    items: [
        {
            xtype: 'box',
            autoEl: {tag: 'iframe', src: '/?page=BannerManager'},
            anchor: '100% 100%'
        }
    ]
});