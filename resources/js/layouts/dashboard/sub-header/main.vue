<template>

    <Row id="dashboard-sub-header" :class="['bg-white', 'border-top']" :style="{ zIndex: '1' }">

        <Col :span="20" :offset="2" class="clearfix">

            <!-- Menu Links -->
            <Menu :active-name="activeLink" mode="horizontal" theme="light" width="auto" key="sub-menu">
                <MenuItem v-for="(menuLink, index) in menuLinks" :key="index" :name="menuLink.name"
                          @click.native="navigateToMenuLink(menuLink.linkName)">

                    <!-- Menu Name -->
                    <span class="text-capitalize">{{ menuLink.name }}</span>

                </MenuItem>
            </Menu>

        </Col>

    </Row>

</template>

<script>

    export default {
        data(){
            return {
                //  Access the user from auth.js
                user: auth.getUser(),
                menuLinks: [
                    {
                        name: 'my stores',
                        linkName: 'show-stores'
                    },
                    {
                        name: 'popular stores',
                        linkName: 'show-popular-stores'
                    },
                    {
                        name: 'advertiser',
                        linkName: 'show-adverts'
                    },
                    {
                        name: 'reports',
                        linkName: 'show-reports'
                    }
                ]
            }
        },
        computed: {
            activeLink(){
                //  Get the active menu link otherwise default to "My Stores" page
                if( ['show-stores'].includes(this.$route.name) ){
                    return 'my stores';
                }else if( ['show-popular-stores'].includes(this.$route.name) ){
                    return 'popular stores';
                }else if( ['show-adverts'].includes(this.$route.name) ){
                    return 'advertiser';
                }else if( ['show-reports'].includes(this.$route.name) ){
                    return 'reports';
                }
            },
        },
        methods: {
            navigateToMenuLink(linkName){

                //  Add the "menu" query to our current store route
                var route = {
                        name: linkName
                    };

                this.navigateToRoute(route);

            },
            navigateToRoute(route){

                /** Note that using router.push() or router.replace() does not allow us to make a
                 *  page refresh when visiting routes. This is undesirable at this moment since our
                 *  current component contains the <router-view />. When the page does not refresh,
                 *  the <router-view /> is not able to receice the nested components defined in the
                 *  route.js file. This means that we are then not able to render the nested
                 *  components and present them. To counter this issue we must construct the
                 *  href and use "window.location.href" to make a hard page refresh.
                 */

                //  Contruct the full path url
                var href = window.location.origin + "/" + VueInstance.$router.resolve(route).href

                //  Visit the url
                window.location.href = href;

            },
        }
    };

</script>
