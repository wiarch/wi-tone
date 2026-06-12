/**
 * Editor ChordPro — letra ancho completo + buscador flotante + preview.
 */

function parseChordProLine(line) {
    const chords = [];
    const lyricChars = [];
    let i = 0;

    while (i < line.length) {
        if (line[i] === '[') {
            const end = line.indexOf(']', i + 1);
            if (end !== -1) {
                chords.push({ pos: lyricChars.length, name: line.slice(i + 1, end) });
                i = end + 1;
                continue;
            }
        }
        lyricChars.push(line[i]);
        i++;
    }

    return { lyrics: lyricChars.join(''), chords };
}

function buildChordLine(chords, lyricLength) {
    if (!chords.length) {
        return '';
    }

    const width = Math.max(
        lyricLength,
        ...chords.map(({ pos, name }) => pos + name.length)
    );
    const chars = Array(width).fill(' ');

    for (const { pos, name } of chords) {
        for (let j = 0; j < name.length; j++) {
            const idx = pos + j;
            if (idx < chars.length) {
                chars[idx] = name[j];
            }
        }
    }

    return chars.join('').replace(/\s+$/, '');
}

function renderPreview(text) {
    const lines = text.replace(/\r\n/g, '\n').split('\n');

    return lines.map((line) => {
        const { lyrics, chords } = parseChordProLine(line);
        const chordLine = buildChordLine(chords, lyrics.length);
        const chordHtml = chordLine
            ? `<div class="chordpro-preview-chords font-mono text-violet-400 text-sm leading-none mb-0.5 whitespace-pre">${escapeHtml(chordLine)}</div>`
            : '';
        const lyricHtml = `<div class="chordpro-preview-lyrics font-mono text-slate-200 text-sm leading-relaxed whitespace-pre">${escapeHtml(lyrics) || '&nbsp;'}</div>`;

        return `<div class="chordpro-preview-line py-1">${chordHtml}${lyricHtml}</div>`;
    }).join('');
}

function escapeHtml(str) {
    return str
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}

function insertAtCursor(textarea, text) {
    const start = textarea.selectionStart;
    const end = textarea.selectionEnd;
    const value = textarea.value;

    textarea.value = value.slice(0, start) + text + value.slice(end);
    const pos = start + text.length;
    textarea.selectionStart = pos;
    textarea.selectionEnd = pos;
    textarea.focus();
    textarea.dispatchEvent(new Event('input', { bubbles: true }));
}

function getCaretViewportPosition(textarea, position) {
    const mirrorProps = [
        'direction', 'boxSizing', 'width', 'height', 'overflowX', 'overflowY',
        'borderTopWidth', 'borderRightWidth', 'borderBottomWidth', 'borderLeftWidth',
        'paddingTop', 'paddingRight', 'paddingBottom', 'paddingLeft',
        'fontStyle', 'fontVariant', 'fontWeight', 'fontSize', 'lineHeight',
        'fontFamily', 'textAlign', 'letterSpacing', 'wordSpacing', 'tabSize',
    ];

    const mirror = document.createElement('div');
    const computed = window.getComputedStyle(textarea);
    const rect = textarea.getBoundingClientRect();

    mirror.style.position = 'absolute';
    mirror.style.visibility = 'hidden';
    mirror.style.whiteSpace = 'pre-wrap';
    mirror.style.wordWrap = 'break-word';
    mirror.style.top = '0';
    mirror.style.left = '-9999px';
    mirror.style.width = `${rect.width}px`;

    mirrorProps.forEach((prop) => {
        mirror.style[prop] = computed[prop];
    });

    const textBefore = textarea.value.substring(0, position);
    mirror.textContent = textBefore;

    const marker = document.createElement('span');
    marker.textContent = textarea.value.substring(position) || '.';
    mirror.appendChild(marker);

    document.body.appendChild(mirror);

    const top = rect.top + marker.offsetTop - textarea.scrollTop + parseInt(computed.borderTopWidth, 10);
    const left = rect.left + marker.offsetLeft - textarea.scrollLeft + parseInt(computed.borderLeftWidth, 10);

    document.body.removeChild(mirror);

    return { top, left };
}

