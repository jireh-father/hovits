<div>
    <h2>영화</h2><br/>
    <h3>영화진흥위원회</h3>

    <form action="/contents/kofic/api/content" target="_blank" id="search_movie_server">
        <input type="hidden" name="search_type" value="server" />
        <input type="hidden" name="content_type" value="movie" />
        <div class="form-inline">
            <div class="form-group">
                <label for="movie_id">movie_id</label><input type="text" class="form-control" id="movie_id" name="content_id">
            </div>
            <button type="submit" class="btn btn-primary">검색</button>
        </div>
    </form>
    <br/>
    <h3>로컬</h3>
    <form action="/contents/kofic/api/content" target="_blank" id="search_movie_local">
        <input type="hidden" name="search_type" value="local"/> <input type="hidden" name="content_type" value="movie"/>
        <div class="form-inline">
            <div class="form-group">
                <label for="movie_id">movie_id</label>
                <input type="text" class="form-control" id="movie_id" name="content_id">
            </div>
            <button type="submit" class="btn btn-primary">검색</button>
        </div>
    </form>
</div>
<br/><br/>
<div>
    <h2>영화인</h2><br/>
    <h3>영화진흥위원회</h3>

    <form action="/contents/kofic/api/content" target="_blank" id="search_people_server">
        <input type="hidden" name="search_type" value="server"/> <input type="hidden" name="content_type" value="people"/>
        <div class="form-inline">
            <div class="form-group">
                <label for="people_id">people_id</label><input type="text" class="form-control" id="people_id" name="content_id">
            </div>
            <button type="submit" class="btn btn-primary">검색</button>
        </div>
    </form>
    <br/>
    <h3>로컬</h3>
    <form action="/contents/kofic/api/content" target="_blank" id="search_people_local">
        <input type="hidden" name="search_type" value="local"/> <input type="hidden" name="content_type" value="people"/>
        <div class="form-inline">
            <div class="form-group">
                <label for="people_id">people_id</label>
                <input type="text" class="form-control" id="people_id" name="content_id">
            </div>
            <button type="submit" class="btn btn-primary">검색</button>
        </div>
    </form>
</div>
<div>
    <h2>Actor</h2><br/>

    <h3>영화진흥위원회</h3>

    <form action="/contents/kofic/api/content" target="_blank">
        <input type="hidden" name="search_type" value="server"/> <input type="hidden" name="content_type" value="movie_actor"/>

        <div class="form-inline">
            <div class="form-group">
                <label for="movie_id">movie_id</label><input type="text" class="form-control" id="movie_id" name="content_id">
            </div>
            <button type="submit" class="btn btn-primary">검색</button>
        </div>
    </form>
    <br/>

    <h3>로컬</h3>

    <form action="/contents/kofic/api/content" target="_blank">
        <input type="hidden" name="search_type" value="local"/> <input type="hidden" name="content_type" value="movie_actor"/>

        <div class="form-inline">
            <div class="form-group">
                <label for="movie_id">movie_id</label> <input type="text" class="form-control" id="movie_id" name="content_id">
            </div>
            <button type="submit" class="btn btn-primary">검색</button>
        </div>
    </form>
</div><br/><br/>
<div>
    <h2>Staff</h2><br/>

    <h3>영화진흥위원회</h3>

    <form action="/contents/kofic/api/content" target="_blank">
        <input type="hidden" name="search_type" value="server"/> <input type="hidden" name="content_type" value="movie_staff"/>

        <div class="form-inline">
            <div class="form-group">
                <label for="movie_id">movie_id</label><input type="text" class="form-control" id="movie_id" name="content_id">
            </div>
            <button type="submit" class="btn btn-primary">검색</button>
        </div>
    </form>
    <br/>

    <h3>로컬</h3>

    <form action="/contents/kofic/api/content" target="_blank">
        <input type="hidden" name="search_type" value="local"/> <input type="hidden" name="content_type" value="movie_staff"/>

        <div class="form-inline">
            <div class="form-group">
                <label for="movie_id">movie_id</label> <input type="text" class="form-control" id="movie_id" name="content_id">
            </div>
            <button type="submit" class="btn btn-primary">검색</button>
        </div>
    </form>
</div><br/><br/>