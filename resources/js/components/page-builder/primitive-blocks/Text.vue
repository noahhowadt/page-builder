<script lang="ts">
import { mergeAttributes   } from '@tiptap/core';
import type {Editor, JSONContent} from '@tiptap/core';
import Link, { isAllowedUri } from '@tiptap/extension-link';
import Underline from '@tiptap/extension-underline';
import StarterKit from '@tiptap/starter-kit';
import { EditorContent, useEditor } from '@tiptap/vue-3';
import { BubbleMenu } from '@tiptap/vue-3/menus';
import { Bold, Italic, Link2, Underline as UnderlineIcon } from 'lucide-vue-next';
import type { PropType, VNode } from 'vue';
import {
  computed,
  createTextVNode,
  defineComponent,
  h,
  inject,
  nextTick,
  onBeforeUnmount,
  ref,
  watch,
} from 'vue';
import { Button as UiButton } from '@/components/ui/button';
import {
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
  Dialog as UiDialog,
} from '@/components/ui/dialog';
import { Input as UiInput } from '@/components/ui/input';
import { Label as UiLabel } from '@/components/ui/label';
import type { LinkablePage, TextMark, TextNode } from '@/types';

const EditorLink = Link.extend({
  addAttributes() {
    return {
      ...this.parent?.(),
      pageId: {
        default: null,
        parseHTML: (element) => {
          const v = (element as HTMLElement).getAttribute('data-page-id');
          return v === null || v === '' ? null : v;
        },
        renderHTML: (attributes) => {
          if (
            attributes.pageId === null ||
            attributes.pageId === undefined ||
            attributes.pageId === ''
          ) {
            return {};
          }
          return { 'data-page-id': String(attributes.pageId) };
        },
      },
    };
  },

  parseHTML() {
    return [
      ...(this.parent?.() ?? []),
      {
        tag: 'span[data-href]',
        getAttrs: (dom) => {
          const el = dom as HTMLElement;
          const href = el.getAttribute('data-href');
          if (
            !href ||
            !this.options.isAllowedUri(href, {
              defaultValidate: (url) =>
                !!isAllowedUri(url, this.options.protocols),
              protocols: this.options.protocols,
              defaultProtocol: this.options.defaultProtocol,
            })
          ) {
            return false;
          }
          const pageId = el.getAttribute('data-page-id');
          return {
            href,
            pageId:
              pageId === null || pageId === '' ? null : pageId,
          };
        },
      },
    ];
  },

  /** Use `<span>` in the editor so links are not navigable while editing. */
  renderHTML({ HTMLAttributes }) {
    const { href, class: className, ...rest } = HTMLAttributes;

    const validationCtx = {
      defaultValidate: (url: string) =>
        !!isAllowedUri(url, this.options.protocols),
      protocols: this.options.protocols,
      defaultProtocol: this.options.defaultProtocol,
    };

    const linkLikeClass =
      'cursor-text select-text underline underline-offset-2 text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300';

    if (!href || !this.options.isAllowedUri(String(href), validationCtx)) {
      return [
        'span',
        mergeAttributes(this.options.HTMLAttributes, {
          ...rest,
          class: [className, 'cursor-text text-neutral-500']
            .filter(Boolean)
            .join(' '),
        }),
        0,
      ];
    }

    return [
      'span',
      mergeAttributes(this.options.HTMLAttributes, {
        ...rest,
        'data-href': String(href),
        class: [linkLikeClass, className].filter(Boolean).join(' '),
      }),
      0,
    ];
  },
}).configure({
  openOnClick: false,
  autolink: false,
  protocols: [{ scheme: 'page', optionalSlashes: true }],
  isAllowedUri: (url, ctx) => {
    if (typeof url === 'string' && /^page:\d+$/i.test(url.trim())) {
      return true;
    }
    return ctx.defaultValidate(url);
  },
});

const MARK_ORDER: readonly TextMark[] = ['bold', 'italic', 'underline'] as const;

const TIPTAP_TO_TEXT_MARK: Record<string, TextMark> = {
  bold: 'bold',
  italic: 'italic',
  underline: 'underline',
};

function normalizeContent(nodes: TextNode[] | undefined): TextNode[] {
  if (nodes?.length) {
    return nodes;
  }
  return [{ type: 'text', text: '' }];
}

