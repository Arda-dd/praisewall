// Small progressive enhancements. Every page works without them.

async function copy(text, button) {
    try {
        await navigator.clipboard.writeText(text);
    } catch {
        return;
    }

    const label = button.textContent;
    button.textContent = 'Copied!';
    setTimeout(() => (button.textContent = label), 1500);
}

document.addEventListener('click', (event) => {
    const copyButton = event.target.closest('[data-copy]');
    if (copyButton) copy(copyButton.dataset.copy, copyButton);

    const copyTarget = event.target.closest('[data-copy-target]');
    if (copyTarget) copy(document.querySelector(copyTarget.dataset.copyTarget).textContent, copyTarget);

    const toggle = event.target.closest('[data-toggle]');
    if (toggle) {
        const target = document.querySelector(toggle.dataset.toggle);
        target.classList.toggle('hidden');
        target.querySelector('input, textarea')?.focus();
    }
});

document.addEventListener('submit', (event) => {
    const message = event.target.dataset.confirm;
    if (message && !window.confirm(message)) event.preventDefault();
});

// Wall form: fill the link from the name until it's edited by hand.
const slugSource = document.querySelector('[data-slug-source]');
const slugTarget = document.querySelector('[data-slug-target]');
if (slugSource && slugTarget) {
    let touched = slugTarget.hasAttribute('data-slug-locked') || slugTarget.value !== '';
    const slugify = (value) =>
        value
            .normalize('NFKD')
            .replace(/[\u0300-\u036f]/g, '')
            .replace(/ı/g, 'i')
            .toLowerCase()
            .replace(/[^a-z0-9]+/g, '-')
            .replace(/^-+|-+$/g, '')
            .slice(0, 80);

    slugTarget.addEventListener('input', () => (touched = true));
    slugSource.addEventListener('input', () => {
        if (!touched) slugTarget.value = slugify(slugSource.value);
    });
}

document.querySelectorAll('input[type="color"]').forEach((input) => {
    const label = input.parentElement.querySelector('[data-color-label]');
    input.addEventListener('input', () => label && (label.textContent = input.value));
});

// Star rating: highlight up to the hovered or chosen star.
document.querySelectorAll('[data-rating]').forEach((group) => {
    const stars = [...group.querySelectorAll('[data-star]')];
    const inputs = [...group.querySelectorAll('input')];
    const paint = (value) =>
        stars.forEach((star) => {
            const on = Number(star.dataset.star) <= value;
            star.style.fill = on ? '#f5a524' : '';
            star.style.transform = on ? 'scale(1.06)' : '';
        });
    const chosen = () => Number(inputs.find((input) => input.checked)?.value ?? 0);

    group.addEventListener('mouseover', (event) => {
        const star = event.target.closest('label')?.querySelector('[data-star]');
        if (star) paint(Number(star.dataset.star));
    });
    group.addEventListener('mouseleave', () => paint(chosen()));
    group.addEventListener('change', () => paint(chosen()));
    paint(chosen());
});

// Embed page: build the snippet from the options and keep the preview in sync.
const embed = document.querySelector('[data-embed]');
if (embed) {
    const snippet = document.getElementById('snippet');
    const preview = embed.querySelector('[data-preview]');
    const field = (name) => embed.querySelector(`[name="${name}"]`);
    const value = (name) => embed.querySelector(`[name="${name}"]:checked`)?.value ?? field(name)?.value ?? '';
    const checked = (name) => field(name).checked;

    // Only what differs from the widget's defaults goes into the snippet.
    const attributes = () => {
        const layout = value('layout');
        const hidden = [...embed.querySelectorAll('[name="show"]')].filter((box) => !box.checked).map((box) => box.value);
        const attrs = { 'data-praisewall': embed.dataset.feed, 'data-layout': layout };

        if (value('style') !== embed.dataset.wallStyle) attrs['data-style'] = value('style');
        if (value('theme') !== 'auto') attrs['data-theme'] = value('theme');
        if (value('radius') !== 'medium') attrs['data-radius'] = value('radius');
        if (value('accent').toLowerCase() !== embed.dataset.wallAccent.toLowerCase()) attrs['data-accent'] = value('accent');
        if (value('columns') && ['masonry', 'grid'].includes(layout)) attrs['data-columns'] = value('columns');
        if (layout === 'popup' && value('position') !== 'bottom-left') attrs['data-position'] = value('position');
        if (value('limit')) attrs['data-limit'] = value('limit');
        if (hidden.length) attrs['data-hide'] = hidden.join(',');
        if (checked('date')) attrs['data-date'] = 'true';
        if (checked('font')) attrs['data-font'] = 'inherit';
        if (!checked('cta')) attrs['data-cta'] = 'false';
        if (!checked('branding')) attrs['data-branding'] = 'false';
        return attrs;
    };

    const update = () => {
        const layout = value('layout');
        embed.querySelectorAll('[data-only]').forEach((element) => {
            element.hidden = !element.dataset.only.split(' ').includes(layout);
        });

        const attrs = attributes();
        const html = Object.entries(attrs)
            .map(([name, val]) => `${name}="${val}"`)
            .join(' ');
        snippet.textContent = `<div ${html}></div>
<script src="${embed.dataset.widget}" async></script>`;

        // A fresh element each time, since the widget mounts an element once.
        const target = document.createElement('div');
        Object.entries(attrs).forEach(([name, val]) => target.setAttribute(name, val));
        try {
            sessionStorage.removeItem(`praisewall:closed:${embed.dataset.wallName}`);
        } catch {
            // Storage can be blocked; the preview popup then stays closed once dismissed.
        }
        preview.replaceChildren(target);
        window.Praisewall?.mount(target);
    };

    embed.querySelectorAll('[data-preview-bg]').forEach((button) =>
        button.addEventListener('click', () => {
            preview.style.background = button.dataset.previewBg;
            embed.querySelectorAll('[data-preview-bg]').forEach((other) => other.setAttribute('aria-pressed', String(other === button)));
        }),
    );

    embed.addEventListener('change', update);
    embed.addEventListener('input', (event) => event.target.type === 'color' && update());
    if (window.Praisewall) update();
    else window.addEventListener('load', update);
}

// File inputs on the collection form: show the chosen photo.
document.querySelectorAll('[data-file]').forEach((label) => {
    const input = label.querySelector('input[type="file"]');
    const preview = label.querySelector('[data-file-preview]');
    const name = label.querySelector('[data-file-label]');
    input.addEventListener('change', () => {
        const file = input.files?.[0];
        if (!file) return;
        name.textContent = file.name;
        const image = document.createElement('img');
        image.src = URL.createObjectURL(file);
        image.alt = '';
        image.className = 'size-full object-cover';
        preview.replaceChildren(image);
    });
});

const shareImage = document.querySelector('[data-share-image]');
if (shareImage) import('./share-image.js').then(({ init }) => init(shareImage));

