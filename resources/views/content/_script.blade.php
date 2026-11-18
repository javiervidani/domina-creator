<script>
// Autosave: every form with data-autosave posts as JSON shortly after typing stops. No keystroke is lost on failure,
// the status text just says "Not saved" and the Save button still works.
(function () {
    var saved = document.getElementById('saved');
    function count(form) {
        var ta = form.querySelector('textarea'), out = form.querySelector('[data-count]');
        if (!ta || !out) return;
        var n = ta.value.length, limit = form.dataset.limit;
        out.textContent = n + ' characters' + (limit && n > limit ? ' (over the limit)' : '');
    }
    document.querySelectorAll('form[data-autosave]').forEach(function (form) {
        var timer, ta = form.querySelector('textarea'), same = form.querySelector('.same');
        count(form);
        function send() {
            saved.textContent = 'Saving…';
            fetch(form.action, { method: 'POST', body: new FormData(form), headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
                .then(function (r) { saved.textContent = r.ok ? 'Saved' : 'Not saved'; })
                .catch(function () { saved.textContent = 'Not saved'; });
        }
        function queue() { count(form); clearTimeout(timer); timer = setTimeout(send, 700); }
        if (ta) ta.addEventListener('input', queue);
        if (document.querySelector('input[name=title]') && form.id === 'masterForm') document.querySelector('input[name=title]').addEventListener('input', queue);
        if (same) same.addEventListener('change', function () {
            ta.readOnly = same.checked;
            if (same.checked) ta.value = ta.dataset.master;
            count(form); clearTimeout(timer); send();
        });
    });
})();
</script>
