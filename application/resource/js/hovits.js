function resizeMenu(this_obj) {
    if ($(this_obj).hasClass('glyphicon-resize-small')) {
        $(this_obj).removeClass('glyphicon-resize-small');
        $(this_obj).addClass('glyphicon-resize-full');
        $("#right_menu").css('right', '-170px');
        $("#right_menu_shadow").css('right', '-170px');
    } else {
        $(this_obj).removeClass('glyphicon-resize-full');
        $(this_obj).addClass('glyphicon-resize-small');
        $("#right_menu").css('right', '0');
        $("#right_menu_shadow").css('right', '0');
    }
}

function isMobile() {
    var check = false;
    (function (a) {
        if (/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce|xda|xiino|android|ipad|playbook|silk/i.test(a) || /1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i.test(a.substr(0, 4)))check = true
    })(navigator.userAgent || navigator.vendor || window.opera);
    return check;
}

function selectMovie(this_obj) {
    if ($(this_obj).hasClass('content-thumb-box-selected')) {
        //취소
        $(this_obj).removeClass('content-thumb-box-selected');
        decreaseProgressBar(1);
        $(this_obj).parent().children('.content-id-hidden').removeAttr('name');
    } else {
        //선택
        var current_selected_cnt = getCurrentSelectedCnt();
        var total_select_limit = getTotalSelectLimit();
        if (current_selected_cnt >= total_select_limit) {
            alert('최대 ' + total_select_limit + '개의 영화를 선택할 수 있습니다.')
            return;
        }
        $(this_obj).addClass('content-thumb-box-selected');
        increaseProgressBar(1);
        $(this_obj).parent().children('.content-id-hidden').attr('name', 'movie_id[]');
    }
}

function selectMovieInTutorialMatch(this_obj, step) {
    var opponent_movie_box = $(this_obj).parents('.content-thumb-container').siblings('.content-thumb-container').find('.content-thumb-box');
    var opponent_icon = opponent_movie_box.find('.glyphicon');
    var this_icon = $(this_obj).find('.glyphicon');
    if ($(this_obj).hasClass('content-thumb-box-selected')) {
        //취소
        decreaseProgressBar(step);
        $(this_obj).removeClass('content-thumb-box-selected');

        $(this_obj).parent().children('.content-id-hidden').removeAttr('name');
        opponent_movie_box.siblings('.content-id-hidden').removeAttr('name');
        //if (isMobile()) {
            $(this_obj).css('opacity', '0.7');
            opponent_movie_box.css('opacity', '0.7');
            this_icon.removeClass('glyphicon-ok');
            this_icon.removeClass('visible');
            this_icon.addClass('hidden');
            opponent_icon.removeClass('glyphicon-remove');
            opponent_icon.removeClass('visible');
            opponent_icon.addClass('hidden');
        //}
    } else {
        //선택
        $(this_obj).addClass('content-thumb-box-selected');
        if (!opponent_movie_box.hasClass('content-thumb-box-selected')) {
            increaseProgressBar(step);
        } else {
            $(this_obj).parents('.content-thumb-container').siblings('.content-thumb-container').find('.content-thumb-box').removeClass('content-thumb-box-selected');
        }
        opponent_movie_box.siblings('.content-id-hidden').attr('name', 'unselected_movie_id[]');
        $(this_obj).parent().children('.content-id-hidden').attr('name', 'selected_movie_id[]');
        //if (isMobile()) {
            $(this_obj).css('opacity', '1');
            opponent_movie_box.css('opacity', '0.2');
            this_icon.removeClass('glyphicon-remove');
            this_icon.addClass('glyphicon-ok');
            this_icon.removeClass('hidden');
            this_icon.addClass('visible');
            opponent_icon.removeClass('glyphicon-ok');
            opponent_icon.addClass('glyphicon-remove');
            opponent_icon.removeClass('hidden');
            opponent_icon.addClass('visible');
        //}
    }
}

