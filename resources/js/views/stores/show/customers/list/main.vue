<template>

    <div>

        <!-- If viewing single customer -->
        <template v-if="customer && isViewingCustomer">

            <!-- Single customer -->
            <singleCustomer :store="store" :location="location" :assignedLocations="assignedLocations"
                          :customers="customers" :customer="customer" @close="handleCloseCustomer()">
            </singleCustomer>

        </template>

        <!-- If viewing a list of customers -->
        <Row v-else class="mt-4">

            <Col :span="22" :offset="1">

                <!-- Heading, Add Customer Button & Watch Video Button -->
                <Row :gutter="12" :class="['border-bottom-dashed', 'mb-4', 'mt-3', 'pb-4']">

                    <Col :span="12">

                        <!-- Heading -->
                        <h1 :class="['font-weight-bold', 'text-muted']">Customers</h1>

                    </Col>

                    <Col :span="12">

                        <div class="clearfix">

                            <!-- Watch Video Button -->
                            <Button type="primary" size="default" @click.native="fetchCustomers()" :class="['float-right']">
                                <Icon type="ios-play-outline" class="mr-1" :size="20" />
                                <span>Watch Video</span>
                            </Button>

                        </div>

                    </Col>

                </Row>

                <Card>

                    <!-- Search Bar, Filters, Arrange Customer Switch, Save Changes Button & Refresh Button -->
                    <Row :gutter="12" class="mb-4">

                        <Col :span="8">

                            <!-- Search Bar -->
                            <Input v-model="searchWord" type="text" size="default" clearable placeholder="Search customer..." icon="ios-search-outline"></Input>

                        </Col>

                        <Col :span="8">

                            <!-- Filters -->
                            <Poptip trigger="hover" word-wrap class="poptip-w-100">

                                <div slot="content">
                                    <span v-if="filterDesc" :class="['font-italic']">{{ filterDesc }}</span>
                                    <span v-else>Add filters for specific customers</span>
                                </div>


                                <Select v-model="selectedFilters" size="default" multiple class="w-100"
                                        prefix="ios-funnel-outline" clearable placeholder="Add filters"
                                        @on-select="handleSelectedFilters()" @on-clear="clearfilterDesc()">

                                    <!-- Filter Options-->
                                    <Option v-for="(status, index) in statuses" :value="status.name" :key="index" :label="status.name"
                                            @mouseover.native="setFilterDesc(status.desc)"
                                            @mouseout.native="clearfilterDesc()">
                                        <span :class="['font-weight-bold']">{{ status.name }}</span>
                                    </Option>

                                </Select>

                            </Poptip>

                        </Col>

                        <Col :span="8" :class="['clearfix']">

                            <!-- Refresh Button -->
                            <Button type="default" size="default" :class="['float-right']"
                                :loading="isLoading" :disabled="isLoading"
                                @click.native="fetchCustomers()">
                                <Icon v-show="!isLoading" type="ios-refresh" class="mr-1" :size="20" />
                                <span>Refresh</span>
                            </Button>

                        </Col>

                    </Row>

                    <!-- Customer Table -->
                    <Table :columns="dynamicColumns" :data="customers" :key="tableRenderKey"
                            :loading="isLoading" no-data-text="No customers found" :style="{ overflow: 'visible' }">

                        <!-- ID Poptip -->
                        <idPoptip slot-scope="{ row, index }" slot="id" :customer="row"></idPoptip>

                        <!-- First Name -->
                        <firstName slot-scope="{ row, index }" slot="first name" :customer="row"></firstName>

                        <!-- Last Name -->
                        <lastName slot-scope="{ row, index }" slot="last name" :customer="row"></lastName>

                        <!-- Summary Poptip -->
                        <checkoutSummary slot-scope="{ row, index }" slot="summary" :localCustomer="row"></checkoutSummary>

                        <template slot-scope="{ row, index }" slot="action">
                            <Button type="default" @click.native="handleViewCustomer(row, index)">View</Button>
                        </template>

                    </Table>

                </Card>

            </Col>

        </Row>

    </div>

</template>

