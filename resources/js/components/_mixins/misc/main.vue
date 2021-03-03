<script>

    import moment from 'moment';

    export default {
        data(){
            return {
                serverErrors: [],
                serverErrorMessage: null,
                serverSuccessMessage: null
            }
        },
        computed: {
            moment: function () {
                return moment();
            }
        },
        methods: {
            formatPrice(money, symbol) {
                let val = (money/1).toFixed(2).replace(',', '.');
                return (symbol ? symbol : '') + val.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
            },
            formatDateTime(date, format) {

                //  If we want a custom format
                if( typeof format === 'string' ){

                    return moment(date).format(format);

                //  If we want the date and time
                }else if( typeof format === 'boolean' && format === true ){

                    return moment(date).format('MMM DD YYYY @H:mmA');

                //  If we want the date and time
                }else{

                    return moment(date).format('MMM DD YYYY');

                }
                
            },
            resetErrors(){
                this.serverErrors = [];
                this.serverErrorMessage = null;
            },
            resetForm(name){

                //  Reset the errors
                this.resetErrors();

                //  Note that name represents the ref name of the form
                this.$refs[name].resetFields();

            }
        },
        directives: {
            focus: {
                inserted: function (el, binding) {
                    //  DOM is not updated yet
                    VueInstance.$nextTick(function () {

                        /** DOM is now updated
                         *
                         *  If the element reference was provided
                         */
                        if( binding.value ){

                            //  Focus on the given reference nested within the current element
                            $(el).find(binding.value).focus();

                        }else{

                            //  Focus on the current element
                            el.focus();

                        }

                    })
                }
            }
        },
        filters: {
            firstLetter: function (word) {

                //  Get the first letter
                return word.charAt(0).toUpperCase();

            }
        }
    }
</script>
