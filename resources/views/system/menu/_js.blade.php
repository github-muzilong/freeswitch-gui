<script>
    layui.use(['element','form','jquery','layer'],function () {
        var $ = layui.jquery;
        var layer = layui.layer;
        var form = layui.form;
        //选择图标
        window.chioceIcon = function (obj) {
            var icon = $(obj).data('class');
            $("input[name='icon']").val(icon);
            $("#icon_box").html('<i class="layui-icon '+$(obj).data('class')+'"></i> '+$(obj).data('name'));
            layer.closeAll();
        };

        //弹出图标
        window.showIconsBox = function () {
            var index = layer.load();
            $.get("/layuiadmin/json/icons.json",function (res) {
                layer.close(index);
                var html = '<ul class="site-doc-icon">';
                $.each(res,function (index,item) {
                    html += '<li onclick="chioceIcon(this)" data-class="'+item.class+'" data-name="'+item.name+'" >';
                    html += '   <i class="layui-icon '+item.class+'"></i>';
                    html += '   <div class="doc-icon-name">'+item.name+'</div>';
                    html += '   <div class="doc-icon-code"><xmp>'+item.unicode+'</xmp></div>';
                    html += '   <div class="doc-icon-fontclass">'+item.class+'</div>';
                    html += '</li>'
                });
                html += '</ul>';
                layer.open({
                    type:1,
                    title:'选择图标',
                    area : ['80%','80%'],
                    content:html
                })
            },'json')
        }
    })
</script>
