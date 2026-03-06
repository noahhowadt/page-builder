<script lang="ts">
import type { Block } from '@/types';
import { usePageBuilderStore } from '@/stores/pageBuilder';
import { type PropType, computed, defineComponent, h } from 'vue';
import BlockWrapper from './BlockWrapper.vue';
import DropZone from './DropZone.vue';
import Container from './primitive-blocks/Container.vue';
import Heading from './primitive-blocks/Heading.vue';
import Paragraph from './primitive-blocks/Paragraph.vue';

const blockComponents: Record<string, ReturnType<typeof defineComponent>> = {
    container: Container,
    heading: Heading,
    paragraph: Paragraph,
};

export default defineComponent({
    name: 'BlockRenderer',
    props: {
        parentId: {
            type: String as PropType<string | null>,
            default: null,
        },
        emptyZoneFillsParent: {
            type: Boolean,
            default: false,
        },
    },
    setup(props) {
        const store = usePageBuilderStore();
        const childBlocks = computed(() => store.getChildBlocks(props.parentId));

        return () => {
            const blocks = childBlocks.value;
            const zonePrefix = props.parentId ?? 'root';
            const firstDropZoneFillsParent = props.emptyZoneFillsParent && blocks.length === 0;

            const nodes: ReturnType<typeof h>[] = [
                h(DropZone, {
                    key: 'drop-0',
                    zoneId: `${zonePrefix}:0`,
                    fillsParent: firstDropZoneFillsParent,
                }),
            ];

            blocks.forEach((block: Block, index: number) => {
                const BlockComponent = blockComponents[block.type];
                if (!BlockComponent) return;

                nodes.push(
                    h(BlockWrapper, { key: block.id, blockId: block.id }, { default: () => h(BlockComponent, { blockId: block.id }) }),
                );

                nodes.push(
                    h(DropZone, {
                        key: `drop-${index + 1}`,
                        zoneId: `${zonePrefix}:${index + 1}`,
                    }),
                );
            });

            return nodes;
        };
    },
});
</script>
