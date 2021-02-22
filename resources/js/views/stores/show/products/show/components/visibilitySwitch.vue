<template>

    <FormItem prop="visible" :error="serverVisibleError" :class="['clearfix', 'mb-0']">
        <div class="d-flex float-right">
            <span class="d-block font-weight-bold mr-2">Visible: </span>
            <Poptip trigger="hover" title="Turn On/Off" placement="bottom-end" word-wrap width="300"
                    content="Turn on to show this product on the store">
                <i-Switch v-model="productForm.visible" :disabled="isLoading" />
            </Poptip>
        </div>
    </FormItem>

</template>

<script>

    export default {
        props: {
            productForm: {
                type: Object,
                default: null
            },
            isLoading: {
                type: Boolean,
                default: false
            },
            serverErrors: {
                type: Array,
                default: function(){
                    return [];
                }
            },
        },
        data(){
            return {

            }
        },
        computed: {
            serverVisibleError(){
                return (this.serverErrors || {}).visible;
            }
        }
    };

</script>
