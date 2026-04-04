import type { LibraryComponent } from '@/types/components';
import {
    type Block,
    type ContainerBlock,
    type HeadingBlock,
    type RootBlock,
    isBlockWithBlockChildren,
    isBlockWithTextChildren,
    ParagraphBlock,
    TextNode
} from '@/types';
import { createId } from '@paralleldrive/cuid2';
import { defineStore } from 'pinia';
import { ref } from 'vue';

export const usePageBuilderStore = defineStore('pageBuilder', () => {
    const structure = ref<Block | null>(null);
    const libraryComponents = ref<LibraryComponent[]>([]);

    function setRoot(block: Block): void {
        structure.value = block;
    }

    function setLibraryComponents(components: LibraryComponent[]): void {
        libraryComponents.value = components;
    }

    function getLibraryComponent(id: number): LibraryComponent | undefined {
        return libraryComponents.value.find((c) => c.id === id);
    }

    function parseComponentStructure(component: LibraryComponent): RootBlock {
        const parsed = JSON.parse(component.structure) as RootBlock;
        return {
            ...parsed,
            id: createId(),
            componentId: component.id,
        };
    }

    const selectedBlockId = ref<string | null>(null);
    const activeDropZoneId = ref<string | null>(null);
    const isDragging = ref(false);

    const dropZoneElements = new Map<string, HTMLElement>();

    /**
     * Deep-clone the structure, stripping children from root blocks that
     * reference a component (keeping only id, type, componentId).
     */
    function getStructureForSave(): Block | null {
        if (!structure.value) return null;
        return stripComponentChildren(JSON.parse(JSON.stringify(structure.value)));
    }

    function stripComponentChildren(block: Block): Block {
        if (isBlockWithBlockChildren(block)) {
            block.children = block.children.map((child) => {
                if (child.type === 'root' && (child as RootBlock).componentId != null) {
                    return {
                        id: child.id,
                        type: 'root' as const,
                        componentId: (child as RootBlock).componentId,
                        children: [],
                    } as RootBlock;
                }
                return stripComponentChildren(child);
            });
        }
        return block;
    }

    function findBlock(blockId: string, root: Block | null = structure.value): Block | null {
        if (!root) return null;
        if (blockId === root.id) return root;
        if (isBlockWithBlockChildren(root)) {
            for (const block of root.children) {
                const found = findBlock(blockId, block);
                if (found) return found;
            }
        }

        return null;
    }

    function getChildBlocks(parentId: string): Array<Block> {
        const parent = findBlock(parentId);
        if (!parent) throw new Error(`Parent block not found: ${parentId}`);
        return isBlockWithBlockChildren(parent) ? parent.children : [];
    }

    function selectBlock(id: string): void {
        selectedBlockId.value = id;
    }

    function clearSelection(): void {
        selectedBlockId.value = null;
    }

    function createBlock(type: Block['type']): Block {
        switch (type) {
            case 'container':
                return { id: createId(), type, config: { direction: 'column', gap: 0, padding: 20 }, children: [] };
            case 'heading':
                return {
                    id: createId(), type, config: { level: 1 }, content: [{
                        type: 'text',
                        text: 'Heading',
                    }]
                } as HeadingBlock;
            case 'paragraph':
                return {
                    id: createId(), type, config: { level: 1 }, content: [{
                        type: 'text',
                        text: 'Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet.'
                    }]
                } as ParagraphBlock;
            default:
                throw new Error(`Invalid block type: ${type}`);
        }
    }

    function insertBlock(parentId: string, index: number, type: Block['type']): void {
        const newBlock = createBlock(type);

        const parent = findBlock(parentId);
        if (!parent) throw new Error(`Trying to insert block into non-existent parent: ${parentId}`);
        if (!isBlockWithBlockChildren(parent)) throw new Error(`Parent block is not a container block: ${parentId}`);
        parent.children.splice(index, 0, newBlock);
    }

    function updateBlockContent(blockId: string, newContent: Array<TextNode>): void {
        const block = findBlock(blockId);
        if (!block) throw new Error(`Block not found: ${blockId}`);
        if (!isBlockWithTextChildren(block)) throw new Error(`Block is not a text block: ${blockId}`);

        block.content = newContent;
    }

    function updateBlockConfig(
        blockId: string,
        updates: Partial<ContainerBlock['config']> | Partial<HeadingBlock['config']>,
    ): void {
        const block = findBlock(blockId);
        if (!block || !('config' in block)) return;
        Object.assign(block.config, updates);
    }

    function parseZoneId(zoneId: string): { parentId: string | null; index: number } {
        console.log('parseZoneId', zoneId);
        const colonIndex = zoneId.lastIndexOf(':');
        const parentPart = zoneId.substring(0, colonIndex);
        return {
            parentId: parentPart,
            index: parseInt(zoneId.substring(colonIndex + 1), 10),
        };
    }

    function insertComponentBlock(parentId: string, index: number, componentId: number): void {
        const component = getLibraryComponent(componentId);
        if (!component) throw new Error(`Library component not found: ${componentId}`);

        const rootBlock = parseComponentStructure(component);
        const parent = findBlock(parentId);
        if (!parent) throw new Error(`Parent block not found: ${parentId}`);
        if (!isBlockWithBlockChildren(parent)) throw new Error(`Parent is not a container: ${parentId}`);
        parent.children.splice(index, 0, rootBlock);
    }

    function dropAtActiveZone(e: DragEvent): void {
        if (!activeDropZoneId.value || !e.dataTransfer) return;

        const { parentId, index } = parseZoneId(activeDropZoneId.value);
        if (!parentId) throw new Error(`Trying to drop at root: ${activeDropZoneId.value}`);

        const componentIdStr = e.dataTransfer.getData('application/x-component-id');
        if (componentIdStr) {
            insertComponentBlock(parentId, index, parseInt(componentIdStr, 10));
            endDrag();
            return;
        }

        const type = e.dataTransfer.getData('application/x-block-type') as Block['type'];
        if (!type) return;

        insertBlock(parentId, index, type);
        endDrag();
    }

    function registerDropZone(id: string, element: HTMLElement): void {
        dropZoneElements.set(id, element);
    }

    function unregisterDropZone(id: string): void {
        dropZoneElements.delete(id);
    }

    function startDrag(): void {
        isDragging.value = true;
    }

    function endDrag(): void {
        isDragging.value = false;
        activeDropZoneId.value = null;
    }

    function findParent(blockId: string, root: Block | null = structure.value): { parent: Block; index: number } | null {
        if (!root || !isBlockWithBlockChildren(root)) return null;
        const children = root.children;
        for (let i = 0; i < children.length; i++) {
            if (children[i].id === blockId) return { parent: root, index: i };
            const found = findParent(blockId, children[i]);
            if (found) return found;
        }
        return null;
    }

    function deleteBlock(blockId: string): boolean {
        if (blockId === structure.value?.id) return false;
        const found = findParent(blockId);
        if (!found) return false;
        const { parent, index } = found;
        if (!isBlockWithBlockChildren(parent)) return false;
        parent.children.splice(index, 1);
        if (selectedBlockId.value === blockId) selectedBlockId.value = null;
        return true;
    }

    function updateClosestDropZone(mouseX: number, mouseY: number): void {
        let closestId: string | null = null;
        let closestDistance = Infinity;

        for (const [id, element] of dropZoneElements) {
            const rect = element.getBoundingClientRect();
            const dx = Math.max(rect.left - mouseX, 0, mouseX - rect.right);
            const dy = Math.max(rect.top - mouseY, 0, mouseY - rect.bottom);
            const distance = Math.sqrt(dx * dx + dy * dy);

            if (distance < closestDistance) {
                closestDistance = distance;
                closestId = id;
            }
        }

        activeDropZoneId.value = closestId;
    }

    return {
        structure,
        setRoot,
        getStructureForSave,
        libraryComponents,
        setLibraryComponents,
        getLibraryComponent,
        parseComponentStructure,
        selectedBlockId,
        activeDropZoneId,
        isDragging,
        findBlock,
        getChildBlocks,
        selectBlock,
        clearSelection,
        deleteBlock,
        insertBlock,
        insertComponentBlock,
        updateBlockContent,
        updateBlockConfig,
        dropAtActiveZone,
        registerDropZone,
        unregisterDropZone,
        startDrag,
        endDrag,
        updateClosestDropZone,
    };
});
