import { readOptions } from './options';
import { render } from './render';
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
  element.setAttribute('data-pw-theme', options.theme);
  element.setAttribute('data-pw-radius', options.radius);
  element.toggleAttribute('data-pw-inherit-font', options.inheritFont);

  const style = document.createElement('style');
  style.textContent = styles;
  const skeleton = document.createElement('div');
  skeleton.className = 'skeleton';
  const floating = options.layout === 'popup' || options.layout === 'badge';
  shadow.replaceChildren(style, ...(floating ? [] : [skeleton]));

  try {
    const data = feed ?? (await loadFeed(element.dataset.praisewall ?? ''));
    element.setAttribute('data-pw-style', options.style ?? data.style ?? 'classic');
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
