import type { Layout, Options, Theme } from './types';

const LAYOUTS: Layout[] = ['masonry', 'carousel', 'list'];
const THEMES: Theme[] = ['light', 'dark', 'auto'];

function oneOf<T extends string>(value: string | undefined, allowed: T[], fallback: T): T {
  return allowed.includes(value as T) ? (value as T) : fallback;
}

function flag(value: string | undefined, fallback: boolean): boolean {
  if (value === undefined) return fallback;
  return !['false', '0', 'no', 'off'].includes(value.trim().toLowerCase());
}

/** Reads the widget options from the element's data attributes. */
export function readOptions(element: HTMLElement): Options {
  const data = element.dataset;
  const limit = Number.parseInt(data.limit ?? '', 10);

  return {
    layout: oneOf(data.layout, LAYOUTS, 'masonry'),
    theme: oneOf(data.theme, THEMES, 'auto'),
    limit: Number.isFinite(limit) && limit > 0 ? limit : null,
    cta: flag(data.cta, true),
    branding: flag(data.branding, true),
  };
}