function initChordProEditor(root) {
    const searchUrl = root.dataset.searchUrl;
    const textarea = root.querySelector('[data-chordpro-textarea]');
    const preview = root.querySelector('[data-chordpro-preview]');
    const floater = root.querySelector('[data-chord-floater]');
    const searchInput = root.querySelector('[data-chord-search]');
    const resultsList = root.querySelector('[data-chord-results]');
    const statusEl = root.querySelector('[data-chord-status]');

    if (!textarea || !preview || !floater || !searchInput || !resultsList || !statusEl) {
        return;
    }

    let chords = [];
    let activeIndex = 0;
    let debounceTimer = null;
    let lastSelectedChord = null;
    let floaterOpen = false;
    let insertPosition = 0;

    function updatePreview() {
        preview.innerHTML = renderPreview(textarea.value);
    }

    function insertChord(name) {
        textarea.selectionStart = insertPosition;
        textarea.selectionEnd = insertPosition;
        insertAtCursor(textarea, `[${name}]`);
        lastSelectedChord = name;
    }

    function positionFloater() {
        const coords = getCaretViewportPosition(textarea, insertPosition);
        const floaterRect = floater.getBoundingClientRect();
        const margin = 8;
        const viewportW = window.innerWidth;
        const viewportH = window.innerHeight;

        let top = coords.top + 22;
        let left = coords.left;

        if (left + floaterRect.width > viewportW - margin) {
            left = viewportW - floaterRect.width - margin;
        }
        if (left < margin) {
            left = margin;
        }
        if (top + floaterRect.height > viewportH - margin) {
            top = coords.top - floaterRect.height - margin;
        }
        if (top < margin) {
            top = margin;
        }

        floater.style.top = `${top}px`;
        floater.style.left = `${left}px`;
    }

    function openFloater() {
        insertPosition = textarea.selectionStart;
        floaterOpen = true;

        floater.classList.remove('hidden', 'opacity-0', 'pointer-events-none');
        floater.classList.add('opacity-100');

        searchInput.value = '';
        activeIndex = 0;
        fetchChords('');

        requestAnimationFrame(() => {
            positionFloater();
            searchInput.focus();
        });
    }

    function closeFloater() {
        if (!floaterOpen) {
            return;
        }

        floaterOpen = false;
        floater.classList.add('opacity-0', 'pointer-events-none');
        floater.classList.remove('opacity-100');

        window.setTimeout(() => {
            if (!floaterOpen) {
                floater.classList.add('hidden');
            }
        }, 150);

        textarea.focus();
    }

    function renderResults() {
        if (!chords.length) {
            resultsList.innerHTML = '<p class="px-3 py-3 text-sm text-slate-500">Sin acordes.</p>';
            return;
        }

        resultsList.innerHTML = chords
            .map((chord, index) => {
                const active = index === activeIndex
                    ? 'bg-violet-600/20 border-violet-500/40 text-white'
                    : 'border-transparent text-slate-300 hover:bg-white/5';

                return `<button type="button" data-chord-index="${index}" class="chord-result-item flex w-full items-center justify-between rounded-lg border px-3 py-2 text-left text-sm transition ${active}">
                    <span class="font-mono font-semibold">${escapeHtml(chord.name)}</span>
                    <span class="text-xs text-slate-500">${escapeHtml(chord.root_note)}</span>
                </button>`;
            })
            .join('');

        resultsList.querySelectorAll('[data-chord-index]').forEach((btn) => {
            btn.addEventListener('mousedown', (event) => {
                event.preventDefault();
            });
            btn.addEventListener('click', () => {
                const chord = chords[Number(btn.dataset.chordIndex)];
                if (chord) {
                    insertChord(chord.name);
                    closeFloater();
                }
            });
        });
    }

    async function fetchChords(query) {
        statusEl.textContent = 'Buscando…';
        try {
            const url = new URL(searchUrl, window.location.origin);
            if (query) {
                url.searchParams.set('q', query);
            }
            const response = await fetch(url.toString(), {
                headers: { Accept: 'application/json' },
            });
            if (!response.ok) {
                throw new Error('Error de búsqueda');
            }
            chords = await response.json();
            activeIndex = 0;
            renderResults();
            statusEl.textContent = `${chords.length} acorde(s)`;
        } catch {
            chords = [];
            renderResults();
            statusEl.textContent = 'Error al cargar acordes';
        }
    }

    searchInput.addEventListener('input', () => {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => fetchChords(searchInput.value.trim()), 200);
    });

    searchInput.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            event.preventDefault();
            closeFloater();
            return;
        }

        if (!chords.length) {
            return;
        }

        if (event.key === 'ArrowDown') {
            event.preventDefault();
            activeIndex = Math.min(activeIndex + 1, chords.length - 1);
            renderResults();
        } else if (event.key === 'ArrowUp') {
            event.preventDefault();
            activeIndex = Math.max(activeIndex - 1, 0);
            renderResults();
        } else if (event.key === 'Enter') {
            event.preventDefault();
            insertChord(chords[activeIndex].name);
            closeFloater();
        }
    });

    textarea.addEventListener('input', updatePreview);

    textarea.addEventListener('keydown', (event) => {
        if (floaterOpen) {
            return;
        }

        if ((event.ctrlKey || event.metaKey) && event.key === 'k') {
            event.preventDefault();
            openFloater();
            return;
        }

        if (event.key === '/' && !event.ctrlKey && !event.metaKey && !event.altKey) {
            event.preventDefault();
            openFloater();
            return;
        }

        if ((event.ctrlKey || event.metaKey) && event.key === ';' && lastSelectedChord) {
            event.preventDefault();
            insertPosition = textarea.selectionStart;
            insertChord(lastSelectedChord);
            updatePreview();
        }
    });

    document.addEventListener('mousedown', (event) => {
        if (!floaterOpen) {
            return;
        }
        if (!floater.contains(event.target) && event.target !== textarea) {
            closeFloater();
        }
    });

    window.addEventListener('resize', () => {
        if (floaterOpen) {
            positionFloater();
        }
    });

    textarea.addEventListener('scroll', () => {
        if (floaterOpen) {
            positionFloater();
        }
    });

    updatePreview();
}

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-chordpro-editor]').forEach(initChordProEditor);
});
