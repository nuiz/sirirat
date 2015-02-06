<?php
/**
 * Created by PhpStorm.
 * User: NUIZ
 * Date: 5/2/2558
 * Time: 17:58
 */
?>
<form method="post" enctype="multipart/form-data">
    <input type="hidden" name="type" value="model"/>
    <div class="form-group">
        <label>Name</label>
        <input type="text" name="name" class="form-control" required="">
    </div>
    <div class="form-group">
        <label>thumbnail</label>
        <input type="file" name="thumbnail" required="">
        <p class="help-block">jpeg,jpg,png</p>
    </div>
    <div class="form-group">
        <label>IOS</label>
        <input type="file" name="ios" required="">
        <p class="help-block">.unity</p>
    </div>
    <div class="form-group">
        <label>Android</label>
        <input type="file" name="android" required="">
        <p class="help-block">.unity</p>
    </div>
    <button type="submit" class="btn btn-default">Submit</button>
</form>