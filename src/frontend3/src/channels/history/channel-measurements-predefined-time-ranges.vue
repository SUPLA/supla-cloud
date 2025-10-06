<template>
  <DropdownMenu class="d-inline-block mx-2 my-2">
    <DropdownMenuTrigger class="btn btn-default btn-wrapped">
        {{ $t('Predefined time ranges') }}
    </DropdownMenuTrigger>
        <ul class="dropdown-menu dropdown-menu-right" v-if="newestLog">
            <li v-for="(range, $index) in availableRanges" :key="$index">
                <a @click="setRange(range)">{{ $t(range.label) }}</a>
            </li>
        </ul>
  </DropdownMenu>
</template>

<script>
  import {DateTime} from "luxon";
  import DropdownMenu from "@/common/gui/dropdown/dropdown-menu.vue";
  import DropdownMenuTrigger from "@/common/gui/dropdown/dropdown-menu-trigger.vue";

  export default {
    components: {DropdownMenuTrigger, DropdownMenu},
        props: {
            storage: Object,
        },
        data() {
            return {
                ranges: [
                    {
                        label: 'last 6 hours', // i18n
                        from: () => DateTime.now().minus({hours: 6}),
                        to: () => DateTime.now(),
                    },
                    {
                        label: 'last 24 hours', // i18n
                        from: () => DateTime.now().minus({hours: 24}),
                        to: () => DateTime.now(),
                    },
                    {
                        label: 'today', // i18n
                        from: () => DateTime.now().startOf('day'),
                        to: () => DateTime.now(),
                    },
                    {
                        label: 'yesterday', // i18n
                        from: () => DateTime.now().minus({days: 1}).startOf('day'),
                        to: () => DateTime.now().minus({days: 1}).endOf('day'),
                    },
                    {
                        label: 'last 7 days', // i18n
                        from: () => DateTime.now().minus({days: 7}),
                        to: () => DateTime.now(),
                    },
                    {
                        label: 'last 30 days', // i18n
                        from: () => DateTime.now().minus({days: 30}),
                        to: () => DateTime.now(),
                    },
                    {
                        label: 'this month', // i18n
                        from: () => DateTime.now().startOf('month'),
                        to: () => DateTime.now(),
                    },
                    {
                        label: 'previous month', // i18n
                        from: () => DateTime.now().minus({months: 1}).startOf('month'),
                        to: () => DateTime.now().minus({months: 1}).endOf('month'),
                    },
                    {
                        label: 'this year', // i18n
                        from: () => DateTime.now().startOf('year'),
                        to: () => DateTime.now(),
                    },
                    {
                        label: 'the whole history', // i18n
                        from: () => DateTime.fromSeconds(this.oldestLog.date_timestamp),
                        to: () => DateTime.fromSeconds(this.newestLog.date_timestamp),
                    },
                ],
                oldestLog: undefined,
                newestLog: undefined,
            };
        },
        async mounted() {
            this.oldestLog = await this.storage.getOldestLog();
            this.newestLog = await this.storage.getNewestLog();
        },
        methods: {
            setRange(range) {
                this.$emit('choose', {afterTimestampMs: range.from().toMillis(), beforeTimestampMs: range.to().toMillis()});
            }
        },
        computed: {
            availableRanges() {
                return this.ranges.filter(range => {
                    return range.from().toSeconds() < this.newestLog.date_timestamp && range.to().toSeconds() > this.oldestLog.date_timestamp;
                });
            }
        }
    };
</script>
