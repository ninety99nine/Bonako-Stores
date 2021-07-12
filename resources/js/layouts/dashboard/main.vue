<template>

    <div>

        <!-- Dashboard Header For Authenticated User -->
        <component v-if="dashboardHeader" :is="dashboardHeader" :heading="heading"></component>

        <!-- Dashboard Sub-Header For Authenticated User -->
        <component v-if="dashboardSubHeader" :is="dashboardSubHeader"></component>

        <!-- Place the custom route content here -->
        <router-view @changeHeading="changeHeading($event)" />

    </div>

</template>

<script>

    import defaultHeader from './header/main.vue';
    import defaultSubHeader from './sub-header/main.vue';

    export default {
        components: { defaultHeader, defaultSubHeader },
        data(){
            return {
                heading: null,
                defaultDashboardHeader: 'defaultHeader'
            }
        },
        computed: {
            dashboardHeader(){
                /**
                 *  Check the route meta for any defined header to use.
                 *  If not set use the Default Dashboard Header.
                 */
                return (this.$route.meta.header || this.defaultDashboardHeader );
            },
            dashboardSubHeader(){
                /**
                 *  Check the route meta for any defined sub header to use
                 */
                return (this.$route.meta.subHeader);
            }
        },
        methods: {
            changeHeading(name){
                this.heading = name;
            }
        },
    };

</script>
