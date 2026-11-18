import { LAYOUTS, PARTS, POSITIONS, RADII, STYLES, THEMES } from './types';
import type { Options, Part } from './types';

function oneOf<T extends string>(value: string | undefined, allowed: readonly T[], fallback: T): T {
  return allowed.includes(value as T) ? (value as T) : fallback;
}

function flag(value: string | undefined, fallback: boolean): boolean {
  if (value === undefined) return fallback;
  return !['false', '0', 'no', 'off'].includes(value.trim().toLowerCase());
}

function int(value: string | undefined, min: number, max: number): number | null {
  const number = Number.parseInt(value ?? '', 10);
  return Number.isFinite(number) && number >= min && number <= max ? number : null;
}

export const HEX = /^#[0-9a-f]{6}$/i;

/** Reads the widget options from the element's data attributes. */
export function readOptions(element: HTMLElement): Options {
  const data = element.dataset;
  const hide = (data.hide ?? '')
    .split(',')
    .map((part) => part.trim().toLowerCase())
    .filter((part): part is Part => (PARTS as readonly string[]).includes(part));

  return {
    layout: oneOf(data.layout, LAYOUTS, 'masonry'),
    style: (STYLES as readonly string[]).includes(data.style ?? '') ? (data.style as Options['style']) : null,
    theme: oneOf(data.theme, THEMES, 'auto'),
    limit: int(data.limit, 1, 500),
    columns: int(data.columns, 1, 6),
    radius: oneOf(data.radius, RADII, 'medium'),
    inheritFont: data.font === 'inherit',
    accent: data.accent && HEX.test(data.accent) ? data.accent : null,
    hide: [...new Set(hide)],
    date: flag(data.date, false),
    cta: flag(data.cta, true),
    branding: flag(data.branding, true),
    position: oneOf(data.position, POSITIONS, 'bottom-left'),
    interval: int(data.interval, 2, 60) ?? 6,
  };
}
