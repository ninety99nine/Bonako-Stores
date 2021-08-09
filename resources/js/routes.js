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
        meta: { layout: 'Dashboard', subHeader: 'defaultSubHeader', middlewareAuth: true },
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
                path: 'location', name: 'show-store-location',
                meta: { layout: 'Dashboard', middlewareAuth: true },
                component: require('./views/stores/show/locations/show/main.vue').default
            },
            {
                path: 'orders', name: 'show-store-orders',
                meta: { layout: 'Dashboard', middlewareAuth: true },
                component: require('./views/stores/show/orders/list/main.vue').default
            },
            {
                path: 'orders/:order_url', name: 'show-store-order',
                meta: { layout: 'Dashboard', middlewareAuth: true },
                component: require('./views/stores/show/orders/show/main.vue').default
            },
            {
                path: 'products', name: 'show-store-products',
                meta: { layout: 'Dashboard', middlewareAuth: true },
                component: require('./views/stores/show/products/list/main.vue').default
            },
            {
                path: 'products/:product_url', name: 'show-store-product',
                meta: { layout: 'Dashboard', middlewareAuth: true },
                component: require('./views/stores/show/products/show/main.vue').default
            },
            {
                path: 'coupons', name: 'show-store-coupons',
                meta: { layout: 'Dashboard', middlewareAuth: true },
                component: require('./views/stores/show/coupons/list/main.vue').default
            },
            {
                path: 'coupons/:coupon_url', name: 'show-store-coupon',
                meta: { layout: 'Dashboard', middlewareAuth: true },
                component: require('./views/stores/show/coupons/show/main.vue').default
            },
            {
                path: 'instant-carts', name: 'show-store-instant-carts',
                meta: { layout: 'Dashboard', middlewareAuth: true },
                component: require('./views/stores/show/instant-carts/list/main.vue').default
            },
            {
                path: 'reports', name: 'show-store-reports',
                meta: { layout: 'Dashboard', middlewareAuth: true },
                component: require('./views/stores/show/reports/list/main.vue').default
            },
            {
                path: 'customers', name: 'show-store-customers',
                meta: { layout: 'Dashboard', middlewareAuth: true },
                component: require('./views/stores/show/customers/list/main.vue').default
            },
            {
                path: 'customers/:customer_url', name: 'show-store-customer',
                meta: { layout: 'Dashboard', middlewareAuth: true },
                component: require('./views/stores/show/customers/show/main.vue').default
            },
        ]
    },

    //  Popular Stores
    {
        path: '/popular-stores', name: 'show-popular-stores',
        meta: { layout: 'Dashboard', subHeader: 'defaultSubHeader', middlewareAuth: true },
        component: require('./views/popular-stores/list/main.vue').default
    },

    //  Adverts
    {
        path: '/adverts', name: 'show-adverts',
        meta: { layout: 'Dashboard', subHeader: 'defaultSubHeader', middlewareAuth: true },
        component: require('./views/adverts/list/main.vue').default
    },

    //  Reports
    {
        path: '/reports', name: 'show-reports',
        meta: { layout: 'Dashboard', subHeader: 'defaultSubHeader', middlewareAuth: true },
        component: require('./views/reports/main.vue').default
    },

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
