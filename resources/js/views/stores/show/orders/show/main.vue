<template>

    <Row class="mt-4">

        <Col :span="22" :offset="1">

            <Row :gutter="12">

                <Col :span="24" :class="['border-bottom-dashed', 'mb-4', 'mt-3', 'pb-4']">

                    <!-- If we are loading, Show Loader -->
                    <Loader v-if="isLoadingOrder" class="mb-2">Searching order</Loader>

                    <!-- Order #, Statuses & Watch Video Button -->
                    <Row v-else-if="!isLoadingOrder && localOrder" :gutter="12">

                        <Col :span="12">

                            <div class="d-flex" :style="{ alignItems: 'flex-end' }">

                                <!-- Back Button -->
                                <Button type="default" size="default" class="mr-4" @click.native="closeOrder()">
                                    <Icon type="md-arrow-back" class="mr-1" :size="20" />
                                    <span>Back</span>
                                </Button>

                                <span :style="{ fontSize: 'x-large', lineHeight: 'initial' }" :class="['font-weight-bold', 'mr-4']">
                                    Order #{{ localOrder.number }}
                                </span>

                                <span :class="['text-muted']">{{ createdDateTime }}</span>

                            </div>

                        </Col>

                        <Col :span="6">

                            <div :class="['d-flex', 'mt-1']" :style="{ alignItems: 'flex-end' }">
                                <orderStatusBadge :status="status"></orderStatusBadge>
                                <orderStatusBadge :status="paymentStatus"></orderStatusBadge>
                                <orderStatusBadge :status="deliveryStatus"></orderStatusBadge>
                            </div>

                        </Col>

                        <Col :span="6" class="clearfix">

                            <!-- Refresh Button -->
                            <Button type="default" size="default" :class="['float-right']"
                                    @click.native="prepareOrder()">
                                <Icon type="ios-refresh" class="mr-1" :size="20" />
                                <span>Refresh</span>
                            </Button>

                            <!-- Watch Video Button -->
                            <Button type="primary" size="default" @click.native="prepareOrder()" :class="['float-right', 'mr-2']">
                                <Icon type="ios-play-outline" class="mr-1" :size="20" />
                                <span>Watch Video</span>
                            </Button>

                        </Col>

                    </Row>

                    <!-- If we are not loading and don't have the order -->
                    <Alert v-else-if="!isLoadingOrder && !localOrder" type="warning" class="mx-5" show-icon>
                        Order Not Found
                        <template slot="desc">
                        We could not get the order, try refreshing your browser. It's also possible that this order has been deleted.
                        </template>
                    </Alert>

                </Col>

                <!-- If we are not loading and have the order -->
                <template v-if="!isLoadingOrder && localOrder">

                    <Col :span="16">

                        <!-- Warning Message Alert -->
                        <Alert v-if="isPendingPayment" type="warning" show-icon>

                            <Row>

                                <Col :span="8">
                                    <span class="font-weight-bold">Pending Payment</span>

                                    <!-- Show the status description -->
                                    <Poptip trigger="hover" placement="top-start" word-wrap width="300" :content="paymentStatus.description">

                                        <!-- Show the info icon -->
                                        <Icon type="ios-information-circle-outline" :size="16" />

                                    </Poptip>
                                </Col>

                                <Col v-if="hasPaymentShortCode" :span="8">
                                    <span>Dial to pay </span>
                                    <span :class="['text-primary', 'font-weight-bold']">{{ paymentShortCode.dialing_code }}</span>

                                    <!-- Show the short code details -->
                                    <Poptip trigger="hover" word-wrap width="300">

                                        <div slot="content" class="py-2" :style="{ lineHeight: 'normal' }">
                                            <p>Inform your customer to Dial <span class="text-primary">{{ paymentShortCode.dialing_code }}</span> to pay for their order using their mobile number <span class="text-primary">{{ customer.mobile_number }}</span></p>
                                        </div>

                                        <!-- Show the info icon -->
                                        <Icon type="ios-information-circle-outline" :size="16" />

                                    </Poptip>

                                </Col>

                                <Col v-if="hasPaymentShortCode" :span="8">

                                    <!-- Payment Short Code Countdown timer -->
                                    <transition name="slide-right-fade">

                                        <countdown v-if="paymentShortCodeExpiryTime" :datetime="paymentShortCodeExpiryTime"
                                                    position="right" @expired="handlePaymentShortcodeExpiryStatus()">
                                        </countdown>

                                    </transition>

                                </Col>

                                <Col v-if="!hasPaymentShortCode" :span="16">
                                    <span>Payment shortcode expired</span>

                                    <!-- Show the status description -->
                                    <Poptip trigger="hover" word-wrap width="400">

                                        <!-- Show the info icon -->
                                        <Icon type="ios-information-circle-outline" :size="16" />

                                        <div slot="content" class="py-2" :style="{ lineHeight: 'normal' }">
                                            <p>The payment shortcode expired before the customer could pay. Generate a new payment shortcode by clicking the <span class="text-primary">Request Payment</span> button. Please inform your customer before sending a payment shortcode.</p>
                                        </div>

                                    </Poptip>
                                </Col>

                            </Row>
                        </Alert>

                        <!-- Success Message Alert -->
                        <Alert v-if="isPaid" type="success" show-icon>
                            <span class="font-weight-bold">Paid</span>

                            <!-- Show the status description -->
                            <Poptip trigger="hover" placement="top-start" word-wrap width="300" :content="paymentStatus.description">

                                <!-- Show the info icon -->
                                <Icon type="ios-information-circle-outline" :size="16" />

                            </Poptip>
                        </Alert>

                        <!-- Warning Message Alert -->
                        <Alert v-if="isPaid && isUndelivered" type="warning" show-icon>
                            <span class="font-weight-bold">Undelivered</span>

                            <!-- Show the status description -->
                            <Poptip trigger="hover" placement="top-start" word-wrap width="300" :content="deliveryStatus.description">

                                <!-- Show the info icon -->
                                <Icon type="ios-information-circle-outline" :size="16" />

                            </Poptip>
                        </Alert>

                        <!-- Success Message Alert -->
                        <Alert v-if="isDelivered" type="success" show-icon>

                            <Row>

                                <Col :span="8">

                                    <span class="font-weight-bold">Delivered</span>

                                    <!-- Show the status description -->
                                    <Poptip trigger="hover" placement="top-start" word-wrap width="300" :content="deliveryStatus.description">

                                        <!-- Show the info icon -->
                                        <Icon type="ios-information-circle-outline" :size="16" />

                                    </Poptip>

                                </Col>

                                <Col v-if="deliveryVerified" :span="16">

                                    <Icon type="ios-checkmark-circle" class="text-success" :size="16" />
                                    <span class="font-weight-bold">Delivery Verified</span>

                                    <!-- Show the status description -->
                                    <Poptip trigger="hover" placement="top" word-wrap width="300" :content="deliveryVerifiedDescription">

                                        <!-- Show the info icon -->
                                        <Icon type="ios-information-circle-outline" :size="16" />

                                    </Poptip>

                                </Col>

                            </Row>

                        </Alert>

                        <!-- Cart Items, Deliver Button, Edit Button -->
                        <Card class="mb-4">

                            <div class="clearfix">
                                <span :class="['float-left', 'font-weight-bold', 'd-block', 'my-3']"
                                    :style="{ fontSize: 'large', lineHeight: 'initial' }">
                                    Cart Items
                                </span>

                                <!-- Deliver Button -->
                                <Button v-if="itemLinesData.length && !isDelivered" type="warning" size="default" :class="['float-right']" @click.native="handleOpenVerifyOrderDeliveryModal()">
                                    <Icon type="ios-thumbs-up" class="mr-1" :size="20" />
                                    <span>Deliver</span>
                                </Button>

                                <!-- Deliver Button -->
                                <Button v-if="itemLinesData.length && !isPaid && !hasPaymentShortCode" type="warning" size="default" :class="['float-right', 'mr-2']" @click.native="handleOpenGeneratePaymentShortcodeModal()">
                                    <span>Request Payment</span>
                                </Button>

                                <!-- Edit Button -->
                                <Button v-if="itemLinesData.length && !isDelivered" type="default" size="default" :class="['float-right', 'mr-2']" @click.native="closeOrder()">
                                    <Icon type="ios-create-outline" class="mr-1" :size="20" />
                                    <span>Edit</span>
                                </Button>

                            </div>

                            <Table :columns="itemLineColumns" :data="itemLinesData"
                                    no-data-text="No items found" :style="{ overflow: 'visible' }">

                                <span slot-scope="{ row, index }" slot="name">
                                    {{ row.name }}
                                </span>

                                <span slot-scope="{ row, index }" slot="price">
                                    {{ row.unit_regular_price.currency_money }}
                                </span>

                                <span slot-scope="{ row, index }" slot="quantity">
                                    {{ row.quantity }}
                                </span>

                                <span slot-scope="{ row, index }" slot="discount" :class="['text-danger']">
                                    {{ row.sale_discount_total.amount > 0 ? '- ' : '' }}{{ row.sale_discount_total.currency_money }}
                                </span>

                                <span slot-scope="{ row, index }" slot="grand_total">
                                    {{ row.grand_total.currency_money }}
                                </span>

                            </Table>

                            <Row>

                                <Col :offset="14" :span="6" :class="['bg-light']">

                                    <ul :style="{ listStyle: 'none', lineHeight: '2em', textAlign: 'right' }">
                                        <li :class="['border-bottom', 'my-2']">
                                            <span :class="['font-weight-bold', 'mr-4']">Sub Total:</span>
                                        </li>
                                        <li>
                                            <span :class="['mr-4']">Coupon Discount:</span>
                                        </li>
                                        <li>
                                            <span :class="['mr-4']">Sale Discount:</span>
                                        </li>
                                        <li :class="['border-bottom']">
                                            <span :class="['mr-4']">Delivery Fee:</span>
                                        </li>
                                        <li :style="{ borderBottom: 'double' }" class="py-2">
                                            <span :class="['font-weight-bold', 'mr-4']">Grand Total:</span>
                                        </li>
                                    </ul>

                                </Col>

                                <Col :span="4" :class="['bg-light']">

                                    <ul :style="{ listStyle: 'none', lineHeight: '2em' }">
                                        <li :class="['border-bottom', 'my-2']">
                                            <span :class="['font-weight-bold']">{{ subTotal.currency_money }}</span>
                                        </li>
                                        <li>
                                            <span :class="['text-danger']">
                                                {{ couponTotal.amount > 0 ? '-' : '' }}
                                                {{ couponTotal.currency_money }}
                                            </span>
                                        </li>
                                        <li>
                                            <span :class="['text-danger']">
                                                {{ saleDiscountTotal.amount > 0 ? '-' : '' }}
                                                {{ saleDiscountTotal.currency_money }}
                                            </span>
                                        </li>
                                        <li :class="['text-success', 'border-bottom']">
                                            {{ deliveryFee.amount > 0 ? '+' : '' }}
                                            {{ deliveryFee.currency_money }}
                                        </li>
                                        <li :style="{ borderBottom: 'double' }" class="py-2">
                                            <span :class="['font-weight-bold']">
                                                {{ grandTotal.currency_money }}
                                            </span>
                                        </li>
                                    </ul>

                                </Col>

                            </Row>

                        </Card>

                        <!-- Coupons Applied, Edit Button -->
                        <Card class="mb-4">

                            <div class="clearfix">
                                <span :class="['float-left', 'font-weight-bold', 'd-block', 'my-3']"
                                    :style="{ fontSize: 'large', lineHeight: 'initial' }">
                                    Coupons Applied
                                </span>

                                <!-- Edit Button -->
                                <Button v-if="couponLinesData.length && !isDelivered" type="default" size="default" :class="['float-right', 'mr-2']" @click.native="closeOrder()">
                                    <Icon type="ios-create-outline" class="mr-1" :size="20" />
                                    <span>Edit</span>
                                </Button>

                            </div>

                            <Table v-if="couponLinesData.length" :columns="couponLineColumns" :data="couponLinesData"
                                    no-data-text="No coupons found" :style="{ overflow: 'visible' }">

                                <span slot-scope="{ row, index }" slot="name">
                                    {{ row.name }}
                                </span>

                                <span slot-scope="{ row, index }" slot="type">
                                    {{ row.is_fixed_rate ? 'Fixed' : row.is_percentage_rate ? 'Percentage' : '' }}
                                </span>

                                <span slot-scope="{ row, index }" slot="rate">
                                    {{ row.is_fixed_rate ? row.fixed_rate.currency_money : row.is_percentage_rate ? row.percentage_rate+'%' : '' }}
                                </span>

                            </Table>

                            <!-- No Coupons Alert -->
                            <Alert v-else type="info" show-icon>
                                <span class="font-weight-bold">No coupons applied</span>
                            </Alert>

                        </Card>

                        <!-- Transaction -->
                        <Card class="mb-4">

                            <div>
                                <span :class="['font-weight-bold', 'd-block', 'my-3']"
                                    :style="{ fontSize: 'large', lineHeight: 'initial' }">
                                    Transaction
                                </span>
                            </div>

                            <!-- Transaction Table -->
                            <Table v-if="transactionData.length" :columns="transactionColumns" :data="transactionData"
                                    no-data-text="No coupons found" :style="{ overflow: 'visible' }">

                                <span slot-scope="{ row, index }" slot="amount">
                                    {{ row.amount.currency_money }}
                                </span>

                                <span slot-scope="{ row, index }" slot="payment_method">
                                    {{ row._embedded.payment_method.name }}
                                </span>

                            </Table>

                            <!-- No transactions Alert -->
                            <Alert v-else type="info" show-icon>
                                <span class="font-weight-bold">No transaction</span>
                            </Alert>

                        </Card>

                        <!-- History -->
                        <Card>

                            <div>
                                <span :class="['font-weight-bold', 'd-block', 'my-3']"
                                    :style="{ fontSize: 'large', lineHeight: 'initial' }">
                                    History
                                </span>

                            </div>

                        </Card>

                    </Col>

                    <Col :span="8" class="clearfix">

                        <!-- Customer -->
                        <Card v-if="customer" class="cursor-pointer mb-2">

                            <span :class="['font-weight-bold', 'd-block', 'mb-3']"
                                    :style="{ fontSize: 'large', lineHeight: 'initial' }">
                                Customer
                            </span>

                            <div :class="['align-items-center', 'd-flex']">
                                <Avatar icon="ios-person" :style="{ background: '#19be6b' }" class="mr-2" />
                                <p class="mr-2">{{ customerName }}</p>
                                <p>
                                    <Icon type="ios-call-outline" class="mr-1" :size="20" />
                                    <span>{{ customer.mobile_number }}</span>
                                </p>
                            </div>

                            <div class="clearfix">

                                <!-- View Button -->
                                <Button type="default" size="default" :class="['float-right']" @click.native="closeOrder()">
                                    <Icon type="md-eye" class="mr-1" :size="20" />
                                    <span>View</span>
                                </Button>

                            </div>

                        </Card>

                        <!-- Delivery Information -->
                        <Card v-if="deliveryLine" class="cursor-pointer mb-2">

                            <span :class="['font-weight-bold', 'd-block', 'mb-3']"
                                    :style="{ fontSize: 'large', lineHeight: 'initial' }">
                                Delivery Information
                            </span>

                            <div class="d-flex">
                                <Avatar icon="ios-pin" :style="{ background: '#19be6b' }" class="mr-2" />
                                <div class="w-100">

                                    <p v-if="deliveryLine.name && (deliveryLine.name != customerName)">
                                        <span class="font-weight-bold">Name: </span><span>{{ deliveryLine.name }}</span>
                                    </p>

                                    <p v-if="deliveryLine.mobile_number && (deliveryLine.mobile_number != customer.mobile_number)">
                                        <span class="font-weight-bold">Mobile: </span>{{ deliveryLine.mobile_number }}<span></span>
                                    </p>

                                    <Divider class="my-2"></Divider>

                                    <p v-if="deliveryLine.physical_address && deliveryType == 'delivery'">
                                        <span class="font-weight-bold">Address: </span>{{ deliveryLine.physical_address }}<span></span>
                                    </p>

                                    <div class="d-flex">

                                        <p v-if="deliveryLine.day" class="mr-2">
                                            <span class="font-weight-bold">Day: </span>{{ deliveryLine.day }}<span></span>
                                        </p>

                                        <p v-if="deliveryLine.time">
                                            <span class="font-weight-bold">Time: </span>{{ deliveryLine.time }}<span></span>
                                        </p>

                                    </div>

                                    <Divider v-if="deliveryLine.destination" class="my-2"></Divider>

                                    <p v-if="deliveryLine.destination">
                                        <span class="font-weight-bold">Destination: </span>{{ deliveryLine.destination }}<span></span>
                                    </p>

                                </div>
                            </div>

                        </Card>

                        <!-- If we are loading, Show Loader -->
                        <Loader v-if="isLoadingReceivedLocation" class="mb-2"></Loader>

                        <!-- Received Location -->
                        <Card v-else class="cursor-pointer mb-2">

                            <Poptip v-if="receivedLocation" trigger="hover" :content="receivedLocationPoptipContent"
                                    word-wrap class="poptip-w-100">

                                <div :class="['align-items-center', 'd-flex']">

                                    <span :class="['font-weight-bold', 'mr-2']"
                                        :style="{ fontSize: 'medium', lineHeight: 'initial' }">
                                        Received Location
                                    </span>

                                    <Tag color="success">{{ receivedLocation.name }}</Tag>

                                </div>

                            </Poptip>

                        </Card>

                        <!-- Shared Locations -->
                        <Card v-if="isReceivingLocation" class="cursor-pointer mb-2">

                            <span :class="['font-weight-bold', 'd-block', 'mb-3']"
                                    :style="{ fontSize: 'medium', lineHeight: 'initial' }">
                                Shared Locations
                            </span>

                            <Poptip v-if="availableSharedLocations" trigger="hover" content="Share this order with other locations" word-wrap class="poptip-w-100">

                                <!-- If we are loading, Show Loader -->
                                <Loader v-if="isLoadingSharedLocations" class="mb-2"></Loader>

                                <Select v-else v-model="selectedSharedLocationIds" :loading="isUpdatingSharedLocations" size="large" class="w-100"
                                        multiple clearable prefix="ios-pin" placeholder="Select locations" @on-select="updateSharedLocations">
                                    <Option v-for="(location, index) in availableSharedLocations"
                                            :value="location.id" :key="index">{{ location.name }}</Option>
                                </Select>

                            </Poptip>

                            <!-- Error Message Alert -->
                            <Alert v-else type="info" class="p-2">
                                No locations found
                            </Alert>

                        </Card>

                    </Col>

                </template>

            </Row>

            <!--
                MODAL TO VERIFY ORDER DELIVERY
            -->
            <template v-if="isOpenVerifyOrderDeliveryModal">

                <verifyOrderDeliveryModal
                    :order="order"
                    @verified="handleVerifiedOrder"
                    @visibility="isOpenVerifyOrderDeliveryModal = $event"
                    :requiresDeliveryConfirmationCode="requiresDeliveryConfirmationCode">
                </verifyOrderDeliveryModal>

            </template>

            <!--
                MODAL TO GENERATE PAYMENT SHORTCODE
            -->
            <template v-if="isOpenGeneratePaymentShortcode">

                <generatePaymentShortcodeModal
                    :order="order"
                    @sentPaymentRequest="handleSentPaymentRequest"
                    @visibility="isOpenGeneratePaymentShortcode = $event">
                </generatePaymentShortcodeModal>

            </template>

        </Col>

    </Row>

