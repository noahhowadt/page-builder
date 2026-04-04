<script lang="ts">
import { usePageBuilderStore } from '@/stores/pageBuilder';
import {
    isBlockWithBlockChildren,
    type Block,
    type RootBlock,
} from '@/types';
import { computed, defineComponent, h, type PropType } from 'vue';
import BlockWrapper from './BlockWrapper.vue';
import DropZone from './DropZone.vue';
import Container from './primitive-blocks/Container.vue';
import Heading from './primitive-blocks/Heading.vue';
import Paragraph from './primitive-blocks/Paragraph.vue';
import Root from './primitive-blocks/Root.vue';

const blockComponents: Record<string, ReturnType<typeof defineComponent>> = {
    container: Container,
    heading: Heading,
    paragraph: Paragraph,
    root: Root,
};

export default defineComponent({
    name: 'BlockRenderer',
    props: {
        parentId: {
            type: String as PropType<string>,
            required: true,
        },
        interactive: {
            type: Boolean as PropType<boolean>,
            default: true,
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
            const interactive = props.interactive;
            const zonePrefix = props.parentId;

            const nodes: ReturnType<typeof h>[] = [];

            if (interactive) {
                nodes.push(
                    h(DropZone, {
                        key: 'drop-0',
                        zoneId: `${zonePrefix}:0`,
                    }),
                );
            }

            children.value.forEach((child: Block, index: number) => {
                const isComponentRoot = child.type === 'root'
                    && (child as RootBlock).componentId != null;

                if (isComponentRoot) {
                    if (interactive) {
                        nodes.push(
                            h(BlockWrapper, { key: child.id, blockId: child.id }, {
                                default: () => h(Root, { blockId: child.id }),
                            }),
                        );
                    } else {
                        nodes.push(
                            h(Root, { key: child.id, blockId: child.id }),
                        );
                    }
                } else {
                    const BlockComponent = blockComponents[child.type];
                    if (!BlockComponent) return;

                    if (interactive) {
                        nodes.push(
                            h(BlockWrapper, { key: child.id, blockId: child.id }, {
                                default: () => h(BlockComponent, { blockId: child.id, interactive }),
                            }),
                        );
                    } else {
                        nodes.push(
                            h(BlockComponent, { key: child.id, blockId: child.id, interactive: false }),
                        );
                    }
                }

                if (interactive) {
                    nodes.push(
                        h(DropZone, {
                            key: `drop-${index + 1}`,
                            zoneId: `${zonePrefix}:${index + 1}`,
                        }),
                    );
                }
            });

            return nodes;
        };
    },
});
</script>
