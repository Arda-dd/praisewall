import { h, safeUrl, svg } from './dom';
import { fill, labels as readLabels, plural } from './labels';
import { HEX } from './options';
import type { Feed, Labels, Options, Testimonial } from './types';

const STAR = 'M10 1.5l2.6 5.3 5.9.9-4.25 4.1 1 5.8L10 14.9l-5.25 2.7 1-5.8L1.5 7.7l5.9-.9L10 1.5z';
const QUOTE = 'M9.5 5C6.4 6.4 4.5 9 4.5 12.6V19h6.2v-6.2H7.6c0-2.2 1.2-3.8 3.1-4.7L9.5 5zm10 0c-3.1 1.4-5 4-5 7.6V19h6.2v-6.2h-3.1c0-2.2 1.2-3.8 3.1-4.7L19.5 5z';
const CHEVRON = 'M12.5 4.5 7 10l5.5 5.5';
const CROSS = 'M5 5l10 10M15 5 5 15';
const LONG_TEXT = 280;

interface Context {
  feed: Feed;
  options: Options;
  labels: Labels;
  locale: string;
}

function stars(rating: number, ctx: Context): HTMLElement {
  const wrapper = h('div', { class: 'stars', role: 'img', 'aria-label': fill(ctx.labels.stars, { rating }) });
  for (let i = 1; i <= 5; i++) {
    wrapper.append(svg('0 0 20 20', STAR, i <= rating ? 'star on' : 'star'));
  }
  return wrapper;
}

function icon(path: string, className: string): SVGSVGElement {
  const element = svg('0 0 20 20', path, className);
  element.setAttribute('fill', 'none');
  element.setAttribute('stroke', 'currentColor');
  element.setAttribute('stroke-width', '2');
  element.setAttribute('stroke-linecap', 'round');
  element.setAttribute('stroke-linejoin', 'round');
  return element;
}

/** A stable hue per name, so initials avatars don't all look the same. */
function hue(name: string): number {
  let hash = 0;
  for (const char of name) hash = (hash * 31 + char.codePointAt(0)!) % 360;
  return hash;
}

export function avatar(testimonial: Testimonial, size = 40): HTMLElement {
  const { author } = testimonial;
  const src = safeUrl(author.avatar_url);

  if (src) {
    return h('img', { class: 'avatar', src, alt: '', loading: 'lazy', width: String(size), height: String(size) });
  }

  const initials = h('span', { class: 'avatar initials', 'aria-hidden': 'true' }, author.initials || '?');
  initials.style.setProperty('--hue', String(hue(author.name)));
  return initials;
}

function body(text: string, ctx: Context, clamp = true): HTMLElement {
  const paragraph = h('p', { class: 'body' }, text);

  if (!clamp || text.length <= LONG_TEXT) return paragraph;

  paragraph.classList.add('clamped');
  const toggle = h('button', { type: 'button', class: 'more', 'aria-expanded': 'false' }, ctx.labels.read_more);
  toggle.addEventListener('click', () => {
    const expanded = paragraph.classList.toggle('clamped') === false;
    toggle.textContent = expanded ? ctx.labels.show_less : ctx.labels.read_more;
    toggle.setAttribute('aria-expanded', String(expanded));
  });

  return h('div', {}, paragraph, toggle);
}

function date(value: string | null, locale: string): HTMLElement | null {
  if (!value) return null;
  const parsed = new Date(`${value}T00:00:00`);
  if (Number.isNaN(parsed.getTime())) return null;

  return h('time', { class: 'date', datetime: value }, new Intl.DateTimeFormat(locale, { month: 'short', year: 'numeric' }).format(parsed));
}

export function card(testimonial: Testimonial, ctx: Context, clamp = true): HTMLElement {
  const { author } = testimonial;
  const { hide } = ctx.options;
  const url = safeUrl(author.url);
  const name = url
    ? h('a', { class: 'name', href: url, target: '_blank', rel: 'nofollow noopener noreferrer' }, author.name)
    : h('span', { class: 'name' }, author.name);

  return h(
    'figure',
    { class: testimonial.featured ? 'card featured' : 'card' },
    h(
      'div',
      { class: 'content' },
      hide.includes('quote') ? null : svg('0 0 24 24', QUOTE, 'quote'),
      testimonial.rating && !hide.includes('rating') ? stars(testimonial.rating, ctx) : null,
      h('blockquote', {}, body(testimonial.body, ctx, clamp)),
    ),
    h(
      'figcaption',
      {},
      hide.includes('avatar') ? null : avatar(testimonial),
      h(
        'span',
        { class: 'who' },
        name,
        author.title && !hide.includes('title') ? h('span', { class: 'title' }, author.title) : null,
        ctx.options.date ? date(testimonial.date, ctx.locale) : null,
      ),
    ),
  );
}