<script>

    import singleCustomer from './../show/main.vue';
    import lastName from '../show/components/lastName.vue';
    import idPoptip from './../show/components/idPoptip.vue';
    import firstName from '../show/components/firstName.vue';
    import checkoutSummary from '../show/components/checkoutSummary.vue';

    import miscMixin from './../../../../../components/_mixins/misc/main.vue'

    export default {
        mixins: [miscMixin],
        components: {
            idPoptip, firstName, lastName, checkoutSummary, singleCustomer
        },
        props: {
            store: {
                type: Object,
                default: null
            },
            location: {
                type: Object,
                default: null
            },
            assignedLocations: {
                type: Array,
                default: function(){
                    return [];
                }
            },
        },
        data(){
            return {
                isViewingCustomer: false,
                tableRenderKey: 1,
                isLoading: false,
                filterDesc: '',
                customer: null,
                customers: [],
                tableColumnsToShowByDefault: [
                    'Selector', 'ID', 'First Name', 'Last Name', 'Summary', 'Created Date'
                ],
                statuses: [
                    {
                        name: 'Used coupons',
                        desc: 'Customers that used coupons while placing orders'
                    },
                    {
                        name: 'Used adverts',
                        desc: 'Customers that used adverts to place orders'
                    },
                    {
                        name: 'Used instant carts',
                        desc: 'Customers that used instant carts to place orders'
                    },
                    {
                        name: 'Has orders placed by customer',
                        desc: 'Customers that submitted orders themselves'
                    },
                    {
                        name: 'Has orders placed by store',
                        desc: 'Customers with orders submitted by the store'
                    },
                    {
                        name: 'Has orders with free delivery',
                        desc: 'Customers with orders '
                    }
                ],
                selectedFilters: ['Active'],
                searchTimeout: null,
                searchWord: '',
            }
        },
        watch: {

            /**
             *  Search customers only 1 second after the user is done typing.
             */
            searchWord: function (val) {

                //  Clear the search timeout variable
                clearTimeout(this.searchTimeout);

                this.searchTimeout = setTimeout(() => {

                    //  Get the customers
                    this.fetchCustomers();

                }, 1000); // 1 sec delay
            }

        },
        computed: {
            customersUrl(){
                return this.location['_links']['bos:customers'].href;
            },
            dynamicColumns(){

                var allowedColumns = [];

                //  Customer Selector
                if(this.tableColumnsToShowByDefault.includes('Selector')){
                    allowedColumns.push({
                        type: 'selection',
                        align: 'center',
                        width: 60
                    });
                }

                //  Customer ID
                if(this.tableColumnsToShowByDefault.includes('ID')){
                    allowedColumns.push(
                        {
                            title: 'ID',
                            slot: 'id',
                            width: 100
                        }
                    );
                }

                //  Customer Last Name
                if(this.tableColumnsToShowByDefault.includes('First Name')){
                    allowedColumns.push(
                        {
                            title: 'First Name',
                            slot: 'first name'
                        }
                    );
                }

                //  Customer First Name
                if(this.tableColumnsToShowByDefault.includes('Last Name')){
                    allowedColumns.push(
                        {
                            title: 'Last Name',
                            slot: 'last name'
                        }
                    );
                }

                //  Customer Summary
                if(this.tableColumnsToShowByDefault.includes('Summary')){
                    allowedColumns.push(
                        {
                            title: 'Summary',
                            slot: 'summary',
                            align: 'center'
                        }
                    );
                }

                //  Created Date
                if(this.tableColumnsToShowByDefault.includes('Created Date')){
                    allowedColumns.push(
                    {
                        title: 'Added Date',
                        sortable: true,
                        render: (h, params) => {
                            return h('Poptip', {
                                style: {
                                    width: '100%',
                                    textAlign:'left'
                                },
                                props: {
                                    width: 280,
                                    wordWrap: true,
                                    trigger:'hover',
                                    placement: 'top',
                                    content: 'Date: '+ this.formatDateTime(params.row.created_at.date, true)
                                }
                            }, [
                                h('span', this.formatDateTime(params.row.created_at.date))
                            ])
                        }
                    })
                }

                //  Action
                allowedColumns.push(
                    {
                        title: 'Action',
                        slot: 'action',
                        width: 80,
                    }
                );

                return allowedColumns;
            },
        },
        methods: {
            handleSelectedFilters(){
                this.clearfilterDesc();
                this.fetchCustomers();
            },
            clearfilterDesc(){
                this.filterDesc = '';
            },
            setFilterDesc(description){
                this.filterDesc = description;
            },
            handleViewCustomer(customer){
                this.customer = customer;
                this.isViewingCustomer = true;
                this.$router.replace({ name: 'show-store-customer', params: Object.assign({}, this.$route.params, { customer_url: encodeURIComponent(customer._links.self.href) }) });
            },
            handleCloseCustomer(){
                this.isViewingCustomer = false;
            },
            fetchCustomers() {
                /**
                 *  Note that we need to use the $nextTick() method to get the latest data of the
                 *  "selectedFilters". This is because everytime we trigger the select option
                 *  "on-select" event, it always brings the "selectedFilters" before its
                 *  updated with the latest selected/unselected option data. This is not
                 *  desired, so the $nextTick() method helps us get the latest updates.
                 */
                this.$nextTick(() => {

                    //  If we have the customers url
                    if( this.customersUrl ){

                        //  Hold constant reference to the current Vue instance
                        const self = this;

                        //  Start loader
                        self.isLoading = true;

                        var statuses = this.selectedFilters.join(',');

                        //  Use the api call() function, refer to api.js
                        api.call('get', this.customersUrl+'?search='+this.searchWord+'&status='+statuses)
                            .then(({data}) => {

                                //  Get the customers
                                self.customers = data['_embedded']['customers'] || [];

                                //  Re-render the table
                                ++self.tableRenderKey;

                                //  Stop loader
                                self.isLoading = false;

                            })
                            .catch(response => {

                                //  Stop loader
                                self.isLoading = false;

                            });
                    }

                });

            }
        },
        created(){

            //  Get the location customers
            this.fetchCustomers();

        }
    };

</script>
