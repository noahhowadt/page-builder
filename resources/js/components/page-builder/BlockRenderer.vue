<script lang="ts">
import { usePageBuilderStore } from '@/stores/pageBuilder';
import {
    isBlockWithBlockChildren,
    type Block
} from '@/types';
import { computed, defineComponent, h, type PropType } from 'vue';
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
            type: String as PropType<string>,
            required: true,
        },
        emptyZoneFillsParent: {
            type: Boolean,
            default: false,
        },
    },
    setup(props) {
        const store = usePageBuilderStore();
        const block = computed(() => store.findBlock(props.parentId));
        const children = computed(() => {
            const b = block.value;
            return b && isBlockWithBlockChildren(b) ? b.children : [];
        });

        return () => {
            const zonePrefix = props.parentId;
            const firstDropZoneFillsParent = props.emptyZoneFillsParent && children.value.length === 0;

            const nodes: ReturnType<typeof h>[] = [
                h(DropZone, {
                    key: 'drop-0',
                    zoneId: `${zonePrefix}:0`,
                    fillsParent: firstDropZoneFillsParent,
                }),
            ];

            children.value.forEach((child: Block, index: number) => {
                const BlockComponent = blockComponents[child.type];
                if (!BlockComponent) return;

                nodes.push(
                    h(BlockWrapper, { key: child.id, blockId: child.id }, { default: () => h(BlockComponent, { blockId: child.id }) }),
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
