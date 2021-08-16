<template>

    <Row :gutter="12">

        <Col :span="20" :offset="2">

            <!-- Heading, Add Advert Button & Watch Video Button -->
            <Row :gutter="12" :class="['border-bottom-dashed', 'mb-4', 'mt-3', 'pb-4']">

                <Col :span="12">

                    <!-- Heading -->
                    <h1 :class="['font-weight-bold', 'text-muted']">Subscriptions</h1>

                </Col>

                <Col :span="12">

                    <div class="clearfix">

                        <!-- Watch Video Button -->
                        <Button type="primary" size="default" @click.native="fetchSubscriptions()" :class="['float-right']">
                            <Icon type="ios-play-outline" class="mr-1" :size="20" />
                            <span>Watch Video</span>
                        </Button>

                    </div>

                </Col>

            </Row>

            <Card>

                <!-- Search Bar, Filters & Refresh Button -->
                <Row :gutter="12" class="mb-4">

                    <Col :span="8">

                        <!-- Search Bar -->
                        <Input v-model="searchWord" type="text" size="default" clearable placeholder="Search subscription..." icon="ios-search-outline"></Input>

                    </Col>

                    <Col :span="8">

                        <!-- Filters -->
                        <Poptip trigger="hover" word-wrap class="poptip-w-100">

                            <div slot="content">
                                <span v-if="filterDesc" :class="['font-italic']">{{ filterDesc }}</span>
                                <span v-else>Add filters for specific subscriptions</span>
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
                            @click.native="fetchSubscriptions()">
                            <Icon v-show="!isLoading" type="ios-refresh" class="mr-1" :size="20" />
                            <span>Refresh</span>
                        </Button>

                    </Col>

                </Row>

                <!-- Subscription Table -->
                <Table :columns="dynamicColumns" :data="subscriptions" :loading="isLoading"
                    no-data-text="No subscriptions found" :style="{ overflow: 'visible' }">

                    <!-- Plan Poptip -->
                    <planPoptip slot-scope="{ row, index }" slot="plan" :subscription="row"></planPoptip>

                    <!-- Amount Poptip -->
                    <amountPoptip slot-scope="{ row, index }" slot="amount" :subscription="row"></amountPoptip>

                    <!-- Active Poptip -->
                    <activePoptip slot-scope="{ row, index }" slot="active" :subscription="row"></activePoptip>

                    <!-- Start At Poptip -->
                    <startAtPoptip slot-scope="{ row, index }" slot="start date" :subscription="row"></startAtPoptip>

                    <!-- End At Poptip -->
                    <endAtPoptip slot-scope="{ row, index }" slot="end date" :subscription="row"></endAtPoptip>

                </Table>

            </Card>

        </Col>

    </Row>

</template>

<script>

    import planPoptip from './../show/components/planPoptip.vue';
    import activePoptip from './../show/components/activePoptip.vue';
    import endAtPoptip from './../show/components/endAtPoptip.vue';
    import amountPoptip from './../show/components/amountPoptip.vue';
    import startAtPoptip from './../show/components/startAtPoptip.vue';

    export default {
        components: {
            planPoptip, activePoptip, amountPoptip, endAtPoptip, startAtPoptip
        },
        props: {
            subscription: {
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
            }
        },
        data(){
            return {
                isLoading: false,
                subscriptions: [],
                user: auth.getUser(),
                tableColumnsToShowByDefault: [
                    'ID', 'Plan', 'Active', 'Amount', 'Start Date', 'End Date'
                ],

                //  Filter attributes
                filterDesc: '',
                statuses: [
                    {
                        name: 'Active',
                        desc: 'Subscriptions that are active'
                    },
                    {
                        name: 'Inactive',
                        desc: 'Subscriptions that are not inactive'
                    },
                ],
                selectedFilters: [],

                //  Search attributes
                searchTimeout: null,
                searchWord: '',
            }
        },
        watch: {

            /**
             *  Search subscriptions only 1 second after the user is done typing.
             */
            searchWord: function (val) {

                //  Clear the search timeout variable
                clearTimeout(this.searchTimeout);

                this.searchTimeout = setTimeout(() => {

                    //  Get the subscriptions
                    this.fetchSubscriptions();

                }, 1000); // 1 sec delay
            }

        },
        computed: {
            subscriptionsUrl(){
                return this.user['_links']['bos:subscriptions'].href;
            },

            dynamicColumns(){

                var allowedColumns = [];

                //  Subscription ID
                if(this.tableColumnsToShowByDefault.includes('ID')){
                    allowedColumns.push(
                        {
                            title: 'ID',
                            key: 'id',
                            width: 100
                        }
                    );
                }

                //  Plan
                if(this.tableColumnsToShowByDefault.includes('Plan')){
                    allowedColumns.push(
                        {
                            title: 'Plan',
                            slot: 'plan'
                        }
                    );
                }

                //  Amount
                if(this.tableColumnsToShowByDefault.includes('Amount')){
                    allowedColumns.push(
                        {
                            title: 'Amount',
                            slot: 'amount'
                        }
                    );
                }

                //  Active
                if(this.tableColumnsToShowByDefault.includes('Active')){
                    allowedColumns.push(
                        {
                            title: 'Active',
                            slot: 'active'
                        }
                    );
                }

                //  Start Date
                if(this.tableColumnsToShowByDefault.includes('Start Date')){
                    allowedColumns.push(
                        {
                            title: 'Start Date',
                            slot: 'start date'
                        }
                    );
                }

                //  End Date
                if(this.tableColumnsToShowByDefault.includes('End Date')){
                    allowedColumns.push(
                        {
                            title: 'End Date',
                            slot: 'end date'
                        }
                    );
                }

                return allowedColumns;

            }
        },
        methods: {
            handleSelectedFilters(){
                this.clearfilterDesc();
                this.fetchSubscriptions();
            },
            clearfilterDesc(){
                this.filterDesc = '';
            },
            setFilterDesc(description){
                this.filterDesc = description;
            },
            fetchSubscriptions() {
                /**
                 *  Note that we need to use the $nextTick() method to get the latest data of the
                 *  "selectedFilters". This is because everytime we trigger the select option
                 *  "on-select" event, it always brings the "selectedFilters" before its
                 *  updated with the latest selected/unselected option data. This is not
                 *  desired, so the $nextTick() method helps us get the latest updates.
                 */
                this.$nextTick(() => {

                    //  If we have the subscription url
                    if( this.subscriptionsUrl ){

                        //  Hold constant reference to the current Vue instance
                        const self = this;

                        //  Start loader
                        self.isLoading = true;

                        var statuses = this.selectedFilters.join(',');

                        //  Use the api call() function, refer to api.js
                        api.call('get', this.subscriptionsUrl+'?search='+this.searchWord+'&status='+statuses)
                            .then(({data}) => {

                                //  Get the subscriptions
                                self.subscriptions = ((data || [])['_embedded'] || [])['subscriptions'];

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

            //  Get the user subscriptions
            this.fetchSubscriptions();

            //  Change dashboard heading
            this.$emit('changeHeading', 'Bonako Online');

        }
    };

</script>
