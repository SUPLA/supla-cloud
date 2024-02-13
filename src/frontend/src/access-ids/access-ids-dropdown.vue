<template>
    <div>
        <SelectForSubjects
            multiple
            class="aid-dropdown"
            :options="aids"
            :caption="aidCaption"
            choose-prompt-i18n="choose the access identifiers"
            v-model="chosenAids"/>
    </div>
</template>

<script>
    import SelectForSubjects from "@/devices/select-for-subjects.vue";

    export default {
        props: {
            value: Array,
        },
        components: {SelectForSubjects},
        data() {
            return {
                aids: undefined,
            };
        },
        mounted() {
            this.fetchAids();
        },
        methods: {
            fetchAids() {
                this.$http.get('accessids').then(({body: aids}) => {
                    this.aids = aids;
                });
            },
            aidCaption(aid) {
                return (aid.caption || `ID${aid.id}`) + ` (${aid.relationsCount.clientApps})`;
            },
        },
        computed: {
            chosenAids: {
                get() {
                    if (!this.value || !this.aids) {
                        return [];
                    } else {
                        const ids = this.value.map(a => a.id);
                        return this.aids.filter(aid => ids.includes(aid.id));
                    }
                },
                set(aids) {
                    this.$emit('input', aids);
                }
            },
        },
    };
</script>
