/**
 * EasyPhoto - Adds a simple image description editor below the post textarea for easier accessibility.
 * Author: Jools <https://friendica.de/profile/jools>
 * License: AGPL-3.0-or-later
 * 
 * SPDX-FileCopyrightText: 2026 [Jools]
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */
(function () {

    // Stabiler Counter für eindeutige Input-IDs – kein Date.now(), kein Kollisionsrisiko
    let _epIdCounter = 0;

    const findImages = (text) => {
        const results = [];
        const counts = {};

        // Pattern 1: Komplex (verlinkt) [url=...][img=...]...[/img][/url]
        const pattern1 = /\[url=([^\]]*?)\]\[img=([^\]]*?)\]((?:(?!\[url=|\[img).)*?)\[\/img\]\[\/url\]/gi;
        let match;
        while ((match = pattern1.exec(text)) !== null) {
            const imgUrl = match[2];
            counts[imgUrl] = (counts[imgUrl] || 0) + 1;

            results.push({
                full: match[0],
                url: match[1],
                img: imgUrl,
                desc: match[3],
                index: match.index,
                length: match[0].length,
                rank: counts[imgUrl],
                type: 'complex'
            });
        }

        // Pattern 2: Einfach [img]URL|Desc[/img]
        const pattern2 = /\[img\]((?:(?!\[img).)*?)\[\/img\]/gi;
        while ((match = pattern2.exec(text)) !== null) {
            const content = match[1];
            const pipeIndex = content.indexOf('|');
            const imgUrl = pipeIndex !== -1 ? content.substring(0, pipeIndex) : content;

            counts[imgUrl] = (counts[imgUrl] || 0) + 1;

            results.push({
                full: match[0],
                img: imgUrl,
                desc: pipeIndex !== -1 ? content.substring(pipeIndex + 1) : "",
                index: match.index,
                length: match[0].length,
                rank: counts[imgUrl],
                type: 'simple'
            });
        }

        // Pattern 3: Alt-Simple [img=URL]Description[/img]
        const pattern3 = /\[img=([^\]]*?)\]((?:(?!\[img).)*?)\[\/img\]/gi;
        while ((match = pattern3.exec(text)) !== null) {
            const imgUrl = match[1];
            counts[imgUrl] = (counts[imgUrl] || 0) + 1;

            results.push({
                full: match[0],
                img: imgUrl,
                desc: match[2],
                index: match.index,
                length: match[0].length,
                rank: counts[imgUrl],
                type: 'alt_simple'
            });
        }

        // Sortieren und Überlappungen filtern (verhindert Doppelmatches zwischen Pattern 1 und 3)
        const sorted = results.sort((a, b) => a.index - b.index);
        const filtered = [];
        let lastEnd = -1;

        for (const item of sorted) {
            if (item.index >= lastEnd) {
                filtered.push(item);
                lastEnd = item.index + item.length;
            }
        }

        return filtered;
    };

    const getImages = (textarea) => {
        if (textarea._epLastValue === textarea.value && textarea._epLastImages) {
            return textarea._epLastImages;
        }
        const images = findImages(textarea.value);
        textarea._epLastValue = textarea.value;
        textarea._epLastImages = images;
        return images;
    };

    const updateTextarea = (textarea, imgIdentity, newDesc, listContainer) => {
        // SICHERHEIT: Eckige Klammern entfernen um BBCode-Injection zu verhindern.
        // Anführungszeichen bleiben – BBCode ist kein HTML, Entities würden wortwörtlich erscheinen.
        const sanitizedDesc = newDesc.replace(/[\[\]]/g, '');
        const currentText = textarea.value;
        const images = getImages(textarea);

        const target = images.find(img => img.img === imgIdentity.img && img.rank === imgIdentity.rank);
        if (!target) return;

        let newTag;
        if (target.type === 'complex') {
            newTag = `[url=${target.url}][img=${target.img}]${sanitizedDesc}[/img][/url]`;
        } else if (target.type === 'alt_simple') {
            newTag = `[img=${target.img}]${sanitizedDesc}[/img]`;
        } else {
            newTag = `[img]${target.img}|${sanitizedDesc}[/img]`;
        }

        const newContent = currentText.substring(0, target.index) +
            newTag +
            currentText.substring(target.index + target.length);

        if (currentText !== newContent) {
            const start = textarea.selectionStart;
            const end = textarea.selectionEnd;

            textarea.value = newContent;
            // Cache invalidieren – MUSS vor renderList() passieren
            textarea._epLastValue = null;

            if (listContainer) {
                renderList(textarea, listContainer);
            }

            // Standard-Event für Friendica-Core (Zeichenzähler, Vorschau etc.)
            textarea.dispatchEvent(new CustomEvent('input', {
                bubbles: true,
                detail: { source: 'easyphoto' }
            }));

            const diff = newTag.length - target.length;
            if (start > target.index) {
                textarea.setSelectionRange(start + diff, end + diff);
            } else {
                textarea.setSelectionRange(start, end);
            }
        }
    };

    const isLocal = (url) => {
        try {
            const currentOrigin = window.location.origin;
            const testUrl = new URL(url, currentOrigin);
            return testUrl.origin === currentOrigin && testUrl.protocol.startsWith('http');
        } catch (e) {
            return false;
        }
    };

    const renderList = (textarea, listContainer) => {
        const images = getImages(textarea);
        if (images.length === 0) {
            listContainer.style.display = 'none';
            listContainer.dataset.fingerprint = '';
            return;
        }

        listContainer.style.display = 'block';

        const newFingerprint = images.map(img => `${img.img}|${img.type}|${img.url || ''}`).join('##');
        const oldFingerprint = listContainer.dataset.fingerprint || '';

        const currentInputs = listContainer.querySelectorAll('.ep-input');
        const shouldRebuild = currentInputs.length !== images.length || newFingerprint !== oldFingerprint;

        if (!shouldRebuild) {
            images.forEach((img, i) => {
                if (document.activeElement !== currentInputs[i]) {
                    currentInputs[i].value = img.desc;
                }
            });
            return;
        }

        listContainer.dataset.fingerprint = newFingerprint;
        listContainer.innerHTML = '';

        images.forEach((img, i) => {
            const row = document.createElement('div');
            row.className = 'ep-row';

            const thumbContainer = document.createElement('div');
            thumbContainer.className = 'ep-thumb-container';
            const thumb = document.createElement('img');
            thumb.className = 'ep-thumb';

            if (isLocal(img.img)) {
                if (/^(https?:|\/)/i.test(img.img)) {
                    thumb.src = img.img;
                }
            } else {
                thumb.src = 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIyNCIgaGVpZ2h0PSIyNCIgdmlld0JveD0iMCAwIDI0IDI0IiBmaWxsPSJub25lIiBzdHJva2U9IiM4ODgiIHN0cm9rZS13aWR0aD0iMSIgc3Ryb2tlLWxpbmVjYXA9InJvdW5kIiBzdHJva2UtbGluZWpvaW49InJvdW5kIj48cmVjdCB4PSIzIiB5PSIzIiB3aWR0aD0iMTgiIGhlaWdodD0iMTgiIHJ4PSIyIiByeT0iMiIvPjxjaXJjbGUgY3g9IjguNSIgY3k9IjguNSIgcj0iMS41Ii8+PHBhdGggZD0iTTIxIDE1bC01LTUtNCA0LTQtNC00IDQiLz48L3N2Zz4=';
                const privacyText = (typeof easyphoto_l10n !== 'undefined') ? easyphoto_l10n.privacy : 'External image (privacy protection)';
                thumb.title = privacyText;
                thumb.alt = privacyText;
            }

            thumbContainer.appendChild(thumb);

            const inputContainer = document.createElement('div');
            inputContainer.className = 'ep-input-container';

            // Stabiler Counter statt Date.now() – keine Kollisionen möglich
            const inputId = `ep-input-${++_epIdCounter}-${i}`;
            const label = document.createElement('label');
            label.htmlFor = inputId;
            const labelText = (typeof easyphoto_l10n !== 'undefined') ? easyphoto_l10n.image : 'Image';
            label.textContent = `${labelText} ${i + 1}`;

            const input = document.createElement('input');
            input.id = inputId;
            input.className = 'ep-input';
            input.type = 'text';
            input.value = img.desc;
            const placeholderText = (typeof easyphoto_l10n !== 'undefined') ? easyphoto_l10n.placeholder : 'Enter image description here...';
            input.placeholder = placeholderText;

            input.dataset.img = img.img;
            input.dataset.rank = img.rank;

            inputContainer.appendChild(label);
            inputContainer.appendChild(input);
            row.appendChild(thumbContainer);
            row.appendChild(inputContainer);
            listContainer.appendChild(row);
        });
    };

    const init = (addedNodes) => {
        const targetNodes = addedNodes || [document];

        targetNodes.forEach(root => {
            if (root.nodeType !== 1) return;
            const textareas = root.tagName === 'TEXTAREA' ? [root] : root.querySelectorAll('textarea');

            textareas.forEach(textarea => {
                if (textarea.classList.contains('ep-processed')) return;
                textarea.classList.add('ep-processed');

                const listContainer = document.createElement('div');
                listContainer.className = 'ep-list';
                listContainer.style.display = 'none';

                textarea.parentNode.insertBefore(listContainer, textarea.nextSibling);

                let lastValue = textarea.value;
                let debounceTimer = null;

                const cleanup = () => {
                    if (textarea._epInterval) clearInterval(textarea._epInterval);
                    if (debounceTimer) clearTimeout(debounceTimer);
                    if (listContainer.parentNode) listContainer.remove();
                    textarea.removeEventListener('input', debouncedRender);
                    textarea.classList.remove('ep-processed');
                    delete textarea._epInterval;
                    delete textarea._epCleanup;
                };

                const safeRender = (e) => {
                    if (e && e.detail && e.detail.source === 'easyphoto') return;
                    if (!textarea.isConnected) { cleanup(); return; }

                    // SICHTBARKEIT: Optimierung für Performance und position:fixed Dialoge
                    if (document.hidden || textarea.getBoundingClientRect().width === 0) {
                        return;
                    }

                    if (textarea.value !== lastValue) {
                        lastValue = textarea.value;
                        renderList(textarea, listContainer);
                    }
                };

                // Debouncing auf input-Event: verhindert Regex-Läufe bei jedem Tastendruck
                const debouncedRender = (e) => {
                    if (e && e.detail && e.detail.source === 'easyphoto') return;
                    clearTimeout(debounceTimer);
                    debounceTimer = setTimeout(() => safeRender(e), 300);
                };

                textarea.addEventListener('input', debouncedRender);

                listContainer.addEventListener('input', (e) => {
                    if (e.target.classList.contains('ep-input')) {
                        const imgIdentity = {
                            img: e.target.dataset.img,
                            rank: parseInt(e.target.dataset.rank)
                        };
                        updateTextarea(textarea, imgIdentity, e.target.value, listContainer);
                    }
                });

                const stopEvents = (e) => e.stopPropagation();
                listContainer.addEventListener('dragover', stopEvents);
                listContainer.addEventListener('drop', stopEvents);
                listContainer.addEventListener('paste', stopEvents);

                renderList(textarea, listContainer);
                textarea._epInterval = setInterval(safeRender, 1500);
                textarea._epCleanup = cleanup;
            });
        });
    };

    const observer = new MutationObserver((mutations) => {
        mutations.forEach((mutation) => {
            if (mutation.addedNodes.length > 0) {
                const relevantAdded = Array.from(mutation.addedNodes).filter(node => {
                    if (node.nodeType !== 1) return false;
                    return node.tagName === 'TEXTAREA' || node.querySelectorAll('textarea').length > 0;
                });
                if (relevantAdded.length > 0) init(relevantAdded);
            }
            if (mutation.removedNodes.length > 0) {
                mutation.removedNodes.forEach(node => {
                    if (node.nodeType !== 1) return;
                    const textareas = node.tagName === 'TEXTAREA' ? [node] : node.querySelectorAll('.ep-processed');
                    textareas.forEach(ta => { if (ta._epCleanup) ta._epCleanup(); });
                });
            }
        });
    });

    observer.observe(document.body, { childList: true, subtree: true });
    document.addEventListener("postprocess_liveupdate", () => init());

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => init());
    } else {
        init();
    }
})();
