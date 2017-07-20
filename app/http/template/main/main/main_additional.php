<div style="position: absolute">
    <table class="table" style="text-align: center">
    <caption>频道列表</caption>
    <thead>
    <tr>
        <th>房主</th>
        <th style="width: 90px;text-align: center">频道名称</th>
        <th colspan="2" style="text-align: center">操作</th>
    </tr>
    </thead>
    <tbody id="channelList">
    <tr>
        <th scope="row">superAdmin</th>
        <td><span>大厅</span><input type="hidden" value="ALL"></td>
        <td><button class="btn btn-primary btn-xs active" type="button">进入</button></td>
        <td><button class="btn btn-default btn-xs disabled" type="button">删除</button></td>
    </tr>
    </tbody>
    </table>

    <!-- Small modal -->
    <button type="button" class="btn btn-primary" data-toggle="modal" data-target=".bs-example-modal-sm">创建频道</button>

    <div class="modal fade bs-example-modal-sm" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel">
        <div class="modal-dialog modal-sm" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">请填写频道名称</h4>
                </div>
                <div class="modal-body">
                    <input type="text" class="form-control" id="newChannel" placeholder="Channel">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-dismiss="modal" onclick="newChannel()">确认创建</button>
                </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->
</div>