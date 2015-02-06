<?php $this->import('/layout/top');?>
<?php $old = $params["old"];?>
<?php $form = isset($_GET["type"])? $_GET["type"]: $old["type"];?>
<div><h1>EDIT</h1></div>
<?php if($old["type"]=="image"){?>
<div><img src="<?php echo $old["image_url"];?>" style="max-width: 100%;"></div>
<?php }else if($old["type"]=="video"){?>
<div><video controls="" src="<?php echo $old["video_url"];?>" style="max-width: 100%;"></video></div>
<?php }else if($old["type"]=="model"){?>
<div>
    <a href="<?php echo $old["ios_url"];?>" download="">ios old file</a><br>
    <a href="<?php echo $old["android_url"];?>" download="">android old file</a>
</div>
<?php }?>
<div style="border-radius: 4px; background: gray; padding: 10px 15px;">
    <form method="get" id="select-form">
        <strong>TYPE: </strong>
        <select id="select-type" name="type" style="display: inline-block; width: 200px;">
            <option value="image">image</option>
            <option value="video">video</option>
            <option value="model">model</option>
        </select>
    </form>
    <script>
        $(function(){
            $('#select-type').change(function(){
                $('#select-form').submit();
            });
            $('#select-type').val("<?php echo $form;?>");
        });
    </script>
</div>
<?php $this->import('/form/'.$form."_edit");?>
<?php $this->import('/layout/bottom');?>