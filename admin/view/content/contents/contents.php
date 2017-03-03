<div>
    <h2>영화</h2><br/>


    <form action="/contents/contents/action" id="action_movie">
        <input type="hidden" name="action_type"/> <input type="hidden" name="content_type" value="movie"/>

        <div class="form-group">
            <label for="page">page</label> <input type="text" class="form-control" id="page" name="page">
        </div>

        <div class="form-group">
            <label for="movie_id">movie_id</label>
            <input type="text" class="form-control" id="movie_id" name="content_id">
        </div>
        <button type="submit" class="btn btn-primary" onclick="$('#action_movie [name=action_type]').val('crawler');">수집</button>
        <button type="submit" class="btn btn-primary" onclick="$('#action_movie [name=action_type]').val('sync');">동기화</button>
    </form>
<br/>

</div><br/><br/>
<div>
    <h2>영화인</h2><br/>


    <form action="/contents/contents/action" id="action_people">
        <input type="hidden" name="action_type"/> <input type="hidden" name="content_type" value="people"/>

        <div class="form-group">
            <label for="page">page</label> <input type="text" class="form-control" id="page" name="page">
        </div>

        <div class="form-group">
            <label for="people_id">people_id</label><input type="text" class="form-control" id="people_id" name="content_id">
        </div>
        <button type="submit" class="btn btn-primary" onclick="$('#action_people [name=action_type]').val('crawler');">수집</button>
        <button type="submit" class="btn btn-primary" onclick="$('#action_people [name=action_type]').val('sync');">동기화</button>
    </form>
    <br/>

</div>