function selectMovieInMatch(this_obj) {
    var opponent_movie_box = $(this_obj).parents('.content-thumb-container').siblings('.content-thumb-container').find('.content-thumb-box');
    var opponent_icon = opponent_movie_box.find('.glyphicon');
    var this_icon = $(this_obj).find('.glyphicon');
    if ($(this_obj).hasClass('content-thumb-box-selected')) {
        //취소
        $(this_obj).removeClass('content-thumb-box-selected');

        $(this_obj).parent().children('.content-id-hidden').removeAttr('name');
        opponent_movie_box.siblings('.content-id-hidden').removeAttr('name');
        //if (isMobile()) {
        $(this_obj).css('opacity', '0.7');
        opponent_movie_box.css('opacity', '0.7');
        this_icon.removeClass('glyphicon-ok');
        this_icon.removeClass('visible');
        this_icon.addClass('hidden');
        opponent_icon.removeClass('glyphicon-remove');
        opponent_icon.removeClass('visible');
        opponent_icon.addClass('hidden');
        //}
    } else {
        //선택
        $(this_obj).addClass('content-thumb-box-selected');
        if (opponent_movie_box.hasClass('content-thumb-box-selected')) {
            $(this_obj).parents('.content-thumb-container').siblings('.content-thumb-container').find('.content-thumb-box').removeClass('content-thumb-box-selected');
        }
        opponent_movie_box.siblings('.content-id-hidden').attr('name', 'unselected_movie_id[]');
        $(this_obj).parent().children('.content-id-hidden').attr('name', 'selected_movie_id[]');
        //if (isMobile()) {
        $(this_obj).css('opacity', '1');
        opponent_movie_box.css('opacity', '0.2');
        this_icon.removeClass('glyphicon-remove');
        this_icon.addClass('glyphicon-ok');
        this_icon.removeClass('hidden');
        this_icon.addClass('visible');
        opponent_icon.removeClass('glyphicon-ok');
        opponent_icon.addClass('glyphicon-remove');
        opponent_icon.removeClass('hidden');
        opponent_icon.addClass('visible');
        //}
    }
}

function mouseOnMovieInTutorialMatch(this_obj) {
    this_obj = $(this_obj);
    var opponent_movie_box = this_obj.parents('.content-thumb-container').siblings('.content-thumb-container').find('.content-thumb-box');
    var opponent_icon = opponent_movie_box.find('.glyphicon');
    var this_icon = this_obj.find('.glyphicon');

    this_obj.css('opacity', '1');
    //1. 대결 영화가 어두어진다
    opponent_movie_box.css('opacity', '1');
    //2. 대결 영화의 아이콘이 x가 된다.
    //opponent_icon.removeClass('hidden');
    //opponent_icon.addClass('visible');
    opponent_icon.removeClass('glyphicon-ok');
    opponent_icon.addClass('glyphicon-remove');
    //3. 해당 영화의 아이콘이 v가 된다.
    //this_icon.removeClass('hidden');
    //this_icon.addClass('visible');
    this_icon.removeClass('glyphicon-remove');
    this_icon.addClass('glyphicon-ok');

    //해당 영화가 선택되지 않은경우
    //if (!this_obj.hasClass('content-thumb-box-selected')) {
    //    if (opponent_movie_box.hasClass('content-thumb-box-selected')) {
    //        //해당 영화의 대결 영화가 선택된 경우
    //        //0. 해당 영화가 밝아진다
    //        this_obj.css('opacity', '1');
    //        //1. 대결 영화가 어두어진다
    //        opponent_movie_box.css('opacity', '0.2');
    //        //2. 대결 영화의 아이콘이 x가 된다.
    //        opponent_icon.removeClass('glyphicon-ok');
    //        opponent_icon.addClass('glyphicon-remove');
    //        //3. 해당 영화의 아이콘이 v가 된다.
    //        this_icon.removeClass('glyphicon-remove');
    //        this_icon.addClass('glyphicon-ok');
    //    } else {
    //        //해당 영화의 대결 영화도 선택되지 경우
    //        this_obj.css('opacity', '1');
    //        opponent_movie_box.css('opacity', '0.2');
    //        this_icon.addClass('glyphicon-ok');
    //        this_icon.removeClass('hidden');
    //        this_icon.addClass('visible');
    //        opponent_icon.addClass('glyphicon-remove');
    //        opponent_icon.removeClass('hidden');
    //        opponent_icon.addClass('visible');
    //    }
    //} else {
    //    //해당 영화가 선택된 경우
    //}
}

function mouseOutMovieInTutorialMatch(this_obj) {
    this_obj = $(this_obj);
    var opponent_movie_box = this_obj.parents('.content-thumb-container').siblings('.content-thumb-container').find('.content-thumb-box');
    var opponent_icon = opponent_movie_box.find('.glyphicon');
    var this_icon = this_obj.find('.glyphicon');
    //해당 영화가 선택되지 않은경우
    if (!this_obj.hasClass('content-thumb-box-selected')) {
        if (opponent_movie_box.hasClass('content-thumb-box-selected')) {
            //해당 영화의 대결 영화가 선택된 경우
            //0. 해당 영화가 어두어진다
            //this_obj.css('opacity', '0.2');
            ////1. 대결 영화가 밝아진다
            //opponent_movie_box.css('opacity', '1');
            //2. 대결 영화의 아이콘이 x가 된다.
            opponent_icon.removeClass('glyphicon-remove');
            opponent_icon.addClass('glyphicon-ok');
            //3. 해당 영화의 아이콘이 v가 된다.
            this_icon.removeClass('glyphicon-ok');
            this_icon.addClass('glyphicon-remove');
        } else {
            //해당 영화의 대결 영화도 선택되지 경우
            this_obj.css('opacity', '0.2');
            opponent_movie_box.css('opacity', '0.2');
            this_icon.removeClass('glyphicon-ok');
            this_icon.removeClass('visible');
            this_icon.addClass('hidden');
            opponent_icon.removeClass('glyphicon-remove');
            opponent_icon.removeClass('visible');
            opponent_icon.addClass('hidden');
        }
    } else {
        //해당 영화가 선택된 경우
    }
}

