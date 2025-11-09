(function(){
    var huntSelect = document.getElementById('bhg-results-select');
    var timeSelect = document.getElementById('bhg-results-timeframe');
    if (!huntSelect || !timeSelect || typeof bhgResults === 'undefined') {
        return;
    }
    var navigate = function(){
        var val = huntSelect.value.split('-');
        if (val.length < 2) {
            return;
        }
        var type = val[0];
        var id = val[1];
        var timeframe = timeSelect.value;
        window.location = bhgResults.base_url + '&timeframe=' + timeframe + '&type=' + type + '&id=' + id;
    };
    huntSelect.addEventListener('change', navigate);
    timeSelect.addEventListener('change', navigate);
})();
