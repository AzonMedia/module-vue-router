# azonmedia/vue-router

## Description
Generates Vue router javascript arrays like:
```javascript
export default [
    {
        path: '/',
        name: 'Home',
        component: () => import('@GuzabaPlatform.Platform/views/Home.vue'),
    },
    {
        path: '/admin',
        name: 'Admin Home',
        component: () => import('@GuzabaPlatform.Platform/views/Admin/Home.vue'),    
        children: [
            {
                path: 'components',
                name: 'Components',
                component: () => import('@GuzabaPlatform.Platform/views/Admin/Components/Components.vue'),    
                meta: {
                    'in_navigation': '1',
                    'additional_template': '@GuzabaPlatform.Platform/views/Admin/Components/NavigationHook.vue',
                }
            },
            {
                path: 'request-caching',
                name: 'Request caching',
                component: () => import('@GuzabaPlatform.RequestCaching/Admin.vue'),    
                meta: {
                    'in_navigation': '1',
                }
            },
            {
                path: 'crud',
                name: 'CRUD',
                component: () => import('@GuzabaPlatform.Crud/Crud.vue'),    
                meta: {
                    'in_navigation': '1',
                    'additional_template': '@GuzabaPlatform.Crud/NavigationHook.vue',
                }
            },
            {
                path: 'crud/:class',
                name: 'CRUD class',
                component: () => import('@GuzabaPlatform.Crud/Crud.vue'),
            },
        ]
    },
];

```

## Installation

```
$ composer require azonmedia/vue-router
```