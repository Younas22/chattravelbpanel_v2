{{-- Apply the saved colour theme before first paint (no flash of the default colour). --}}
<script>
    (function () {
        try {
            var t = localStorage.getItem('tbp-theme');
            if (t && t !== 'blue') document.documentElement.setAttribute('data-theme', t);
        } catch (e) {}
    })();
</script>
