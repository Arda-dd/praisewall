import { render } from './render';
import { readOptions } from './options';
import styles from './styles.css?inline';
import type { Feed } from './types';

const mounted = new WeakSet<HTMLElement>();

async function loadFeed(url: string): Promise<Feed> {
  const response = await fetch(url, { headers: { Accept: 'application/json' } });
  if (!response.ok) throw new Error(`HTTP ${response.status}`);
  return (await response.json()) as Feed;
}

/**
 * Renders a wall into the element. The feed url comes from `data-praisewall`,
 * or the feed can be passed directly (used by the wall page, which already has it).
 */
export async function mount(element: HTMLElement, feed?: Feed): Promise<void> {
  if (mounted.has(element)) return;
  mounted.add(element);

  const options = readOptions(element);
  const shadow = element.shadowRoot ?? element.attachShadow({ mode: 'open' });
  element.setAttribute('data-theme', options.theme);

  const style = document.createElement('style');
  style.textContent = styles;
  const skeleton = document.createElement('div');
  skeleton.className = 'skeleton';
  shadow.replaceChildren(style, skeleton);

  try {
    const data = feed ?? (await loadFeed(element.dataset.praisewall ?? ''));
    shadow.replaceChildren(style, render(data, options));
  } catch (error) {
    shadow.replaceChildren();
    console.warn('[Praisewall] Could not load testimonials:', error);
  }
}

export function mountAll(root: ParentNode = document): void {
  root.querySelectorAll<HTMLElement>('[data-praisewall]').forEach((element) => void mount(element));
}

declare global {
  interface Window {
    Praisewall?: { mount: typeof mount; mountAll: typeof mountAll };
  }
}

window.Praisewall = { mount, mountAll };

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', () => mountAll());
} else {
  mountAll();
}
