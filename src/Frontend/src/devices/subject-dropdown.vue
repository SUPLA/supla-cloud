<template>
    <div class="subject-dropdown">
        <ul class="nav nav-tabs">
            <li class="active"><a>Kanały</a></li>
            <li><a>Grupy kanałów</a></li>
            <li><a>Sceny</a></li>
        </ul>
        <channels-dropdown v-model="subjectId"></channels-dropdown>
    </div>
</template>

<script>
    import ChannelsDropdown from "./channels-dropdown";

    export default {
        props: ['type', 'value'],
        components: {ChannelsDropdown},
        data() {
            return {
                subjectId: undefined,
                subjectType: undefined
            };
        },
        mounted() {

        },
        methods: {}
    };
</script>

<style lang="scss">
    @import "../styles/variables";

    .subject-dropdown {

        .nav-tabs {
            li > a {
                padding: 3px 5px;
            }
        }
    }
</style>
