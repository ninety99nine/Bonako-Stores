<template>

    <div v-if="countdown" class="clearfix">

        <div :class="['float-'+position]">

            <Poptip trigger="hover" word-wrap width="300"
                    :content="countdown">

                <small>
                    <Icon type="md-time" />
                    <span v-if="title" class="font-weight-bold">{{ title }}</span>
                    <span v-if="humanize">{{ countdown_humanized == 0 ? zeroText : countdown_humanized }}</span>
                    <span v-else>{{ countdown == 0 ? zeroText : countdown }}</span>
                </small>

            </Poptip>

        </div>

    </div>

</template>

<script>

    import moment from 'moment';

    export default {
        props: {
            title: {
                type: String,
                default: 'Expires In:'
            },
            zeroText: {
                type: String,
                default: 'Expired'
            },
            datetime: {
                type: String,
                default: null
            },
            position: {
                type: String,
                default: 'left'
            },
            humanize:{
                type: Boolean,
                default: false
            }
        },
        data(){
            return {
                countdown: '',
                countdown_humanized: ''
            }
        },
        methods: {
            moment: function () {
                return moment();
            }
        },
        mounted: function(){
            setInterval(() => {

                if( this.datetime ){

                    var now  = moment(new Date()).format('DD/MM/YYYY HH:mm:ss');
                    var then = moment(this.datetime).format('DD/MM/YYYY HH:mm:ss');

                    var milliseconds = moment(then, "DD/MM/YYYY HH:mm:ss").diff(moment(now, "DD/MM/YYYY HH:mm:ss"));

                    var days = moment.duration(milliseconds).days();
                    var hours = moment.duration(milliseconds).hours();
                    var minutes = moment.duration(milliseconds).minutes();
                    var seconds = moment.duration(milliseconds).seconds();

                    this.countdown = '';
                    this.countdown_humanized = '';

                    //  If we have days
                    if( days != 0 ){
                        //  outputs: "2 days"
                        this.countdown += (days + (days == '01' ? ' day' : ' days') +' ');
                        this.countdown_humanized = this.countdown;
                    }

                    //  If we have hours
                    if( hours != 0 ){
                        //  outputs: "20 hours"
                        this.countdown += (hours + (hours == '01' ? ' hour' : ' hours') +' ');
                        this.countdown_humanized += (days == 0) ? this.countdown : '';
                    }

                    //  If we have minutes
                    if( minutes != 0 ){
                        //  outputs: "10 mins"
                        this.countdown += (minutes + (minutes == '01' ? ' min' : ' mins') +' ');
                        this.countdown_humanized += (days == 0 && hours == 0) ? this.countdown : '';
                    }

                    //  outputs: "5 secs"
                    this.countdown += (seconds + (seconds == '01' ? ' sec' : ' secs') +' ');
                    this.countdown_humanized += (days == 0 && hours == 0 && minutes == 0) ? this.countdown : '';

                    //  If we still have time to show
                    if( parseInt(days) + parseInt(hours) + parseInt(minutes) + parseInt(seconds) > 0 ){

                        //  outputs: "2 days 20 hours 10 mins 5 secs"
                        this.countdown = this.countdown.trim();

                        //  outputs: "2 days left", "3 hours left", "4 minutes left" or "5 seconds left"
                        this.countdown_humanized = this.countdown_humanized.trim() + ' left';

                    }else{

                        this.countdown = 0;

                        this.countdown_humanized = 0;

                    }

                }

            }, 1000);
        }
    }
</script>
