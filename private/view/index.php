<?php $this->import('/layout/top');?>
    <div>
        <form class="form-inline">
            <div class="form-group">
                <label class="sr-only">Search</label>
                <input type="text" class="form-control" name="keyword" placeholder="Search keyword" value="<?php echo @$_GET["keyword"];?>">
            </div>
            <button type="submit" class="btn btn-default">Search</button>
        </form>
    </div>
    <div style="text-align: right;">
        <a class="waves-effect waves-light btn" href="<?php echo \Main\Helper\URL::absolute('/marker/add');?>">Add</a>
    </div>
    <table class="table table-bordered">
        <thead>
        <tr>
            <th data-field="id"></th>
            <th data-field="name">Name</th>
            <th data-field="type"></th>
            <th data-field="thumbnail"></th>
            <th data-field="edit">edit</th>
            <th data-field="delete">delete</th>
        </tr>
        </thead>

        <tbody>
        <?php foreach($params['items'] as $key=> $item){?>
        <tr>
            <td data-field="id"><?php echo $key+1;?></td>
            <td data-field="name"><?php echo $item['name'];?></td>
            <td data-field="name"><?php echo $item['type'];?></td>
            <td data-field="thumb"><div style="width: 40px; height: 40px; background-image: url(<?php echo $item['thumbnail_url'];?>); background-size: cover;" /></td>
            <td><a href="<?php echo \Main\Helper\URL::absolute('/marker/edit/'.$item['id']);?>"><i class="glyphicon glyphicon-edit"></i></a></td>
            <td><a class="delete-btn" href="<?php echo \Main\Helper\URL::absolute('/marker/delete/'.$item['id']);?>"><i class="glyphicon glyphicon-floppy-remove"></i></a></td>
        </tr>
        <?php }?>
        </tbody>
    </table>
    <script>
        $(function(){
            $('.delete-btn').click(function(e){
                if(!window.confirm('Are you shure?')){
                    e.preventDefault();
                }
            });
        });
    </script>
<?php $this->import('/layout/bottom');?>