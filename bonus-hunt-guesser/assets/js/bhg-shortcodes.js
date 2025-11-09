document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.bhg-tabs a').forEach(function (tab) {
        tab.addEventListener('click', function (event) {
            event.preventDefault();
            var targetId = this.getAttribute('href').substring(1);
            document.querySelectorAll('.bhg-tabs li').forEach(function (li) {
                li.classList.remove('active');
            });
            document.querySelectorAll('.bhg-tab-pane').forEach(function (pane) {
                pane.classList.remove('active');
            });
            this.parentElement.classList.add('active');
            var target = document.getElementById(targetId);
            if (target) {
                target.classList.add('active');
            }
        });
    });

    var huntSelect = document.querySelector('.bhg-hunt-select');
    if (huntSelect && huntSelect.form) {
        huntSelect.addEventListener('change', function () {
            var pageInputs = huntSelect.form.querySelectorAll('[name="bhg_hunt_page"]');
            if (pageInputs.length) {
                pageInputs.forEach(function (input) {
                    input.parentNode.removeChild(input);
                });
            }
            huntSelect.form.submit();
        });
    }

});
