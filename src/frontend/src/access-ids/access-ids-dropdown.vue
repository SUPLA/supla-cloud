<template>
    <div>
        <div class="select-loader"
            v-if="!aids">
            <button-loading-dots></button-loading-dots>
        </div>
        <select class="selectpicker"
            :disabled="!aids"
            ref="dropdown"
            data-live-search="true"
            data-width="100%"
            data-style="btn-default btn-wrapped"
            multiple
            v-model="chosenAids">
            <!--            <option :value="undefined"-->
            <!--                :title="$t('choose the access identifiers')"-->
            <!--                v-show="!hideNone && chosenChannel">-->
            <!--                {{ $t('None') }}-->
            <!--            </option>-->
            <option v-for="aid in aids"
                :key="aid.id"
                :value="aid">
                {{ aidCaption(aid) }}
            </option>
        </select>
    </div>
</template>

<script>
    import Vue from "vue";
    import $ from "jquery";
    import "@/common/bootstrap-select";
    import ButtonLoadingDots from "../common/gui/loaders/button-loading-dots.vue";

    export default {
        props: {
            value: Array,
        },
        components: {ButtonLoadingDots},
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
                this.aids = undefined;
                this.$http.get('accessids').then(({body: aids}) => {
                    this.aids = aids;
                    this.initSelectPicker();
                });
            },
            aidCaption(aid) {
                return (aid.caption || `ID${aid.id}`) + ` (${aid.relationsCount.clientApps})`;
            },
            updateDropdownOptions() {
                Vue.nextTick(() => $(this.$refs.dropdown).selectpicker('refresh'));
            },
            initSelectPicker() {
                Vue.nextTick(() => $(this.$refs.dropdown).selectpicker(this.selectOptions));
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
            selectOptions() {
                return {
                    noneSelectedText: this.$t('choose access identifiers'),
                    liveSearchPlaceholder: this.$t('Search'),
                    noneResultsText: this.$t('No results match {0}'),
                };
            },
        },
        watch: {
            '$i18n.locale'() {
                $(this.$refs.dropdown).selectpicker('destroy');
                this.initSelectPicker();
            },
        }
    };
</script>