function mouseOnMovieInThumb(this_obj) {
    $(this_obj).find('.content-thumb-menu').show();
}

function mouseOutMovieInThumb(this_obj) {
    $(this_obj).find('.content-thumb-menu').hide();
}

function increaseProgressBar(step) {
    var current_selected_cnt = getCurrentSelectedCnt();
    var total_select_limit = getTotalSelectLimit();
    if (current_selected_cnt >= total_select_limit) {
        //갯수만 추가
        increaseCurrentSelected();
        return;
    }
    increaseCurrentSelected();
    //프로그레바까지 변경
    changeProgressBar(step);
}

function decreaseProgressBar(step) {
    var current_selected_cnt = getCurrentSelectedCnt();
    var total_select_limit = getTotalSelectLimit();
    if (current_selected_cnt < 1 || current_selected_cnt > total_select_limit) {
        //갯수만 추가
        decreaseCurrentSelected();
        return;
    }
    decreaseCurrentSelected();
    //프로그레바까지 변경
    changeProgressBar(step);
}

function getTotalSelectLimit() {
    return parseInt($("#total_select_limit").text());
}

function getCurrentSelectedCnt() {
    return parseInt($("#progress_bar").attr('aria-valuenow'));
}

function increaseCurrentSelected() {
    $("#progress_bar").attr('aria-valuenow', getCurrentSelectedCnt() + 1);
    $("#current_selected").html(getCurrentSelectedCnt());
}

function decreaseCurrentSelected() {
    $("#progress_bar").attr('aria-valuenow', getCurrentSelectedCnt() - 1);
    $("#current_selected").html(getCurrentSelectedCnt());
}

function changeProgressBar(step) {
    var current_selected_cnt = getCurrentSelectedCnt();
    var total_select_limit = getTotalSelectLimit();
    var percent = current_selected_cnt / total_select_limit * 100;
    $("#progress_bar").css('width', percent + '%');
    var progress_bar = $("#progress_bar");
    if (current_selected_cnt === total_select_limit) {
        progress_bar.removeClass('progress-bar-gray');
        progress_bar.addClass('progress-bar-danger');
        $("#next_button").removeClass('hidden');
        $("#next_button").addClass('visible');
        if (step == 1) {
            $("#tutorial_message").html('다음 버튼을 클릭하여 STEP 2로 이동하세요.');
        } else if (step == 2) {
            $("#tutorial_message").html('다음 버튼을 클릭하여 STEP 3로 이동하세요.');
        } else {
            $("#tutorial_message").html('완료 버튼을 클릭하여 당신의 취향을 확인하세요.');
        }
        $("#step-icon").html('STEP ' + step + ' 완료');
        $("#step-icon").removeClass('label-gray')
        $("#step-icon").addClass('label-danger')
    } else if (current_selected_cnt < total_select_limit) {
        if (!progress_bar.hasClass('progress-bar-gray')) {
            progress_bar.removeClass('progress-bar-danger');
            progress_bar.addClass('progress-bar-gray');
            $("#next_button").removeClass('visible');
            $("#next_button").addClass('hidden');

            if (step == 1) {
                $("#tutorial_message").html('취향분석을 위해 봤던 영화를 선택해주세요.');
            } else if (step == 2) {
                $("#tutorial_message").html('각 영화 대결에서 더 좋아하는 영화를 선택해주세요.');
            } else {
                $("#tutorial_message").html('STEP2 에서 선택한 영화들과 선택하지 않은 영화들의 대결에서 더 좋아하는 영화를 선택해주세요.');
            }
            $("#step-icon").html('STEP ' + step);
            $("#step-icon").removeClass('label-danger')
            $("#step-icon").addClass('label-gray')
        }
    }
}

//function showTitleBox(this_obj) {
//    $(this_obj).parent().children('.movie-title-box').removeClass('hidden');
//    $(this_obj).parent().children('.movie-title-box').addClass('visible');
//}
//
//function hideTitleBox(this_obj) {
//    $(this_obj).parent().children('.movie-title-box').removeClass('visible');
//    $(this_obj).parent().children('.movie-title-box').addClass('hidden');
//}