function escapeForParagraph(text: string): string {
  return text
    .replaceAll('&', '&amp;')
    .replaceAll('<', '&lt;')
    .replaceAll('>', '&gt;')
    .replaceAll('"', '&quot;')
    .replaceAll("'", '&#039;');
}

function wrapMarkHtml(html: string, mark: TextMark): string {
  switch (mark) {
    case 'bold':
      return `<strong>${html}</strong>`;
    case 'italic':
      return `<em>${html}</em>`;
    case 'underline':
      return `<u>${html}</u>`;
    default:
      return html;
  }
}

function escapeForHrefAttr(url: string): string {
  return url
    .replaceAll('&', '&amp;')
    .replaceAll('"', '&quot;')
    .replaceAll("'", '&#039;');
}

type UrlValidation =
  | { ok: true; href: string }
  | { ok: false; message: string };

/**
 * External / mailto URLs only (internal paths rejected until supported).
 */
function validateAndNormalizeUrl(input: string): UrlValidation {
  const trimmed = input.trim();
  if (!trimmed) {
    return { ok: true, href: '' };
  }
  if (
    trimmed.startsWith('/') ||
    trimmed.startsWith('?') ||
    trimmed.startsWith('#')
  ) {
    return {
      ok: false,
      message: 'Internal links are not supported yet. Use a full https:// URL.',
    };
  }
  let candidate = trimmed;
  if (
    !/^https?:\/\//i.test(candidate) &&
    !/^mailto:/i.test(candidate)
  ) {
    candidate = `https://${candidate}`;
  }
  try {
    const parsed = new URL(candidate);
    if (
      parsed.protocol !== 'http:' &&
      parsed.protocol !== 'https:' &&
      parsed.protocol !== 'mailto:'
    ) {
      return { ok: false, message: 'Only http(s) and mailto links are allowed.' };
    }
    return { ok: true, href: parsed.href };
  } catch {
    return { ok: false, message: 'Enter a valid URL (e.g. https://example.com).' };
  }
}

function linkFromStoredHref(url: string | undefined): TextNode['link'] | undefined {
  const v = validateAndNormalizeUrl(url ?? '');
  if (!v.ok || v.href === '') {
    return undefined;
  }
  return { url: v.href };
}

function textNodesToHtml(nodes: TextNode[]): string {
  const inner = nodes
    .map((node) => {
      let innerHtml = escapeForParagraph(node.text);
      for (const mark of node.marks ?? []) {
        innerHtml = wrapMarkHtml(innerHtml, mark);
      }
      const pageId =
        node.link?.pageId != null &&
        typeof node.link.pageId === 'number' &&
        node.link.pageId > 0
          ? node.link.pageId
          : undefined;
      const urlLink = node.link?.url
        ? linkFromStoredHref(node.link.url)
        : undefined;
      if (pageId != null && (node.link?.url == null || node.link.url === '')) {
        innerHtml = `<a href="${escapeForHrefAttr(`page:${pageId}`)}" data-page-id="${String(pageId)}">${innerHtml}</a>`;
      } else if (urlLink?.url) {
        innerHtml = `<a href="${escapeForHrefAttr(urlLink.url)}">${innerHtml}</a>`;
      }
      return innerHtml;
    })
    .join('');
  return `<p>${inner}</p>`;
}

function marksFromTiptap(marks: JSONContent['marks']): TextMark[] | undefined {
  if (!marks?.length) {
    return undefined;
  }
  const out: TextMark[] = [];
  for (const m of marks) {
    const mapped = TIPTAP_TO_TEXT_MARK[m.type ?? ''];
    if (mapped && !out.includes(mapped)) {
      out.push(mapped);
    }
  }
  return out.length ? sortMarks(out) : undefined;
}

function sortMarks(marks: TextMark[]): TextMark[] {
  return [...marks].sort(
    (a, b) => MARK_ORDER.indexOf(a) - MARK_ORDER.indexOf(b),
  );
}

function mergeAdjacentTextNodes(nodes: TextNode[]): TextNode[] {
  const merged: TextNode[] = [];
  for (const n of nodes) {
    const prev = merged[merged.length - 1];
    const sameMarks =
      JSON.stringify(prev?.marks ?? []) === JSON.stringify(n.marks ?? []);
    const sameLink =
      JSON.stringify(prev?.link ?? null) === JSON.stringify(n.link ?? null);
    if (prev && sameMarks && sameLink) {
      prev.text += n.text;
    } else {
      merged.push({
        type: 'text',
        text: n.text,
        marks: n.marks?.length ? sortMarks([...n.marks]) : undefined,
        ...(n.link ? { link: n.link } : {}),
      });
    }
  }
  return merged;
}

