<?php $this->import('/layout/top');?>
<?php
if(isset($params['data'])){
    $data = $params['data'];
}
else {
    $data = array();
    $data['name'] = '';
}
?>
    <h3><?php echo $params['title'];?></h3>
    <div class="row">
        <form class="col s12 news-form" enctype="multipart/form-data" method="post" action="<?php echo $params['action'];?>">
            <div class="row">
                <div class="input-field col s12">
                    <input id="name" name="name" type="text" class="validate" required value="<?php echo $data['name'];?>">
                    <label for="name">Title</label>
                </div>
            </div>
            <div class="row">
                <div class="row">
                    <div class="input-field col s12">
                        <input id="marker" name="marker" type="file" class="validate" style="margin-top: 40px;" <?php echo isset($params['data'])? '': 'required';?>>
                        <label for="marker">Maker</label>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="row">
                    <div class="input-field col s12">
                        <input id="ios" name="ios" type="file" class="validate" style="margin-top: 40px;"  <?php echo isset($params['data'])? '': 'required';?>>
                        <label for="ios">IOS file</label>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="row">
                    <div class="input-field col s12">
                        <input id="android" name="android" type="file" class="validate" style="margin-top: 40px;"  <?php echo isset($params['data'])? '': 'required';?>>
                        <label for="android">Android file</label>
                    </div>
                </div>
            </div>
            <div>
                <button class="btn waves-effect waves-light submit-btn" type="submit">Submit
                    <i class="mdi-content-send right"></i>
                </button>
            </div>
        </form>
    </div>
<?php $this->import('/layout/bottom');?>