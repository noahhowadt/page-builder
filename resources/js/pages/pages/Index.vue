<script setup lang="ts">
import PageController from '@/actions/App/Http/Controllers/PageController';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import Checkbox from '@/components/ui/checkbox/Checkbox.vue';
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
import type { Page } from '@/types/pages';
import { Link, router, useForm } from '@inertiajs/vue3';
import { FileText, Pencil, Plus, Trash2 } from 'lucide-vue-next';
import { computed, ref } from 'vue';

defineProps<{
  pages: Array<Page>;
}>();

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Dashboard',
    href: dashboard()
  }
];

const createModalOpen = ref(false);
const editModalOpen = ref(false);
const deleteConfirmOpen = ref(false);
const editingPage = ref<Page | null>(null);
const pageToDelete = ref<Page | null>(null);

const form = useForm({
  title: '',
  slug: ''
});

const editForm = useForm({
  title: '',
  slug: '',
  is_published: false
});

const submit = () => {
  form.post(PageController.store.url(), {
    onSuccess: () => {
      createModalOpen.value = false;
      form.reset();
    }
  });
};

function openEditDialog(page: Page) {
  editingPage.value = page;
  editForm.reset();
  editForm.title = page.title;
  editForm.slug = page.slug.startsWith('/') ? page.slug : `/${page.slug}`;
  editForm.is_published = page.is_published;
  editModalOpen.value = true;
}

function closeEditDialog() {
  editModalOpen.value = false;
  editingPage.value = null;
}

const submitEdit = () => {
  if (!editingPage.value) return;
  editForm.put(PageController.update.url({ page: editingPage.value.id }), {
    onSuccess: () => {
      closeEditDialog();
    }
  });
};

const editDialogTitle = computed(() =>
  editingPage.value ? `Edit "${editingPage.value.title}"` : 'Edit page'
);

function openDeleteConfirm(page: Page) {
  pageToDelete.value = page;
  deleteConfirmOpen.value = true;
}

function closeDeleteConfirm() {
  deleteConfirmOpen.value = false;
  pageToDelete.value = null;
}

function confirmDelete() {
  if (!pageToDelete.value) return;
  router.delete(PageController.destroy.url({ page: pageToDelete.value.id }), {
    onSuccess: () => closeDeleteConfirm()
  });
}

const deleteConfirmTitle = computed(() =>
  pageToDelete.value ? `Delete "${pageToDelete.value.title}"?` : 'Delete page?'
);
</script>

