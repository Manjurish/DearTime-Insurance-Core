<?php     

return [
    'custom' => [

        'theme' => 'light',					    // options[String]: 'light'(default), 'dark', 'semi-dark'
        'sidebarCollapsed' => false,			// options[Boolean]: true, false(default)
        'navbarColor' => '',			        // options[String]: bg-primary, bg-info, bg-warning, bg-success, bg-danger, bg-dark (default: '' for #fff)
        'menuType' => 'fixed',			  // options[String]: fixed(default) / static
        'navbarType' => 'floating',				// options[String]: floating(default) / static / sticky / hidden
        'footerType' => 'static',				// options[String]: static(default) / sticky / hidden
        'bodyClass' => '',                      // add custom class
        'pageHeader' => true,                   // options[Boolean]: true(default), false (Page Header for Breadcrumbs)
        'contentLayout' => '',                  // options[String]: "" (default), content-left-sidebar, content-right-sidebar, content-detached-left-sidebar, content-detached-right-sidebar
        'blankPage' => false,                   // options[Boolean]: true, false(default)
        'mainLayoutType' => 'horizontal'        // Options[String]: horizontal, vertical(default)

    ],

];
