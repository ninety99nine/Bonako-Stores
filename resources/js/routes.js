//  Import Vue Router
import VueRouter from 'vue-router';

//  Set routes
let routes = [

    //  Login
    {
        alias: '/',
        path: '/login', name: 'login',
        meta: { layout: 'Basic', middlewareGuest: true },
        component: require('./views/auth/login/main.vue').default
    },

    //  Register
    {
        alias: '/signup',
        path: '/register', name: 'register',
        meta: { layout: 'Basic', middlewareGuest: true },
        component: require('./views/auth/register/main.vue').default
    },

    //  Forgot Password
    {
        path: '/forgot-password', name: 'forgot-password',
        meta: { layout: 'Basic', middlewareGuest: true },
        component: require('./views/auth/forgot-password/main.vue').default
    },

    //  Reset Password
    {
        path: '/reset-password', name: 'reset-password',
        meta: { layout: 'Basic', middlewareGuest: true },
        component: require('./views/auth/reset-password/main.vue').default
    },

    //  Stores
    {
        path: '/stores', name: 'show-stores',
        meta: { layout: 'Dashboard', middlewareAuth: true },
        component: require('./views/stores/list/main.vue').default
    },

   //  Create Store
   {
        path: '/stores/create', name: 'create-store',
        meta: { layout: 'Dashboard', middlewareAuth: true },
        component: require('./views/stores/create/main.vue').default
    },

    //  Single Store Overview
    {
        path: '/stores/:store_url',
        meta: { layout: 'Dashboard', middlewareAuth: true },
        component: require('./views/stores/show/main.vue').default,
        children: [
            {
                path: '/', name: 'show-store-overview',
                meta: { layout: 'Dashboard', middlewareAuth: true },
                component: require('./views/stores/show/overview/main.vue').default
            },
            {
                path: 'instant-carts', name: 'show-store-instant-carts',
                meta: { layout: 'Dashboard', middlewareAuth: true },
                component: require('./views/stores/show/instant-carts/main.vue').default
            }
        ]
    },

    //  Single Store Locations
    {
        path: '/stores/:store_url/locations',
        meta: { layout: 'Dashboard', middlewareAuth: true },
        component: require('./views/stores/show/main.vue').default,
        children: [
            {
                path: '/', name: 'show-locations',
                meta: { layout: 'Dashboard', middlewareAuth: true },
                component: require('./views/stores/show/locations/list/main.vue').default
            },
            {
                path: 'create', name: 'create-location',
                meta: { layout: 'Dashboard', middlewareAuth: true },
                component: require('./views/stores/show/locations/create/main.vue').default
            },
            {
                path: ':location_url', name: 'show-location',
                meta: { layout: 'Dashboard', middlewareAuth: true },
                component: require('./views/stores/show/locations/show/main.vue').default
            },
            {
                path: ':location_url/products', name: 'show-location-products',
                meta: { layout: 'Dashboard', middlewareAuth: true },
                component: require('./views/stores/show/locations/show/products/main.vue').default
            },
            {
                path: ':location_url/instant-carts', name: 'show-location-instant-carts',
                meta: { layout: 'Dashboard', middlewareAuth: true },
                component: require('./views/stores/show/instant-carts/main.vue').default
            }
        ]
    }

];

//  Initialise the router
const router = new VueRouter({
    routes
});

/** We can use the beforeEach() method to perform authentication. This means
 *  before accessing a given route, we can check if the route requires an
 *  authenticated user, if it does, then we just check if the user is
 *  authenticated. If the are not, we can redirect them back to the
 *  login page.
 */
router.beforeEach((to, from, next) => {

    console.log('From URL: '+ from.fullPath);
    console.log('To URL: '+ to.fullPath);

    /** Retrieve the matched route and check if it has meta.middlewareAuth set to true or set at all.
     *  If it's set to true it means we require the user to be authenticated to access the route and
     *  if they're not we're redirecting them to the login page.
     */      
    if (to.matched.some(record => record.meta.middlewareAuth)) {     
        //  Check if user is authenticated
        if (!auth.isAuthenticated()) {
            
            console.log('The user is not authenticated');
            
            console.log('We must go to the login page');

            /** Redirect to the login page. Save the current url so that 
             *  we can redirect back after we login
             */ 
            next({
                name: 'login',
                query: { redirect: to.fullPath }
            });

            return;
        }
    }

    console.log('beforeEach: Check if the user is authenticated');
    console.log('auth.isAuthenticated()');
    console.log(auth.isAuthenticated());

    /** Retrieve the matched route and check if it has meta.middlewareGuest set to true or set at all.
     *  If it's set to true it means the authenticated user cannot access the route and
     *  if they are we're redirecting them to the dashboard overview page
     */     
    if (to.matched.some(record => record.meta.middlewareGuest)) {     
        //  Check if user is authenticated
        if (auth.isAuthenticated()) {

            
            console.log('The user is authenticated');
            
            console.log('We must go to the stores page');

            /** Redirect to the stores page
             */ 
            next({
                name: 'show-stores',
            });

            return;
        }
    }

    next();
})

export default router;