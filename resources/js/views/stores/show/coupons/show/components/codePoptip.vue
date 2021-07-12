<template>

    <Poptip word-wrap width="320" trigger="hover" placement="top" :style="{ wordBreak: 'break-word' }">

        <div slot="content" :style="{ whiteSpace: 'initial' }">
            <div v-if="coupon.activation_type.type == 'use code'" :class="['border-bottom-dashed', 'py-2', 'mb-2']">
                <span class="font-weight-bold">Code: </span>
                <span v-if="coupon.code" class="text-success">{{ coupon.code }}</span>
                <span v-else class="text-danger">No code</span>
            </div>
            <div>{{ coupon.activation_type.description }}</div>
        </div>

        <template v-if="coupon.activation_type.type == 'use code'">
            <span v-if="coupon.code">{{ coupon.code }}</span>
            <span v-else class="text-danger">No code</span>
        </template>

        <span v-else>N/A</span>

    </Poptip>

</template>

<script type="text/javascript">
    export default {
        props: {
            coupon: {
                type: Object,
                default: null
            },
            placement: {
                type: String,
                default: 'top'
            }
        },
        data(){
            return {

            }
        },
        computed: {
            type(){

                if(this.coupon.activation_type.type == 'use code'){

                    return 'success';

                }else{

                    return 'warning';

                }
            }
        }
    }
</script>