function reducedMotion(): boolean {
  return typeof window.matchMedia === 'function' && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
}

/** Runs `tick` every `seconds` while the element is on the page, pausing on hover and focus. */
function autoplay(element: HTMLElement, seconds: number, tick: () => void): void {
  if (reducedMotion()) return;

  let paused = false;
  element.addEventListener('mouseenter', () => (paused = true));
  element.addEventListener('mouseleave', () => (paused = false));
  element.addEventListener('focusin', () => (paused = true));
  element.addEventListener('focusout', () => (paused = false));

  const timer = window.setInterval(() => {
    if (!element.isConnected) return window.clearInterval(timer);
    if (!paused && document.visibilityState !== 'hidden') tick();
  }, seconds * 1000);
}

function carousel(cards: HTMLElement[]): HTMLElement {
  const track = h('div', { class: 'track' }, ...cards);

  // A second, hidden copy makes the marquee loop without a jump.
  if (cards.length > 1) {
    for (const original of cards) {
      const copy = original.cloneNode(true) as HTMLElement;
      copy.setAttribute('aria-hidden', 'true');
      copy.setAttribute('inert', '');
      track.append(copy);
    }
    track.style.setProperty('--duration', `${Math.max(20, cards.length * 7)}s`);
  }

  return h('div', { class: 'carousel', tabindex: '0' }, track);
}

function slider(testimonials: Testimonial[], ctx: Context): HTMLElement {
  const slides = testimonials.map((testimonial, index) => {
    const slide = h('div', { class: index === 0 ? 'slide active' : 'slide', role: 'group', 'aria-roledescription': 'slide', 'aria-label': `${index + 1} / ${testimonials.length}` }, card(testimonial, ctx, false));
    if (index !== 0) slide.setAttribute('aria-hidden', 'true');
    return slide;
  });
  const dots = testimonials.map((_, index) =>
    h('button', { type: 'button', class: index === 0 ? 'dot active' : 'dot', 'aria-label': `${index + 1} / ${testimonials.length}` }),
  );

  let current = 0;
  const show = (index: number) => {
    current = (index + slides.length) % slides.length;
    slides.forEach((slide, i) => {
      slide.classList.toggle('active', i === current);
      slide.toggleAttribute('aria-hidden', i !== current);
    });
    dots.forEach((dot, i) => {
      dot.classList.toggle('active', i === current);
      dot.setAttribute('aria-current', String(i === current));
    });
  };

  dots.forEach((dot, index) => dot.addEventListener('click', () => show(index)));
  const previous = h('button', { type: 'button', class: 'arrow', 'aria-label': ctx.labels.previous }, icon(CHEVRON, 'chevron'));
  const next = h('button', { type: 'button', class: 'arrow next', 'aria-label': ctx.labels.next }, icon(CHEVRON, 'chevron'));
  previous.addEventListener('click', () => show(current - 1));
  next.addEventListener('click', () => show(current + 1));

  const element = h(
    'div',
    { class: 'slider', 'aria-roledescription': 'carousel', tabindex: '0' },
    h('div', { class: 'slides', 'aria-live': 'polite' }, ...slides),
    slides.length > 1 ? h('div', { class: 'controls' }, previous, h('div', { class: 'dots' }, ...dots), next) : null,
  );

  element.addEventListener('keydown', (event) => {
    if (event.key === 'ArrowLeft') show(current - 1);
    if (event.key === 'ArrowRight') show(current + 1);
  });

  if (slides.length > 1) autoplay(element, ctx.options.interval, () => show(current + 1));

  return element;
}

