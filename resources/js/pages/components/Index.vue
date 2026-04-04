<script setup lang="ts">
import ComponentController from '@/actions/App/Http/Controllers/ComponentController';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
  DialogTrigger
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import type { ComponentListItem } from '@/types/components';
import { Link, useForm } from '@inertiajs/vue3';
import { Pencil, Plus, Puzzle } from 'lucide-vue-next';
import { computed, ref } from 'vue';

defineProps<{
  components: Array<ComponentListItem>;
}>();

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Dashboard',
    href: dashboard()
  }
];

const createModalOpen = ref(false);
const editModalOpen = ref(false);
const editingComponent = ref<ComponentListItem | null>(null);

const form = useForm({
  name: ''
});

const editForm = useForm({
  name: ''
});

const submit = () => {
  form.post(ComponentController.store.url(), {
    onSuccess: () => {
      createModalOpen.value = false;
      form.reset();
    }
  });
};

function openEditDialog(component: ComponentListItem) {
  editingComponent.value = component;
  editForm.reset();
  editForm.name = component.name;
  editModalOpen.value = true;
}

function closeEditDialog() {
  editModalOpen.value = false;
  editingComponent.value = null;
}

const submitEdit = () => {
  if (!editingComponent.value) return;
  editForm.put(
    ComponentController.update.url({ component: editingComponent.value.id }),
    {
      onSuccess: () => {
        closeEditDialog();
      }
    }
  );
};

const editDialogTitle = computed(() =>
  editingComponent.value
    ? `Edit "${editingComponent.value.name}"`
    : 'Edit component'
);
</script>

<template>
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="p-6">
      <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-semibold tracking-tight">Components</h1>
        <Dialog v-model:open="createModalOpen">
          <DialogTrigger as-child>
            <Button>
              <Plus class="size-4" />
              New component
            </Button>
          </DialogTrigger>
          <DialogContent class="sm:max-w-md">
            <DialogHeader>
              <DialogTitle>Create component</DialogTitle>
              <DialogDescription>
                Add a reusable component. You can change the name anytime.
              </DialogDescription>
            </DialogHeader>
            <form @submit.prevent="submit" class="space-y-4 pt-2">
              <div class="grid gap-2">
                <Label for="name">Name</Label>
                <Input
                  id="name"
                  v-model="form.name"
                  type="text"
                  placeholder="Component name"
                  required
                  autocomplete="off"
                />
                <InputError :message="form.errors.name" />
              </div>
              <DialogFooter>
                <Button
                  type="button"
                  variant="outline"
                  @click="createModalOpen = false"
                >
                  Cancel
                </Button>
                <Button type="submit" :disabled="form.processing">
                  Create component
                </Button>
              </DialogFooter>
            </form>
          </DialogContent>
        </Dialog>

        <Dialog v-model:open="editModalOpen">
          <DialogContent class="sm:max-w-md">
            <DialogHeader>
              <DialogTitle>{{ editDialogTitle }}</DialogTitle>
              <DialogDescription>
                Update the component name.
              </DialogDescription>
            </DialogHeader>
            <form @submit.prevent="submitEdit" class="space-y-4 pt-2">
              <div class="grid gap-2">
                <Label for="edit-name">Name</Label>
                <Input
                  id="edit-name"
                  v-model="editForm.name"
                  type="text"
                  placeholder="Component name"
                  required
                  autocomplete="off"
                />
                <InputError :message="editForm.errors.name" />
              </div>
              <DialogFooter>
                <Button
                  type="button"
                  variant="outline"
                  @click="closeEditDialog"
                >
                  Cancel
                </Button>
                <Button type="submit" :disabled="editForm.processing">
                  Save changes
                </Button>
              </DialogFooter>
            </form>
          </DialogContent>
        </Dialog>
      </div>

      <div
        v-if="components.length === 0"
        class="rounded-xl border border-neutral-200 dark:border-neutral-800 bg-neutral-50 dark:bg-neutral-900/50 flex flex-col items-center justify-center py-16 px-6 text-center"
      >
        <div
          class="rounded-full bg-neutral-200 dark:bg-neutral-700 p-4 mb-4 text-neutral-500 dark:text-neutral-400"
        >
          <Puzzle class="size-8" stroke-width="1.5" />
        </div>
        <h2 class="text-lg font-medium text-neutral-900 dark:text-neutral-100 mb-1">
          No components yet
        </h2>
        <p class="text-sm text-neutral-500 dark:text-neutral-400 max-w-sm">
          Create your first component to get started. They will appear here once you add them.
        </p>
      </div>

      <div
        v-else
        class="rounded-xl border border-neutral-200 dark:border-neutral-800 bg-white dark:bg-neutral-950 shadow-sm overflow-hidden"
      >
        <table class="min-w-full divide-y divide-neutral-200 dark:divide-neutral-800">
          <thead class="bg-neutral-50 dark:bg-neutral-900/80">
            <tr>
              <th
                scope="col"
                class="px-5 py-3.5 text-left text-xs font-medium uppercase tracking-wider text-neutral-500 dark:text-neutral-400"
              >
                ID
              </th>
              <th
                scope="col"
                class="px-5 py-3.5 text-left text-xs font-medium uppercase tracking-wider text-neutral-500 dark:text-neutral-400"
              >
                Name
              </th>
              <th
                scope="col"
                class="px-5 py-3.5 text-left text-xs font-medium uppercase tracking-wider text-neutral-500 dark:text-neutral-400"
              >
                Created at
              </th>
              <th scope="col" class="relative px-5 py-3.5 w-10">
                <span class="sr-only">Actions</span>
              </th>
            </tr>
          </thead>
          <tbody class="divide-y divide-neutral-200 dark:divide-neutral-800">
            <tr
              v-for="component in components"
              :key="component.id"
              class="bg-white dark:bg-neutral-950 transition-colors hover:bg-neutral-50 dark:hover:bg-neutral-900/50"
            >
              <td class="whitespace-nowrap px-5 py-4 text-sm tabular-nums text-neutral-600 dark:text-neutral-400">
                {{ component.id }}
              </td>
              <td class="px-5 py-4">
                <Link
                  :href="ComponentController.edit.url({ component: component.id })"
                  class="text-sm font-medium text-neutral-900 dark:text-neutral-100 hover:text-neutral-600 dark:hover:text-neutral-300 transition-colors underline-offset-4 hover:underline"
                >
                  {{ component.name }}
                </Link>
              </td>
              <td class="whitespace-nowrap px-5 py-4 text-sm text-neutral-600 dark:text-neutral-400">
                {{ new Date(component.created_at).toLocaleString() }}
              </td>
              <td class="whitespace-nowrap px-5 py-4 text-right">
                <Button
                  type="button"
                  variant="ghost"
                  size="icon"
                  class="size-8 text-neutral-500 hover:text-neutral-900 dark:text-neutral-400 dark:hover:text-neutral-100"
                  :aria-label="`Edit ${component.name}`"
                  @click="openEditDialog(component)"
                >
                  <Pencil class="size-4" />
                </Button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </AppLayout>
</template>
