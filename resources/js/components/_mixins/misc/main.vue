<script>

    import moment from 'moment';

    export default {
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
            formatDateTime(date, withTime = false) {
                if( withTime ){
                    return moment(date).format('MMM DD YYYY @H:mmA');
                }else{
                    return moment(date).format('MMM DD YYYY');
                }
            },
        }
    }
</script>