function parsePositivePageId(raw: unknown): number | undefined {
  if (raw === null || raw === undefined || raw === '') {
    return undefined;
  }
  const n = typeof raw === 'number' ? raw : parseInt(String(raw), 10);
  return Number.isFinite(n) && n > 0 ? n : undefined;
}

function linkFromTiptapMarks(
  marks: JSONContent['marks'],
): TextNode['link'] | undefined {
  const linkMark = marks?.find((m) => m.type === 'link');
  if (!linkMark?.attrs) {
    return undefined;
  }
  const href = 'href' in linkMark.attrs
    ? String(linkMark.attrs.href ?? '')
    : '';
  const pageIdAttr = parsePositivePageId(linkMark.attrs.pageId);
  if (/^page:\d+$/i.test(href.trim())) {
    const fromHref = parseInt(href.trim().replace(/^page:/i, ''), 10);
    const pageId =
      pageIdAttr ?? (Number.isFinite(fromHref) && fromHref > 0 ? fromHref : undefined);
    if (pageId != null) {
      return { pageId };
    }
  }
  if (pageIdAttr != null) {
    return { pageId: pageIdAttr };
  }
  if (!href) {
    return undefined;
  }
  const validated = validateAndNormalizeUrl(href);
  if (!validated.ok || validated.href === '') {
    return undefined;
  }
  return { url: validated.href };
}

function inlineToTextNodes(nodes: JSONContent[] | undefined): TextNode[] {
  if (!nodes?.length) {
    return [{ type: 'text', text: '' }];
  }
  const result: TextNode[] = [];
  for (const n of nodes) {
    if (n.type === 'text' && typeof n.text === 'string') {
      const link = linkFromTiptapMarks(n.marks);
      result.push({
        type: 'text',
        text: n.text,
        marks: marksFromTiptap(n.marks),
        link,
      });
    } else if (n.type === 'hardBreak') {
      const last = result[result.length - 1];
      if (last) {
        last.text += '\n';
      } else {
        result.push({ type: 'text', text: '\n' });
      }
    }
  }
  if (!result.length) {
    return [{ type: 'text', text: '' }];
  }
  return mergeAdjacentTextNodes(result);
}

function docToTextNodes(doc: JSONContent): TextNode[] {
  const first = doc.content?.[0];
  if (first?.type === 'paragraph') {
    return inlineToTextNodes(first.content);
  }
  return [{ type: 'text', text: '' }];
}

function renderTextNodeVNode(node: TextNode): VNode {
  let vnode: VNode = createTextVNode(node.text);
  const marks = node.marks ?? [];
  for (let i = marks.length - 1; i >= 0; i--) {
    const mark = marks[i];
    if (mark === 'bold') {
      vnode = h('strong', vnode);
    } else if (mark === 'italic') {
      vnode = h('em', vnode);
    } else if (mark === 'underline') {
      vnode = h('u', vnode);
    }
  }
  const pageOnlyId =
    node.link?.pageId != null &&
    node.link.pageId > 0 &&
    (node.link?.url == null || node.link.url === '');
  if (pageOnlyId) {
    return h(
      'span',
      {
        class: 'text-neutral-600 dark:text-neutral-400',
        title: 'Page link (URL not resolved yet)',
      },
      [vnode],
    );
  }
  const rawUrl = node.link?.url;
  if (rawUrl) {
    const validated = validateAndNormalizeUrl(rawUrl);
    if (validated.ok && validated.href) {
      vnode = h(
        'a',
        {
          href: validated.href,
          target: '_blank',
          rel: 'noopener noreferrer',
          class:
            'text-blue-600 underline underline-offset-2 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300',
        },
        [vnode],
      );
    }
  }
  return vnode;
}

