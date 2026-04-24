/**
 * EasyPhoto - Adds a simple image description editor below the post textarea for easier accessibility.
 * Author: Jools <https://friendica.de/profile/jools>
 * License: AGPL-3.0-or-later
 * 
 * SPDX-FileCopyrightText: 2026 [Jools]
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */
(function () {

    const findImages = (text) => {
        const results = [];
        const counts = {};

        // Pattern 1: Komplex (verlinkt) [url=...][img=...]...[/img][/url]
        // DEFENSIV: Wir erlauben im Inhaltsbereich KEIN neues [url= oder [img
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
        // DEFENSIV: Auch hier darf kein neues [img] im Inhalt vorkommen
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

        return results.sort((a, b) => a.index - b.index);
    };



    const updateTextarea = (textarea, inputIndex, newDesc) => {
        // SICHERHEIT: Wir entfernen eckige Klammern [ ] und spitze Klammern < > 
        // aus der Beschreibung, um das Aufbrechen von BBCode oder HTML zu verhindern.
        const sanitizedDesc = newDesc.replace(/[\[\]<>]/g, '');

        const currentText = textarea.value;
        const images = findImages(currentText);

        // Wir nehmen das Bild an der exakten Listen-Position
        const target = images[inputIndex];
        if (!target) return;

        let newTag;
        if (target.type === 'complex') {
            newTag = `[url=${target.url}][img=${target.img}]${sanitizedDesc}[/img][/url]`;
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

            // Cursor-Position korrigieren, falls sie nach dem bearbeiteten Bereich lag
            const diff = newTag.length - target.length;
            if (start > target.index) {
                textarea.setSelectionRange(start + diff, end + diff);
            } else {
                textarea.setSelectionRange(start, end);
            }
        }
    };

    const isLocal = (url) => {
        const currentOrigin = window.location.origin;
        return url.startsWith(currentOrigin) || url.startsWith('/') || !url.includes('://');
    };

    const renderList = (textarea, listContainer) => {
        const images = findImages(textarea.value);
        if (images.length === 0) {
            listContainer.style.display = 'none';
            listContainer.dataset.fingerprint = '';
            return;
        }

        listContainer.style.display = 'block';

        // FINGERABDRUCK: Wir prüfen nicht nur die Anzahl, sondern auch den Inhalt (Reihenfolge + URLs)
        const newFingerprint = images.map(img => img.img).join('|');
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

        // Wir speichern den neuen Fingerabdruck
        listContainer.dataset.fingerprint = newFingerprint;

        // Full Rebuild
        listContainer.innerHTML = '';
        images.forEach((img, i) => {
            const row = document.createElement('div');
            row.className = 'ep-row';

            const thumbContainer = document.createElement('div');
            thumbContainer.className = 'ep-thumb-container';
            const thumb = document.createElement('img');
            thumb.className = 'ep-thumb';

            // DATENSCHUTZ: Nur lokale Bilder direkt laden
            if (isLocal(img.img)) {
                thumb.src = img.img;
            } else {
                // Platzhalter für externe Bilder (kein automatisches Tracking)
                thumb.src = 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIyNCIgaGVpZ2h0PSIyNCIgdmlld0JveD0iMCAwIDI0IDI0IiBmaWxsPSJub25lIiBzdHJva2U9IiM4ODgiIHN0cm9rZS13aWR0aD0iMSIgc3Ryb2tlLWxpbmVjYXA9InJvdW5kIiBzdHJva2UtbGluZWpvaW49InJvdW5kIj48cmVjdCB4PSIzIiB5PSIzIiB3aWR0aD0iMTgiIGhlaWdodD0iMTgiIHJ4PSIyIiByeT0iMiIvPjxjaXJjbGUgY3g9IjguNSIgY3k9IjguNSIgcj0iMS41Ii8+PHBhdGggZD0iTTIxIDE1bC01LTUtNCA0LTQtNC00IDQiLz48L3N2Zz4=';
                thumb.title = 'Externes Bild (Datenschutz)';
                thumb.alt = 'Externes Bild (Datenschutz)';
            }

            thumbContainer.appendChild(thumb);

            const inputContainer = document.createElement('div');
            inputContainer.className = 'ep-input-container';
            const label = document.createElement('label');
            label.textContent = `Bild ${i + 1}`;
            const input = document.createElement('input');
            input.className = 'ep-input';
            input.type = 'text';
            input.value = img.desc;
            input.placeholder = 'Bildbeschreibung hier eingeben...';

            // Listener wird jetzt über Event Delegation am Container gehandelt

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

                const cleanup = () => {
                    if (textarea._epInterval) clearInterval(textarea._epInterval);
                    if (listContainer.parentNode) listContainer.remove();
                    // WICHTIG: Klasse entfernen, damit eine Re-Initialisierung möglich ist
                    textarea.classList.remove('ep-processed');
                    delete textarea._epInterval;
                    delete textarea._epCleanup;
                };


                const safeRender = () => {
                    if (!textarea.isConnected) {
                        cleanup();
                        return;
                    }

                    if (textarea.value !== lastValue) {
                        lastValue = textarea.value;
                        renderList(textarea, listContainer);
                    }
                };

                textarea.addEventListener('input', safeRender);

                // EVENT DELEGATION: Ein einziger Listener am Container für alle Inputs
                // Das ist immun gegen Rebuilds der Liste!
                listContainer.addEventListener('input', (e) => {
                    if (e.target.classList.contains('ep-input')) {
                        const allInputs = Array.from(listContainer.querySelectorAll('.ep-input'));
                        const myIndex = allInputs.indexOf(e.target);
                        updateTextarea(textarea, myIndex, e.target.value);
                    }
                });

                // SCHUTZSCHILD: Verhindert, dass Friendica-Upload-Events unsere Inputs "kapern"
                const stopEvents = (e) => e.stopPropagation();
                listContainer.addEventListener('dragover', stopEvents);
                listContainer.addEventListener('drop', stopEvents);
                listContainer.addEventListener('paste', stopEvents);

                renderList(textarea, listContainer);

                // Wir speichern die Intervall-ID direkt am Element für einen gezielten Zugriff
                textarea._epInterval = setInterval(safeRender, 3500);
                textarea._epCleanup = cleanup; // Referenz für den Observer
            });
        });
    };

    // MutationObserver für neue UND entfernte Textareas
    const observer = new MutationObserver((mutations) => {
        mutations.forEach((mutation) => {
            // Neue Elemente verarbeiten
            if (mutation.addedNodes.length > 0) {
                init(Array.from(mutation.addedNodes));
            }

            // Entfernte Elemente proaktiv aufräumen
            if (mutation.removedNodes.length > 0) {
                mutation.removedNodes.forEach(node => {
                    if (node.nodeType !== 1) return;
                    const textareas = node.tagName === 'TEXTAREA' ? [node] : node.querySelectorAll('.ep-processed');
                    textareas.forEach(ta => {
                        if (ta._epCleanup) ta._epCleanup();
                    });
                });
            }
        });
    });

    observer.observe(document.body, { childList: true, subtree: true });

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => init());
    } else {
        init();
    }
})();


