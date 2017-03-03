function setVendorIdData() {
    ajax('/contents/action/movie/set', $("#id_form").serialize(), function (data, error) {
        if (!data || error) {
            alert('에러');
            return;
        }
        if (data === 'success') {
            alert('성공');
        } else {
            alert('실패');
        }
    }, 'text', 5000, 'post');
}

function openVendorMovieDetail(this_obj, vendor) {
    if (!$("#vendor_id").val()) {
        return false;
    }

    if (vendor === 'cgv') {
        $(this_obj).attr('href', 'http://www.cgv.co.kr/movies/detail-view/?midx=' + $("#vendor_id").val());
    } else if (vendor === 'naver') {
        $(this_obj).attr('href', 'http://movie.naver.com/movie/bi/mi/basic.nhn?code=' + $("#vendor_id").val());
    }

    return true;
}

function removeNewMap(sync_id_list, vendor) {
    if (!sync_id_list) {
        return false;
    }
    ajax('/contents/action/contentSyncLog/remove', {'sync_id_list': sync_id_list, 'is_ajax': 1, 'vendor': vendor}, function (data, error, msg) {
        if (error) {
            alert('실패');
        } else {
            alert('성공');
        }
    });
    return true;
}