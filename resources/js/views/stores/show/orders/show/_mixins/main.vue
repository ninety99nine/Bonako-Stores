<script>

    export default {
        computed: {
            isCancelled(){
                return this.order._embedded.status.name == 'Cancelled' ? true : false;
            },
            cancelledClass(){
                return this.isCancelled ? ['cut-text', (this.isCancelled() ? 'text-danger text-cancelled' : 'text-dark')] : [];
            }
        }
    }
</script>
