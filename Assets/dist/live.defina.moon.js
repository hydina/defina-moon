let debounceTimers = new Map();

document.addEventListener('input', (e) => {
    const el = e.target;
    if (!el.hasAttribute('wire:model.live')) return;

    const root = el.closest('[wire\\:id]');
    if (!root) return;

    const componentId = root.getAttribute('wire:id');
    const prop = el.getAttribute('wire:model.live');
    const value = el.type === 'checkbox' ? el.checked : el.value;
    clearTimeout(debounceTimers.get(componentId));
    const timer = setTimeout(() => {
        const activeAttr = el.getAttribute('wire:model.live');
        const selectionStart = el.selectionStart;
        const selectionEnd = el.selectionEnd;
        sendLivewireUpdate(root, { [prop]: value }, []).then(() => {
            const updatedRoot = document.querySelector(`[wire\\:id="${componentId}"]`);
            const newInput = updatedRoot?.querySelector(`[wire\\:model\\.live="${activeAttr}"]`);
            if (newInput) {
                newInput.focus();
                if (typeof selectionStart === 'number') {
                    newInput.setSelectionRange(selectionStart, selectionEnd);
                }
            }
        });
    }, 250);
    debounceTimers.set(componentId, timer);
});
