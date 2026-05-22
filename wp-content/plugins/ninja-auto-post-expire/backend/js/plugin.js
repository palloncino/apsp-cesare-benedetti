jQuery(document).ready(function(){
    var dateToday = new Date();
    jQuery('#njtape_expiration_date').datetimepicker({
        datepicker: 'Y-m-d',
        formatTime:"H:i",
        format:'Y-m-d H:i',
        step: 5,
        minDate: dateToday
    });
});