<template>
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="p-6">
      <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-semibold tracking-tight">Pages</h1>
        <Dialog v-model:open="createModalOpen">
          <DialogTrigger as-child>
            <Button>
              <Plus class="size-4" />
              New page
            </Button>
          </DialogTrigger>
          <DialogContent class="sm:max-w-md">
            <DialogHeader>
              <DialogTitle>Create page</DialogTitle>
              <DialogDescription>
                Add a new page. Title and URL slug are required.
              </DialogDescription>
            </DialogHeader>
            <form @submit.prevent="submit" class="space-y-4 pt-2">
              <div class="grid gap-2">
                <Label for="title">Title</Label>
                <Input
                  id="title"
                  v-model="form.title"
                  type="text"
                  placeholder="Page title"
                  required
                  autocomplete="off"
                />
                <InputError :message="form.errors.title" />
              </div>
              <div class="grid gap-2">
                <Label for="slug">URL slug</Label>
                <Input
                  id="slug"
                  v-model="form.slug"
                  type="text"
                  placeholder="/path/to/page"
                  required
                  autocomplete="off"
                  class="font-mono"
                />
                <InputError :message="form.errors.slug" />
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
                  Create page
                </Button>
              </DialogFooter>
            </form>
          </DialogContent>
        </Dialog>

        <!-- Edit page dialog -->
        <Dialog v-model:open="editModalOpen">
          <DialogContent class="sm:max-w-md">
            <DialogHeader>
              <DialogTitle>{{ editDialogTitle }}</DialogTitle>
              <DialogDescription>
                Update title, URL slug, and published status. Published at is set automatically when you publish.
              </DialogDescription>
            </DialogHeader>
            <form @submit.prevent="submitEdit" class="space-y-4 pt-2">
              <div class="grid gap-2">
                <Label for="edit-title">Title</Label>
                <Input
                  id="edit-title"
                  v-model="editForm.title"
                  type="text"
                  placeholder="Page title"
                  required
                  autocomplete="off"
                />
                <InputError :message="editForm.errors.title" />
              </div>
              <div class="grid gap-2">
                <Label for="edit-slug">URL slug</Label>
                <Input
                  id="edit-slug"
                  v-model="editForm.slug"
                  type="text"
                  placeholder="/path/to/page"
                  required
                  autocomplete="off"
                  class="font-mono"
                />
                <InputError :message="editForm.errors.slug" />
              </div>
              <div class="flex items-center gap-2">
                <Checkbox
                  id="edit-published"
                  v-model="editForm.is_published"
                  type="checkbox"
                  class="size-4 rounded border-neutral-300 text-primary focus:ring-primary dark:border-neutral-600 dark:bg-neutral-800"
                />
                <Label for="edit-published" class="cursor-pointer font-normal">
                  Published
                </Label>
              </div>
              <InputError :message="editForm.errors.is_published" />
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

        <!-- Delete confirmation dialog -->
        <Dialog v-model:open="deleteConfirmOpen">
          <DialogContent class="sm:max-w-md">
            <DialogHeader>
              <DialogTitle>{{ deleteConfirmTitle }}</DialogTitle>
              <DialogDescription>
                This action cannot be undone. The page and its content will be permanently removed.
              </DialogDescription>
            </DialogHeader>
            <DialogFooter class="gap-2">
              <Button
                type="button"
                variant="outline"
                @click="closeDeleteConfirm"
              >
                Cancel
              </Button>
              <Button
                type="button"
                variant="destructive"
                @click="confirmDelete"
              >
                Delete page
              </Button>
            </DialogFooter>
          </DialogContent>
        </Dialog>
      </div>

      <!-- Empty state -->
      <div
        v-if="pages.length === 0"
        class="rounded-xl border border-neutral-200 dark:border-neutral-800 bg-neutral-50 dark:bg-neutral-900/50 flex flex-col items-center justify-center py-16 px-6 text-center"
      >
        <div
          class="rounded-full bg-neutral-200 dark:bg-neutral-700 p-4 mb-4 text-neutral-500 dark:text-neutral-400"
        >
          <FileText class="size-8" stroke-width="1.5" />
        </div>
        <h2 class="text-lg font-medium text-neutral-900 dark:text-neutral-100 mb-1">
          No pages yet
        </h2>
        <p class="text-sm text-neutral-500 dark:text-neutral-400 max-w-sm">
          Create your first page to get started. Pages will appear here once you add them.
        </p>
      </div>

      <!-- Table -->
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
                Title
              </th>
              <th
                scope="col"
                class="px-5 py-3.5 text-left text-xs font-medium uppercase tracking-wider text-neutral-500 dark:text-neutral-400"
              >
                URL path
              </th>
              <th
                scope="col"
                class="px-5 py-3.5 text-left text-xs font-medium uppercase tracking-wider text-neutral-500 dark:text-neutral-400"
              >
                Published
              </th>
              <th
                scope="col"
                class="px-5 py-3.5 text-left text-xs font-medium uppercase tracking-wider text-neutral-500 dark:text-neutral-400"
              >
                Published at
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
              v-for="page in pages"
              :key="page.id"
              class="bg-white dark:bg-neutral-950 transition-colors hover:bg-neutral-50 dark:hover:bg-neutral-900/50"
            >
              <td class="whitespace-nowrap px-5 py-4 text-sm tabular-nums text-neutral-600 dark:text-neutral-400">
                {{ page.id }}
              </td>
              <td class="px-5 py-4">
                <Link
                  :href="PageController.edit.url({ page: page.id })"
                  class="text-sm font-medium text-neutral-900 dark:text-neutral-100 hover:text-neutral-600 dark:hover:text-neutral-300 transition-colors underline-offset-4 hover:underline"
                >
                  {{ page.title }}
                </Link>
              </td>
              <td class="whitespace-nowrap px-5 py-4 text-sm text-neutral-600 dark:text-neutral-400 font-mono">
                {{ page.slug.startsWith('/') ? page.slug : `/${page.slug}` }}
              </td>
              <td class="whitespace-nowrap px-5 py-4">
                <span
                  :class="[
                    'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium',
                    page.is_published
                      ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-400'
                      : 'bg-neutral-100 text-neutral-600 dark:bg-neutral-800 dark:text-neutral-400'
                  ]"
                >
                  {{ page.is_published ? 'Yes' : 'No' }}
                </span>
              </td>
              <td class="whitespace-nowrap px-5 py-4 text-sm text-neutral-600 dark:text-neutral-400">
                {{ page.published_at ? new Date(page.published_at).toLocaleString() : '—' }}
              </td>
              <td class="whitespace-nowrap px-5 py-4 text-sm text-neutral-600 dark:text-neutral-400">
                {{ new Date(page.created_at).toLocaleString() }}
              </td>
              <td class="whitespace-nowrap px-5 py-4 text-right">
                <div class="flex items-center justify-end gap-1">
                  <Button
                    type="button"
                    variant="ghost"
                    size="icon"
                    class="size-8 text-neutral-500 hover:text-neutral-900 dark:text-neutral-400 dark:hover:text-neutral-100"
                    :aria-label="`Edit ${page.title}`"
                    @click="openEditDialog(page)"
                  >
                    <Pencil class="size-4" />
                  </Button>
                  <Button
                    type="button"
                    variant="ghost"
                    size="icon"
                    class="size-8 text-neutral-500 hover:text-destructive dark:text-neutral-400 dark:hover:text-destructive"
                    :aria-label="`Delete ${page.title}`"
                    @click="openDeleteConfirm(page)"
                  >
                    <Trash2 class="size-4" />
                  </Button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </AppLayout>
</template>