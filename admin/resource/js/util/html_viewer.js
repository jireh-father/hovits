/**
 * Created by 서일근 on 2015-07-08.
 */
function testHtml() {
    ajax('/util/htmlViewer/htmlBeautifier', {html: $("#html_contents").val()}, function (result_data, error, meesage) {
        if (error === true) {
            if (meesage) {
                alert(meesage)
            } else {
                alert('fail');
            }
            return;
        } else {
            if (!result_data) {
                return;
            }

            $("#html_contents").val(result_data);
            $("#html_view").empty();
            $("#html_view").html($("#html_contents").val());

            $(this_obj).parent().find('.selected_id').empty();
            $(this_obj).parent().find('.selected_id').append(buildOptions(result_data));
        }
    }, 'json', 5000, 'post');
}
