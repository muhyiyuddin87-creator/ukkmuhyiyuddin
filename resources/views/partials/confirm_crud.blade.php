<script>
    // Batasi seluruh input tanggal agar tidak bisa memilih tanggal sebelum hari ini
    function enforceMinDateToday() {
        const now = new Date();
        const year = now.getFullYear();
        const month = String(now.getMonth() + 1).padStart(2, '0');
        const day = String(now.getDate()).padStart(2, '0');
        const todayStr = `${year}-${month}-${day}`;

        document.querySelectorAll('input[type="date"]').forEach(function (input) {
            if (!input.hasAttribute('min') || input.getAttribute('min') < todayStr) {
                input.setAttribute('min', todayStr);
            }
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', enforceMinDateToday);
    } else {
        enforceMinDateToday();
    }

    document.addEventListener('submit', function (event) {
        const form = event.target;
        if (!(form instanceof HTMLFormElement) || form.hasAttribute('onsubmit')) {
            return;
        }

        const action = new URL(form.action, window.location.href);
        if (/\/(login|logout|forgot-password|reset-password)\/?$/.test(action.pathname)) {
            return;
        }

        const method = (form.querySelector('input[name="_method"]')?.value || form.method || 'GET').toUpperCase();
        if (!['POST', 'PUT', 'PATCH', 'DELETE'].includes(method)) {
            return;
        }

        const actionText = method === 'DELETE'
            ? 'menghapus data ini'
            : (method === 'POST' ? 'menyimpan data ini' : 'menyimpan perubahan ini');

        if (!window.confirm(`Apakah Anda yakin ingin ${actionText}?`)) {
            event.preventDefault();
            event.stopImmediatePropagation();
        }
    }, true);
</script>
