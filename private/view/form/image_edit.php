<?php
/**
 * Created by PhpStorm.
 * User: NUIZ
 * Date: 5/2/2558
 * Time: 17:58
 */
$old = $params["old"];
?>
<style type="text/css">

</style>
<div style="padding: 20px 0;">
    <form method="post" enctype="multipart/form-data">
        <input type="hidden" name="type" value="image" />
        <div class="form-group">
            <label>Name</label>
            <input type="text" name="name" class="form-control" placeholder="Name" value="<?php echo $old["name"];?>">
        </div>
        <div class="form-group">
            <label>click url</label>
            <input type="text" name="click_url" class="form-control" placeholder="example: http://google.com" value="<?php echo $old["click_url"];?>">
        </div>
        <div class="form-group">
            <label for="exampleInputFile">Image</label>
            <input type="file" name="image">
            <p class="help-block">jpg,jpeg,png</p>
        </div>
        <button type="submit" class="btn btn-default">Submit</button>
    </form>
</div>