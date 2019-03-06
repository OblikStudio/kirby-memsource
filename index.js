panel.plugin('oblik/memsource', {
  views: {
    todos: {
      label: 'Memsource',
      icon: 'globe',
      component: {
        template: `
          <k-view class='k-todos-view'>
            <k-header>
              Memsource
            </k-header>

            Plugin.

          </k-view>
        `
      }
    }
  }
});