function popup(testimonials: Testimonial[], ctx: Context): HTMLElement | null {
  const key = `praisewall:closed:${ctx.feed.name}`;
  try {
    if (window.sessionStorage.getItem(key)) return null;
  } catch {
    // Storage can be blocked; then the popup just shows.
  }

  const slot = h('div', { class: 'slot' });
  const close = h('button', { type: 'button', class: 'close', 'aria-label': ctx.labels.close }, icon(CROSS, 'cross'));
  const element = h('aside', { class: `popup ${ctx.options.position}`, 'aria-label': ctx.labels.about }, close, slot);

  let current = 0;
  const show = (index: number) => {
    current = index % testimonials.length;
    const testimonial = testimonials[current]!;
    const { author } = testimonial;
    const rating = testimonial.rating && !ctx.options.hide.includes('rating') ? stars(testimonial.rating, ctx) : null;

    slot.replaceChildren(
      h(
        'div',
        { class: 'toast' },
        ctx.options.hide.includes('avatar') ? null : avatar(testimonial),
        h('div', { class: 'toast-text' }, h('div', { class: 'toast-head' }, h('span', { class: 'name' }, author.name), rating), h('p', { class: 'body clamped-3' }, testimonial.body)),
      ),
    );
  };

  close.addEventListener('click', () => {
    element.remove();
    try {
      window.sessionStorage.setItem(key, '1');
    } catch {
      // Ignore, it just shows again next time.
    }
  });

  show(0);
  if (testimonials.length > 1) {
    autoplay(element, Math.max(ctx.options.interval, 5), () => {
      element.classList.add('switching');
      window.setTimeout(() => {
        show(current + 1);
        element.classList.remove('switching');
      }, 300);
    });
  }

  return element;
}

function badge(testimonials: Testimonial[], ctx: Context): HTMLElement {
  const summary = ctx.feed.summary ?? {
    count: testimonials.length,
    rated: testimonials.filter((t) => t.rating).length,
    average: null,
  };
  const average = summary.average;
  const faces = ctx.options.hide.includes('avatar') ? null : h('span', { class: 'faces' }, ...testimonials.slice(0, 4).map((t) => avatar(t, 32)));
  const content = [
    faces,
    h(
      'span',
      { class: 'badge-text' },
      average !== null ? h('span', { class: 'badge-score' }, stars(Math.round(average), ctx), h('b', {}, average.toFixed(1))) : null,
      h('span', { class: 'badge-count' }, plural(ctx.labels.reviews, summary.count, ctx.locale)),
    ),
  ];

  const url = safeUrl(ctx.feed.wall_url);
  return url
    ? h('a', { class: 'badge', href: url, target: '_blank', rel: 'noopener', 'aria-label': ctx.labels.about }, ...content)
    : h('div', { class: 'badge' }, ...content);
}

function footer(ctx: Context): HTMLElement | null {
  const collect = safeUrl(ctx.feed.collect_url);
  const parts: HTMLElement[] = [];

  if (ctx.options.cta && collect) {
    parts.push(h('a', { class: 'cta', href: collect, target: '_blank', rel: 'noopener' }, ctx.labels.leave));
  }

  if (ctx.options.branding) {
    parts.push(h('a', { class: 'branding', href: 'https://github.com/Arda-dd/praisewall', target: '_blank', rel: 'noopener' }, 'Praisewall'));
  }

  return parts.length ? h('div', { class: 'footer' }, ...parts) : null;
}

export function render(feed: Feed, options: Options): HTMLElement {
  const ctx: Context = { feed, options, labels: readLabels(feed), locale: feed.locale || 'en' };
  const testimonials = options.limit ? feed.testimonials.slice(0, options.limit) : feed.testimonials;
  const floating = options.layout === 'popup' || options.layout === 'badge';

  let content: HTMLElement | null;
  if (testimonials.length === 0) {
    content = floating ? null : h('p', { class: 'empty' }, ctx.labels.empty);
  } else if (options.layout === 'popup') {
    content = popup(testimonials, ctx);
  } else if (options.layout === 'badge') {
    content = badge(testimonials, ctx);
  } else if (options.layout === 'slider') {
    content = slider(testimonials, ctx);
  } else if (options.layout === 'carousel') {
    content = carousel(testimonials.map((t) => card(t, ctx)));
  } else {
    content = h('div', { class: options.layout }, ...testimonials.map((t) => card(t, ctx)));
  }

  const root = h('section', { class: `root layout-${options.layout}`, lang: ctx.locale, 'aria-label': ctx.labels.about }, content, floating ? null : footer(ctx));
  const accent = options.accent ?? feed.accent_color;
  root.style.setProperty('--accent', HEX.test(accent) ? accent : '#6d28d9');
  if (options.columns) root.style.setProperty('--columns', String(options.columns));

  return root;
}
