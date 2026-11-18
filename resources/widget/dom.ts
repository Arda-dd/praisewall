type Attributes = Record<string, string | null | undefined>;
type Child = Node | string | null | undefined | false;

/** Small element builder. Text always goes in as text nodes, never as HTML. */
export function h(tag: string, attributes: Attributes = {}, ...children: Child[]): HTMLElement {
  const element = document.createElement(tag);

  for (const [name, value] of Object.entries(attributes)) {
    if (value !== null && value !== undefined) element.setAttribute(name, value);
  }

  for (const child of children) {
    if (child === null || child === undefined || child === false) continue;
    element.append(typeof child === 'string' ? document.createTextNode(child) : child);
  }

  return element;
}

const SVG = 'http://www.w3.org/2000/svg';

export function svg(viewBox: string, path: string, className: string): SVGSVGElement {
  const element = document.createElementNS(SVG, 'svg');
  element.setAttribute('viewBox', viewBox);
  element.setAttribute('class', className);
  element.setAttribute('aria-hidden', 'true');
  const shape = document.createElementNS(SVG, 'path');
  shape.setAttribute('d', path);
  element.append(shape);
  return element;
}

/** Only http(s) links are rendered, so a stored "javascript:" url can't run. */
export function safeUrl(url: string | null | undefined): string | null {
  if (!url) return null;
  try {
    const parsed = new URL(url, window.location.href);
    return parsed.protocol === 'http:' || parsed.protocol === 'https:' ? parsed.href : null;
  } catch {
    return null;
  }
}