function formatToolbarButton(options: {
  label: string;
  active: boolean;
  onToggle: () => void;
  icon: typeof Bold;
}): VNode {
  return h(
    'button',
    {
      type: 'button',
      title: options.label,
      'aria-label': options.label,
      'aria-pressed': options.active,
      class: [
        'inline-flex size-8 shrink-0 items-center justify-center rounded-md text-sm transition-colors',
        'border border-neutral-200 bg-white text-neutral-800 shadow-sm outline-none',
        'hover:bg-neutral-50 focus-visible:ring-2 focus-visible:ring-neutral-400 focus-visible:ring-offset-1',
        'dark:border-neutral-600 dark:bg-neutral-900 dark:text-neutral-100 dark:hover:bg-neutral-800',
        'dark:focus-visible:ring-neutral-500',
        options.active
          ? 'bg-neutral-100 dark:bg-neutral-800'
          : '',
      ].join(' '),
      onMousedown: (e: MouseEvent) => {
        e.preventDefault();
      },
      onClick: (e: MouseEvent) => {
        e.preventDefault();
        options.onToggle();
      },
    },
    [h(options.icon, { class: 'size-4', strokeWidth: 2 })],
  );
}

export default defineComponent({
  name: 'PrimitiveText',
  components: {
    EditorContent,
    BubbleMenu,
    UiDialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    UiButton,
    UiInput,
    UiLabel,
  },
  props: {
    content: { type: Array as PropType<Array<TextNode>>, default: () => [] },
    onUpdateContent: {
      type: Function as PropType<(nodes: TextNode[]) => void>,
    },
  },
  setup(props, { expose }) {
    const linkablePages = inject(
      'linkablePages',
      computed<LinkablePage[]>(() => []),
    );
    const isEditing = ref(false);
    /** Bumps when the Tiptap selection/doc changes so the toolbar active states re-render. */
    const editorUiTick = ref(0);
    const linkDialogOpen = ref(false);
    const linkUrlDraft = ref('');
    const linkPageIdDraft = ref('');
    const linkUrlError = ref('');
    /**
     * Editor blur can fire before the link dialog is marked open; skip one commit cycle.
     */
    const deferEditorBlurCommit = ref(false);

    watch(linkDialogOpen, (open) => {
      if (open) {
        linkUrlError.value = '';
      }
    });

    function submitLinkDialog(ed: Editor): void {
      const pageIdStr = linkPageIdDraft.value;
      const hasPage =
        pageIdStr !== '' &&
        pageIdStr != null &&
        !Number.isNaN(Number(pageIdStr)) &&
        Number(pageIdStr) > 0;

      if (hasPage) {
        const pageId = Number(pageIdStr);
        linkUrlError.value = '';
        ed.chain()
          .focus()
          .setLink(
            { href: `page:${pageId}`, pageId } as {
              href: string;
              pageId: number;
            },
          )
          .run();
        linkDialogOpen.value = false;
        nextTick(() => {
          ed.chain().focus().run();
        });
        return;
      }

      const result = validateAndNormalizeUrl(linkUrlDraft.value);
      if (!result.ok) {
        linkUrlError.value = result.message;
        return;
      }
      linkUrlError.value = '';
      if (result.href === '') {
        ed.chain().focus().unsetLink().run();
      } else {
        ed.chain()
          .focus()
          .setLink(
            { href: result.href, pageId: null } as {
              href: string;
              pageId: null;
            },
          )
          .run();
      }
      linkDialogOpen.value = false;
      nextTick(() => {
        ed.chain().focus().run();
      });
    }

    function removeLinkFromDialog(ed: Editor): void {
      linkUrlError.value = '';
      ed.chain().focus().unsetLink().run();
      linkDialogOpen.value = false;
      linkUrlDraft.value = '';
      linkPageIdDraft.value = '';
      nextTick(() => {
        ed.chain().focus().run();
      });
    }

    const editor = useEditor({
      content: textNodesToHtml(normalizeContent(props.content)),
      extensions: [
        StarterKit.configure({
          heading: false,
          bulletList: false,
          orderedList: false,
          listItem: false,
          blockquote: false,
          codeBlock: false,
          horizontalRule: false,
          strike: false,
          code: false,
        }),
        Underline,
        EditorLink,
      ],
      editable: false,
      onSelectionUpdate: () => {
        editorUiTick.value += 1;
      },
      onTransaction: () => {
        editorUiTick.value += 1;
      },
      editorProps: {
        attributes: {
          class:
            'w-full min-h-[1.5em] border-0 bg-transparent p-0 outline-none focus:ring-0 whitespace-pre-wrap cursor-text',
        },
        handleKeyDown: (_view: unknown, event: KeyboardEvent) => {
          if (event.key === 'Enter' && !event.shiftKey) {
            event.preventDefault();
            commitEditing();
            return true;
          }
          return false;
        },
      },
      onBlur: () => {
        nextTick(() => {
          if (deferEditorBlurCommit.value || linkDialogOpen.value) {
            return;
          }
          commitEditing();
        });
      },
    });

    function commitEditing(): void {
      if (!isEditing.value) {
        return;
      }

      const json = editor.value?.getJSON();
      if (json) {
        props.onUpdateContent?.(docToTextNodes(json));
      }
      isEditing.value = false;
      editor.value?.setEditable(false);
    }

    function startEditing(): void {
      isEditing.value = true;
      editor.value?.commands.setContent(
        textNodesToHtml(normalizeContent(props.content)),
      );
      editor.value?.setEditable(true);
      nextTick(() => {
        editor.value?.commands.focus('end');
      });
    }

    watch(
      () => props.content,
      (nodes) => {
        if (isEditing.value) {
          return;
        }
        editor.value?.commands.setContent(
          textNodesToHtml(normalizeContent(nodes)),
        );
      },
      { deep: true },
    );

    onBeforeUnmount(() => {
      editor.value?.destroy();
    });

    expose({ startEditing });

    return () => {
      if (!isEditing.value) {
        const nodes = normalizeContent(props.content);
        const hasRenderable = nodes.some((n) => n.text.length > 0);
        if (!hasRenderable) {
          return createTextVNode(' ');
        }
        return h(
          'span',
          { class: 'inline whitespace-pre-wrap' },
          nodes.map((node) => renderTextNodeVNode(node)),
        );
      }
      const currentEditor = editor.value;
      if (!currentEditor) {
        return createTextVNode(' ');
      }

      void editorUiTick.value;

      return h('div', { class: 'relative' }, [
        h(EditorContent, { editor: currentEditor }),
        h(
          BubbleMenu,
          {
            editor: currentEditor,
            pluginKey: 'primitiveTextBubbleMenu',
            options: {
              placement: 'top',
              offset: 8,
            },
          },
          () =>
            h(
              'div',
              {
                class:
                  'flex items-center gap-0.5 rounded-lg border border-neutral-200 bg-white p-1 shadow-lg dark:border-neutral-600 dark:bg-neutral-900',
                role: 'toolbar',
                'aria-label': 'Text formatting',
              },
              [
                formatToolbarButton({
                  label: 'Bold',
                  active: currentEditor.isActive('bold'),
                  icon: Bold,
                  onToggle: () => {
                    currentEditor.chain().focus().toggleBold().run();
                  },
                }),
                formatToolbarButton({
                  label: 'Italic',
                  active: currentEditor.isActive('italic'),
                  icon: Italic,
                  onToggle: () => {
                    currentEditor.chain().focus().toggleItalic().run();
                  },
                }),
                formatToolbarButton({
                  label: 'Underline',
                  active: currentEditor.isActive('underline'),
                  icon: UnderlineIcon,
                  onToggle: () => {
                    currentEditor.chain().focus().toggleUnderline().run();
                  },
                }),
                h(
                  'button',
                  {
                    type: 'button',
                    title: 'Link',
                    'aria-label': 'Link',
                    'aria-pressed': currentEditor.isActive('link'),
                    class: [
                      'inline-flex size-8 shrink-0 items-center justify-center rounded-md text-sm transition-colors',
                      'border border-neutral-200 bg-white text-neutral-800 shadow-sm outline-none',
                      'hover:bg-neutral-50 focus-visible:ring-2 focus-visible:ring-neutral-400 focus-visible:ring-offset-1',
                      'dark:border-neutral-600 dark:bg-neutral-900 dark:text-neutral-100 dark:hover:bg-neutral-800',
                      'dark:focus-visible:ring-neutral-500',
                      currentEditor.isActive('link')
                        ? 'bg-neutral-100 dark:bg-neutral-800'
                        : '',
                    ].join(' '),
                    onMousedown: (e: MouseEvent) => {
                      e.preventDefault();
                      deferEditorBlurCommit.value = true;
                    },
                    onClick: (e: MouseEvent) => {
                      e.preventDefault();
                      const attrs = currentEditor.getAttributes('link');
                      const href = String(attrs.href ?? '');
                      const pageIdAttr = attrs.pageId;
                      if (
                        pageIdAttr != null &&
                        pageIdAttr !== '' &&
                        String(pageIdAttr) !== 'null'
                      ) {
                        linkPageIdDraft.value = String(pageIdAttr);
                        linkUrlDraft.value = '';
                      } else if (/^page:\d+$/i.test(href)) {
                        linkPageIdDraft.value = href.replace(/^page:/i, '');
                        linkUrlDraft.value = '';
                      } else {
                        linkPageIdDraft.value = '';
                        linkUrlDraft.value = href;
                      }
                      linkDialogOpen.value = true;
                      nextTick(() => {
                        deferEditorBlurCommit.value = false;
                      });
                    },
                  },
                  [h(Link2, { class: 'size-4', strokeWidth: 2 })],
                ),
              ],
            ),
        ),
        h(
          UiDialog,
          {
            open: linkDialogOpen.value,
            'onUpdate:open': (open: boolean) => {
              linkDialogOpen.value = open;
              if (!open) {
                nextTick(() => {
                  currentEditor.chain().focus().run();
                });
              }
            },
          },
          {
            default: () =>
              h(DialogContent, { class: 'sm:max-w-md' }, [
                h(DialogHeader, {}, [
                  h(DialogTitle, {}, 'Link'),
                  h(
                    DialogDescription,
                    {},
                    'Pick a site page, or enter an external https:// / http:// / mailto: URL. Page links are stored by id until URLs are resolved.',
                  ),
                ]),
                h('div', { class: 'grid gap-4 py-2' }, [
                  h('div', { class: 'grid gap-2' }, [
                    h(UiLabel, { for: 'primitive-text-link-page' }, () => 'Page'),
                    h(
                      'select',
                      {
                        id: 'primitive-text-link-page',
                        class:
                          'border-input bg-background h-9 w-full rounded-md border px-3 py-1 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50 dark:bg-input/30',
                        value: linkPageIdDraft.value,
                        onChange: (ev: Event) => {
                          const v = (ev.target as HTMLSelectElement).value;
                          linkPageIdDraft.value = v;
                          if (v !== '') {
                            linkUrlDraft.value = '';
                          }
                        },
                      },
                      [
                        h('option', { value: '' }, 'None'),
                        ...linkablePages.value.map((p: LinkablePage) =>
                          h(
                            'option',
                            { value: String(p.id) },
                            p.title,
                          ),
                        ),
                      ],
                    ),
                  ]),
                  h('div', { class: 'grid gap-2' }, [
                    h(UiLabel, { for: 'primitive-text-link-url' }, () => 'URL'),
                    h(UiInput, {
                      id: 'primitive-text-link-url',
                      modelValue: linkUrlDraft.value,
                      type: 'url',
                      placeholder: 'https://example.com',
                      autocomplete: 'off',
                      'onUpdate:modelValue': (v: string | number) => {
                        const s = String(v);
                        linkUrlDraft.value = s;
                        if (s.trim() !== '') {
                          linkPageIdDraft.value = '';
                        }
                      },
                      onKeydown: (e: KeyboardEvent) => {
                        if (e.key === 'Enter') {
                          e.preventDefault();
                          submitLinkDialog(currentEditor);
                        }
                      },
                    }),
                  ]),
                  ...(linkUrlError.value
                    ? [
                        h(
                          'p',
                          {
                            class:
                              'text-sm text-red-600 dark:text-red-400',
                            role: 'alert',
                          },
                          linkUrlError.value,
                        ),
                      ]
                    : []),
                ]),
                h(
                  DialogFooter,
                  {
                    class: 'flex flex-col gap-2 sm:flex-row sm:justify-end',
                  },
                  [
                    h(
                      UiButton,
                      {
                        variant: 'outline',
                        class: 'w-full sm:w-auto',
                        onClick: () => {
                          linkDialogOpen.value = false;
                        },
                      },
                      () => 'Cancel',
                    ),
                    h(
                      UiButton,
                      {
                        variant: 'outline',
                        class: 'w-full sm:w-auto',
                        onClick: () => removeLinkFromDialog(currentEditor),
                      },
                      () => 'Remove link',
                    ),
                    h(
                      UiButton,
                      {
                        class: 'w-full sm:w-auto',
                        onClick: () => submitLinkDialog(currentEditor),
                      },
                      () => 'Apply',
                    ),
                  ],
                ),
              ]),
          },
        ),
      ]);
    };
  },
});
</script>
