$(function() {
    $(".clickable-row").click(function() {

        $(this).addClass('danger');
        var url = $(this).find('td:first a').attr('href');
        //alert(url);

        window.location = url;
    });

});