</template>

<script>

    import orderStatusBadge from './components/orderStatusBadge.vue';
    import miscMixin from './../../../../../components/_mixins/misc/main.vue';
    import Loader from './../../../../../components/_common/loaders/default.vue';
    import countdown from './../../../../../components/_common/countdown/default.vue';
    import verifyOrderDeliveryModal from './../components/verifyOrderDeliveryModal.vue';
    import generatePaymentShortcodeModal from './../components/generatePaymentShortcodeModal.vue';

    export default {
        mixins: [miscMixin],
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
            order: {
                type: Object,
                default: null
            },
            orders: {
                type: Array,
                default: function(){
                    return [];
                }
            }
        },
        components: {
            orderStatusBadge, Loader, countdown, verifyOrderDeliveryModal, generatePaymentShortcodeModal
        },
        data () {
            return {
                isLoadingOrder: false,
                localOrder: this.order,
                itemLineColumns: [
                    {
                        title: 'Name',
                        width: 200,
                        slot: 'name'
                    },
                    {
                        title: 'Price',
                        slot: 'price'
                    },
                    {
                        title: 'Quantity',
                        slot: 'quantity',
                        align: 'center'
                    },
                    {
                        title: 'Sale Discount',
                        slot: 'discount'
                    },
                    {
                        title: 'Total',
                        slot: 'grand_total'
                    },
                ],
                couponLineColumns: [
                    {
                        title: 'Name',
                        width: 200,
                        slot: 'name'
                    },
                    {
                        title: 'Type',
                        slot: 'type',
                    },
                    {
                        title: 'Rate',
                        slot: 'rate'
                    }
                ],
                transactionColumns: [
                    {
                        title: 'Number',
                        key: 'number'
                    },
                    {
                        title: 'Type',
                        key: 'type',
                    },
                    {
                        title: 'Amount',
                        slot: 'amount'
                    },
                    {
                        title: 'Description',
                        key: 'description',
                        width: 200
                    },
                    {
                        title: 'Payment Method',
                        slot: 'payment_method',
                        width: 150
                    }
                ],
                isOpenGeneratePaymentShortcode: false,
                isOpenVerifyOrderDeliveryModal: false,
                isLoadingReceivedLocation: false,
                isUpdatingSharedLocations: false,
                isLoadingSharedLocations: false,
                selectedSharedLocationIds: [],
                receivedLocation: null,
                sharedLocations: [],
            }
        },
        watch: {
            //  If the route changes
            $route (newVal, oldVal) {

                //  Prepare the order
                this.prepareOrder();

            }
        },
        computed: {
            status(){
                return (this.localOrder._embedded.status || {});
            },
            paymentStatus(){
                return (this.localOrder._embedded.payment_status || {});
            },
            deliveryStatus(){
                return (this.localOrder._embedded.delivery_status || {});
            },
            isPaid(){
                return this.localOrder._attributes.is_paid
            },
            isPendingPayment(){
                return (this.paymentStatus.name == 'Pending');
            },
            isDelivered(){
                return this.localOrder._attributes.is_delivered
            },
            isUndelivered(){
                return !this.localOrder._attributes.is_delivered
            },
            deliveryVerified(){
                return !this.localOrder._attributes.delivery_verified
            },
            deliveryVerifiedDescription(){
                return this.localOrder._attributes.delivery_verified_description
            },
            requiresDeliveryConfirmationCode(){
                return this.localOrder._attributes.requires_delivery_confirmation_code
            },
            activeCart(){
                return (this.localOrder._embedded.active_cart || {});
            },
            subTotal(){
                return (this.activeCart.sub_total || 0);
            },
            couponTotal(){
                return (this.activeCart.coupon_total || 0);
            },
            saleDiscountTotal(){
                return (this.activeCart.sale_discount_total || 0);
            },
            deliveryFee(){
                return (this.activeCart.delivery_fee || 0);
            },
            grandTotal(){
                return (this.activeCart.grand_total || 0);
            },
            itemLinesData(){
                return ((this.activeCart._embedded || {}).item_lines || []);
            },
            couponLinesData(){
                return ((this.activeCart._embedded || {}).coupon_lines || []);
            },
            transactionData(){
                if( (this.localOrder._embedded || {}).transaction ){
                    return [ (this.localOrder._embedded || {}).transaction ];
                }else{
                    return [];
                }
            },
            createdDateTime(){
                return this.formatDateTime(this.localOrder.created_at, 'MMM DD, YYYY')
                        +' at '+ this.formatDateTime(this.localOrder.created_at, 'h:mma');
            },
            transaction(){
                return (this.localOrder._embedded.transaction || {});
            },
            customer(){
                return (this.localOrder._embedded.customer || {});
            },
            customerName(){
                return this.customer._attributes.name;
            },
            deliveryLine(){
                return (this.localOrder._embedded.delivery_line || {});
            },
            deliveryType(){
                return this.deliveryLine.delivery_type;
            },
            orderUrl(){
                if(this.localOrder){
                    //  If we have the order url via order resource
                    return this.localOrder['_links']['self']['href'];
                }else{
                    //  If we have the order url via route
                    return decodeURIComponent(this.$route.params.order_url);
                }
            },
            receivedLocationUrl(){
                return this.localOrder ? this.localOrder['_links']['bos:received-location'].href : null;
            },
            sharedLocationsUrl(){
                return this.localOrder ? this.localOrder['_links']['bos:shared-locations'].href : null;
            },
            availableSharedLocations(){
                return this.assignedLocations.filter((location) => {
                    return (location.id !== this.location.id);
                });
            },
            isReceivingLocation(){
                return (this.location.id === (this.receivedLocation || {}).id);
            },
            receivedLocationPoptipContent(){
                return (this.isReceivingLocation)
                    ? 'This order was received on this location ('+this.receivedLocation.name+')'
                        : 'This order was shared from '+this.receivedLocation.name;
            },
            hasPaymentShortCode(){
                return this.localOrder['_attributes']['payment_short_code'] ? true : false;
            },
            paymentShortCode(){
                return (this.localOrder['_attributes']['payment_short_code'] || {});
            },
            paymentShortCodeExpiryTime(){
                return this.paymentShortCode.expires_at;
            }
        },
        methods: {
            closeOrder(){

                //  If we have the orders
                if( this.orders.length ){

                    //  Notify parent to show orders list
                    this.$emit('close');

                }else{

                    /** Note that using router.push() or router.replace() does not allow us to make a
                     *  page refresh when visiting routes. This is undesirable at this moment since our
                     *  parent component contains the <router-view />. When the page does not refresh,
                     *  the <router-view /> is not able to receice the nested components defined in the
                     *  route.js file. This means that we are then not able to render the nested
                     *  components and present them. To counter this issue we must construct the
                     *  href and use "window.location.href" to make a hard page refresh.
                     */

                    var storeUrl = this.store['_links']['self'].href;

                    //  Set the route to view store orders
                    var route = {
                            name: 'show-store-orders',
                            params: {
                                store_url: encodeURIComponent(storeUrl)
                            }
                        };

                    //  Contruct the full path url
                    var href = window.location.origin + "/" + VueInstance.$router.resolve(route).href

                    //  Visit the url
                    window.location.href = href;

                }
            },
            /** Note the use of "async" and "await". This helps us to perform the
             *  api call and wait for the response before we continue any futher
             */
            async fetchOrder() {

                //  Hold constant reference to the current Vue instance
                const self = this;

                //  Start loader
                self.isLoadingOrder = true;

                //  Use the api call() function, refer to api.js
                await api.call('get', this.orderUrl)
                    .then(({data}) => {

                        //  Get the order
                        self.localOrder = data || null;

                        //  Stop loader
                        self.isLoadingOrder = false;

                    })
                    .catch(response => {

                        //  Stop loader
                        self.isLoadingOrder = false;

                    });
            },
            fetchReceivedLocation() {

                //  If we have the order received location url
                if( this.receivedLocationUrl ){

                    //  Hold constant reference to the current Vue instance
                    const self = this;

                    //  Start loader
                    self.isLoadingReceivedLocation = true;

                    //  Use the api call() function, refer to api.js
                    api.call('get', this.receivedLocationUrl)
                        .then(({data}) => {

                            //  Get the received location
                            self.receivedLocation = data;

                            //  Stop loader
                            self.isLoadingReceivedLocation = false;

                        })
                        .catch(response => {

                            //  Stop loader
                            self.isLoadingReceivedLocation = false;

                        });
                }
            },
            fetchSharedLocations() {

                //  If we have the order shared locations url
                if( this.sharedLocationsUrl ){

                    //  Hold constant reference to the current Vue instance
                    const self = this;

                    //  Start loader
                    self.isLoadingSharedLocations = true;

                    //  Use the api call() function, refer to api.js
                    api.call('get', this.sharedLocationsUrl)
                        .then(({data}) => {

                            //  Get the shared locations
                            self.sharedLocations = (((data || {})._embedded || {}).locations || []);

                            //  Set the selected shared locations
                            self.setSelectedSharedLocationIds(self.sharedLocations);

                            //  Stop loader
                            self.isLoadingSharedLocations = false;

                        })
                        .catch(response => {

                            //  Stop loader
                            self.isLoadingSharedLocations = false;

                        });
                }
            },
            setSelectedSharedLocationIds(locations){
                this.selectedSharedLocationIds = locations.map((location) => {
                    return location.id
                });
            },
            updateSharedLocations() {

                /**
                 *  Note that we need to use the $nextTick() method to get the latest data of the
                 *  "selectedSharedLocationIds". This is because everytime we trigger the select
                 *  option "on-select" event, it always brings the "selectedSharedLocationIds"
                 *  before its updated with the latest selected/unselected option data. This
                 *  is not desired, so the $nextTick() method helps us get the latest
                 *  updates.
                 */
                this.$nextTick(() => {

                    //  If we have the order shared locations url
                    if( this.sharedLocationsUrl ){

                        //  Hold constant reference to the current Vue instance
                        const self = this;

                        //  Start loader
                        self.isUpdatingSharedLocations = true;

                        let data = {
                                postData: {
                                    location_ids: this.selectedSharedLocationIds
                                }
                            };

                        //  Use the api call() function, refer to api.js
                        api.call('post', this.sharedLocationsUrl, data)
                            .then(({data}) => {

                                //  Get the shared locations
                                self.sharedLocations = (((data || {})._embedded || {}).locations || []);

                                //  Set the selected shared locations
                                self.setSelectedSharedLocationIds(self.sharedLocations);

                                //  Stop loader
                                self.isUpdatingSharedLocations = false;

                            })
                            .catch(response => {

                                //  Stop loader
                                self.isUpdatingSharedLocations = false;

                            });
                    }

                });
            },
            handleOpenVerifyOrderDeliveryModal(){
                this.isOpenVerifyOrderDeliveryModal = true;
            },
            handleOpenGeneratePaymentShortcodeModal(){
                this.isOpenGeneratePaymentShortcode = true;
            },
            handleVerifiedOrder(){

                //  Fetch the order
                this.prepareOrder();

            },
            handleSentPaymentRequest(){
                //  Make the modal is closed
                this.isOpenGeneratePaymentShortcode = false;

                //  Fetch the order
                this.prepareOrder();
            },
            handlePaymentShortcodeExpiryStatus(){

                if( this.isPendingPayment && this.hasPaymentShortCode ){

                    //  Fetch the order
                    this.prepareOrder();

                }

            },
            /** Note the use of "async" and "await". This helps us to perform the
             *  api call and wait for the response before we continue any futher
             */
            async prepareOrder(){

                //  Fetch the order
                await this.fetchOrder();

                //  Fetch the received location
                this.fetchReceivedLocation();

                //  Fetch the shared locations
                this.fetchSharedLocations();

            }
        },
        created(){

            //  Prepare the order
            this.prepareOrder();

        }
    }
</script>
