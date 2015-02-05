<?php
/**
 * Created by PhpStorm.
 * User: NUIZ
 * Date: 5/2/2558
 * Time: 17:58
 */
?>
<form>
    <div class="form-group">
        <label>Name</label>
        <input type="text" name="name" class="form-control">
    </div>
    <div class="form-group">
        <label for="exampleInputFile">thumbnail</label>
        <input type="file" name="thumbnail">
        <p class="help-block">jpeg,jpg,png</p>
    </div>
    <div class="form-group">
        <label for="exampleInputFile">IOS</label>
        <input type="file" name="ios">
        <p class="help-block">.unity</p>
    </div>
    <div class="form-group">
        <label for="exampleInputFile">Android</label>
        <input type="file" name="android">
        <p class="help-block">.unity</p>
    </div>
    <button type="submit" class="btn btn-default">Submit</button>
</form>