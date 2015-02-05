<?php $this->import('/layout/top');?>
<?php $form = isset($_GET["type"])? $_GET["type"]: "image";?>
<div><h1>Add</h1></div>
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
<?php $this->import('/form/'.$form."_add");?>
<?php $this->import('/layout/bottom');?>