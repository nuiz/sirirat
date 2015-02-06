<?php
/**
 * Created by PhpStorm.
 * User: NUIZ
 * Date: 5/2/2558
 * Time: 17:58
 */
?>
<style type="text/css">

</style>
<div style="padding: 20px 0;">
    <form method="post" enctype="multipart/form-data">
        <input type="hidden" name="type" value="image" />
        <div class="form-group">
            <label>Name</label>
            <input type="text" name="name" class="form-control" placeholder="Name" required="">
        </div>
        <div class="form-group">
            <label for="exampleInputFile">Image</label>
            <input type="file" name="image" required="">
            <p class="help-block">jpg,jpeg,png</p>
        </div>
        <button type="submit" class="btn btn-default">Submit</button>
    </form>
</div>