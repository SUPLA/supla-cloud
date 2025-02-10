import {setActivePinia} from "pinia";
import {createTestingPinia} from "@pinia/testing";
import {useChannelsStore} from "@/stores/channels-store";
import ChannelFunction from "@/common/enums/channel-function";
import ChannelType from "@/common/enums/channel-type";

describe('ChannelsStore', () => {
    beforeEach(() => {
        setActivePinia(createTestingPinia({
            initialState: {
                channels: {
                    all: {
                        1: {
                            id: 1,
                            typeId: ChannelType.SENSORNO,
                            functionId: ChannelFunction.OPENINGSENSOR_GATE,
                            iodeviceId: 1,
                            'function': {output: false},
                        },
                        2: {
                            id: 2,
                            typeId: ChannelType.RELAY,
                            functionId: ChannelFunction.CONTROLLINGTHEGATE,
                            iodeviceId: 1,
                            'function': {output: true},
                        },
                        3: {
                            id: 3,
                            typeId: ChannelType.RELAY,
                            functionId: ChannelFunction.CONTROLLINGTHEGATE,
                            iodeviceId: 2,
                            'function': {output: true},
                        },
                    },
                    ids: [1, 2, 3],
                }
            }
        }));
    })

    it('creates list of channels', async () => {
        const store = useChannelsStore();
        expect(store.list).toHaveLength(3);
        expect(store.list[1].id).toEqual(2);
    });

    it('filters by nothing', async () => {
        const store = useChannelsStore();
        expect(store.filteredChannels()).toHaveLength(3);
        expect(store.filteredChannels(undefined)).toHaveLength(3);
    });

    it('filters by deviceIds', async () => {
        const store = useChannelsStore();
        expect(store.filteredChannels({deviceIds: '1'})).toHaveLength(2);
        expect(store.filteredChannels({deviceIds: 1})).toHaveLength(2);
        expect(store.filteredChannels('deviceIds=1')).toHaveLength(2);
        expect(store.filteredChannels('deviceIds=2')).toHaveLength(1);
        expect(store.filteredChannels('deviceIds=1,2')).toHaveLength(3);
    });

    it('filters by skipIds', async () => {
        const store = useChannelsStore();
        expect(store.filteredChannels({skipIds: '1'})).toHaveLength(2);
        expect(store.filteredChannels({skipIds: 1})).toHaveLength(2);
        expect(store.filteredChannels('skipIds=1')).toHaveLength(2);
        expect(store.filteredChannels('skipIds=1,2')).toHaveLength(1);
    });

    it('filters by type', async () => {
        const store = useChannelsStore();
        expect(store.filteredChannels({type: '1000'})).toHaveLength(1);
        expect(store.filteredChannels({type: 1000})).toHaveLength(1);
        expect(store.filteredChannels('type=2900')).toHaveLength(2);
        expect(store.filteredChannels('type=RELAY')).toHaveLength(2);
        expect(store.filteredChannels('type=RELAY,1000')).toHaveLength(3);
    });

    it('filters by function', async () => {
        const store = useChannelsStore();
        expect(store.filteredChannels({'function': '60'})).toHaveLength(1);
        expect(store.filteredChannels({'function': 60})).toHaveLength(1);
        expect(store.filteredChannels('function=20')).toHaveLength(2);
        expect(store.filteredChannels('function=CONTROLLINGTHEGATE')).toHaveLength(2);
        expect(store.filteredChannels('function=CONTROLLINGTHEGATE,60')).toHaveLength(3);
    });

    it('filters by fnc', async () => {
        const store = useChannelsStore();
        expect(store.filteredChannels({'fnc': '60'})).toHaveLength(1);
        expect(store.filteredChannels({'fnc': 60})).toHaveLength(1);
        expect(store.filteredChannels('fnc=20')).toHaveLength(2);
        expect(store.filteredChannels('fnc=CONTROLLINGTHEGATE')).toHaveLength(2);
        expect(store.filteredChannels('fnc=CONTROLLINGTHEGATE,60')).toHaveLength(3);
    });

    it('filters by io', async () => {
        const store = useChannelsStore();
        expect(store.filteredChannels({io: 'output'})).toHaveLength(2);
        expect(store.filteredChannels({io: 'input'})).toHaveLength(1);
        expect(store.filteredChannels('io=output')).toHaveLength(2);
        expect(store.filteredChannels('io=input')).toHaveLength(1);
    });

    it('filters by multiple', async () => {
        const store = useChannelsStore();
        expect(store.filteredChannels('deviceIds=1&io=output')).toHaveLength(1);
    });
});
