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
        <input type="hidden" name="type" value="video"/>
        <div class="form-group">
            <label>Name</label>
            <input type="text" name="name" class="form-control" value="<?php echo $old["name"];?>">
        </div>
        <div class="form-group">
            <label>Thumbnail</label>
            <input type="file" name="thumbnail">
            <p class="help-block">jpg,jpeg,png</p>
        </div>
        <div class="form-group">
            <label>Video</label>
            <input type="file" name="video">
            <p class="help-block">mp4</p>
        </div>
        <button type="submit" class="btn btn-default">Submit</button>
    </form>
</div>