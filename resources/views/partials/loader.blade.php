<!-- NProgress CSS -->
<link rel="stylesheet" href="https://unpkg.com/nprogress@0.2.0/nprogress.css">
<style>
    #nprogress .bar {
        background: #4361ee !important;
        height: 3px !important;
    }

    #nprogress .peg {
        box-shadow: 0 0 10px #4361ee, 0 0 5px #4361ee !important;
    }

    #nprogress .spinner-icon {
        border-top-color: #4361ee !important;
        border-left-color: #4361ee !important;
    }
</style>

<!-- NProgress JS -->
<script src="https://unpkg.com/nprogress@0.2.0/nprogress.js"></script>
<script>
    NProgress.configure({
        showSpinner: true,
        speed: 400,
        minimum: 0.1
    });

    document.addEventListener('DOMContentLoaded', function () {
        // Show progress on link clicks
        document.querySelectorAll('a:not([target="_blank"]):not([href^="#"]):not([href^="javascript"])').forEach(link => {
            link.addEventListener('click', function (e) {
                if (!e.ctrlKey && !e.metaKey && this.href && !this.href.includes('#')) {
                    NProgress.start();
                }
            });
        });

        // Show progress on form submit
        document.querySelectorAll('form:not([target="_blank"])').forEach(form => {
            form.addEventListener('submit', function () {
                NProgress.start();
            });
        });

        // Hide progress when page loads
        window.addEventListener('pageshow', function () {
            NProgress.done();
        });
    });
</script>