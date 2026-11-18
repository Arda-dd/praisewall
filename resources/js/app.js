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
    const value = (name) => embed.querySelector(`[name="${name}"]:checked`)?.value ?? embed.querySelector(`[name="${name}"]`)?.value;
    const checked = (name) => embed.querySelector(`[name="${name}"]`).checked;

    const attributes = () => {
        const attrs = { 'data-praisewall': embed.dataset.feed, 'data-layout': value('layout'), 'data-theme': value('theme') };
        if (value('limit')) attrs['data-limit'] = value('limit');
        if (!checked('cta')) attrs['data-cta'] = 'false';
        if (!checked('branding')) attrs['data-branding'] = 'false';
        return attrs;
    };

    const update = () => {
        const attrs = attributes();
        const html = Object.entries(attrs)
            .map(([name, val]) => `${name}="${val}"`)
            .join(' ');
        snippet.textContent = `<div ${html}></div>\n<script src="${embed.dataset.widget}" async></script>`;

        // A fresh element each time, since the widget mounts an element once.
        const target = document.createElement('div');
        Object.entries(attrs).forEach(([name, val]) => target.setAttribute(name, val));
        preview.replaceChildren(target);
        window.Praisewall?.mount(target);
    };

    embed.addEventListener('change', update);
    if (window.Praisewall) update();
    else window.addEventListener('load', update);
}
