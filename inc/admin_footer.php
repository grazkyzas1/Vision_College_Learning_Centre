</div> <!-- End Main Container -->

<footer class="py-3 text-center text-muted border-top bg-white mt-auto small">
    <div class="container">
        © 2026 Vision College Learning Centre — Admin Control Panel
    </div>
</footer>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="js/floating-totop-button.js"></script>
<script src="/js/script.js"></script>
<script src="../js/imoViewer.js"></script>
<script src='../js/imoViewer-min.js'></script>
<script>
    $(function() {
        $('#file-input').imoViewer({
            'preview': '#image-previewer',
            'maxWidth': 9999,
            'maxHeight': 9999
        })
    });
</script>
<script src="/js/InterActiveMultiSelect.js"></script>
<script src="/js/InterActiveMultiSelect.min.js"></script>
<script>
    $(document).ready(function() {
        $('#skills').interActiveMultiSelect({
            mode: 'checkbox',
            placeholder: 'Select',
            search: false,
            searchPlaceholder: 'Search...',
            noResultsText: 'No results found',
            selectAllText: 'Select All',
            clearText: 'Clear'
        });
    });
</script>
<script src="/js/jquery.otherdropdown.js"></script>
<script src="/js/jquery.otherdropdown.min.js"></script>
<script>
    $(function() {
        $('#example').otherDropdown({
            placeholder: "New Option Here",
            classes: "myClass-1 myClass-2"
        });
    });
</script>

</body>

</html>