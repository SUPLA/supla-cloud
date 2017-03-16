<template>
    <div class="row">
        <div class="col-xs-12">
            <vuetable
                api-url="schedule/"
                table-wrapper="#content"
                :fields="columns"
                :item-actions="itemActions"
            ></vuetable>
        </div>
    </div>
</template>

<script type="text/babel">
    export default {
        name: 'schedule-list',
        data() {
            return {
                columns: [
                    {
                        name: 'caption',
                        title: this.$t('Name')
                    },
                    {
                        name: 'action',
                        title: this.$t('Action'),
                        callback: 'translatedCaption',
                    },
                    '__actions'
                ],
                itemActions: [
                    {name: 'view-item', label: '', icon: 'zoom icon', class: 'ui teal button'},
                    {name: 'edit-item', label: '', icon: 'edit icon', class: 'ui orange button'},
                    {name: 'delete-item', label: '', icon: 'delete icon', class: 'ui red button'}
                ]
            }
        },
        methods: {
            translatedCaption(value) {
                return this.$t(value.caption);
            }
        }
    };
</script>
